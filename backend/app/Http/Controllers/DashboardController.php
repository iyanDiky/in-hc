<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\SuratMasukDisposisi;
use App\Models\Lamaran;
use App\Models\BagianSeksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ringkasan Statistik Utama Dashboard
     */
    public function summary(Request $request)
    {
        $now = Carbon::now();
        $startThisMonth = $now->copy()->startOfMonth()->toDateString();
        $endThisMonth = $now->copy()->endOfMonth()->toDateString();

        $startLastMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $endLastMonth = $now->copy()->subMonth()->endOfMonth()->toDateString();

        // Total Keseluruhan (Aktif / Non-deleted)
        $totalSuratMasuk = SuratMasuk::count();
        $totalSuratKeluar = SuratKeluar::count();
        $totalDisposisi = SuratMasukDisposisi::whereHas('suratMasuk')->count();
        $totalPelamar = Lamaran::count();
        $totalSurat = $totalSuratMasuk + $totalSuratKeluar;

        // Total Bulan Ini
        $suratMasukBulanIni = SuratMasuk::whereBetween('tanggal_surat', [$startThisMonth, $endThisMonth])->count();
        $suratKeluarBulanIni = SuratKeluar::whereBetween('tanggal_surat', [$startThisMonth, $endThisMonth])->count();
        $disposisiBulanIni = SuratMasukDisposisi::whereHas('suratMasuk')->whereBetween('disposisi_waktu', [$startThisMonth . ' 00:00:00', $endThisMonth . ' 23:59:59'])->count();
        $pelamarBulanIni = Lamaran::whereBetween('tanggal_diterima', [$startThisMonth, $endThisMonth])->count();
        $totalBulanIni = $suratMasukBulanIni + $suratKeluarBulanIni;

        // Total Bulan Lalu
        $suratMasukBulanLalu = SuratMasuk::whereBetween('tanggal_surat', [$startLastMonth, $endLastMonth])->count();
        $suratKeluarBulanLalu = SuratKeluar::whereBetween('tanggal_surat', [$startLastMonth, $endLastMonth])->count();
        $pelamarBulanLalu = Lamaran::whereBetween('tanggal_diterima', [$startLastMonth, $endLastMonth])->count();
        $totalBulanLalu = $suratMasukBulanLalu + $suratKeluarBulanLalu;

        // Pertumbuhan vs Bulan Lalu
        $growthPercentage = 0;
        if ($totalBulanLalu > 0) {
            $growthPercentage = round((($totalBulanIni - $totalBulanLalu) / $totalBulanLalu) * 100, 1);
        } elseif ($totalBulanIni > 0) {
            $growthPercentage = 100;
        }

        // Persentase Surat Masuk yang sudah didisposisi
        $suratMasukTerdisposisi = SuratMasuk::has('disposisi')->count();
        $persentaseDisposisi = $totalSuratMasuk > 0 ? round(($suratMasukTerdisposisi / $totalSuratMasuk) * 100, 1) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_surat' => $totalSurat,
                'total_surat_masuk' => $totalSuratMasuk,
                'total_surat_keluar' => $totalSuratKeluar,
                'total_disposisi' => $totalDisposisi,
                'total_pelamar' => $totalPelamar,
                'surat_masuk_bulan_ini' => $suratMasukBulanIni,
                'surat_keluar_bulan_ini' => $suratKeluarBulanIni,
                'disposisi_bulan_ini' => $disposisiBulanIni,
                'pelamar_bulan_ini' => $pelamarBulanIni,
                'total_bulan_ini' => $totalBulanIni,
                'total_bulan_lalu' => $totalBulanLalu,
                'growth_percentage' => $growthPercentage,
                'surat_masuk_terdisposisi' => $suratMasukTerdisposisi,
                'persentase_disposisi' => $persentaseDisposisi,
            ]
        ]);
    }

    /**
     * Data Grafik Tren Bulanan (12 Bulan)
     */
    public function chartMonthly(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        // Inisialisasi array 12 bulan bernilai 0
        $dataSuratMasuk = array_fill(1, 12, 0);
        $dataSuratKeluar = array_fill(1, 12, 0);
        $dataDisposisi = array_fill(1, 12, 0);
        $dataPelamar = array_fill(1, 12, 0);

        // Cek database driver (PostgreSQL vs MySQL/SQLite)
        $driver = DB::connection()->getDriverName();
        $monthExpr = $driver === 'pgsql' ? 'EXTRACT(MONTH FROM %s)::int' : 'MONTH(%s)';

        // Agregasi Surat Masuk
        $suratMasukPerBulan = SuratMasuk::selectRaw(sprintf($monthExpr, 'tanggal_surat') . ' as month, count(*) as total')
            ->whereYear('tanggal_surat', $year)
            ->groupBy(DB::raw(sprintf($monthExpr, 'tanggal_surat')))
            ->pluck('total', 'month');

        foreach ($suratMasukPerBulan as $month => $total) {
            $dataSuratMasuk[(int)$month] = (int) $total;
        }

        // Agregasi Surat Keluar
        $suratKeluarPerBulan = SuratKeluar::selectRaw(sprintf($monthExpr, 'tanggal_surat') . ' as month, count(*) as total')
            ->whereYear('tanggal_surat', $year)
            ->groupBy(DB::raw(sprintf($monthExpr, 'tanggal_surat')))
            ->pluck('total', 'month');

        foreach ($suratKeluarPerBulan as $month => $total) {
            $dataSuratKeluar[(int)$month] = (int) $total;
        }

        // Agregasi Disposisi
        $disposisiPerBulan = SuratMasukDisposisi::whereHas('suratMasuk')
            ->selectRaw(sprintf($monthExpr, 'disposisi_waktu') . ' as month, count(*) as total')
            ->whereYear('disposisi_waktu', $year)
            ->groupBy(DB::raw(sprintf($monthExpr, 'disposisi_waktu')))
            ->pluck('total', 'month');

        foreach ($disposisiPerBulan as $month => $total) {
            $dataDisposisi[(int)$month] = (int) $total;
        }

        // Agregasi Data Pelamar
        $pelamarPerBulan = Lamaran::selectRaw(sprintf($monthExpr, 'tanggal_diterima') . ' as month, count(*) as total')
            ->whereYear('tanggal_diterima', $year)
            ->groupBy(DB::raw(sprintf($monthExpr, 'tanggal_diterima')))
            ->pluck('total', 'month');

        foreach ($pelamarPerBulan as $month => $total) {
            $dataPelamar[(int)$month] = (int) $total;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'year' => (int) $year,
                'categories' => array_values($months),
                'series' => [
                    [
                        'name' => 'Surat Masuk',
                        'data' => array_values($dataSuratMasuk)
                    ],
                    [
                        'name' => 'Surat Keluar',
                        'data' => array_values($dataSuratKeluar)
                    ],
                    [
                        'name' => 'Disposisi Selesai',
                        'data' => array_values($dataDisposisi)
                    ],
                    [
                        'name' => 'Data Pelamar',
                        'data' => array_values($dataPelamar)
                    ]
                ]
            ]
        ]);
    }

    /**
     * Data Grafik Volume 7 Hari Terakhir
     */
    public function chartWeekly(Request $request)
    {
        $days = [];
        $categories = [];
        $dataSuratMasuk = [];
        $dataSuratKeluar = [];
        $dataPelamar = [];
        $dataTotal = [];

        // 7 hari terakhir mundur dari hari ini
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $dayName = $date->locale('id')->isoFormat('ddd, D MMM');
            
            $categories[] = $dayName;

            $countMasuk = SuratMasuk::whereDate('tanggal_surat', $dateStr)->count();
            $countKeluar = SuratKeluar::whereDate('tanggal_surat', $dateStr)->count();
            $countPelamar = Lamaran::whereDate('tanggal_diterima', $dateStr)->count();

            $dataSuratMasuk[] = $countMasuk;
            $dataSuratKeluar[] = $countKeluar;
            $dataPelamar[] = $countPelamar;
            $dataTotal[] = $countMasuk + $countKeluar + $countPelamar;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'total_weekly' => array_sum($dataTotal),
                'series' => [
                    [
                        'name' => 'Total Aktivitas & Pelamar',
                        'data' => $dataTotal
                    ]
                ]
            ]
        ]);
    }

    /**
     * Data Distribusi Bagian Seksi & Kinerja Disposisi (RadialBar / Pie)
     */
    public function chartDistribution(Request $request)
    {
        // Distribusi Surat Keluar per Bagian Seksi
        $distribution = SuratKeluar::selectRaw('bagian_seksi_request, count(*) as total')
            ->whereNotNull('bagian_seksi_request')
            ->groupBy('bagian_seksi_request')
            ->orderByDesc('total')
            ->limit(4)
            ->with('bagianSeksiRequest')
            ->get();

        $labels = [];
        $series = [];
        $rawCounts = [];

        $totalKeluar = SuratKeluar::whereNotNull('bagian_seksi_request')->count();

        foreach ($distribution as $item) {
            $nama = $item->bagianSeksiRequest ? $item->bagianSeksiRequest->bagian_seksi : 'Bagian / Seksi';
            $count = (int) $item->total;
            $percentage = $totalKeluar > 0 ? round(($count / $totalKeluar) * 100) : 0;

            $labels[] = $nama;
            $series[] = $percentage;
            $rawCounts[] = $count;
        }

        // Jika data kurang dari 4, fallback default label yang informatif
        if (empty($labels)) {
            $labels = ['Bagian Operasional', 'Bagian SDM', 'Bagian Keuangan', 'Bagian Umum'];
            $series = [25, 25, 25, 25];
            $rawCounts = [0, 0, 0, 0];
        }

        // Status Disposisi
        $totalMasuk = SuratMasuk::count();
        $disposisiDone = SuratMasuk::has('disposisi')->count();
        $disposisiPending = max(0, $totalMasuk - $disposisiDone);
        $persentaseSelesai = $totalMasuk > 0 ? round(($disposisiDone / $totalMasuk) * 100) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'series' => $series,
                'raw_counts' => $rawCounts,
                'total_disposisi_selesai' => $disposisiDone,
                'total_disposisi_pending' => $disposisiPending,
                'persentase_selesai' => $persentaseSelesai
            ]
        ]);
    }

    /**
     * Aktivitas Terkini (Surat Masuk, Keluar, Disposisi, dan Pelamar)
     */
    public function recentActivities(Request $request)
    {
        $activities = collect();

        // 1. Surat Masuk Terbaru
        $latestSuratMasuk = SuratMasuk::with('userInput')
            ->latest('created_at')
            ->limit(4)
            ->get();

        foreach ($latestSuratMasuk as $sm) {
            $activities->push([
                'id' => $sm->id,
                'type' => 'surat_masuk',
                'title' => 'Surat Masuk Baru',
                'subtitle' => $sm->perihal . ' (' . $sm->nomor_surat . ')',
                'user' => $sm->userInput ? $sm->userInput->nama : 'Pengirim: ' . $sm->pengirim,
                'badge_color' => 'primary',
                'icon' => 'ti ti-mail-plus',
                'created_at' => $sm->created_at ? $sm->created_at->toISOString() : null,
                'date_formatted' => $sm->created_at ? $sm->created_at->format('H:i') : '-',
            ]);
        }

        // 2. Surat Keluar Terbaru
        $latestSuratKeluar = SuratKeluar::with(['userInput', 'userRequest', 'bagianSeksiRequest'])
            ->latest('created_at')
            ->limit(4)
            ->get();

        foreach ($latestSuratKeluar as $sk) {
            $activities->push([
                'id' => $sk->id,
                'type' => 'surat_keluar',
                'title' => 'Surat Keluar Dibuat',
                'subtitle' => $sk->perihal . ' (No: ' . $sk->nomor_surat . ')',
                'user' => $sk->userRequest ? $sk->userRequest->nama : ($sk->userInput ? $sk->userInput->nama : 'Tujuan: ' . $sk->tujuan),
                'badge_color' => 'success',
                'icon' => 'ti ti-file-export',
                'created_at' => $sk->created_at ? $sk->created_at->toISOString() : null,
                'date_formatted' => $sk->created_at ? $sk->created_at->format('H:i') : '-',
            ]);
        }

        // 3. Disposisi Terbaru
        $latestDisposisi = SuratMasukDisposisi::whereHas('suratMasuk')
            ->with(['disposisiOleh', 'suratMasuk'])
            ->latest('created_at')
            ->limit(4)
            ->get();

        foreach ($latestDisposisi as $disp) {
            $activities->push([
                'id' => $disp->id,
                'type' => 'disposisi',
                'title' => 'Disposisi Diteruskan',
                'subtitle' => $disp->suratMasuk ? $disp->suratMasuk->perihal : ($disp->catatan ?: 'Disposisi Surat Masuk'),
                'user' => $disp->disposisiOleh ? $disp->disposisiOleh->nama : 'Pimpinan',
                'badge_color' => 'warning',
                'icon' => 'ti ti-arrow-forward-up',
                'created_at' => $disp->created_at ? $disp->created_at->toISOString() : null,
                'date_formatted' => $disp->created_at ? $disp->created_at->format('H:i') : '-',
            ]);
        }

        // 4. Data Pelamar Terbaru
        $latestPelamar = Lamaran::with('userInput')
            ->latest('created_at')
            ->limit(4)
            ->get();

        foreach ($latestPelamar as $pel) {
            $activities->push([
                'id' => $pel->id,
                'type' => 'pelamar',
                'title' => 'Berkas Pelamar Baru',
                'subtitle' => $pel->nama . ' (' . $pel->pendidikan . ' - ' . ($pel->institusi ?: 'Pelamar') . ')',
                'user' => 'No. Lamaran: #' . $pel->nomor_lamaran,
                'badge_color' => 'info',
                'icon' => 'ti ti-user-check',
                'created_at' => $pel->created_at ? $pel->created_at->toISOString() : null,
                'date_formatted' => $pel->created_at ? $pel->created_at->format('H:i') : '-',
            ]);
        }

        // Urutkan gabungan aktivitas berdasarkan created_at descending dan ambil 6 teratas
        $sorted = $activities->sortByDesc('created_at')->values()->take(6);

        return response()->json([
            'success' => true,
            'data' => $sorted
        ]);
    }

    /**
     * Data Terkini (Surat Masuk, Surat Keluar, & Pelamar) untuk Tabel Tabbed
     */
    public function recentSurat(Request $request)
    {
        // 5 Surat Masuk Terkini
        $suratMasuk = SuratMasuk::with(['disposisi', 'userInput'])
            ->latest('tanggal_surat')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'surat_masuk',
                    'nomor_surat' => $item->nomor_surat,
                    'perihal' => $item->perihal,
                    'pihak' => $item->pengirim,
                    'pihak_label' => 'Pengirim',
                    'tanggal_surat' => $item->tanggal_surat,
                    'status' => $item->disposisi->count() > 0 ? 'Sudah Disposisi' : 'Belum Disposisi',
                    'status_color' => $item->disposisi->count() > 0 ? 'success' : 'warning',
                ];
            });

        // 5 Surat Keluar Terkini
        $suratKeluar = SuratKeluar::with(['userRequest', 'bagianSeksiRequest'])
            ->latest('tanggal_surat')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'surat_keluar',
                    'nomor_surat' => $item->nomor_surat,
                    'perihal' => $item->perihal,
                    'pihak' => $item->tujuan,
                    'pihak_label' => 'Tujuan',
                    'tanggal_surat' => $item->tanggal_surat,
                    'status' => 'Terkirim',
                    'status_color' => 'info',
                ];
            });

        // 5 Data Pelamar Terkini
        $pelamar = Lamaran::with('userInput')
            ->latest('tanggal_diterima')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'pelamar',
                    'nomor_surat' => '#' . $item->nomor_lamaran,
                    'perihal' => $item->nama,
                    'pihak' => $item->institusi ? ($item->institusi . ($item->jurusan ? ' - ' . $item->jurusan : '')) : 'Pendidikan: ' . $item->pendidikan,
                    'pihak_label' => 'Institusi / Jurusan',
                    'tanggal_surat' => $item->tanggal_diterima,
                    'status' => $item->pendidikan,
                    'status_color' => 'primary',
                ];
            });

        // Gabungan semua untuk tab Semua
        $all = $suratMasuk->concat($suratKeluar)->concat($pelamar)->sortByDesc('tanggal_surat')->values()->take(6);

        return response()->json([
            'success' => true,
            'data' => [
                'all' => $all,
                'surat_masuk' => $suratMasuk,
                'surat_keluar' => $suratKeluar,
                'pelamar' => $pelamar
            ]
        ]);
    }
}

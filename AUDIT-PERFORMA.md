# Audit performa akses LAN / Wi-Fi

Tanggal: 9 November 2026. Pemeriksaan kode dan pengukuran pada Docker lokal di Mac ini, bukan pada komputer server ruangan. Tidak ada perubahan kode aplikasi atau database selama audit; login pengujian membuat token autentikasi sementara.

**Pembaruan setelah audit:** optimasi telah diimplementasikan. Deployment standar sekarang memakai frontend statis Nginx, gzip, cache aset, proxy satu origin, dan aset font/ikon lokal. Query dashboard, indeks, batas pagination, serta loader library sudah diperbaiki. Bukti pengujian dan cara menjalankan versi baru ada di [README-LAN.md](README-LAN.md). Temuan di bawah merekam kondisi awal sebelum perbaikan.

## Kesimpulan

Prioritas pertama adalah deployment frontend. Compose menyajikan Vite development (`npm run dev -- --host`), bukan hasil build statis. Aset yang diuji tidak memakai gzip dan mengirim `Cache-Control: no-cache` (browser dapat melakukan revalidasi, bukan berarti selalu mengunduh ulang seluruh isi). Pada data dan mesin ini API cepat; belum ada bukti bahwa database menjadi penyebab utama kelambatan Wi-Fi. Kondisi radio Wi-Fi, throughput, perangkat klien, serta beban komputer server ruangan belum diukur.

## Bukti pengukuran

Lima permintaan berurutan per endpoint, sudah login, tanpa filter, pagination 10 baris. Angka median bukan pengujian banyak pengguna.

| Endpoint | Median lokal |
| --- | ---: |
| dashboard/summary | 18,9 ms |
| dashboard/chart-monthly | 13,9 ms |
| dashboard/chart-weekly | 16,3 ms |
| dashboard/chart-distribution | 14,5 ms |
| dashboard/recent-activities | 16,8 ms |
| dashboard/recent-surat | 13,7 ms |
| surat-masuk/datatables | 14,2 ms |
| surat-keluar/datatables | 14,2 ms |

Enam endpoint dashboard paralel selesai sekitar 54,8 ms. Data aktual: 243 surat masuk, 249 surat keluar, 197 disposisi, 52 lamaran (termasuk baris terhapus bila ada). Query daftar surat masuk memakai sequential scan dan sort, tetapi selesai 0,250 ms pada skala sekarang. Statistik perkiraan `pg_stat_user_tables` belum mencerminkan jumlah aktual, sehingga jumlah di atas diambil dengan COUNT langsung.

CSS utama 787.090 byte; ApexCharts 491.895 byte. Respons keduanya tanpa Content-Encoding meskipun klien meminta gzip. Simulasi gzip lokal menghasilkan masing-masing 109.778 dan 126.104 byte; ini estimasi ukuran, bukan hasil deployment yang sudah diterapkan. Direktori template public sekitar 135 MB, tetapi tidak seluruh direktori diunduh pada satu kunjungan.

## Temuan dan prioritas

1. **Tinggi — server frontend development.** `docker-compose.yml`, service frontend. Permintaan modul development dan transformasi runtime menambah pekerjaan/request. Sajikan hasil `npm ci && npm run build` melalui Nginx, aktifkan gzip, gunakan cache panjang untuk aset ber-hash serta revalidasi HTML. Library `/dist` tanpa hash perlu strategi versi/cache tersendiri agar pembaruan tidak tertahan cache lama.
2. **Tinggi — aset awal dan dependensi internet.** `frontend/index.html` memuat ApexCharts global termasuk di login, icon dari jsDelivr; `frontend/public/dist/css/style.min.css` mengimpor Google Fonts; Dashboard mengambil avatar dari ui-avatars.com. Internet lambat/terblokir dapat memperlambat tampilan meskipun server LAN cepat. Layani font/icon/avatar lokal dan muat grafik ketika dibutuhkan. Ukur waterfall browser sebelum menghapus CSS template agar tampilan tidak rusak.
3. **Sedang — pemuatan library berurutan dan duplikasi.** `frontend/src/layout/MainLayout.vue:383` menunggu script satu per satu; baris 391–392 memuat Select2 full dan non-full. Cek script yang sudah ada mencegah duplikasi jQuery dari index, jadi jangan menghitung jQuery sebagai unduhan ganda tanpa bukti. Pertahankan satu Select2, atur dependensi secara eksplisit, dan muat hanya plugin halaman terkait. Tabel melakukan polling setiap 100 ms sampai DataTables siap; jika library gagal, polling dapat terus berjalan.
4. **Sedang — lintas origin API.** `frontend/src/utils/api.js:4` mengarahkan API ke hostname yang sama pada port 8000 sementara frontend port 5173. Header JSON/Bearer membutuhkan preflight lintas origin; `backend/config/cors.php` memakai max_age 0. Sajikan UI, `/api`, dan `/storage` dalam satu origin melalui reverse proxy; ubah helper API/storage secara bersamaan. Jumlah dan dampak preflight belum diukur lewat browser klien.
5. **Sedang — query dashboard berulang.** `DashboardController::chartWeekly` melakukan 3 COUNT x 7 hari; `chartDistribution` melakukan COUNT per bagian. Gunakan agregasi per tanggal/per bagian lalu isi tanggal kosong di PHP. Summary juga memiliki banyak COUNT. Enam request dashboard saat ini sudah dimulai paralel, jadi klaim frontend dashboard serial tidak tepat. Cache singkat hanya bila toleransi keterlambatan statistik disepakati.
6. **Sedang — indeks dan pencarian saat data tumbuh.** Belum ada indeks tanggal/urutan utama surat masuk dan keluar, maupun foreign key `surat_masuk_disposisi.surat_masuk_id`. Filter `whereYear` mengekstrak tahun, sehingga perlu ubah ke rentang tanggal agar indeks tanggal biasa berguna. Pencarian LOWER + LIKE `%kata%` memerlukan evaluasi indeks trigram jika skala membesar; indeks B-tree biasa bukan solusi pencarian substring. Buat indeks berdasarkan EXPLAIN pada data representatif; saat ini query lokal belum lambat.
7. **Sedang — pagination belum dibatasi server.** Datatables surat masuk/keluar melewatkan LIMIT jika length <= 0, dan tidak membatasi panjang maksimum. Validasi start/length dan tetapkan batas wajar agar satu request tidak mengambil semua data. Surat keluar juga mengambil daftar user sampai 1000 untuk dropdown; pertimbangkan pencarian bertahap jika jumlah user besar. Pencarian tabel sudah memiliki debounce.
8. **Sedang — konfigurasi backend development.** `.env` memakai APP_ENV=local, APP_DEBUG=true, LOG_LEVEL=debug; bootstrap/cache tidak memiliki config/routes cache. Siapkan environment deployment terpisah, APP_DEBUG=false, cache konfigurasi/rute setelah env final. OPcache sudah aktif, jadi tidak perlu menganggapnya belum dipasang. Bind mount source tetap digunakan; pengaruh overhead filesystem belum diisolasi.
9. **Kapasitas perlu diuji — PHP-FPM 5 worker.** pm.max_children=5 dan start_servers=2. Permintaan banyak pengguna dapat antre; belum terbukti terjadi. Ukur latency p95 dan RAM tiap worker sebelum menaikkan batas. Penggunaan CPU container saat idle rendah; pengukuran idle bukan bukti kapasitas saat ramai.
10. **Bug terpisah — redirect login.** `frontend/src/router/index.js` merujuk route bernama Home ketika pengguna yang sudah login membuka login, padahal route yang tersedia bernama Dashboard. Ini dapat memunculkan error navigasi, bukan penyebab transfer Wi-Fi lambat.

## Urutan tindak lanjut

1. Siapkan deployment LAN dengan build statis Nginx, gzip, cache aset yang benar, dan satu origin UI/API/storage. Pertahankan volume database.
2. Hilangkan ketergantungan internet pada tampilan utama, hapus Select2 ganda, dan atur kesiapan plugin tanpa polling tanpa batas.
3. Agregasikan query dashboard, batasi pagination, dan tambahkan indeks terukur. Jalankan uji regresi hasil filter, pagination, statistik, dan file evidence.
4. Bandingkan perangkat klien yang sama melalui localhost server, LAN kabel, dan Wi-Fi. Rekam cold/warm load, jumlah request, ukuran transfer, TTFB, preflight, serta p95 API dengan jumlah pengguna representatif. Bila localhost cepat tetapi transfer LAN lambat, periksa sinyal/interferensi/access-point isolation, throughput, dan sumber daya host server.

Audit ini tidak mengubah deployment atau menjanjikan peningkatan angka sebelum implementasi dan pengukuran ulang pada jaringan sasaran.

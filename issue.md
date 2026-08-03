# Issue: Implementasi Halaman Dashboard Utama (Persuratan & Analytics)

## 1. Deskripsi Fitur
Halaman **Dashboard** adalah halaman landing utama yang pertama kali terbuka setelah pengguna berhasil login ke dalam sistem IN-HC. Halaman ini berfungsi sebagai pusat kontrol dan monitoring eksekutif yang menyajikan metrik statistik penting, visualisasi grafik interaktif untuk data **Surat Masuk** dan **Surat Keluar**, riwayat aktivitas persuratan terkini, serta distribusi surat berdasarkan unit/bagian seksi.

Desain antarmuka dan visualisasi grafik mengadopsi layout modern dari template **Modernize** (`template_frontend/package/html/main/index5.html`), yang mencakup komponen *Line Chart Analytics*, *Gradient Area Chart*, *RadialBar Distribution*, *Activity Stream*, dan *Tabbed Recent Letters Table*.

---

## 2. Analisis Komponen & Tata Letak Antarmuka (Berdasarkan `index5.html`)

### 2.1 Kartu Sambutan Pengguna (User Greeting Header)
- **Komponen**: Banner ringkas di bagian atas dashboard.
- **Elemen**:
  - Foto profil avatar pengguna dengan border aksen tema.
  - Sapaan ramah dinamis: *"Halo, **[Nama Pengguna]**!"*.
  - Subteks keterangan waktu dinamis: *"Selamat datang di Sistem Informasi Persuratan IN-HC — [Hari, Tanggal Bulan Tahun]"*.

### 2.2 Kartu Utama: Analisis Tren Persuratan Bulanan (*Financial Income Chart*)
Mengadopsi layout kartu utama dari `index5.html` baris 1953–2031:
- **Sisi Kiri (Ringkasan Metrik)**:
  - Judul: **Statistik & Tren Persuratan**.
  - Total volume seluruh surat (Surat Masuk + Surat Keluar) dalam tahun berjalan.
  - Indikator persentase pertumbuhan volume surat dibandingkan bulan sebelumnya (badge hijau/merah).
- **Sisi Kanan (ApexCharts Line / Smooth Area Chart)**:
  - Filter tahun (Dropdown Select Tahun, default: tahun aktif).
  - Grafik multi-series ApexCharts:
    - **Series 1 (Surat Masuk)**: Garis warna Oranye / Amber (`#fa896b`).
    - **Series 2 (Surat Keluar)**: Garis warna Biru Modern (`#615dff`).
    - **Series 3 (Disposisi Selesai)**: Garis warna Cyan / Teal (`#3dd9eb`).
  - Sumbu X: 12 Bulan (Januari – Desember) atau rentang periode per dasawarsa.
  - Tooltip interaktif yang mendukung tema *Light Mode* dan *Dark Mode*.
- **Footer Kartu (3 Kolom Ringkasan Bordered)**:
  - **Kolom 1**: Total **Surat Masuk** (dengan bullet point warna merah/oranye).
  - **Kolom 2**: Total **Surat Keluar** (dengan bullet point warna biru).
  - **Kolom 3**: Total **Disposisi Terlaksana** (dengan bullet point warna info/teal).

### 2.3 Kartu Volume Aktivitas Mingguan (*Sales Hourly / Gradient Area Chart*)
Mengadopsi layout kartu dari `index5.html` baris 2102–2123:
- **Karakteristik**: Card dengan aksen warna `bg-light-primary` elegan dan rounded corner.
- **Visualisasi**:
  - Judul: **Aktivitas Surat 7 Hari Terakhir**.
  - Subteks: *"Volume surat masuk & keluar harian (Senin – Minggu)"*.
  - Grafik ApexCharts Area dengan efek *gradient fill* dan *smooth curve*.
  - Tombol aksi cepat: Navigasi cepat ke rekapitulasi data.

### 2.4 Kartu Alur Aktivitas Terbaru (*Upcoming Activity Stream*)
Mengadopsi layout dari `index5.html` baris 2033–2100:
- **Komponen**: Timeline vertikal transaksi persuratan terbaru (5 aktivitas terakhir).
- **Format Item**:
  - Ikon penanda warna-warni bulat:
    - Surat Masuk Baru: `<i class="ti ti-mail-plus fs-6 text-primary"></i>` dengan `bg-light-primary`.
    - Surat Keluar Dibuat: `<i class="ti ti-file-export fs-6 text-success"></i>` dengan `bg-light-success`.
    - Disposisi Diteruskan: `<i class="ti ti-arrow-forward-up fs-6 text-warning"></i>` dengan `bg-light-warning`.
    - Penghapusan / Pembatalan: `<i class="ti ti-trash fs-6 text-danger"></i>` dengan `bg-light-danger`.
  - Judul ringkas: Perihal / Nomor surat.
  - Subteks: Nama pembuat / pengirim surat.
  - Waktu transaksi (format jam:menit atau relative time: *"5 menit yang lalu"*).

### 2.5 Kartu Distribusi & Kinerja Bagian/Seksi (*Team Performance / RadialBar*)
Mengadopsi layout dari `index5.html` baris 2509–2587:
- **Visualisasi**:
  - Judul: **Distribusi Surat per Bagian / Seksi**.
  - ApexCharts RadialBar atau Donut Chart yang memperlihatkan proporsi permintaan nomor surat keluar dan disposisi surat masuk per Bagian/Seksi.
  - Ringkasan statistik persentase surat yang telah terselesaikan/terdisposisi.

### 2.6 Kartu Tabel Tabular Surat Terkini (*Order Status / Tabbed Table*)
Mengadopsi layout dari `index5.html` baris 2124–2253:
- **Tab Navigasi**:
  - Tab 1: **Semua Surat**
  - Tab 2: **Surat Masuk Terbaru**
  - Tab 3: **Surat Keluar Terbaru**
- **Kolom Tabel**:
  - **Surat / Perihal**: Avatar inisial/ikon, nomor surat, dan perihal.
  - **Pengirim / Tujuan**: Asal pengirim surat masuk atau instansi tujuan surat keluar.
  - **Tanggal**: Tanggal surat.
  - **Status / Kategori**: Badge pill (`bg-light-primary`, `bg-light-success`, `bg-light-warning`).
  - **Aksi**: Tombol direct view ke detail surat.

---

## 3. Rencana Arsitektur & Spesifikasi Backend (Laravel)

### 3.1 Controller Baru: `backend/app/Http/Controllers/DashboardController.php`
Controller ini bertugas menyediakan data agregasi dan analitik untuk dashboard.

#### Method yang Dibutuhkan:
1. **`summary(Request $request)`**:
   - Menghitung total akumulasi data:
     - `total_surat_masuk`: Total data surat masuk aktif (`delete_at IS NULL`).
     - `total_surat_keluar`: Total data surat keluar aktif (`delete_at IS NULL`).
     - `total_disposisi`: Total catatan disposisi surat masuk.
     - `surat_masuk_bulan_ini`: Total surat masuk pada bulan berjalan.
     - `surat_keluar_bulan_ini`: Total surat keluar pada bulan berjalan.
     - `persentase_kenaikan`: Perbandingan total surat bulan ini vs bulan lalu.

2. **`chartMonthly(Request $request)`**:
   - Menerima parameter opsional: `year` (Default: tahun saat ini `Y`).
   - Melakukan agregasi count per bulan (Bulan 1 s/d 12) untuk:
     - Volume Surat Masuk per bulan.
     - Volume Surat Keluar per bulan.
     - Volume Disposisi per bulan.
   - Response berupa array 12 data point per series untuk ApexCharts.

3. **`chartWeekly(Request $request)`**:
   - Menghitung volume transaksi persuratan per hari selama 7 hari terakhir (atau Senin s/d Minggu minggu berjalan).
   - Menghasilkan categories hari (`['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']`) dan series angka.

4. **`chartDistribution(Request $request)`**:
   - Agregasi volume surat keluar berdasarkan `bagian_seksi_request`.
   - Mengambil 4–5 bagian/seksi teratas dan grup 'Lainnya'.
   - Menghasilkan series persentase dan label untuk RadialBar/Donut chart.

5. **`recentActivities(Request $request)`**:
   - Mengambil 5–10 log aktivitas / data persuratan terbaru (kombinasi surat masuk, disposisi, dan surat keluar terbaru diurutkan berdasarkan `created_at DESC`).

6. **`recentSurat(Request $request)`**:
   - Mengambil 5 surat masuk terbaru dan 5 surat keluar terbaru beserta relasi pembuat dan bagian seksi.

### 3.2 Pendaftaran Route API: `backend/routes/api.php`
```php
// Dashboard Analytics
Route::prefix('dashboard')->group(function () {
    Route::post('/summary', [\App\Http\Controllers\DashboardController::class, 'summary']);
    Route::post('/chart-monthly', [\App\Http\Controllers\DashboardController::class, 'chartMonthly']);
    Route::post('/chart-weekly', [\App\Http\Controllers\DashboardController::class, 'chartWeekly']);
    Route::post('/chart-distribution', [\App\Http\Controllers\DashboardController::class, 'chartDistribution']);
    Route::post('/recent-activities', [\App\Http\Controllers\DashboardController::class, 'recentActivities']);
    Route::post('/recent-surat', [\App\Http\Controllers\DashboardController::class, 'recentSurat']);
});
```

---

## 4. Rencana Implementasi Frontend (Vue 3 + Vite)

### 4.1 Pembuatan Komponen View: `frontend/src/views/Dashboard.vue`
- Menggunakan `ref`, `onMounted`, `watch`, dan `computed` dari Vue 3 Composition API.
- Mengintegrasikan ApexCharts via `window.ApexCharts` (sudah tersedia di template library) atau `apexcharts` package.
- Menghubungkan setiap widget ke endpoint API backend dengan animasi loading state (skeleton / spinner halus).
- Mendukung dynamic theme listener (beradaptasi mulus saat tombol Dark/Light mode di-toggle).

### 4.2 Pembaruan Router: `frontend/src/router/index.js`
- Mengubah rute root `/` agar memuat `Dashboard.vue` (bukan lagi placeholder `Jabatan.vue`).
- Menambahkan rute `/dashboard` sebagai alias resmi:
```javascript
{
  path: '',
  name: 'Dashboard',
  component: () => import('../views/Dashboard.vue')
},
{
  path: 'dashboard',
  name: 'DashboardAlias',
  component: () => import('../views/Dashboard.vue')
}
```

### 4.3 Penambahan Menu Navigasi: `frontend/src/layout/MainLayout.vue`
Menambahkan grup menu **Home / Dashboard** di posisi paling atas sidebar navigasi:
```html
<li class="nav-small-cap">
  <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
  <span class="hide-menu">Home</span>
</li>
<li class="sidebar-item" :class="{ 'selected': route.path === '/' || route.path === '/dashboard' }">
  <router-link class="sidebar-link" to="/" aria-expanded="false" @click="onMenuClick">
    <span><i class="ti ti-dashboard"></i></span>
    <span class="hide-menu">Dashboard</span>
  </router-link>
</li>
```

### 4.4 Script Library ApexCharts: `frontend/index.html`
Memastikan script ApexCharts template dimuat pada halaman utama:
```html
<script src="/dist/libs/apexcharts/dist/apexcharts.min.js"></script>
```

---

## 5. Checklist Tahapan Eksekusi

### Fase 1: Backend Development
- [ ] Buat Controller `DashboardController.php` di `backend/app/Http/Controllers/`.
- [ ] Implementasikan query agregasi untuk method `summary()`.
- [ ] Implementasikan query bulanan (12 bulan) untuk method `chartMonthly()` dengan filter tahun.
- [ ] Implementasikan query mingguan (7 hari terakhir) untuk method `chartWeekly()`.
- [ ] Implementasikan query distribusi bagian seksi untuk method `chartDistribution()`.
- [ ] Implementasikan query data & aktivitas terbaru untuk `recentActivities()` dan `recentSurat()`.
- [ ] Daftarkan seluruh route dashboard di `backend/routes/api.php`.
- [ ] Validasi respon JSON backend menggunakan Postman / curl.

### Fase 2: Frontend Development
- [ ] Daftarkan script `apexcharts.min.js` di `frontend/index.html` jika belum aktif.
- [ ] Buat file tampilan utama `frontend/src/views/Dashboard.vue`.
- [ ] Susun struktur layout grid HTML sesuai referensi `index5.html`.
- [ ] Integrasikan grafik ApexCharts Line (Monthly Trend Surat Masuk vs Keluar).
- [ ] Integrasikan grafik ApexCharts Area (Weekly Trend 7 Hari).
- [ ] Integrasikan grafik ApexCharts RadialBar (Distribusi Seksi).
- [ ] Hubungkan komponen Activity Stream dan Recent Letters Table ke API backend.
- [ ] Perbarui routing di `frontend/src/router/index.js` agar default landing page mengarah ke Dashboard.
- [ ] Tambahkan menu Dashboard pada Sidebar `frontend/src/layout/MainLayout.vue`.
- [ ] Pastikan kompatibilitas Dark Mode pada semua chart dan text card.

### Fase 3: Pengujian & Validasi
- [ ] Uji responsivitas dashboard pada tampilan Desktop, Tablet, dan Mobile.
- [ ] Uji performa render grafik saat data bernilai 0 (empty state) maupun banyak data.
- [ ] Uji toggle Dark Mode dan pastikan warna chart & teks menyesuaikan otomatis.
- [ ] Jalankan `npm run build` untuk memverifikasi tidak ada kesalahan kompilasi frontend.

---

## 6. Kriteria Keberhasilan (Acceptance Criteria)
1. Setelah login berhasil, pengguna langsung diarahkan ke halaman Dashboard (`/`).
2. Terdapat menu Dashboard aktif di urutan teratas sidebar navigasi.
3. Statistik ringkasan (Total Surat Masuk, Surat Keluar, Disposisi) tampil akurat sesuai data database.
4. Grafik tren bulanan menampilkan perbandingan visual antara Surat Masuk dan Surat Keluar secara interaktif.
5. Grafik tren harian/mingguan dan distribusi bagian/seksi ter-render rapi dan responsif.
6. Riwayat surat terbaru dapat ditinjau langsung melalui tab tabel di dashboard.
7. Desain antarmuka harmonis dengan tema Modernize dan mendukung mode Dark/Light secara sempurna.

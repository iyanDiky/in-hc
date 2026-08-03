# Issue: Implementasi CRUD Pengelolaan Data Pelamar (Rekrutmen Lamaran Kerja)

## 1. Deskripsi Fitur
Modul **Pengelolaan Data Pelamar** (Lamaran Kerja) adalah modul pada sistem IN-HC yang digunakan oleh bagian SDM / Personalia untuk mencatat, mengelola, memfilter, dan mengarsipkan berkas berkas pelamar kerja yang masuk ke perusahaan.

Modul ini mengadopsi tampilan antarmuka dan interaksi yang identik dengan halaman **Surat Masuk** / **Surat Keluar** (gaya *eco-product-list* dari template Modernize), yang mencakup integrasi Server-Side DataTables, filter dinamis (rentang tanggal & jenjang pendidikan), modal form tambah/edit dengan berkas lampiran (*evidence*), modal preview detail pelamar beserta penampil berkas dokumen (PDF/Gambar), serta notifikasi interaktif SweetAlert2.

> [!NOTE]
> Berbeda dengan modul Surat Masuk, pada modul Data Pelamar **tidak terdapat fitur alur disposisi**.

---

## 2. Spesifikasi Database & Skema Migrasi

### 2.1 Skema Tabel `lamarans`
Tabel ini menyimpan data profil pelamar kerja beserta berkas lampiran lamaran:

| Kolom | Tipe Data | Keterangan / Atribut |
| :--- | :--- | :--- |
| `id` | `uuid` (char 36) | Primary Key (UUID v7) |
| `nomor_lamaran` | `integer` | Nomor urut lamaran otomatis per tahun berjalan (auto-increment per tahun) |
| `tanggal_diterima` | `date` | Tanggal berkas lamaran diterima di perusahaan (`YYYY-MM-DD`) |
| `nama` | `string` (varchar 255) | Nama lengkap pelamar kerja |
| `tempat_lahir` | `string` (varchar 255) | Tempat lahir pelamar (nullable / opsional) |
| `tanggal_lahir` | `date` / `string` (varchar 255) | Tanggal lahir pelamar |
| `pendidikan` | `enum` | Pilihan: `['SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'LAINNYA']` |
| `institusi` | `string` (varchar 255) | Nama sekolah / universitas / perguruan tinggi asal |
| `jurusan` | `string` (varchar 255) | Program studi / keahlian / jurusan |
| `evidence` | `string` (varchar 255) | Path file berkas lamaran/CV (PDF/Gambar, nullable) |
| `catatan` | `string` (varchar 255) | Catatan kualifikasi tambahan atau posisi yang dilamar (nullable) |
| `user_input` | `uuid` (char 36) | Foreign key merujuk ke `users.id` (petugas yang menginputkan) |
| `created_at` | `timestamp` | Waktu data dibuat (otomatis) |
| `created_by` | `string` (varchar 255) | Format Audit Trail: `user_id,bagian_seksi_id,unit_kerja_id` |
| `updated_at` | `timestamp` | Waktu data diperbarui (otomatis) |
| `updated_by` | `string` (varchar 255) | Format Audit Trail: `user_id,bagian_seksi_id,unit_kerja_id` |
| `delete_at` | `dateTime` | Waktu soft delete (nullable) |
| `delete_by` | `string` (varchar 255) | Format Audit Trail saat data dihapus (nullable) |

---

### 2.2 File Migrasi: `backend/database/migrations/YYYY_MM_DD_HHMMSS_create_lamarans_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lamarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('nomor_lamaran')->index();
            $table->date('tanggal_diterima')->index();
            $table->string('nama', 255);
            $table->string('tempat_lahir', 255)->nullable();
            $table->string('tanggal_lahir', 255)->nullable();
            $table->enum('pendidikan', ['SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'LAINNYA'])->default('S1');
            $table->string('institusi', 255)->nullable();
            $table->string('jurusan', 255)->nullable();
            $table->string('evidence', 255)->nullable();
            $table->string('catatan', 255)->nullable();
            $table->uuid('user_input')->index();

            // Audit Trail & Soft Delete
            $table->timestamp('created_at')->useCurrent();
            $table->string('created_by', 255)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('updated_by', 255)->nullable();
            $table->dateTime('delete_at')->nullable();
            $table->string('delete_by', 255)->nullable();

            $table->foreign('user_input')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lamarans');
    }
};
```

---

## 3. Spesifikasi Backend (Laravel)

### 3.1 Eloquent Model: `backend/app/Models/Lamaran.php`
Model wajib mengimplementasikan trait `UuidV7`, `SoftDeletes`, dan `AuditTrail`.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidV7;
use App\Traits\AuditTrail;
use Illuminate\Support\Facades\Auth;

class Lamaran extends Model
{
    use HasFactory, UuidV7, SoftDeletes, AuditTrail;

    public const DELETED_AT = 'delete_at';

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->user_input && Auth::check()) {
                $model->user_input = Auth::id();
            }
        });
    }

    public function userInput()
    {
        return $this->belongsTo(User::class, 'user_input');
    }
}
```

---

### 3.2 Backend Controller: `backend/app/Http/Controllers/LamaranController.php`

Controller ini mengelola seluruh operasi CRUD dan query DataTables.

#### Logika Penomoran Otomatis (`nomor_lamaran`):
- `nomor_lamaran` bertipe integer yang bersifat auto-increment berdasarkan tahun dari `tanggal_diterima`.
- Rumus penentuan nomor:
  ```php
  $year = Carbon::parse($request->tanggal_diterima)->year;
  $maxNumber = Lamaran::whereYear('tanggal_diterima', $year)->max('nomor_lamaran') ?? 0;
  $nomorLamaran = $maxNumber + 1;
  ```
- Pada form tambah data di frontend, field `nomor_lamaran` tidak perlu diisi manual oleh pengguna. Setelah berhasil disimpan, nomor lamaran yang diterbitkan ditampilkan pada pesan notifikasi SweetAlert2.

#### Daftar Method yang Diimplementasikan:
1. **`datatables(Request $request)`**:
   - Menerima parameter DataTables (`draw`, `start`, `length`, `search.value`, `order`).
   - **Filter Tanggal**: Filter `startDate` dan `endDate` berdasarkan kolom `tanggal_diterima`.
   - **Filter Pendidikan**: Filter `pendidikan` jika dipilih (contoh: `S1`, `D3`, dll).
   - **Pencarian Global (`search.value`)**: Mencari kecocokan kata kunci pada `nama`, `nomor_lamaran`, `institusi`, `jurusan`, `tempat_lahir`, atau `catatan`.
   - **Sorting**: Default diurutkan berdasarkan `tanggal_diterima DESC` dan `nomor_lamaran DESC`.
   - Mengembalikan response format standar DataTables JSON (`draw`, `recordsTotal`, `recordsFiltered`, `data`).

2. **`list(Request $request)`**:
   - Mengembalikan data pelamar dalam bentuk pagination standar.

3. **`detail(Request $request)`**:
   - Menerima parameter `id` (UUID).
   - Mengambil data pelamar lengkap beserta relasi `userInput`.

4. **`nextNumber(Request $request)`**:
   - Menerima parameter opsional `tanggal_diterima` (default: hari ini).
   - Menghitung dan mengembalikan estimasi nomor berikutnya untuk tahun tersebut:
     ```json
     {
       "success": true,
       "data": {
         "next_number": 1,
         "year": 2026
       }
     }
     ```

5. **`create(Request $request)`**:
   - **Validasi Input**:
     - `tanggal_diterima`: `required|date`
     - `nama`: `required|string|max:255`
     - `tempat_lahir`: `nullable|string|max:255`
     - `tanggal_lahir`: `nullable|string|max:255`
     - `pendidikan`: `required|in:SMA,D3,D4,S1,S2,S3,LAINNYA`
     - `institusi`: `nullable|string|max:255`
     - `jurusan`: `nullable|string|max:255`
     - `evidence`: `nullable|file|mimes:pdf,jpg,jpeg,png|max:10240` (Maksimal 10MB)
     - `catatan`: `nullable|string|max:255`
   - **Proses Penyimpanan**:
     - Hitung auto-increment `nomor_lamaran` untuk tahun `tanggal_diterima`.
     - Upload file `evidence` jika ada ke direktori `storage/app/public/evidence_lamaran/`.
     - Simpan data dan kembalikan response JSON dengan status 201 berisi data yang baru dibuat beserta `nomor_lamaran`.

6. **`update(Request $request)`**:
   - Menerima parameter `id` (UUID).
   - Validasi data input serupa dengan method create.
   - Jika terdapat upload file `evidence` baru, simpan file baru dan hapus/gantikan referensi file lama.
   - Perbarui data di database.

7. **`delete(Request $request)`**:
   - Menerima parameter `id` (UUID).
   - Menjalankan soft delete (`$lamaran->delete()`), trait `AuditTrail` akan otomatis mengisi kolom `delete_by`.

---

### 3.3 Pendaftaran API Routes: `backend/routes/api.php`
Daftarkan group route `lamaran` di dalam middleware `auth:sanctum`:

```php
// Pengelolaan Data Pelamar (Lamaran)
Route::prefix('lamaran')->group(function () {
    Route::post('/datatables', [\App\Http\Controllers\LamaranController::class, 'datatables']);
    Route::post('/list', [\App\Http\Controllers\LamaranController::class, 'list']);
    Route::post('/detail', [\App\Http\Controllers\LamaranController::class, 'detail']);
    Route::post('/next-number', [\App\Http\Controllers\LamaranController::class, 'nextNumber']);
    Route::post('/create', [\App\Http\Controllers\LamaranController::class, 'create']);
    Route::post('/update', [\App\Http\Controllers\LamaranController::class, 'update']);
    Route::post('/delete', [\App\Http\Controllers\LamaranController::class, 'delete']);
});
```

---

## 4. Spesifikasi Frontend (Vue 3 + Vite)

### 4.1 Tampilan Utama: `frontend/src/views/Pelamar.vue`
Halaman ini mengadopsi struktur visual dan layout dari `SuratMasuk.vue` / `SuratKeluar.vue`:

1. **Header Breadcrumb**:
   - Judul: `Data Pelamar`
   - Breadcrumb: `Rekrutmen / Data Pelamar`

2. **Top Toolbar**:
   - Input pencarian cepat dengan icon search dan debounce 350ms.
   - Tombol Toggle **Filter** (dengan indikator status aktif).
   - Tombol **+ Tambah Pelamar** (membuka modal form tambah).

3. **Collapsible Filter Panel**:
   - Input **Tanggal Awal** (`filter.startDate`).
   - Input **Tanggal Akhir** (`filter.endDate`).
   - Dropdown **Jenjang Pendidikan** (`filter.pendidikan`: `Semua`, `SMA`, `D3`, `D4`, `S1`, `S2`, `S3`, `LAINNYA`).
   - Tombol **Terapkan** & **Reset**.

4. **Tabel Data (Server-Side DataTables)**:
   - **Kolom 1: Pelamar & Nomor Lamaran**:
     - Ikon profil (`<i class="ti ti-user-check"></i>` dengan `bg-light-primary text-primary`).
     - Nama lengkap pelamar (teks tebal).
     - Nomor urut lamaran di bawah nama (`No. Lamaran: 001/2026` atau angka integer `No. Lamaran: #1`).
   - **Kolom 2: Tanggal Diterima** (format tanggal Indonesia: `DD MMMM YYYY`).
   - **Kolom 3: Pendidikan & Institusi**:
     - Badge jenjang pendidikan (pill warna elegan, contoh: `bg-light-info text-info`).
     - Nama institusi dan jurusan di bawah badge (teks muted).
   - **Kolom 4: Tempat & Tanggal Lahir**:
     - Contoh: `Jakarta, 15 Mei 1998` atau tanda `-` jika kosong.
   - **Kolom 5: Catatan / Posisi**:
     - Keterangan kualifikasi / posisi yang dilamar.
   - **Kolom 6: Aksi (Dropdown Menu / Action Buttons)**:
     - **Detail Pelamar**: Membuka modal preview informasi lengkap dan berkas lampiran.
     - **Edit Data**: Membuka modal edit data.
     - **Hapus Data**: Konfirmasi hapus data dengan SweetAlert2.
     - **Lihat / Unduh Berkas**: Direct link membuka berkas evidence di tab baru jika tersedia.

5. **Modal Form Tambah / Edit**:
   - `tanggal_diterima`: Date input (Required, default: hari ini).
   - `nama`: Text input (Required).
   - `tempat_lahir`: Text input (Opsional).
   - `tanggal_lahir`: Text / Date input (Opsional).
   - `pendidikan`: Select input options `['SMA', 'D3', 'D4', 'S1', 'S2', 'S3', 'LAINNYA']` (Required).
   - `institusi`: Text input (Opsional).
   - `jurusan`: Text input (Opsional).
   - `evidence`: File upload (PDF / JPG / PNG, Max 10MB) dengan info nama file lama saat edit.
   - `catatan`: Textarea / Text input (Opsional).

6. **Modal Detail & Embedded Evidence Viewer**:
   - Menampilkan ringkasan lengkap data pelamar secara rapi dalam grid informasi.
   - Menampilkan viewer berkas dokumen langsung pada modal (menggunakan `<embed>` / `<iframe>` untuk PDF atau `<img>` untuk berkas gambar).

---

### 4.2 Router Navigation: `frontend/src/router/index.js`
Daftarkan rute `/pelamar` di dalam child route `MainLayout`:
```javascript
{
  path: 'pelamar',
  name: 'Pelamar',
  component: () => import('../views/Pelamar.vue')
}
```

---

### 4.3 Sidebar Menu: `frontend/src/layout/MainLayout.vue`
Tambahkan menu **Data Pelamar** di bawah grup menu **Rekrutmen** (diletakkan setelah grup Persuratan dan sebelum grup Master Data):

```html
<li class="nav-small-cap">
  <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
  <span class="hide-menu">Rekrutmen</span>
</li>
<li class="sidebar-item" :class="{ 'selected': route.path.startsWith('/pelamar') }">
  <router-link class="sidebar-link" to="/pelamar" aria-expanded="false" @click="onMenuClick">
    <span><i class="ti ti-user-check"></i></span>
    <span class="hide-menu">Data Pelamar</span>
  </router-link>
</li>
```

---

## 5. Checklist Tahapan Eksekusi

### Fase 1: Backend Development
- [ ] Buat file migrasi database tabel `lamarans` dan jalankan `php artisan migrate`.
- [ ] Buat Eloquent Model `Lamaran.php` lengkap dengan trait `UuidV7`, `SoftDeletes`, `AuditTrail`, dan relasi ke `User`.
- [ ] Buat Controller `LamaranController.php` dengan implementasi method `datatables`, `list`, `detail`, `nextNumber`, `create`, `update`, dan `delete`.
- [ ] Daftarkan endpoint API di `backend/routes/api.php`.
- [ ] Pastikan upload berkas tersimpan dengan benar di disk publik (`storage/app/public/evidence_lamaran/`).
- [ ] Validasi sintaks PHP dengan `php -l`.

### Fase 2: Frontend Development
- [ ] Daftarkan rute `/pelamar` di `frontend/src/router/index.js`.
- [ ] Tambahkan grup menu `Rekrutmen` dan item `Data Pelamar` di sidebar `frontend/src/layout/MainLayout.vue`.
- [ ] Buat view komponen `frontend/src/views/Pelamar.vue` mengadopsi styling dan struktur dari `SuratMasuk.vue` / `SuratKeluar.vue`.
- [ ] Implementasikan DataTables server-side dengan filter rentang tanggal dan jenjang pendidikan.
- [ ] Implementasikan Modal Form Tambah (nomor otomatis) dan Edit data pelamar.
- [ ] Implementasikan Modal Detail dengan embedded file viewer (PDF/Image).
- [ ] Implementasikan konfirmasi hapus data dengan SweetAlert2.
- [ ] Pastikan keselarasan tampilan Dark Mode dan responsive layout pada perangkat mobile.

### Fase 3: Pengujian & Validasi
- [ ] Uji fungsionalitas Tambah Pelamar (dengan dan tanpa upload berkas lampiran).
- [ ] Uji filter rentang tanggal dan filter pendidikan pada tabel.
- [ ] Uji pencarian instan DataTables.
- [ ] Uji Edit data pelamar dan penggantian berkas dokumen lampiran.
- [ ] Uji Hapus data pelamar (soft delete) dan pastikan kolom `delete_at` & `delete_by` terisi.
- [ ] Uji pratinjau dokumen lampiran pada Modal Detail.
- [ ] Jalankan `npm run build` untuk memastikan tidak ada kesalahan kompilasi.

---

## 6. Kriteria Keberhasilan (Acceptance Criteria)
1. Tabel data pelamar dapat menampilkan daftar pelamar dengan DataTables server-side secara cepat dan responsif.
2. Pengguna dapat menyaring data berdasarkan rentang tanggal diterima dan jenjang pendidikan.
3. Nomor lamaran ter-generate otomatis secara berurutan per tahun saat data disimpan.
4. Berkas lamaran (PDF/Gambar) dapat diunggah, dilihat melalui modal detail, dan diunduh langsung.
5. Seluruh operasi tambah, ubah, dan hapus tercatat otomatis dalam kolom audit trail (`created_by`, `updated_by`, `delete_by`).
6. Antarmuka bersih, konsisten dengan tema Modernize, dan mendukung Light/Dark mode dengan sempurna tanpa ada error kompilasi frontend maupun backend.

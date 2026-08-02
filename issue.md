# Issue: Implementasi CRUD Pengelolaan Data Surat Keluar

## 1. Deskripsi Fitur (Overview)
Modul **Surat Keluar** merupakan bagian dari sistem persuratan pada aplikasi **IN-HC** yang digunakan untuk mencatat, mengelola, dan mengarsipkan surat-surat yang dikeluarkan oleh instansi/unit kerja.

Tampilan antarmuka (UI) akan mengadopsi standar modern yang telah diterapkan pada halaman **Surat Masuk** (menggunakan referensi template *eco-product-list*), dengan penyesuaian khusus: **Surat Keluar tidak memiliki alur disposisi**.

---

## 2. Rancangan Basis Data (Database Design)

### 2.1 Skema Tabel `surat_keluars` (atau `surat_keluar`)
Berikut adalah struktur tabel yang akan dibuat melalui migrasi Laravel:

| Nama Kolom | Tipe Data | Keterangan / Atribut |
| :--- | :--- | :--- |
| `id` | `VARCHAR(36)` / UUID | **Primary Key**, UUIDv7, Not Null |
| `tanggal_surat` | `DATE` | Tanggal resmi surat dikeluarkan |
| `nomor_surat` | `VARCHAR(255)` | Nomor surat keluar |
| `tujuan` | `VARCHAR(255)` | Pihak/instansi/unit tujuan surat |
| `perihal` | `VARCHAR(255)` | Perihal / subjek isi surat |
| `evidence` | `VARCHAR(255)` | Path file berkas lampiran surat (PDF/Gambar), *Nullable* |
| `catatan` | `VARCHAR(255)` | Catatan tambahan, *Nullable* |
| `user_input` | `VARCHAR(36)` / UUID | Foreign Key -> `users.id` (User yang menginput data ke sistem) |
| `user_request` | `VARCHAR(36)` / UUID | Foreign Key -> `users.id` (User/pegawai pemohon surat keluar) |
| `bagian_seksi_request` | `VARCHAR(36)` / UUID | Foreign Key -> `bagian_seksi.id` (Unit/Bagian Seksi pemohon surat) |
| `created_at` | `TIMESTAMP` | Default: `now()` |
| `created_by` | `VARCHAR(255)` | Audit Trail: format `users_id,bagian_seksi_id,unit_kerja_id`, *Nullable* |
| `updated_at` | `TIMESTAMP` | Default: `now()`, on update `now()` |
| `updated_by` | `VARCHAR(255)` | Audit Trail: format `users_id,bagian_seksi_id,unit_kerja_id`, *Nullable* |
| `delete_at` | `DATETIME` | Soft Delete: timestamp waktu penghapusan, *Nullable* |
| `delete_by` | `VARCHAR(255)` | Audit Trail: format `users_id,bagian_seksi_id,unit_kerja_id`, *Nullable* |

---

## 3. Panduan Implementasi Backend (Laravel)

### 3.1 Migration
Buat migration baru: `database/migrations/xxxx_xx_xx_create_surat_keluars_table.php`
- Gunakan `$table->uuid('id')->primary();`
- Gunakan `$table->dateTime('delete_at')->nullable();` untuk soft delete
- Tambahkan index pada `user_input`, `user_request`, `bagian_seksi_request`, `tanggal_surat`, `nomor_surat`.

```php
Schema::create('surat_keluars', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->date('tanggal_surat');
    $table->string('nomor_surat');
    $table->string('tujuan');
    $table->string('perihal');
    $table->string('evidence')->nullable();
    $table->string('catatan')->nullable();
    $table->uuid('user_input')->index();
    $table->uuid('user_request')->index()->nullable();
    $table->uuid('bagian_seksi_request')->index()->nullable();

    $table->timestamp('created_at')->useCurrent();
    $table->string('created_by', 255)->nullable();
    $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
    $table->string('updated_by', 255)->nullable();
    $table->dateTime('delete_at')->nullable();
    $table->string('delete_by', 255)->nullable();
});
```

### 3.2 Eloquent Model: `App\Models\SuratKeluar`
File: `backend/app/Models/SuratKeluar.php`
- Gunakan trait: `HasFactory`, `UuidV7`, `SoftDeletes`, `AuditTrail`.
- Tentukan konstanta soft delete: `public const DELETED_AT = 'delete_at';`
- Tambahkan relasi Eloquent:
  - `userInput()`: `belongsTo(User::class, 'user_input')`
  - `userRequest()`: `belongsTo(User::class, 'user_request')`
  - `bagianSeksiRequest()`: `belongsTo(BagianSeksi::class, 'bagian_seksi_request')`
- Otomatisasi `user_input` pada event model `booted()` saat create jika belum terisi:
```php
protected static function booted()
{
    static::creating(function ($model) {
        if (!$model->user_input && Auth::check()) {
            $model->user_input = Auth::id();
        }
    });
}
```

### 3.3 Controller: `App\Http\Controllers\SuratKeluarController`
File: `backend/app/Http/Controllers/SuratKeluarController.php`
Wajib mengimplementasikan method-method berikut:

1. **`datatables(Request $request)`**:
   - Menghandle request DataTables (Server-side processing).
   - Eager loading relasi: `with(['userRequest:id,nama,npp', 'bagianSeksiRequest:id,bagian_seksi,kode', 'userInput:id,nama,npp'])`.
   - **Filter Tanggal**: Filter `start_date` (`tanggal_surat >= start_date`) dan `end_date` (`tanggal_surat <= end_date`).
   - **Filter Bagian Seksi**: Filter `bagian_seksi_request` jika disediakan.
   - **Pencarian Global (`search.value`)**: Mencari kecocokan kata kunci pada `nomor_surat`, `perihal`, `tujuan`, nama `userRequest`, atau `bagianSeksiRequest`.
   - **Sorting**: Mengurutkan berdasarkan kolom (`tanggal_surat`, `nomor_surat`, `perihal`, `tujuan`, `created_at`). Default: `tanggal_surat DESC`.
   - **Pagination**: Menerapkan `start` & `length` untuk pagination.
   - Mengembalikan response JSON standar DataTables (`draw`, `recordsTotal`, `recordsFiltered`, `data`).

2. **`list(Request $request)`**:
   - Mengembalikan daftar surat keluar dalam bentuk pagination standar Laravel.

3. **`detail(Request $request)`**:
   - Menerima parameter `id` (UUID).
   - Mengambil data surat keluar beserta relasi pemohon, bagian seksi, dan penginput.

4. **`create(Request $request)`**:
   - Validasi input:
     - `tanggal_surat`: `required|date`
     - `nomor_surat`: `required|string|max:255`
     - `tujuan`: `required|string|max:255`
     - `perihal`: `required|string|max:255`
     - `user_request`: `nullable|uuid|exists:users,id`
     - `bagian_seksi_request`: `nullable|uuid|exists:bagian_seksi,id`
     - `evidence`: `nullable|file|mimes:pdf,jpg,jpeg,png|max:10240` (Maksimal 10MB)
     - `catatan`: `nullable|string|max:255`
   - Upload file lampiran (`evidence`) ke storage publik: `storage/app/public/evidence_keluar/` atau `storage/app/public/evidence/`.
   - Simpan data ke database dan kembalikan response 201.

5. **`update(Request $request)`**:
   - Menerima parameter `id` (UUID).
   - Validasi data serupa dengan method `create`.
   - Jika ada upload file `evidence` baru, simpan file baru; jika tidak ada, pertahankan file lama.
   - Update data ke database.

6. **`delete(Request $request)`**:
   - Menerima parameter `id` (UUID).
   - Menjalankan soft delete (`$suratKeluar->delete()`), trait `AuditTrail` akan otomatis mengisi `delete_by`.

### 3.4 API Routes: `backend/routes/api.php`
Tambahkan route endpoint di dalam group middleware `auth:sanctum`:
```php
// Surat Keluar
Route::prefix('surat-keluar')->group(function () {
    Route::post('/datatables', [\App\Http\Controllers\SuratKeluarController::class, 'datatables']);
    Route::post('/list', [\App\Http\Controllers\SuratKeluarController::class, 'list']);
    Route::post('/detail', [\App\Http\Controllers\SuratKeluarController::class, 'detail']);
    Route::post('/create', [\App\Http\Controllers\SuratKeluarController::class, 'create']);
    Route::post('/update', [\App\Http\Controllers\SuratKeluarController::class, 'update']);
    Route::post('/delete', [\App\Http\Controllers\SuratKeluarController::class, 'delete']);
});
```

---

## 4. Panduan Implementasi Frontend (Vue 3 + Vite)

### 4.1 Tampilan Utama: `frontend/src/views/SuratKeluar.vue`
Halaman ini mengadopsi struktur tampilan yang sama persis dengan `SuratMasuk.vue` (gaya *eco-product-list* template Modernize):

1. **Header Breadcrumb**:
   - Judul: `Surat Keluar`
   - Breadcrumb: `Persuratan / Surat Keluar`

2. **Top Toolbar**:
   - Input Pencarian instan (debounce 350ms).
   - Tombol Toggle **Filter** (dengan indikator status aktif).
   - Tombol **+ Tambah Surat Keluar** (membuka modal form).

3. **Collapsible Filter Panel**:
   - Input **Tanggal Awal** (`filter.startDate`).
   - Input **Tanggal Akhir** (`filter.endDate`).
   - Dropdown **Bagian / Seksi Pemohon** (`filter.bagianSeksiId`).
   - Tombol **Terapkan** & **Reset**.

4. **Tabel Data (Server-Side DataTables)**:
   - **Kolom 1: Perihal & Nomor Surat**
     - Ikon surat keluar (misal: `<i class="ti ti-file-export"></i>` dengan warna `bg-light-info text-info`).
     - Judul perihal (bold & truncate).
     - Nomor surat di bawah perihal (text muted).
   - **Kolom 2: Tanggal Surat** (format tanggal Indonesia: `DD MMMM YYYY`).
   - **Kolom 3: Tujuan** (Nama instansi/penerima surat).
   - **Kolom 4: Pemohon & Bagian Seksi**
     - Menampilkan nama pemohon (`user_request.nama`) dan kode/nama bagian seksi (`bagian_seksi_request.bagian_seksi`).
   - **Kolom 5: Aksi (Dropdown Menu)**
     - Detail Surat (membuka modal preview detail atau navigasi ke halaman detail).
     - Edit Surat (membuka modal edit form).
     - Hapus Surat (konfirmasi SweetAlert2).
     - Unduh / Lihat Evidence (jika berkas tersedia).

5. **Modal Form Tambah / Edit**:
   - Form input reaktif:
     - `tanggal_surat` (Input Date, Required)
     - `nomor_surat` (Input Text, Required)
     - `tujuan` (Input Text, Required)
     - `perihal` (Input Text, Required)
     - `user_request` (Dropdown Select User / Pegawai Pemohon, Opsional/Required)
     - `bagian_seksi_request` (Dropdown Select Bagian Seksi, Opsional/Required - otomatis terisi jika user dipilih)
     - `evidence` (File upload PDF / Gambar)
     - `catatan` (Textarea, Opsional)

6. **Modal Detail / Preview Surat Keluar**:
   - Menampilkan detail informasi lengkap surat keluar.
   - Preview lampiran evidence (PDF Viewer bawaan `<embed>` / `<iframe>` atau Image Preview) jika terdapat file bukti lampiran.

### 4.2 Router Navigation: `frontend/src/router/index.js`
Tambahkan route untuk surat keluar:
```javascript
{
  path: 'surat-keluar',
  name: 'SuratKeluar',
  component: () => import('../views/SuratKeluar.vue')
}
```

### 4.3 Sidebar Menu: `frontend/src/layout/MainLayout.vue`
Tambahkan menu `Surat Keluar` di sidebar menu persuratan tepat di bawah menu `Surat Masuk`:
```html
<li class="sidebar-item" :class="{ 'selected': route.path.startsWith('/surat-keluar') }">
  <router-link class="sidebar-link" to="/surat-keluar" aria-expanded="false" @click="onMenuClick">
    <span><i class="ti ti-file-export"></i></span>
    <span class="hide-menu">Surat Keluar</span>
  </router-link>
</li>
```

---

## 5. Checklist Tahapan Eksekusi

### Fase 1: Backend
- [ ] Buat file migrasi database untuk tabel `surat_keluars` dengan skema yang telah ditentukan.
- [ ] Jalankan migrasi `php artisan migrate`.
- [ ] Buat Eloquent Model `SuratKeluar` lengkap dengan Trait `UuidV7`, `SoftDeletes`, `AuditTrail`, dan relasi ke `User` & `BagianSeksi`.
- [ ] Buat Controller `SuratKeluarController` yang mengimplementasikan method `datatables`, `list`, `detail`, `create`, `update`, `delete`.
- [ ] Daftarkan API routes di `backend/routes/api.php`.
- [ ] Uji endpoint API menggunakan Postman/cURL (Create, Datatables, Filter, Update, Delete).

### Fase 2: Frontend
- [ ] Tambahkan menu Surat Keluar pada sidebar di `frontend/src/layout/MainLayout.vue`.
- [ ] Daftarkan route `/surat-keluar` di `frontend/src/router/index.js`.
- [ ] Buat komponen `frontend/src/views/SuratKeluar.vue` dengan tampilan menyerupai `SuratMasuk.vue` (tanpa fitur disposisi).
- [ ] Implementasikan DataTables server-side dengan filter rentang tanggal dan bagian seksi pemohon.
- [ ] Implementasikan Modal Form Tambah dan Edit lengkap dengan upload file evidence.
- [ ] Implementasikan Modal Detail & Preview file evidence.
- [ ] Implementasikan konfirmasi hapus data dengan SweetAlert2.

### Fase 3: Pengujian & Validasi
- [ ] Uji tambah data surat keluar baru dengan & tanpa lampiran file.
- [ ] Uji filter rentang tanggal pada tabel surat keluar.
- [ ] Uji fitur pencarian global.
- [ ] Uji edit data dan penggantian lampiran file.
- [ ] Uji hapus data (soft delete) dan pastikan kolom `delete_at` & `delete_by` terisi.
- [ ] Uji responsive layout pada layar handphone/mobile.
- [ ] Jalankan build frontend `npm run build` untuk memastikan tidak ada error kompilasi.

---

## 6. Kriteria Keberhasilan (Acceptance Criteria)
1. User dapat melihat daftar surat keluar dengan layout tabel modern yang rapi dan responsif.
2. Filter rentang tanggal dan pencarian berfungsi cepat melalui server-side DataTables.
3. User dapat menambahkan, memperbarui, melihat detail, dan menghapus data surat keluar.
4. File evidence (PDF/Gambar) dapat diunggah, diunduh, dan dipratinjau langsung di aplikasi.
5. Seluruh aktivitas pencatatan dan penghapusan data tercatat pada kolom Audit Trail (`created_by`, `updated_by`, `delete_by`).
6. Tidak ada error build frontend maupun backend.

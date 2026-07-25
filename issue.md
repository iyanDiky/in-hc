# Perencanaan Implementasi CRUD: Jabatan, Unit Kerja, dan Bagian Seksi

Dokumen ini berisi panduan implementasi teknis (planning) untuk membangun fitur CRUD pada tabel `jabatan`, `unit_kerja`, dan `bagian_seksi`. Panduan ini dirancang sistematis agar dapat dieksekusi dengan mudah oleh programmer atau AI (LLM).

## 1. Spesifikasi Sistem
- **Backend:** Laravel REST API.
- **Frontend:** Vue.js.
- **UI Template:** Slicing referensi desain dari folder `template_frontend/package/html/main`.
- **Primary Key:** Wajib menggunakan **UUID v7** (bukan *auto-increment* atau UUID v4).
- **Audit Trail:** Terdapat mekanisme pencatatan riwayat perubahan (Soft Deletes dan pencatatan aktor).

---

## 2. Backend: Database Migration
Buat *migration* untuk ketiga tabel tersebut. Urutan pembuatannya harus `jabatan`, `unit_kerja`, baru kemudian `bagian_seksi` (karena ada *foreign key*).

**Struktur Kolom Standar (untuk semua tabel):**
```php
$table->uuid('id')->primary(); // Akan diisi UUID v7 dari Model
$table->string('kode', 10);
$table->string('nama_kolom_tabel', 255); // contoh: 'jabatan', 'unit_kerja', 'bagian_seksi'

// Khusus tabel bagian_seksi tambahkan foreign key:
// $table->uuid('unit_kerja_id');
// $table->foreign('unit_kerja_id')->references('id')->on('unit_kerja');

// Audit Trail
$table->timestamp('created_at')->useCurrent();
$table->string('created_by', 64)->nullable();
$table->timestamp('updated_at')->useCurrent();
$table->string('updated_by', 64)->nullable();
$table->timestamp('delete_at')->nullable(); // Soft delete column
$table->string('delete_by', 64)->nullable();
```

---

## 3. Backend: Setup Model (Laravel)
Buat model `Jabatan`, `UnitKerja`, dan `BagianSeksi`. Semua model harus mengimplementasikan logika berikut:

1. **UUID v7 Configuration:**
   Gunakan trait `HasUuids`. Karena Laravel (tergantung versi) default-nya v4, pastikan metode pembentukan UUID di-override menggunakan UUID v7.
   ```php
   use Illuminate\Support\Str;
   use Illuminate\Database\Eloquent\Concerns\HasUuids;

   public function newUniqueId() {
       return (string) Str::uuid7();
   }
   ```
2. **Soft Deletes Custom Column:**
   Tambahkan trait `SoftDeletes` dan atur konstantanya:
   ```php
   public const DELETED_AT = 'delete_at';
   ```
3. **Otomatisasi Audit Trail (*Bootable Observer*):**
   Buat *boot method* (atau *Trait* khusus) untuk mengisi `created_by`, `updated_by`, dan `delete_by` saat *event* Eloquent dijalankan (`creating`, `updating`, `deleting`).
   **Aturan Nilai:** Format nilainya adalah gabungan dari `users_id,bagian_seksi_id,unit_kerja_id`. Pastikan model membaca data *User* yang sedang login untuk men-generate format string tersebut.

---

## 4. Backend: Controller & API Routes
Buat Controller terpisah (`JabatanController`, `UnitKerjaController`, `BagianSeksiController`). Tiap controller harus menyediakan 5 endpoint RPC:
1. `/list`: Menampilkan data, support *search* dan *pagination*. (Khusus `BagianSeksi`, pastikan melakukan `with('unitKerja')` untuk mengambil relasinya).
2. `/detail`: Mengambil data spesifik berdasarkan `id`.
3. `/create`: Validasi *request*, simpan data baru.
4. `/update`: Validasi *request*, perbarui data yang ada.
5. `/delete`: Lakukan fungsi `delete()` agar model memicu mekanisme *Soft Delete* dan mencatat `delete_by`.

Daftarkan endpoint tersebut di `routes/api.php` di bawah perlindungan *middleware authentication* (misal: Sanctum).

---

## 5. Frontend: Integrasi Vue.js & UI Slicing
1. **Slicing Template:** Buka folder `template_frontend/package/html/main`, pelajari kerangka HTML tabel, *form*, dan *modal* yang ada, lalu konversikan struktur tersebut menjadi komponen Vue.js (`.vue`).
2. **Struktur Halaman:** Buat 3 menu/halaman baru:
   - `/jabatan`
   - `/unit-kerja`
   - `/bagian-seksi`
3. **Komponen Tabel & Form:**
   - Gunakan DataTables atau struktur *table* dari referensi *slicing* HTML.
   - Buat fungsi aksi: Tambah, Edit, dan Hapus (gunakan SweetAlert untuk konfirmasi hapus).
4. **Dependensi Dropdown (Penting):**
   Pada form penambahan/edit data **Bagian Seksi**, sediakan *select dropdown* dinamis untuk kolom `unit_kerja_id`. Komponen ini harus melakukan fetch HTTP GET/POST ke API `/unit-kerja/list` saat komponen di-*mount*, lalu menampilkannya sebagai opsi (*option*) bagi pengguna.

---
*Dokumen ini bersifat teknis. Jika kamu mengerti, silakan buatkan implementasinya file per file secara berurutan.*

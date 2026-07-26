# Perencanaan Implementasi CRUD: Pengelolaan Data User

Dokumen ini berisi panduan implementasi teknis (planning) untuk membangun fitur CRUD pada tabel `users`. Panduan ini dirancang secara sistematis agar dapat dieksekusi dengan mudah oleh programmer atau AI (LLM).

## 1. Spesifikasi Sistem
- **Backend:** Laravel REST API.
- **Frontend:** Vue.js.
- **UI Template:** Slicing referensi desain dari folder `template_frontend/package/html/main`.
- **Primary Key:** Wajib menggunakan **UUID v7** (bukan *auto-increment* atau UUID v4).
- **Audit Trail:** Terdapat mekanisme pencatatan riwayat perubahan (Soft Deletes dan pencatatan aktor).

---

## 2. Backend: Database Migration
Buat atau ubah *migration* untuk tabel `users`. Berhubung Laravel sudah menyediakan *migration* bawaan untuk `users`, modifikasi *file migration* tersebut (atau buat *migration* baru untuk me-replace struktur lamanya) agar sesuai dengan rancangan berikut:

**Struktur Kolom Tabel `users`:**
```php
$table->uuid('id')->primary(); // Akan diisi UUID v7 dari Model
$table->string('npp', 16)->unique();
$table->string('tempat_lahir', 255);
$table->date('tanggal_lahir');
$table->uuid('jabatan_id');
$table->uuid('bagian_seksi_id');
$table->string('username', 255)->unique();
$table->string('password', 255);
$table->enum('level', ['admin', 'user']);

// Definisi Foreign Key
$table->foreign('jabatan_id')->references('id')->on('jabatan');
$table->foreign('bagian_seksi_id')->references('id')->on('bagian_seksi');

// Audit Trail
$table->timestamp('created_at')->useCurrent();
$table->string('created_by', 64)->nullable();
$table->timestamp('updated_at')->useCurrent();
$table->string('updated_by', 64)->nullable();
$table->timestamp('delete_at')->nullable(); // Soft delete column khusus
$table->string('delete_by', 64)->nullable();
```

---

## 3. Backend: Setup Model (Laravel)
Modifikasi model `App\Models\User`. Model ini harus mengimplementasikan logika berikut:

1. **UUID v7 & Audit Trail Configuration:**
   Gunakan trait kustom yang sudah ada di proyek, yaitu `UuidV7` dan `AuditTrail`.
   ```php
   use App\Traits\UuidV7;
   use App\Traits\AuditTrail;
   use Illuminate\Database\Eloquent\SoftDeletes;

   class User extends Authenticatable {
       use UuidV7, SoftDeletes, AuditTrail;
       // ...
   }
   ```
2. **Soft Deletes Custom Column:**
   Atur konstanta SoftDeletes agar menggunakan `delete_at`.
   ```php
   public const DELETED_AT = 'delete_at';
   ```
3. **Relasi (Relationships):**
   Tambahkan fungsi relasi ke model `Jabatan` dan `BagianSeksi`.
   ```php
   public function jabatan() {
       return $this->belongsTo(Jabatan::class, 'jabatan_id');
   }

   public function bagianSeksi() {
       return $this->belongsTo(BagianSeksi::class, 'bagian_seksi_id');
   }
   ```
4. **Mutator Password:**
   Pastikan *password* di-*hash* otomatis menggunakan `Hash::make()` ketika *user* dibuat atau di-update (bisa melalui controller atau casts/mutator).

---

## 4. Backend: Database Seeder
Buat *seeder* (`UserSeeder`) untuk menyisipkan satu data admin default dengan detail berikut:
- **Username:** `admin`
- **Password:** `divisihc**` (Wajib dienkripsi menggunakan `Hash::make('divisihc**')` atau `bcrypt('divisihc**')`)
- **Level:** `admin`
- Kolom lainnya (`npp`, `tempat_lahir`, `tanggal_lahir`, `jabatan_id`, `bagian_seksi_id`) bisa diisi dengan data dummy atau statis yang valid untuk menghindari *error* *foreign key* (atau pastikan master datanya disisipkan terlebih dahulu).

Daftarkan `UserSeeder` ini ke dalam `DatabaseSeeder.php`.

---

## 5. Backend: Controller & API Routes
Buat `UserController` yang menyediakan 5 endpoint RPC:
1. `/users/list`: Menampilkan data, *support* *search* (berdasarkan `npp` atau `username`) dan *pagination*. Wajib memanggil relasi `with(['jabatan', 'bagianSeksi'])`.
2. `/users/detail`: Mengambil data spesifik berdasarkan `id`.
3. `/users/create`: Validasi *request* (termasuk *unique rule* untuk `npp` dan `username` yang mengabaikan `delete_at`, serta validasi `level`), hash password, lalu simpan data baru.
4. `/users/update`: Validasi *request* (abaikan ID user tersebut untuk unique rule), update password hanya jika ada *input* password baru, perbarui data termasuk `level`.
5. `/users/delete`: Lakukan fungsi `delete()` agar terpicu mekanisme *Soft Delete* dan tercatatnya `delete_by`.

Daftarkan endpoint tersebut di `routes/api.php` menggunakan grup rute `/users`.

---

## 6. Frontend: Integrasi Vue.js & UI Slicing
1. **Struktur Halaman:** Buat menu/halaman baru di Vue.js:
   - `/users` (File: `src/views/Users.vue`)
2. **Komponen Tabel & Tampilan:**
   - Gunakan struktur tabel dari template (terapkan referensi UI seperti di form dan tabel sebelumnya).
   - Tampilkan kolom: NPP, Username, Tempat/Tgl Lahir, Jabatan, Bagian Seksi, Level, dan Aksi.
3. **Form Modal & Select2:**
   - Gunakan modal form yang terintegrasi dengan gaya template.
   - Sediakan *dropdown* dinamis untuk `jabatan_id` dan `bagian_seksi_id` yang mengambil dari `/jabatan/list` dan `/bagian-seksi/list`.
   - Sediakan *dropdown* statis untuk `level` dengan opsi `admin` dan `user`.
   - **Wajib:** Terapkan class `select2 form-control` beserta inisialisasi script jQuery `select2()` dengan `dropdownParent` menunjuk ke modal agar tidak ter-*block* oleh *focus trap* modal. Pastikan ada penundaan (`setTimeout`) setelah `nextTick()` saat menginisialisasi Select2.
4. **Notifikasi (SweetAlert2):**
   - Gunakan `window.Swal.fire()` untuk menampilkan notifikasi sukses setelah menyimpan atau mengubah data.
   - Gunakan `window.Swal.fire()` dengan `icon: 'warning'` beserta konfirmasi `showCancelButton` sebagai *prompt* penegasan sebelum menjalankan fungsi hapus data.
   - Tangkap *response error* dari API dan tampilkan pesan *error*-nya menggunakan SweetAlert2.

---
*Dokumen ini bersifat teknis dan menjadi pedoman utama pengerjaan fitur.*

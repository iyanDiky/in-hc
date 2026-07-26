# Perencanaan Implementasi Login & Autentikasi

## Deskripsi Singkat
Mengimplementasikan proses *Login* yang utuh dari Backend hingga Frontend.
Backend akan memvalidasi *username* dan *password* menggunakan data pada tabel `users`, lalu menerbitkan token akses menggunakan **Laravel Sanctum**. 
Sedangkan Frontend akan memiliki halaman login berdasarkan *template* UI `authentication-login.html` dan akan mengelola sesi pengguna menggunakan *localStorage* dan Vue Router *Navigation Guards*.

## Kebutuhan Sistem & Analisis Saat Ini
- **Backend**: Laravel dengan tabel `users` dan model `User` (sudah mengimplementasikan `HasApiTokens` dan *hashing* otomatis pada kolom `password`). Sanctum sudah ter-*install*.
- **Frontend**: Menggunakan Vue 3 + Vue Router. Perlu membuat halaman baru untuk login dengan rute `/login`.
- **Referensi UI**: Menggunakan *slicing* HTML dari `/Users/macbookair/self-projects/in-hc/template_frontend/package/html/main/authentication-login.html`.

## Rencana Perubahan (Action Plan)

### 1. Backend (API Authentication)
- **Controller**: Membuat `AuthController.php` dengan fungsi `login` dan `logout`.
  - Fungsi `login` memvalidasi `username` dan `password`.
  - Jika valid, menghasilkan *Plain Text Token* (Sanctum) yang dikembalikan ke *client* bersama data *user*.
  - Fungsi `logout` akan menghapus token aktif milik pengguna.
- **Routes**: Menambahkan di `routes/api.php`:
  - `POST /login` (publik)
  - `POST /logout` (terproteksi *auth:sanctum*)
- **Auth Guard**: Mengamankan rute *master data* (Users, Jabatan, UnitKerja, BagianSeksi) menggunakan *middleware* `auth:sanctum`.

### 2. Frontend (Vue 3 UI & State)
- **Tampilan Halaman Login**:
  - Membuat *file* baru `frontend/src/views/Login.vue`.
  - Mengonversi HTML statis dari *template* UI `authentication-login.html` menjadi komponen Vue yang fungsional (menghubungkan input `v-model` ke `username` dan `password`).
- **Axios & API Interceptor**:
  - Menyisipkan token akses (*Bearer Token*) secara dinamis ke seluruh *request* API jika pengguna sudah login.
  - Menangani jika mendapatkan status 401 (Unauthorized) dengan mengalihkan pengguna ke halaman login.
- **Routing & Navigation Guard (`router/index.js`)**:
  - Mendaftarkan rute `/login` tanpa *layout* utama (hanya berdiri sendiri).
  - Mengubah rute yang sudah ada (`/users`, `/jabatan`, `/unit-kerja`, dll.) sebagai *children* dari layout utama dan mewajibkan pengguna sudah *login* (`meta: { requiresAuth: true }`).
  - Menambahkan *global navigation guard* (`router.beforeEach`) untuk mengecek ketersediaan token. Jika mengakses rute terproteksi tanpa token, alihkan ke `/login`.

## Pertanyaan Terbuka & Persetujuan (User Review Required)
> [!IMPORTANT]
> **Keputusan Manajemen Sesi**: Mengingat saat ini kita belum mengimplementasikan alat manajemen *state* khusus (seperti Pinia atau Vuex) di *frontend*, proses penyimpanan Token Sanctum akan dilakukan secara *default* menggunakan `localStorage`. Apakah pendekatan sederhana ini sudah disetujui, atau kamu ingin aku menambahkan *library* khusus seperti `Pinia` untuk ini?

> [!TIP]
> **Tampilan Halaman Error/Redirect**: Saat pengguna dipaksa keluar (*force logout* atau belum login), fitur UI notifikasi kecil seperti *SweetAlert* yang menyatakan "Sesi Anda telah habis, silakan login kembali" bisa ditambahkan. Haruskah kutambahkan itu di dalam *router guard* kita?

## Langkah Pengujian (Verification Plan)
### Pengujian Manual:
1. Menjalankan *frontend* dan *backend*, mencoba masuk ke rute `/users` secara langsung (harus ditolak ke `/login`).
2. Masuk menggunakan akun dari *seeder* (`username: admin`, `password: Bankkalsel1*`).
3. Memastikan setelah berhasil login, pengguna diarahkan ke *dashboard* atau halaman Master Data.
4. Mengecek proses pencabutan Token via fitur *Logout*.

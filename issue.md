# Planning: Implementasi Fitur Disposisi Surat Masuk

Dokumen ini berisi rancangan dan langkah-langkah implementasi (CRUD) untuk fitur "Disposisi Surat Masuk" pada aplikasi In-HC. Dokumen ini dibuat agar dapat langsung dieksekusi oleh programmer.

## 1. Rancangan Database (Skema Migration)

Terdapat 2 tabel baru yang perlu dibuat menggunakan Laravel Migration.

### 1.1 Tabel `surat_masuk_disposisi`
Tabel ini menyimpan data utama setiap kali sebuah disposisi dilakukan.
- `id` : `uuid` (Primary Key, Not Null)
- `surat_masuk_id` : `uuid` (Foreign Key ke `surat_masuk.id`)
- `disposisi_oleh` : `uuid` (Foreign Key ke `users.id`)
- `disposisi_waktu` : `datetime`
- `catatan` : `text` (Tambahan disarankan: untuk menyimpan teks keterangan disposisi hasil inputan komentar)
- `evidence` : `varchar(255)` (Opsional, untuk melampirkan berkas)
- **Audit Trail**:
  - `created_at` : `timestamp` (default: `now()`)
  - `created_by` : `varchar(255)` (Format: `users_id,bagian_seksi_id,unit_kerja_id`)
  - `updated_at` : `timestamp` (default: `now()`)
  - `updated_by` : `varchar(255)` (Format: `users_id,bagian_seksi_id,unit_kerja_id`)
  - `delete_at` : `datetime` (default: `null`)
  - `delete_by` : `varchar(255)` (Format: `users_id,bagian_seksi_id,unit_kerja_id`)

### 1.2 Tabel `surat_masuk_disposisi_tujuan`
Tabel relasi (pivot) karena satu kali disposisi bisa ditujukan ke banyak Bagian Seksi di unit kerja.
- `id` : `uuid` (Primary Key, Not Null)
- `surat_masuk_disposisi_id` : `uuid` (Foreign Key ke `surat_masuk_disposisi.id`)
- `tujuan_disposisi` : `uuid` (Foreign Key ke `bagian_seksi.id`)
- **Audit Trail**:
  - `created_at` : `timestamp` (default: `now()`)
  - `created_by` : `varchar(255)` (Format: `users_id,bagian_seksi_id,unit_kerja_id`)
  - `updated_at` : `timestamp` (default: `now()`)
  - `updated_by` : `varchar(255)` (Format: `users_id,bagian_seksi_id,unit_kerja_id`)
  - `delete_at` : `datetime` (default: `null`)
  - `delete_by` : `varchar(255)` (Format: `users_id,bagian_seksi_id,unit_kerja_id`)

> **Catatan Penting untuk Programmer**: 
> Pada skema awal, tidak ada kolom untuk menampung teks keterangan (komentar) disposisi secara eksplisit. Kami menyarankan penambahan kolom `catatan` bertipe `text` pada tabel `surat_masuk_disposisi` untuk menyimpan teks keterangan tersebut secara utuh.

---

## 2. Backend (Laravel API)

### 2.1 Model & Relasi
Buat Model `SuratMasukDisposisi` dan `SuratMasukDisposisiTujuan` dengan relasi:
- `SuratMasuk` *hasMany* `SuratMasukDisposisi`
- `SuratMasukDisposisi` *belongsTo* `SuratMasuk` dan `User` (`disposisi_oleh`)
- `SuratMasukDisposisi` *hasMany* `SuratMasukDisposisiTujuan`
- `SuratMasukDisposisiTujuan` *belongsTo* `BagianSeksi` (`tujuan_disposisi`)

**Trait Audit Trail**:
Pastikan logika pada *Model Boot* (seperti `creating`, `updating`, `deleting`) mengisi nilai `created_by`, `updated_by`, dan `delete_by` dengan format string yang berisikan parameter session atau token pengguna (contohnya di-generate menjadi gabungan `"{users_id},{bagian_seksi_id},{unit_kerja_id}"`).

### 2.2 Controller `SuratMasukDisposisiController`
Buat *endpoint* API baru:
- `GET /surat-masuk/{id}/disposisi` : Mengambil daftar riwayat disposisi (termasuk relasi `disposisi_oleh` dan `tujuan` dari bagian seksi terkait).
- `POST /surat-masuk/disposisi` : Menyimpan data disposisi baru.
  - *Payload* yang diterima: `surat_masuk_id`, teks komentar (`catatan`), array berisi kumpulan ID `bagian_seksi_id` yang menjadi tujuan disposisi (hasil parsing dari @mention), dan parameter `evidence` jika ada.
  - Peringatan: Gunakan *Database Transaction* ketika melakukan Create data karena melibatkan dua tabel sekaligus (Tabel utama disposisi dan tabel pivot tujuan).

---

## 3. Frontend (Vue 3)

### 3.1 Penyesuaian Halaman Surat Masuk (`SuratMasuk.vue`)
- Cari *render function* untuk kolom **Aksi** pada DataTables.
- Ubah *event* tombol "Detail" (`.btn-detail`).
- **Sebelumnya**: Membuka modal pop-up detail (`showDetailModal`).
- **Sekarang**: Arahkan navigasi halaman (menggunakan `vue-router` seperti `router.push()`) ke halaman rute baru, misalnya `/surat-masuk/:id/detail`.

### 3.2 Pembuatan Halaman Baru (`SuratMasukDetail.vue`)
Buat *file* komponen Vue baru ini yang memiliki 2 area antarmuka utama:

#### Bagian Atas: Detail Surat Masuk
- Mengambil data surat masuk via API `GET /surat-masuk/detail/{id}`.
- Menampilkan seluruh informasi detail surat masuk dengan desain kartu (*card*).

#### Bagian Bawah: Kolom Disposisi & Riwayat
- **List Riwayat Disposisi**: Menampilkan semua disposisi yang sudah pernah dibuat. Setiap item di *timeline* menampilkan nama yang men-disposisi, waktu, list tag tujuan (bagian seksi), dan isi teks komentarnya.
- **Form Input Disposisi (Mention UI)**:
  - Buat desain menyerupai kotak komentar sosial media.
  - Implementasikan fitur interaktif: Ketika pengguna mengetik lambang `@`, maka *dropdown* berisikan opsi/daftar semua `Bagian Seksi` akan muncul.
  - Pengguna dapat me-mention (*tag*) banyak Bagian Seksi sekaligus.
  - Setelah menyebut/men-*tag* nama bagian, teks yang diketik setelahnya adalah *keterangan disposisinya* (yang akan ter-save ke database bersamaan).
  - Siapkan fungsi `submit` / "Kirim" yang mengekstrak ID-ID Bagian Seksi dari sistem tag tersebut dan membungkusnya sebagai array, dan memisahkannya dari teks deskripsi utamanya, lalu dikirim via API *POST* ke backend.

## 4. Rencana Kerja (Checklist)

- [ ] **DB**: Buat file migration untuk `surat_masuk_disposisi` & `surat_masuk_disposisi_tujuan` sesuai ketentuan audit trail unik.
- [ ] **Model**: Susun class Model, relasi, beserta Observer / *boot trait* untuk custom value Audit Trail.
- [ ] **API**: Bangun `SuratMasukDisposisiController` (Fungsi List & Store Transaksional).
- [ ] **Router Frontend**: Daftarkan *route* `/surat-masuk/:id/detail` pada router Vue.
- [ ] **UI Surat Masuk**: Modifikasi `.btn-detail` agar menjadi link navigasi.
- [ ] **UI Detail Surat Masuk**: Implementasi struktur utama halaman `SuratMasukDetail.vue`.
- [ ] **UI Input Mention**: Pasang/Coding fitur interaktif `@mention` untuk memilih Bagian Seksi dalam textarea komentar disposisi.
- [ ] **Testing & Quality Assurance**.

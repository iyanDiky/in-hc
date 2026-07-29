# Rencana Pengembangan (Planning): CRUD Surat Masuk

Dokumen ini berisi panduan dan spesifikasi untuk mengimplementasikan fitur CRUD (Create, Read, Update, Delete) pengelolaan data **Surat Masuk**. Panduan ini ditujukan bagi programmer atau AI agent agar dapat melakukan implementasi secara terstruktur.

## 1. Spesifikasi Database

Berikut adalah rancangan tabel `surat_masuk` yang harus dibuat di dalam database.

```dbml
table surat_masuk {
  id varchar(36) [pk, not null] // WAJIB menggunakan UUID v7
  tanggal_surat date
  nomor_surat varchar(255)
  pengirim varchar(255)
  tujuan varchar(255)
  perihal varchar(255)
  evidence varchar(255)
  catatan varchar(255)
  user_input varchar(36) [ref: > users.id] // Diisi otomatis sesuai dengan user login yang menambahkan data

  // Fitur: Audit Trail
  created_at timestamp [default: `now()`]
  created_by varchar(64) // Value: generate kombinasi dari users_id, bagian_seksi_id, unit_kerja_id
  
  updated_at timestamp [default: `now()`]
  updated_by varchar(64) // Value: generate kombinasi dari users_id, bagian_seksi_id, unit_kerja_id
  
  delete_at datetime [default: null] // Implementasi Soft Delete
  delete_by varchar(64) // Value: generate kombinasi dari users_id, bagian_seksi_id, unit_kerja_id
}
```

### Ketentuan Khusus Backend & Database:
1. **Primary Key (ID):** Kolom `id` **wajib** digenerate menggunakan format **UUID v7** (berbasis waktu) untuk performa indeks yang lebih baik dan keunikan.
2. **Relasi User:** Kolom `user_input` harus merelasikan data surat dengan user yang sedang login saat itu (diisi otomatis oleh sistem di backend, bukan inputan manual dari form frontend).
3. **Audit Trail:** Kolom `*_by` (`created_by`, `updated_by`, `delete_by`) harus digenerate berdasarkan identitas user yang melakukan aksi, yang mencakup kombinasi dari: `users_id`, `bagian_seksi_id`, dan `unit_kerja_id`.
4. **Soft Delete:** Saat menghapus, data tidak dihapus permanen dari database, melainkan mengupdate kolom `delete_at` menjadi waktu saat itu, dan `delete_by` dengan data user yang menghapus.

## 2. Spesifikasi Frontend & UI/UX

1. **Slicing dari Template:** Antarmuka (UI) untuk CRUD Surat Masuk **wajib** menggunakan referensi dan melakukan slicing dari template frontend yang sudah ada di dalam folder `template_frontend/`. 
2. **Konsistensi Desain:** Pastikan penggunaan class CSS, komponen tabel, form, tombol, dan modal konsisten dengan struktur yang ada pada `template_frontend/`.
3. **Fungsionalitas Halaman:**
   - **Tabel / List Data:** Tampilkan daftar surat masuk dengan kolom penting seperti Tanggal, Nomor Surat, Pengirim, Tujuan, dan Perihal. Harus mendukung pagination atau pencarian.
   - **Form Tambah (Create) & Edit (Update):** Buat form yang rapi untuk mengisi data-data terkait. Khusus input `evidence` mungkin berupa upload file atau link (sesuaikan dengan tipe varchar). Form *tidak perlu* menyertakan input untuk `id`, `user_input`, atau data audit trail, karena ini akan ditangani backend.
   - **Aksi Delete:** Tambahkan tombol hapus dengan konfirmasi (untuk mencegah salah klik) yang akan mentrigger API soft delete.

## 3. Langkah-Langkah Implementasi (Task Breakdown)

Bagi programmer/AI model, silakan ikuti tahapan berikut secara berurutan:

- [ ] **Langkah 1: Database & Model**
  - Buat migration file untuk tabel `surat_masuk` sesuai skema di atas.
  - Implementasikan library pembuat **UUID v7**.
  - Buat Model/Entity untuk `SuratMasuk` dengan mengaktifkan fitur Soft Delete dan konfigurasi auto-fill untuk `user_input` dan kolom Audit Trail.

- [ ] **Langkah 2: Backend API (Controller & Service)**
  - Buat endpoint `GET /api/surat-masuk` untuk menampilkan daftar (pastikan data yang memiliki `delete_at` terisi tidak ikut tertampil).
  - Buat endpoint `POST /api/surat-masuk` untuk Create. Otomatisasi pengisian ID (UUID v7), `user_input`, `created_by`, dan `created_at`.
  - Buat endpoint `PUT /api/surat-masuk/:id` untuk Update. Otomatisasi pengisian `updated_by` dan `updated_at`.
  - Buat endpoint `DELETE /api/surat-masuk/:id` untuk Soft Delete. Otomatisasi pengisian `delete_by` dan `delete_at`.

- [ ] **Langkah 3: Frontend UI Slicing**
  - Eksplorasi folder `template_frontend/` untuk menemukan layout, tabel, dan form komponen yang tepat.
  - Buat halaman List Surat Masuk dan hubungkan dengan API `GET`.
  - Buat halaman/modal untuk Form Tambah & Edit, pastikan desain rapi sesuai template.
  - Tambahkan interaksi konfirmasi saat menghapus data.

- [ ] **Langkah 4: Integrasi & Testing**
  - Hubungkan semua antarmuka (Create, Update, Delete) ke endpoint API.
  - Lakukan uji coba dengan akun dummy, pastikan `user_input` dan `audit_trail` (created/updated/deleted_by) terisi dengan format yang benar.
  - Validasi bahwa UUID v7 sukses digenerate di primary key.

---
*Dokumen ini dibuat secara otomatis. Gunakan ini sebagai acuan (prompt/issue) utama saat melakukan coding.*

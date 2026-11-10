# Menjalankan IN-HC di jaringan ruangan

Frontend sekarang berupa build statis Nginx. Browser menggunakan satu alamat untuk halaman, API, dan evidence; Vite development tidak dipakai pada deployment standar.

## Menjalankan atau memperbarui

Jalankan dari folder proyek dengan Docker aktif:

```sh
sh scripts/start-lan.sh
```

Script membangun image, menyalakan layanan, menjalankan migrasi tambahan tanpa menghapus data, dan menyegarkan cache Laravel. Pada komputer lain, siapkan `backend/.env` dengan APP_KEY dan kredensial database yang benar. Backend memakai host database `db` dan port internal `5432`. Jika dependensi PHP belum tersedia, jalankan `docker compose run --rm backend composer install --no-interaction --optimize-autoloader` terlebih dahulu. Database baru kosong memerlukan setup data/akun terpisah; script tidak menjalankan seeder atau mereset password/data lama.

Buka `http://localhost:5173` pada server atau `http://IP-KOMPUTER-SERVER:5173` dari perangkat di Wi-Fi yang sama. Izinkan port TCP 5173 pada firewall server bila diperlukan. IP server harus sesuai jaringan aktif; folder aplikasi tidak dapat mengubah pengaturan Wi-Fi/firewall komputer lain. Backend port 8000 dan database port 5436 hanya dibuka pada loopback server. Akses pengguna cukup melalui 5173.

Perubahan frontend perlu build ulang melalui script di atas. Instalasi dependensi saat build membutuhkan internet; penggunaan aplikasi setelah build tidak lagi mengambil font, ikon, atau avatar dari internet. Aset Vite ber-hash memakai cache satu tahun; HTML dan aset template tanpa hash direvalidasi dengan ETag agar versi terbaru dapat dimuat.

Jangan menjalankan `docker compose down -v` jika ingin mempertahankan database. Memindahkan folder saja tidak memindahkan volume PostgreSQL: pindahkan database melalui backup/restore serta folder `backend/storage/app/public` untuk evidence.

## Institusi pendidikan pada data pelamar

Kolom institusi kini menampilkan maksimal 10 saran setelah minimal 2 karakter, dengan jeda pencarian 300 ms. Pilih saran menggunakan klik atau tombol panah dan Enter. Nama yang belum ada boleh tetap diketik; master institusi baru dibuat bersamaan dengan penyimpanan pelamar yang berhasil. Mengetik, membatalkan formulir, atau validasi formulir yang gagal tidak menambah master.

Migrasi `2026_11_10_000001_create_institusi_pendidikan` mengambil nama institusi dari data lama dan menghubungkannya ke master tanpa mengubah nama pada catatan lama. Huruf besar/kecil dan spasi berlebih dianggap nama yang sama; singkatan dan variasi ejaan tetap dianggap berbeda. Nama institusi boleh dikosongkan dan master yang sudah ada tetap tersedia untuk pelamar lainnya. Jalankan script deployment di atas untuk menerapkan migrasi dan build frontend saat memperbarui aplikasi di PC kantor.

## Development opsional

```sh
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d frontend
```

Vite diproksikan ke backend Docker untuk `/api` dan `/storage`. Backend tetap memakai pengaturan deployment standar. Kembali ke mode LAN dengan `sh scripts/start-lan.sh`. Untuk perubahan `.env`, segarkan cache konfigurasi Laravel; nilai APP_ENV/APP_DEBUG/LOG_LEVEL pada Compose mengungguli `.env`.

## Validasi yang telah dilakukan

- Build production berhasil; tiga tes loader frontend dan dua tes Laravel bawaan lolos.
- Tujuh respons API dibandingkan sebelum/sesudah: summary, monthly, weekly, distribution, recent-surat, daftar surat masuk, dan daftar surat keluar identik pada parameter pengujian.
- Rute SPA langsung, aset awal, autentikasi melalui `/api`, batas pagination dan pemisahan halaman diverifikasi melalui HTTP.
- Evidence dari proxy frontend identik byte demi byte dengan respons backend.
- Grafik mingguan memakai tiga query agregasi, menggantikan 21 COUNT. Indeks tanggal, urutan, serta relasi disposisi ditambahkan lewat migrasi.
- CSS utama ditransfer sekitar 114 KB setelah gzip (sebelumnya 787 KB); ApexCharts sekitar 128 KB (sebelumnya 492 KB) dan hanya dimuat saat dashboard dibuka. Ini ukuran HTTP aktual lokal, bukan klaim waktu muat pada Wi-Fi sasaran.

Pengujian visual/interaksi browser belum selesai karena fasilitas browser pengujian tidak tersedia. Pengukuran Wi-Fi dan beban banyak pengguna tetap perlu dilakukan pada komputer ruangan. Jumlah worker PHP tidak dinaikkan tanpa pengukuran kapasitas RAM. Statistik tidak diberi cache agar tetap mencerminkan perubahan data terbaru.

Build dependency yang ada juga melaporkan tujuh temuan npm berlevel high saat instalasi; pembaruan dependency untuk temuan tersebut belum termasuk perubahan performa ini.

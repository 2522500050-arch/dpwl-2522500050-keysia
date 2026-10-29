# pertemuan-07

A. Memastikan Artefak P6 Siap Digunakan

A.1. Memeriksa Kesiapan Aplikasi P6
Sebelum memulai pengembangan fitur transaksi pada Pertemuan 7 (P7), dilakukan pemeriksaan kesiapan terhadap aplikasi baseline hasil pengembangan Pertemuan 6 (P6) pada workspace dpwl-nim.

Langkah-Langkah Pemeriksaan:
Workspace: Membuka workspace utama dpwl-nim menggunakan Visual Studio Code.
Server Lokal: Menjalankan Laragon dan memastikan layanan Apache serta MySQL aktif, kemudian mengakses aplikasi melalui URL lokal http://localhost/dpwl-nim/.

Koneksi Database: Memastikan koneksi basis data MySQL berjalan tanpa galat (error).

Pengujian Fitur P6:

[x] Autentikasi Admin: Login, pengelolaan admin, dan logout berjalan normal.

[x] Autentikasi Penyewa: Registrasi, login penyewa, dan logout berjalan normal.

[x] Katalog & Detail Kendaraan: Halaman katalog dan detail kendaraan menampilkan data serta foto kendaraan dengan benar.

[x] Pengelolaan Data Kendaraan (Admin): Fungsi CRUD kendaraan beserta unggah/hapus foto unit berjalan dengan baik.

[x] Profil Penyewa: Tampilan profil penyewa dan pembaruan data (termasuk unggah KTP/SIM) berjalan normal.

Catatan Hasil Pemeriksaan P6:
Seluruh fitur dasar dari P6 telah diuji dan berfungsi dengan baik tanpa adanya kendala atau galat. Workspace siap digunakan sebagai baseline pengembangan fitur transaksi P7.

A.2. Menyimpan Hasil Praktikum dan Memperbarui Git

Setelah memastikan seluruh fitur P6 berfungsi normal, dilakukan pengarsipan baseline awal untuk P7 dan sinkronisasi repositori Git dengan langkah-langkah berikut:

Dokumentasi: Memperbarui berkas dpwl-nim-nama/pertemuan-07/README.md dengan catatan hasil pengujian kesiapan P6.

Pengarsipan Workspace: Menyalin seluruh berkas dari workspace utama dpwl-nim/ ke folder arsip dpwl-nim-nama/pertemuan-07/.

Sinkronisasi Git: Menjalankan perintah Git pada terminal untuk menyimpan perubahan ke repositori:

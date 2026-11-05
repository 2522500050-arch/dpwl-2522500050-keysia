# pertemuan-08

Tahap awal
Dokumentasi Workflow: Mencatat setiap alur pengerjaan fitur baru (Manajemen Penyewa) dan hasil pengujian fungsi CRUD berbasis MVC. Persyaratan Penilaian: Memenuhi poin kelengkapan tugas UTS sesuai bobot penilaian yang ditentukan. Riwayat Pengembangan (Traceability): Menjelaskan ringkasan perubahan kode yang menyertai setiap riwayat commit dan push pada repositori GitHub.

Tahap nomor 1
Menambahkan satu item menu navigasi baru bernama Manajemen Penyewa pada tata letak (layout) navigasi khusus admin. Menu ini berfungsi mengarahkan pengguna ke halaman utama pengelolaan data penyewa.

Tahap 2 - Soal 1: Menu Navigasi
views/templates/sidebar.php : ditambah menu Manajemen Penyewa (ikon fa-address-card) setelah menu Manajemen Kendaraan.
config/routes.php : ditambah route berikut.
URL Tujuan
admin/penyewa Mpenyewa_controller/index (daftar)
admin/penyewa/tambah Mpenyewa_controller/tambah
admin/penyewa/ubah/(:any) Mpenyewa_controller/ubah/
admin/penyewa/hapus/(:any) Mpenyewa_controller/hapus/

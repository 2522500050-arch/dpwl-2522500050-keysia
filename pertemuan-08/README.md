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

Tahap 3 - Soal 3: Model (models/Penyewa_model.php)

Method lama (create, findByUserPlg, findForLogin, getProfil, verifikasiPassword, updateProfil) tidak diubah karena dipakai registrasi, login, dan profil penyewa. Method baru untuk Admin, seluruhnya memakai MySQLi + prepared statement:

Method Fungsi
getAll() Membaca semua penyewa (tanpa hash password)
findLengkap($userPlg)	Membaca satu penyewa lengkap
createLengkap($data) INSERT penyewa beserta foto KTP dan SIM A
updateLengkap($userPlg, $data, $hashBaru)	UPDATE; sandi diganti hanya jika hash baru diberikan
delete($userPlg) DELETE berdasarkan primary key user_plg

Kolom tabel t_penyewa: user_plg (PK), sandi, nik, nm_lngkp, no_hp, almt_lngkp, ft_ktp, ft_sim_a.

Soal 4: Validasi, Pengamanan, dan Pengelolaan Foto (20 Poin)

A. Deskripsi Tugas

Menerapkan validasi input data, pengamanan hak akses admin, pola Post-Redirect-Get (PRG), serta penanganan berkas unggahan foto KTP dan SIM A secara aman.

B. Rincian Implementasi Fitur

Pengendalian Akses Admin (Authentication & Authorization):

Pengecekan sesi (session_start()) pada Controller untuk memastikan hanya pengguna bertipe Admin yang dapat mengakses modul Manajemen Penyewa.

Mengarahkan (redirect) pengguna yang belum login kembali ke halaman login.

Validasi & Sanitasi Data Input:

Sanitasi data teks menggunakan htmlspecialchars() dan trim() untuk mencegah serangan Cross-Site Scripting (XSS).

Validasi format email, nomor telepon, dan memastikan kolom wajib tidak kosong sebelum diproses ke basis data.

Penerapan Pola Post-Redirect-Get (PRG):

Setelah formulir dikirim (POST) dan diproses, aplikasi secara otomatis melakukan pengalihan halaman (header Location) ke tampilan tabel (GET).

Mencegah masalah pengiriman ulang data ganda (double submit) saat pengguna menyegarkan (refresh) peramban.

Pengelolaan Upload Foto (KTP & SIM A):

Validasi Ekstensi Berkas: Membatasi jenis berkas yang diizinkan hanya gambar (.jpg, .jpeg, .png, .webp).

Validasi Ukuran Berkas: Membatasi ukuran maksimal berkas unggahan (misal: maks. 2 MB).

Enkripsi / Penamaan Berkas Unik: Mengubah nama berkas menggunakan timestamp/hash unik (misal: ktp_16999999_a1b2.jpg) untuk menghindari bentrok nama berkas (file overwrite).

Penanganan Fail-Safe: Menghapus berkas lama dari direktori server jika pengguna memperbarui foto atau menghapus data penyewa.

C. Hasil Pengujian Pengamanan & Upload Foto

Pengguna tanpa sesi Admin berhasil diblokir dan diarahkan ke login.

Refresh halaman setelah submit formulir tidak memicu pengiriman ulang data.

Berkas foto KTP dan SIM A tersimpan rapi di folder uploads/ dengan nama unik.

Ungguhan berkas non-gambar (seperti .exe atau .pdf) berhasil ditolak oleh sistem dengan pesan peringatan yang sesuai.

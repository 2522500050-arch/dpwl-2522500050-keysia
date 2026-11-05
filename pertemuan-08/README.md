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

Tahap 5 - Soal 2: View (views/admin/penyewa_view.php)

Mengikuti pola mobil_view.php: form dan tabel dalam satu View.

Form tambah/ubah: username (hanya baca saat ubah), password dan konfirmasi, NIK, nama, no. HP, alamat, unggah Foto KTP dan Foto SIM A dengan pratinjau.
Tabel DataTables: No, Username, NIK, Nama, No. HP, Alamat, Foto KTP, Foto SIM A, Aksi.
Semua keluaran memakai htmlspecialchars().
Pesan flash dan pesan error validasi ditampilkan di atas form.

Tahap 6 - Soal 5: Hasil pengujian

Diuji di http://localhost/dpwl-2522500050/ setelah login admin. Isi kolom Hasil sesuai kenyataan pengujian (Berhasil / Gagal + catatan).

No Skenario Hasil yang diharapkan Hasil
1 Menu Manajemen Penyewa di sidebar diklik Halaman daftar penyewa terbuka
2 Buka admin/penyewa tanpa login Diarahkan ke halaman login
3 Tampilan daftar penyewa Tabel berisi seluruh data, foto tampil
4 Tambah penyewa data valid + foto KTP dan SIM Data tersimpan, pesan sukses, foto tampil
5 Tambah dengan username yang sudah ada Ditolak: "Username sudah digunakan."
6 Tambah dengan NIK 15 digit atau berisi huruf Ditolak, pesan validasi NIK
7 Tambah dengan password kurang dari 8 karakter Ditolak, pesan validasi password
8 Tambah dengan konfirmasi password berbeda Ditolak, pesan konfirmasi tidak sama
9 Tambah dengan no. HP berisi huruf Ditolak, pesan validasi no. HP
10 Unggah berkas bukan gambar (misal .pdf atau .txt yang diganti nama .jpg) Ditolak, pesan format foto
11 Unggah gambar lebih dari 5 MB Ditolak, pesan ukuran foto
12 Tambah tanpa foto Data tersimpan, tabel menampilkan "Belum ada foto"
13 Cek password di basis data Tersimpan sebagai hash (diawali $2y$)
14 Ubah data tanpa mengisi password Data berubah, password lama tetap bisa login
15 Ubah dengan password baru Password berubah, login penyewa dengan password baru berhasil
16 Ubah dengan mengganti foto KTP Foto baru tampil, file foto lama terhapus dari uploads/penyewa/
17 Hapus penyewa tanpa transaksi Data dan foto terhapus, pesan sukses
18 Hapus penyewa yang punya transaksi Gagal dengan pesan jelas, data tidak rusak
19 Muat ulang halaman setelah tambah/ubah/hapus Tidak ada kirim ulang form (PRG berjalan)
20 Fitur lama: login admin, Manajemen Admin Tetap berjalan
21 Fitur lama: Manajemen Kendaraan (tambah, ubah, hapus) Tetap berjalan
22 Fitur lama: katalog, login/registrasi/profil penyewa, keranjang Tetap berjalan

Catatan temuan dan perbaikan selama pengujian:

(isi jika ada, misalnya: "Skenario 6 awalnya lolos, diperbaiki dengan ...")

Tahap 7 - Soal 6: Ekspor basis data dan struktur berkas

Basis data diekspor dari phpMyAdmin (Export, metode Custom, format SQL) setelah pengujian selesai dan disimpan di repositori sebagai: ISI-NAMA-FILE.sql.

Berkas yang ditambah atau diubah pada UTS ini:

Berkas Status
application/controllers/Mpenyewa_controller.php Baru
application/views/admin/penyewa_view.php Baru
application/models/Penyewa_model.php Diubah (method baru ditambahkan)
application/config/routes.php Diubah (4 route baru)
application/views/templates/sidebar.php Diubah (menu baru)
application/controllers/Mobil_controller.php Diubah (perbaikan bug tambah)

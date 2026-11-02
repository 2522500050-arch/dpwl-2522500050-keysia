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

B. Membuat Model dan Controller Transaksi serta Route Tambah ke Keranjang

B.1. Membuat Berkas application/models/Transaksi_model.php

Pada tahap awal pembuatan komponen transaksi, dibuat model Transaksi_model.php yang memuat fondasi logika transaksi dan mekanisme keamanan akses bersamaan (concurrency control) menggunakan fitur kunci berbasis fungsi MySQL (GET_LOCK & RELEASE_LOCK).

1. Inisialisasi Berkas & Koneksi Database

Berkas dibuat di lokasi application/models/Transaksi_model.php dengan struktur dasar untuk menghubungkan objek mysqli global $db ke properti kelas:

<?php
class Transaksi_model
{
    private mysqli $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }
}


2. Implementasi Penguncian Proses (Locking Mechanism)

Untuk mengendalikan race condition saat pembuatan kode transaksi secara simultan oleh multiple user, ditambahkan metode penguncian ambilKunci() dan pelepasan kunci lepasKunci():

ambilKunci(string $namaKunci, int $timeout = 10): void
Mengeksekusi kueri SELECT GET_LOCK(?, ?) dengan batas waktu (timeout) 10 detik. Jika penguncian gagal diperoleh, sistem melemparkan RuntimeException.

lepasKunci(string $namaKunci): void
Mengeksekusi kueri SELECT RELEASE_LOCK(?) untuk melepaskan kunci yang dipegang oleh thread saat ini setelah operasi selesai.

3. Penataan Format dan Pembuatan Kode Transaksi Otomatis

Ditambahkan metode kunciDanBuatKodeSewa(): array untuk menghasilkan kode transaksi dengan format unik harian INV/YYYYMMDD/NN:

Format Tanggal: Diambil menggunakan DateTimeImmutable dengan zona waktu Asia/Jakarta (Ymd).

Format Penomoran: Menggunakan pola urutan 2 digit (01 s.d. 99) setiap harinya.

Mekanisme Penguncian:

Mengunci nama kunci berbasis tanggal: dpwl_inv_YYYYMMDD.

Mencari nomor transaksi terakhir pada hari yang sama dari tabel t_sewa menggunakan kueri WHERE kode_sewa LIKE 'INV/YYYYMMDD/%' ORDER BY kode_sewa DESC LIMIT 1.

Mengincrement nomor terakhir. Jika sudah melebihi 99, dilemparkan RuntimeException.

Mengembalikan array berisi kode_sewa baru dan nama_kunci yang masih dipegang.

Seluruh kesalahan ditangkap (exception handling) dan melepaskan kunci secara otomatis sebelum meneruskan exception.

Catatan Teknis B.1:

Kode pada tahap ini merupakan fondasi dasar model transaksi dan belum mencakup metode penambahan item ke keranjang secara lengkap. Kunci transaksi harus tetap dipegang hingga seluruh alur penyimpanan header transaksi selesai pada tahap selanjutnya.

Bagian B.2: Membuat Berkas application\controllers\Transaksi_controller.php
Pada tahap ini, dilakukan pembuatan controller transaksi yang berfungsi untuk menangani permintaan penyewa dalam menambahkan kendaraan ke keranjang sewa. Controller ini bertugas memeriksa autentikasi login penyewa, memastikan metode permintaan HTTP yang digunakan, meneruskan data ke model transaksi, serta mengembalikan penyewa ke halaman asal dengan pesan hasil proses.

Bagian B.3: Menambahkan Route Transaksi
Pada tahap ini, dilakukan penambahan route baru untuk menghubungkan URL permintaan aplikasi dengan method yang ada di dalam controller transaksi. Route ini digunakan untuk memproses pengiriman data formulir penambahan kendaraan dari halaman katalog maupun detail kendaraan ke metode tambah() pada controller transaksi.

Bagian C.1: Mengubah Formulir pada application\views\katalog\detail.php

1. Deskripsi Perubahan

Pada tahap ini, dilakukan penyesuaian pada formulir tombol penambahan ke keranjang (Add to Cart) di halaman detail kendaraan (application\views\katalog\detail.php). Perubahan ini bertujuan untuk mengintegrasikan tombol keranjang dengan kontrol hak akses penyewa dan status ketersediaan unit kendaraan.

Formulir ini dikondisikan ke dalam 3 status tampilan:

Penyewa Belum Login: Menampilkan pesan notifikasi untuk login/registrasi beserta tombol pengarah ke auth/login-penyewa dan auth/register.

Kendaraan Tidak Tersedia: Menampilkan notifikasi (alert) bahwa kendaraan tidak dapat ditambahkan ke keranjang karena status unit tidak tersedia.

Penyewa Sudah Login & Kendaraan Tersedia: Menampilkan formulir POST yang mengarah ke site_url('transaksi/tambah') dengan membawa input tersembunyi (no_plat dan return_to = detail) serta tombol Tambah ke Keranjang.

2. Berkas yang Diubah

application\views\katalog\detail.php

3. Perubahan Kode (Diff/Snippet)

Mengganti blok formulir standar Add To Cart sebelumnya dengan kondisional PHP berikut:

<?php if (!$statusTersedia): ?>

    <div class="alert alert-secondary" role="status">
        Kendaraan ini tidak dapat ditambahkan ke keranjang karena statusnya tidak tersedia.
    </div>

<?php elseif (empty($_SESSION['penyewa_user_plg'])): ?>

    <div class="alert alert-info" role="status">
        Silakan login sebagai penyewa atau registrasi akun terlebih dahulu untuk menambahkan kendaraan ke keranjang.
    </div>
    <div class="row g-2 pb-3">
        <div class="col-md-6 d-grid">
            <a href="<?= htmlspecialchars(site_url('auth/login-penyewa'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success btn-lg">
                Login Penyewa
            </a>
        </div>
        <div class="col-md-6 d-grid">
            <a href="<?= htmlspecialchars(site_url('auth/register'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-success btn-lg">
                Registrasi
            </a>
        </div>
    </div>

<?php else: ?>

    <form action="<?= htmlspecialchars(site_url('transaksi/tambah'), ENT_QUOTES, 'UTF-8') ?>" method="POST">
        <input type="hidden" name="no_plat" value="<?= htmlspecialchars((string) ($mobil['no_plat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="return_to" value="detail">
        <div class="d-grid pb-3">
            <button type="submit" class="btn btn-success btn-lg">
                Tambah ke Keranjang
            </button>
        </div>
    </form>

<?php endif; ?>

4. Hasil Pengujian Tampilan dan Fungsionalitas

Pengujian dilakukan melalui peramban web pada URL http://localhost/dpwl-[NIM]/katalog/detail/[NO_PLAT]:

Kondisi Belum Login:

Menampilkan notifikasi info dan tombol opsi Login Penyewa & Registrasi.

Kondisi Kendaraan Tidak Tersedia:

Menampilkan notifikasi berwarna abu-abu (alert-secondary) bahwa unit tidak tersedia.

Kondisi Sudah Login (Penyewa) & Unit Tersedia:

Tombol hijau bertuliskan Tambah ke Keranjang tampil aktif dan siap mengirimkan data via metode POST.

Bagian C.2: Mengubah Formulir pada application\views\katalog\index.php

1. Deskripsi Perubahan

Pada tahap ini, dilakukan penyesuaian pada tombol penambahan kendaraan di halaman katalog kendaraan (application\views\katalog\index.php). Perubahan ini bertujuan untuk mengondisikan status tombol berdasarkan ketersediaan unit kendaraan dan status autentikasi penyewa saat berada di tampilan katalog utama.

Tampilan tombol pada kartu kendaraan (card item) dikondisikan sebagai berikut:

Kendaraan Tersedia & Penyewa Sudah Login: Menampilkan formulir dengan metode POST mengarah ke route transaksi/tambah dengan data tersembunyi no_plat dan return_to = katalog. Tombol Tambah ke Keranjang berada dalam kondisi aktif.

Kendaraan Tersedia & Penyewa Belum Login: Menampilkan tombol Tambah ke Keranjang dalam keadaan nonaktif (disabled).

Kendaraan Tidak Tersedia / Sedang Disewa: Menampilkan tombol nonaktif (disabled) bertuliskan Tidak Dapat Disewa dengan warna tombol sekunder (btn-secondary).

2. Berkas yang Diubah

application\views\katalog\index.php

3. Perubahan Kode (Diff/Snippet)

Mengganti blok kondisi tombol pada perulangan foreach ($daftarMobil as $mobil) di application\views\katalog\index.php:

<?php if ($mobil['status_unit'] === 'tersedia'): ?>

    <?php if (!empty($_SESSION['penyewa_user_plg'])): ?>
        <form action="<?= htmlspecialchars(site_url('transaksi/tambah'), ENT_QUOTES, 'UTF-8') ?>" method="POST">
            <input type="hidden" name="no_plat" value="<?= htmlspecialchars((string) $mobil['no_plat'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="return_to" value="katalog">
            <button type="submit" class="btn btn-success w-100">
                Tambah ke Keranjang
            </button>
        </form>
    <?php else: ?>
        <button type="button" class="btn btn-success w-100" disabled>
            Tambah ke Keranjang
        </button>
    <?php endif; ?>

<?php else: ?>

    <button type="button" class="btn btn-secondary w-100" disabled>
        Tidak Dapat Disewa
    </button>

<?php endif; ?>

4. Hasil Pengujian Tampilan dan Fungsionalitas

Pengujian dilakukan melalui peramban web pada URL http://localhost/dpwl-[NIM]/katalog:

Kondisi Belum Login:

Tombol Tambah ke Keranjang untuk unit yang berstatus tersedia tampil dalam keadaan nonaktif (disabled).

Kondisi Sudah Login sebagai Penyewa:

Tombol Tambah ke Keranjang untuk unit yang berstatus tersedia berubah menjadi tombol aktif dan siap mengirimkan formulir penambahan keranjang via POST.

Kendaraan Tidak Tersedia:

Kendaraan berstatus tidak tersedia atau sedang disewa menampilkan tombol nonaktif bertuliskan Tidak Dapat Disewa.

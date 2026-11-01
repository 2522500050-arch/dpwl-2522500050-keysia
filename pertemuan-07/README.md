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

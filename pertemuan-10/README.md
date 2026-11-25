# pertemuan-10

# Pertemuan 10 (P10) - Pelunasan dan Cetakan / Dokumen Transaksi Berbasis MVC

## Identitas Mahasiswa

- **NIM:** [2522500050]
- **Nama:** [Keysia Riski Amalia]
- **Kelas / Kelompok:** [SI3A]
- **Mata Kuliah:** Desain dan Pemrograman Web Lanjutan (IS203)
- **Dosen Pengampu:** Yohanes Setiawan Japriadi, M.Kom.

---

## Kondisi Awal & Pemeriksaan Artefak P9

Aplikasi pada P10 melanjutkan _baseline_ dari folder `pertemuan-09\`. Hasil pemeriksaan awal fitur P9 sebelum pengembangan P10:

- [x] Login sebagai admin dan akses halaman dashboard admin berjalan normal.
- [x] Login sebagai penyewa dan akses katalog/transaksi berjalan normal.
- [x] Penyewa dapat melihat daftar & detail pembayaran DP serta mengunggah bukti transfer.
- [x] Admin dapat memverifikasi pembayaran DP hingga status pembayaran menjadi `valid` dan status transaksi menjadi `disetujui`.
- [x] Informasi kendaraan pada detail transaksi tampil dengan benar.

---

## Perubahan Struktur Basis Data

Menambahkan nilai ENUM `'lunas'` pada kolom `status_sewa` di tabel `t_sewa` dengan tetap mempertahankan nilai ENUM sebelumnya (`diajukan`, `disetujui`, `ditolak`, `aktif`, `selesai`, `batal`, `keranjang`). Status kendaraan pada tabel `t_mobil` tidak diubah pada pertemuan ini.

**Perintah SQL yang dijalankan:**

```sql
ALTER TABLE `t_sewa` CHANGE `status_sewa` `status_sewa`
ENUM('diajukan','disetujui','ditolak','aktif','selesai','batal','keranjang','lunas')
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT 'keranjang';
```

## Perubahan Struktur Basis Data (Tahap B)

Pada tahap ini dilakukan penyesuaian struktur basis data pada tabel `t_sewa` untuk mendukung pencatatan status transaksi yang telah melunasi sisa kewajiban pembayaran (`lunas`).

- **Tabel yang Diubah:** `t_sewa`
- **Kolom yang Diubah:** `status_sewa`
- **Detail Perubahan:** Menambahkan nilai ENUM `'lunas'` dengan tetap mempertahankan seluruh nilai ENUM sebelumnya (`diajukan`, `disetujui`, `ditolak`, `aktif`, `selesai`, `batal`, `keranjang`).
- **Catatan Integrasi:** Perubahan hanya dilakukan pada tabel `t_sewa`. Struktur dan status kendaraan pada tabel `t_mobil` tidak diubah pada pertemuan ini karena proses serah terima kendaraan dibahas pada Pertemuan 11.

**Perintah SQL yang Dijalankan:**

```sql
ALTER TABLE `t_sewa` CHANGE `status_sewa` `status_sewa`
ENUM('diajukan','disetujui','ditolak','aktif','selesai','batal','keranjang','lunas')
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NULL DEFAULT 'keranjang';
```

## Implementasi Model Pelunasan (Tahap C)

Pada tahap ini ditambahkan empat _method_ baru di dalam berkas `application\models\Pembayaran_model.php` untuk mengelola logika pelunasan transaksi tanpa menghapus _method_ pembayaran dari P9.

### Daftar Method yang Ditambahkan

1. **`getDaftarPelunasan(): array`**
   - Mengambil daftar transaksi berstatus `disetujui` atau `lunas` yang memiliki detail kendaraan pada `t_pilih`.
   - Menghitung `total_transaksi` berdasarkan _snapshot_ tarif pada `t_pilih` (`hrg_sewa * n_hari`), `total_terbayar` dari pembayaran berstatus `valid` pada `t_bayar`, serta `sisa_kewajiban`.
2. **`getDetailPelunasan(string $kodeSewa): ?array`**
   - Mengambil informasi lengkap transaksi berdasarkan `kode_sewa`, meliputi data penyewa (`t_penyewa`), ringkasan biaya, riwayat pembayaran (`t_bayar`), serta rincian kendaraan yang disewa (`t_pilih` berelasi dengan `t_mobil`).
3. **`buatKodeBayarPelunasan(string $tanggalKode): string`**
   - _Method_ internal (`private`) untuk membuat nomor urut kode pembayaran harian berformat `PAY/YYYYMMDD/NN` (maksimal 99 transaksi per hari) di bawah perlindungan _named lock_ `dpwl_payment_code_sequence`.
4. **`simpanPelunasan(string $kodeSewa, string $metodeBayar, ?string $namaFileBukti = null): string`**
   - Mencatat pelunasan penuh sebesar seluruh sisa kewajiban dengan metode `tunai` atau `transfer` (wajib menyertakan berkas bukti untuk transfer).
   - Dijalankan secara atomik menggunakan transaksi basis data (`begin_transaction`, `SELECT ... FOR UPDATE`, `commit`, dan `rollback`) serta _named lock_ `GET_LOCK` / `RELEASE_LOCK`.
   - Langsung mencatat status pembayaran sebagai `valid` dan mengubah `t_sewa.status_sewa` menjadi `lunas` tanpa mengubah status kendaraan pada tabel `t_mobil`.

### Hasil Pemeriksaan Awal Model (C.5)

- [x] `getDaftarPelunasan()` menyaring transaksi yang memenuhi syarat pelunasan dan menghitung sisa kewajiban dengan benar.
- [x] `getDetailPelunasan()` menyusun data transaksi, penyewa, riwayat pembayaran, dan rincian kendaraan secara lengkap.
- [x] Kode pembayaran yang dihasilkan mengikuti format `PAY/YYYYMMDD/NN` dan menggunakan _named lock_ yang sama dengan P9.
- [x] Nominal pelunasan otomatis menggunakan seluruh nilai sisa kewajiban (`$sisaKewajiban`) dan dibatasi maksimal `8.388.607` (`MEDIUMINT`).
- [x] Validasi menolak metode pembayaran selain `tunai` dan `transfer`, serta menolak transaksi yang sudah memiliki _record_ `jns_byr = 'pelunasan'`.
- [x] Pencatatan pelunasan mengubah `status_sewa` menjadi `lunas` dan mempertahankan status kendaraan pada `t_mobil`.
- [x] Mekanisme `try-catch` dengan `rollback()` diterapkan untuk membatalkan transaksi apabila terjadi kegagalan di tengah proses.

## Implementasi Controller Pelunasan & Dokumen Transaksi (Tahap D)

Pada tahap ini berkas `application\controllers\Pembayaran_controller.php` dikembangkan dengan mengintegrasikan `Pembayaran_model.php` serta menambahkan empat _method_ baru untuk sisi admin tanpa menghapus fitur pembayaran DP penyewa dari P9.

### Daftar Method yang Ditambahkan

1. **`pelunasanIndex(): void`**
   - Memastikan pengguna sudah login sebagai admin melalui `require_admin_login()` dan membatasi metode HTTP ke `GET`.
   - Mengambil daftar transaksi yang dapat dilunasi dari `Pembayaran_model::getDaftarPelunasan()` dan mengirimkan datanya ke _view_ `admin/pelunasan`.
2. **`pelunasanDetail(string $kodeSewa = ''): void`**
   - Mengonversi karakter `~` pada parameter URL kembali menjadi `/`, lalu mengambil data lengkap transaksi dari `Pembayaran_model::getDetailPelunasan($kodeSewa)`.
   - Menampilkan halaman detail dan formulir pelunasan melalui _view_ `admin/pelunasan_detail`, atau mengembalikan respons `404` apabila transaksi tidak ditemukan.
3. **`prosesPelunasan(string $kodeSewa = ''): void`**
   - Memproses pengiriman formulir pelunasan (`POST`) dari admin dengan pilihan metode `tunai` atau `transfer`.
   - Memvalidasi dan mengunggah berkas bukti pembayaran (`ft_byr`, maksimal 5 MB) melalui _helper_ `simpanFileBukti()` khusus untuk metode `transfer`, lalu mencatat pelunasan secara atomik melalui `Pembayaran_model::simpanPelunasan()`.
   - Menerapkan pola _Post-Redirect-Get_ (PRG) melalui `redirectDenganPesan()` serta menghapus berkas bukti baru (`hapusBuktiBaru()`) apabila terjadi kegagalan penyimpanan.
4. **`dokumenTransaksi(string $kodeSewa = ''): void`**
   - Mengambil rincian transaksi, penyewa, kendaraan, dan riwayat pembayaran melalui `getDetailPelunasan()` dan meneruskannya ke _view_ `admin/dokumen_transaksi` untuk dicetak.

### Pemertahanan Fitur P9 & Penyesuaian Integrasi

- Seluruh _method_ pembayaran DP milik penyewa dari P9 (`index()`, `detail()`, `bukti()`, `unggah()`) beserta validasi CSRF dan _helper_ penyimpanan berkas tetap dipertahankan dan berfungsi normal.
- Menambahkan _method_ jembatan `redirectDenganPesan()`, `simpanFileBukti()`, dan `hapusBuktiBaru()` agar selaras dengan _helper_ `bukti_helper.php` dari P9.

### Hasil Pemeriksaan Awal Controller (D.6)

- [x] Seluruh _method_ pelunasan dan dokumen transaksi telah dilindungi oleh `require_admin_login()`.
- [x] Pembatasan metode HTTP (`GET` untuk tampilan halaman dan `POST` untuk pemrosesan pelunasan) telah diterapkan.
- [x] Validasi ketersediaan kode transaksi, pilihan metode (`tunai`/`transfer`), dan unggahan bukti transfer berjalan sesuai ketentuan.
- [x] Penanganan pengecualian (`try-catch`) dan pola PRG telah diterapkan dengan baik.

### E. Membuat Route dan Integrasi Menu Pelunasan

#### 1. Daftar Berkas yang Diubah

- `application\config\routes.php`
- `application\views\templates\sidebar.php`

#### 2. Penambahan Route Baru

Menambahkan rute untuk menghubungkan URL pelunasan dan dokumen transaksi ke `Pembayaran_controller` dengan memperhatikan urutan spesifik ke umum serta mempertahankan seluruh rute P9:

- **Route Daftar Pelunasan:** `$route['admin/pembayaran/pelunasan'] = 'Pembayaran_controller/pelunasanIndex';`
- **Route Proses Pelunasan:** `$route['admin/pembayaran/pelunasan/proses/(:any)'] = 'Pembayaran_controller/prosesPelunasan/$1';`
- **Route Dokumen Transaksi:** `$route['admin/pembayaran/dokumen/(:any)'] = 'Pembayaran_controller/dokumenTransaksi/$1';`
- **Route Detail Pelunasan:** `$route['admin/pembayaran/pelunasan/(:any)'] = 'Pembayaran_controller/pelunasanDetail/$1';`

#### 3. Integrasi Menu Navigasi Admin

- Menambahkan menu **Pelunasan Transaksi** pada `application\views\templates\sidebar.php` yang mengarah ke URL `admin/pembayaran/pelunasan` khusus untuk antarmuka admin.

#### 4. Hasil Pemeriksaan Route dan Navigasi

- Seluruh route baru berhasil didefinisikan tanpa menimpa atau bertentangan dengan route P9.
- Urutan penulisan route sudah sesuai (route spesifik seperti `proses/(:any)` ditempatkan sebelum route umum `pelunasan/(:any)`).
- Route proses pelunasan sudah terhubung ke method `prosesPelunasan()` yang membatasi akses hanya menggunakan metode HTTP `POST`.
- Seluruh method tujuan (`pelunasanIndex`, `pelunasanDetail`, `prosesPelunasan`, `dokumenTransaksi`) sudah diproteksi menggunakan `require_admin_login()`.
- Menu **Pelunasan Transaksi** berhasil tampil pada sidebar halaman admin dan mengarah ke URL yang sesuai.
- **Catatan Pengujian:** Saat tautan menu diklik pada tahap ini, aplikasi menampilkan pesan fallback error (_"Daftar transaksi pelunasan belum dapat ditampilkan. Silakan coba kembali."_) karena berkas View (`admin/pelunasan.php`) belum dibuat dan baru akan diimplementasikan pada tahap F.

### F. Membuat View Daftar dan Detail Pelunasan

#### 1. Daftar Berkas yang Ditambahkan / Diperbaiki

- `application\views\admin\pelunasan.php` (Baru — Halaman daftar transaksi yang dapat diproses pelunasannya)
- `application\views\admin\pelunasan_detail.php` (Baru — Halaman detail transaksi, riwayat pembayaran, dan formulir pencatatan pelunasan)
- `application\models\Pembayaran_model.php` (Perbaikan inisialisasi koneksi database `global $db;` pada `__construct()`)
- `application\models\Transaksi_model.php` (Memastikan deklarasi class sesuai dan tidak tertimpa)

#### 2. Fitur yang Diimplementasikan pada View

- **Daftar Pelunasan (`pelunasan.php`):** Menampilkan daftar transaksi dengan sisa kewajiban pembayaran meliputi kode sewa, identitas penyewa, nomor HP, periode sewa, status transaksi, total transaksi, total terbayar, sisa kewajiban, serta tautan menuju halaman detail.
- **Detail & Form Pelunasan (`pelunasan_detail.php`):**
  - Menampilkan informasi lengkap transaksi, identitas penyewa, rincian kendaraan (tarif snapshot), ringkasan kewajiban, dan riwayat pembayaran (DP maupun pelunasan).
  - Menyediakan formulir pencatatan pelunasan dengan metode `POST` dan `enctype="multipart/form-data"` untuk nominal tetap sebesar seluruh sisa kewajiban.
  - Menggunakan JavaScript dinamis untuk menampilkan input unggah bukti transfer (`ft_byr`) secara wajib hanya saat metode **Transfer** dipilih, dan menyembunyikannya saat metode **Tunai** dipilih.
  - Menyembunyikan formulir pelunasan secara otomatis apabila transaksi sudah memiliki record pelunasan atau berstatus `lunas`.
  - Menerapkan _output escaping_ menggunakan `htmlspecialchars()` pada seluruh data teks untuk mencegah serangan XSS.

#### 3. Hasil Pemeriksaan dan Pengujian Aktual

- Halaman daftar pelunasan (`admin/pembayaran/pelunasan`) berhasil menampilkan daftar transaksi yang masih memiliki sisa kewajiban pembayaran.
- Tautan **Detail / Proses** berhasil mengarahkan ke halaman detail transaksi yang sesuai.
- Pemilihan metode **Tunai** menyembunyikan input bukti transfer, sedangkan metode **Transfer** menampilkan dan mewajibkan unggahan bukti transfer.
- Proses pencatatan pelunasan berhasil dijalankan dengan metode `POST`, menampilkan pesan notifikasi sukses, mengubah status transaksi menjadi `lunas`, memperbarui sisa kewajiban menjadi `Rp 0`, dan menyembunyikan formulir pelunasan agar tidak terjadi pelunasan ganda.
- Fitur pembayaran dari Pertemuan 9 (verifikasi DP admin dan unggah bukti DP penyewa) tetap berjalan dengan baik tanpa gangguan.

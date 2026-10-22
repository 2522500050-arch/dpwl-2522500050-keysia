# pertemuan-05

Berikut draft penjelasan untuk poin **A.5** yang dapat dimasukkan ke dalam berkas `README.md`:

---

### Tahap A: Integrasi Menu Navigasi Admin dan Konfigurasi Route Awal

**Deskripsi Perubahan:**
Mengintegrasikan menu baru **Manajemen Kendaraan** ke dalam sidebar panel admin serta menambahkan konfigurasi _route_ dasar untuk mengarahkan navigasi tersebut ke _controller_ kendaraan.

**Berkas yang Terdampak:**

- `application/views/templates/sidebar.php` (Penambahan struktur menu Manajemen Kendaraan)
- `application/config/routes.php` (Penambahan route `$route['admin/mobil'] = 'Mobil_controller/index';`)

**Hasil Pengujian:**

- Menu **Manajemen Kendaraan** berhasil muncul di bawah kelompok menu Manajemen Admin pada _sidebar_.
- Ketika menu diklik, URL pada peramban berhasil berubah menjadi `admin/mobil`.
- Tampilan sistem mengeluarkan pesan _"Controller tidak ditemukan."_. Hal ini merupakan kondisi yang diharapkan (normal) karena file `Mobil_controller.php` belum dibuat pada tahap ini.

Berikut draft penjelasan untuk poin **B.3** yang dapat dimasukkan ke dalam berkas `README.md`:

---

### Tahap B: Menambahkan Route Manajemen Kendaraan (Tambah, Ubah, Hapus)

**Deskripsi Perubahan:**
Melengkapi konfigurasi routing aplikasi pada `routes.php` dengan menambahkan route untuk penanganan operasi CRUD data kendaraan (tambah, ubah, dan hapus). Parameter `(:any)` digunakan pada route ubah dan hapus untuk menerima identitas kendaraan (`no_plat`) dari URL.

**Berkas yang Terdampak:**

- `application/config/routes.php`

**Penambahan Route:**

```php
$route['admin/mobil/tambah'] = 'Mobil_controller/tambah';
$route['admin/mobil/ubah/(:any)'] = 'Mobil_controller/ubah/$1';
$route['admin/mobil/hapus/(:any)'] = 'Mobil_controller/hapus/$1';

```

**Hasil Pengujian:**

- Akses URL ke `admin/mobil/tambah`, `admin/mobil/ubah/1`, maupun `admin/mobil/hapus/1` dapat diproses oleh _router_ aplikasi.
- Tampilan sistem tetap mengeluarkan pesan _"Controller tidak ditemukan."_. Hal ini merupakan kondisi yang diharapkan (normal) pada tahap B karena berkas `Mobil_controller.php` belum dibuat dan baru akan diimplementasikan pada tahap selanjutnya (Tahap C).

### Tahap C: Mengimplementasikan READ Data Kendaraan dengan MVC

**Deskripsi Perubahan:**
Mengimplementasikan fitur untuk membaca dan menampilkan daftar data kendaraan dari basis data (`t_mobil`) menggunakan arsitektur MVC.

**Berkas yang Ditambahkan / Dimodifikasi:**

- `application/models/Mobil_model.php` (Membuat model & method `getAll()` untuk query `t_mobil`)
- `application/controllers/Mobil_controller.php` (Membuat controller, pengecekan autentikasi admin, dan method `index()`)
- `application/views/admin/mobil_view.php` (Membuat view tabel daftar kendaraan lengkap dengan format status, foto, dan tombol aksi)

**Hasil Pengujian:**

- Navigasi ke `admin/mobil` berhasil menampilkan tabel data kendaraan.
- Data dari tabel `t_mobil` muncul sesuai dengan kolom masing-masing (No. Plat, Nama Mobil, Kursi, Bagasi, Jenis, Transmisi, Tahun, Harga, Status, Foto).
- Jika data di tabel kosong, halaman tetap dapat dirender dengan rapi.
- Proteksi autentikasi berfungsi (hanya admin tersesi yang bisa mengakses halaman).

### Tahap D: Mengimplementasikan INSERT Data Kendaraan dengan MVC

**Deskripsi Perubahan:**
Mengimplementasikan fitur penambahan data kendaraan baru (INSERT) menggunakan pola MVC. Fitur ini mencakup pembuatan formulir tambah data, validasi server-side, penanganan unggah berkas foto kendaraan (depan, belakang, interior), serta pengamanan query menggunakan prepared statements.

**Berkas yang Ditambahkan / Dimodifikasi:**

- `application/models/Mobil_model.php` (Menambahkan method `create()` untuk query INSERT data kendaraan)
- `application/controllers/Mobil_controller.php` (Menambahkan method `tambah()`, `renderMobil()`, `validateMobilInput()`, `uploadFoto()`, dan `hapusFotoBaru()`)
- `application/views/admin/mobil_view.php` (Menambahkan elemen formulir tambah data kendaraan dan skrip JavaScript untuk live preview foto)

**Hasil Pengujian:**

- Formulir tambah data dapat diakses dan berhasil mengirimkan data melalui method POST.
- Validasi server-side berhasil mencegat data yang tidak valid (seperti nomor plat kosong atau ukuran file melebihi batas).
- Berkas foto berhasil terunggah dan tersimpan di folder `uploads/mobil/` dengan nama unik.
- Data kendaraan baru berhasil masuk ke dalam database `t_mobil` dan langsung tampil pada tabel daftar kendaraan.

### Tahap E: Mengimplementasikan UPDATE Data Kendaraan dengan MVC

**Deskripsi Perubahan:**
Mengimplementasikan fitur pengubahan data kendaraan (UPDATE) menggunakan pola MVC. Fitur ini mencakup pengambilan data lama berdasarkan nomor plat (Primary Key), pengisian otomatis ke formulir edit, penanganan pembaruan berkas foto (menghapus foto lama jika diganti foto baru), serta eksekusi query UPDATE menggunakan prepared statement.

**Berkas yang Ditambahkan / Dimodifikasi:**

- `application/models/Mobil_model.php` (Menambahkan method `findByNoPlat()` untuk pencarian data dan `update()` untuk query pembaruan data)
- `application/controllers/Mobil_controller.php` (Menambahkan method `ubah()` untuk memproses pencarian, validasi, penggantian file foto lama, dan pembaruan data)
- `application/views/admin/mobil_view.php` (Menyesuaikan formulir agar mendukung mode edit dengan menampilkan data lama dan pratinjau foto tersimpan)

**Hasil Pengujian:**

- Tombol "Ubah" pada tabel kendaraan berfungsi dengan benar dan mengarahkan ke URL `admin/mobil/ubah/{no_plat}`.
- Formulir berhasil terisi otomatis (_auto-fill_) dengan data kendaraan yang dipilih.
- Jika foto tidak diubah, foto lama tetap dipertahankan; jika diunggah foto baru, foto lama di server otomatis terhapus dan digantikan foto baru.
- Pesan notifikasi _flash message_ ("Data kendaraan berhasil diperbarui.") berhasil muncul setelah proses pengubahan berhasil.

### Tahap F: Mengimplementasikan DELETE Data Kendaraan dengan MVC

**Deskripsi Perubahan:**
Mengimplementasikan fitur penghapusan data kendaraan (DELETE) menggunakan pola MVC. Fitur ini menjamin proteksi sesi autentikasi admin, pembatasan metode HTTP (hanya menerima request POST), penghapusan records pada database `t_mobil`, serta pembersihan berkas foto fisik (depan, belakang, interior) pada direktori `uploads/mobil/`.

**Berkas yang Ditambahkan / Dimodifikasi:**

- `application/models/Mobil_model.php` (Menambahkan method `delete()` menggunakan prepared statement)
- `application/controllers/Mobil_controller.php` (Menambahkan method `hapus()` untuk validasi login, pengecekan metode request POST, eksekusi query hapus, dan pembersihan file foto via `hapusFotoBaru()`)

**Hasil Pengujian:**

- Pengajuan request DELETE selain metode POST berhasil dicegat dengan pesan error "Metode request tidak diizinkan."
- Tombol "Hapus" pada tabel berhasil memicu konfirmasi penghapusan dan menghapus data kendaraan terpilih.
- Data kendaraan berhasil terhapus dari tabel database `t_mobil`.
- Berkas foto yang berkaitan dengan kendaraan tersebut (foto depan, belakang, interior) di direktori `uploads/mobil/` berhasil terhapus secara otomatis.
- Jika terjadi kegagalan pada query database, berkas foto tetap dipertahankan dan sistem menampilkan pesan peringatan.
- Notifikasi _flash message_ ("Data kendaraan berhasil dihapus.") tampil setelah pengalihan halaman kembali ke daftar kendaraan.

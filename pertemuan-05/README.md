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

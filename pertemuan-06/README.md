# pertemuan-06

## A. Mengintegrasikan TemplateMo sebagai Antarmuka Sisi Pengguna

### A.1. Memeriksa Struktur TemplateMo Zay Shop dan Aset yang Digunakan

- **Sumber Templat**: TemplateMo 559 - Zay Shop (`templatemo_559_zay_shop.zip`).
- **Tujuan Integrasi**: Digunakan sebagai dasar antarmuka sisi pengguna (_front-end_) aplikasi rental kendaraan.
- **Hasil Identifikasi Struktur Berkas**:
  - `index.html`: Referensi untuk tata letak (_layout_) utama, header, navigasi, carousel/hero section, dan footer.
  - `shop.html`: Referensi untuk antarmuka katalog kendaraan (kartu produk/unit).
  - `shop-single.html`: Referensi untuk antarmuka detail informasi kendaraan.
  - `assets/css/`: Berisi berkas penataan gaya seperti `bootstrap.min.css`, `templatemo.css`, `custom.css`, `fontawesome.css`, dll.
  - `assets/js/`: Berisi skrip interaktif seperti `bootstrap.bundle.min.js`, `jquery-1.11.0.min.js`, `custom.js`, `slick.min.js`, dll.
  - `assets/img/`: Berisi aset gambar bawaan templat.
  - `assets/webfonts/`: Berisi berkas font dan font ikon (FontAwesome).

### A.2. Mengidentifikasi Komponen Halaman, Navigasi, dan Konten

- **Header & Navigasi**: Logo Zay Shop akan disesuaikan menjadi identitas aplikasi rental kendaraan, serta menu navigasi disesuaikan untuk menghubungkan halaman Beranda, Katalog Kendaraan, Login, Registrasi, atau Profil Penyewa.
- **Katalog (`shop.html`)**: Kartu produk toko pakaian diubah strukturnya menjadi kartu daftar kendaraan yang menampilkan foto, nama kendaraan, spesifikasi singkat, tarif sewa, dan tombol status/detail.
- **Detail (`shop-single.html`)**: Informasi spesifikasi produk disesuaikan untuk menampilkan rincian komplit kendaraan (merk, tipe, transmisi, kapasitas seat, nomor plat, harga sewa per hari, dan galeri/thumbnail foto).
- **Footer**: Mengubah informasi kontak, alamat, serta tautan navigasi bawah sesuai kebutuhan aplikasi rental kendaraan.

### A.3. Menyiapkan Aset TemplateMo pada Workspace Aplikasi

- Seluruh isi folder `assets/` dari hasil ekstraksi TemplateMo telah disalin ke dalam direktori aplikasi:
  `assets/zay/`
- **Struktur Direktori Aset**:
  ```text
  dpwl-[NIM]/
  └── assets/
      └── zay/
          ├── css/
          ├── img/
          ├── js/
          └── webfonts/
  ```

## B. Mengimplementasikan Katalog Kendaraan Berbasis MVC

### 1. Tahap Pengembangan yang Dikerjakan

- Menambahkan method retrieval data katalog kendaraan pada model `Mobil_model.php`.
- Menambahkan konfigurasi route baru untuk mengakses halaman katalog.
- Membuat Controller `Katalog_controller.php` untuk menangani logika pemanggilan data dan pengiriman data ke View.
- Membuat komponen View modular untuk katalog yang terdiri dari `header.php`, `navbar.php`, `footer.php`, dan `index.php` di dalam direktori `application/views/katalog/`.

---

### 2. Berkas / Komponen yang Ditambahkan, Diubah, atau Dihapus

- **Diubah:** `application/models/Mobil_model.php` (menambahkan method `getKatalog()`).
- **Diubah:** `application/config/routes.php` (menambahkan route `'katalog'`).
- **Ditambahkan:** `application/controllers/Katalog_controller.php`.
- **Ditambahkan:** `application/views/katalog/header.php`.
- **Ditambahkan:** `application/views/katalog/navbar.php`.
- **Ditambahkan:** `application/views/katalog/footer.php`.
- **Ditambahkan:** `application/views/katalog/index.php`.

---

### 3. Perubahan dan Fitur yang Diimplementasikan

- **Query Data Katalog (`Mobil_model.php`):** Method `getKatalog()` mengeksekusi query SQL `SELECT` untuk mengambil daftar seluruh kendaraan dari tabel `t_mobil` dengan urutan nama kendaraan secara ascending (`ORDER BY nm_mobil ASC`).
- **Routing Aplikasi (`routes.php`):** Mendaftarkan route `$route['katalog'] = 'Katalog_controller/index';`.
- **Controller Katalog (`Katalog_controller.php`):** Menginstansiasi `Mobil_model`, memanggil data katalog via `getKatalog()`, dan mengirimkan variabel `$daftarMobil` ke view `katalog/index`.
- **Integrasi Antarmuka / View Katalog:**
  - `header.php`: Memuat pustaka CSS Zay Shop (Bootstrap, FontAwesome, Slick, Custom CSS) dengan helper `base_url()`.
  - `navbar.php`: Menyediakan navigasi responsif, pengecekan status login penyewa dari `$_SESSION['penyewa_user_plg']`, serta pemberitahuan flash message.
  - `footer.php`: Menyediakan bagian penutup halaman serta pemanggilan skrip JavaScript dasar dan Slick slider.
  - `index.php`: Memuat komponen hero banner, kategori kendaraan, serta iterasi daftar kendaraan (`foreach`) yang menampilkan foto, spesifikasi (jenis, tahun, transmisi, kapasitas), harga sewa, dan status ketersediaan unit.

---

### 4. Langkah Pengujian yang Dilakukan

1. Membuka peramban (browser) dan mengakses URL `http://localhost/dpwl-[NIM]/index.php/katalog`.
2. Memeriksa keberhasilan pemuatan aset CSS, FontAwesome, dan JavaScript pada halaman.
3. Memeriksa komponen navigasi Top Nav dan Main Navbar (logo, menu Beranda, Katalog Kendaraan, Registrasi, Login).
4. Menguji carousel hero banner dan section kategori kendaraan (SUV, MPV, City Car).
5. Memeriksa penampilan data kendaraan pada grid katalog:
   - Apabila data tersedia, memastikan foto, detail spesifikasi, harga sewa formatted (Rp), serta badge status unit (Tersedia / Sedang disewa / Tidak tersedia) tampil dengan benar.
   - Memastikan tombol **Tambah ke Keranjang** atau **Tidak Dapat Disewa** tampil sesuai kondisi `status_unit`.
6. Memeriksa tampilan pengondisian saat data katalog kosong.

---

### 5. Hasil Aktual Pengujian dan Penanganan Masalah

- **Status Akses URL:** Halaman katalog berhasil diakses via `http://localhost/dpwl-[NIM]/index.php/katalog` tanpa error.
- **Pemuatan Aset:** Seluruh CSS, font ikon, dan skrip JavaScript berhasil dimuat dari direktori `assets/zay/`.
- **Penyajian Data:** Data kendaraan dari basis data `t_mobil` berhasil ditarik dan dirender ke dalam kartu katalog secara dinamis.
- **Kendala & Solusi:**
  - _Kendala:_ Gambar kendaraan tidak muncul / _broken link_.
  - _Solusi:_ Memastikan foto kendaraan terunggah pada folder `uploads/mobil/` atau mengunggahnya melalui fitur Manajemen Kendaraan.

---

### 6. Status Fitur dan Hasil Verifikasi

| Nama Fitur / Komponen     | Status Verifikasi | Catatan                                                         |
| :------------------------ | :---------------- | :-------------------------------------------------------------- |
| Model `getKatalog()`      | **Berhasil**      | Menampilkan daftar kendaraan terurut `nm_mobil ASC`.            |
| Routing `/katalog`        | **Berhasil**      | Meneruskan permintaan ke `Katalog_controller/index`.            |
| Integrasi Header & Footer | **Berhasil**      | Aset terhubung menggunakan helper `base_url()`.                 |
| Render Grid Katalog       | **Berhasil**      | Menampilkan daftar mobil, spesifikasi, dan status ketersediaan. |

---

### 7. Perintah Git yang Dijalankan

```bash
git add .
git commit -m "Menyiapkan route, model, controller, dan view katalog kendaraan untuk P6"
git push
```

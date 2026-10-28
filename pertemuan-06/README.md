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

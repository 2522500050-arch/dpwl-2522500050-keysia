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

## C. Mengimplementasikan Detail Kendaraan

### 1. Tahap Pengembangan yang Dikerjakan

- Menambahkan method retrieval data spesifik kendaraan berdasarkan ID pada model `Mobil_model.php`.
- Menambahkan konfigurasi route baru untuk mengakses halaman detail kendaraan dengan parameter ID.
- Menambahkan method `detail()` pada Controller `Katalog_controller.php` untuk menangani logika pencarian data mobil dan pengiriman data ke View.
- Membuat komponen View detail kendaraan (`detail.php`) di dalam direktori `application/views/katalog/`.

---

### 2. Berkas / Komponen yang Ditambahkan, Diubah, atau Dihapus

- **Diubah:** `application/models/Mobil_model.php` (menambahkan method `findById()`).
- **Diubah:** `application/config/routes.php` (menambahkan route `'katalog/detail/(:any)'`).
- **Diubah:** `application/controllers/Katalog_controller.php` (menambahkan method `detail($id)`).
- **Ditambahkan:** `application/views/katalog/detail.php`.

---

### 3. Perubahan dan Fitur yang Diimplementasikan

- **Query Detail Kendaraan (`Mobil_model.php`):** Method `findById(string $id)` mengeksekusi query SQL `SELECT` menggunakan _prepared statement_ (`WHERE id_mobil = ? LIMIT 1`) untuk mencegah SQL Injection dan mengambil data lengkap 1 unit kendaraan.
- **Routing Detail (`routes.php`):** Mendaftarkan route `$route['katalog/detail/(:any)'] = 'Katalog_controller/detail/$1';`.
- **Controller Detail (`Katalog_controller.php`):**
  - Menerima parameter `$idMobil`.
  - Memanggil `Mobil_model->findById($idMobil)`.
  - Jika data tidak ditemukan, mengembalikan HTTP status 404 dan menampilkan pesan galat.
  - Jika ditemukan, mengirimkan variabel `$mobil` ke view `katalog/detail`.
- **View Detail Kendaraan (`detail.php`):**
  - Mengintegrasikan header dan navbar modular.
  - Menampilkan galeri foto utama beserta thumbnail foto sekunder yang dapat diganti secara interaktif via JavaScript.
  - Menampilkan spesifikasi teknis lengkap (merk/model, jenis, tahun pembuatan, tipe transmisi, kapasitas penumpang, nomor polisi/plat, harga sewa per hari, dan status ketersediaan unit).
  - Menyediakan tombol navigasi **Kembali ke Katalog** serta tombol **Sewa Sekarang** / **Tidak Dapat Disewa** berdasarkan kondisi status unit.

---

### 4. Langkah Pengujian yang Dilakukan

1. Membuka peramban (browser) dan memilih salah satu kendaraan dari halaman katalog, atau mengakses URL langsung `http://localhost/dpwl-[NIM]/index.php/katalog/detail/[ID_MOBIL]`.
2. Memeriksa ketepatan data spesifikasi yang ditampilkan sesuai dengan data pada basis data.
3. Menguji interaktivitas galeri thumbnail foto kendaraan (memastikan foto utama berganti saat thumbnail diklik).
4. Memeriksa responsivitas dan status tombol aksi (Sewa / Tidak Dapat Disewa).
5. Menguji skenario data tidak ditemukan dengan menguji URL ID yang tidak valid (misal: `/katalog/detail/MBL-999999`) untuk memastikan penanganan galat 404 berjalan dengan benar.

---

### 5. Hasil Aktual Pengujian dan Penanganan Masalah

- **Status Pemuatan Halaman:** Halaman detail kendaraan berhasil dirender dengan sempurna sesuai ID yang dipanggil.
- **Galeri Foto:** Fitur perpindahan foto utama melalui klik thumbnail berjalan dengan lancar tanpa kendala JS.
- **Penanganan Galat (404):** Pengujian dengan ID dummy/tidak valid berhasil menampilkan pesan error 404 "Data kendaraan tidak ditemukan" secara presisi.
- **Kendala & Solusi:**
  - _Kendala:_ Thumbnail foto tidak merespons klik.
  - _Solusi:_ Memastikan skrip event listener JavaScript pada `detail.php` sudah memuat selektor class thumbnail yang tepat.

---

### 6. Status Fitur dan Hasil Verifikasi

| Nama Fitur / Komponen            | Status Verifikasi | Catatan                                                      |
| :------------------------------- | :---------------- | :----------------------------------------------------------- |
| Model `findById()`               | **Berhasil**      | Menarik data spesifik berdasarkan `id_mobil`.                |
| Routing `/katalog/detail/(:any)` | **Berhasil**      | Parameter ID berhasil diteruskan ke controller.              |
| View Galeri & Detail             | **Berhasil**      | Tampil dinamis lengkap dengan interaksi thumbnail foto.      |
| Penanganan Error 404             | **Berhasil**      | Menampilkan respons 404 jika ID mobil tidak ada di database. |

---

## D. Mengimplementasikan Registrasi Penyewa / Pelanggan

### 1. Tahap Pengembangan yang Dikerjakan

- Menambahkan method query pencarian dan pembuatan data penyewa baru pada model `Penyewa_model.php`.
- Menambahkan konfigurasi route baru untuk mengakses halaman registrasi penyewa.
- Menambahkan method `register()` pada Controller `Auth.php` untuk menangani alur validasi input, hashing kata sandi, dan proses penyimpanan akun penyewa.
- Membuat komponen View registrasi penyewa (`register.php`) di dalam direktori `application/views/auth/`.

---

### 2. Berkas / Komponen yang Ditambahkan, Diubah, atau Dihapus

- **Diubah:** `application/models/Penyewa_model.php` (menambahkan method `findByUserPlg()` dan `create()`).
- **Diubah:** `application/config/routes.php` (menambahkan route `'auth/register'`).
- **Diubah:** `application/controllers/Auth.php` (menambahkan method `register()`).
- **Ditambahkan:** `application/views/auth/register.php`.

---

### 3. Perubahan dan Fitur yang Diimplementasikan

- **Model Penyewa (`Penyewa_model.php`):**
  - `findByUserPlg(string $userPlg)`: Mengambil data akun penyewa berdasarkan username untuk pengecekan duplikasi saat pendaftaran.
  - `create(array $data)`: Memasukkan data pendaftaran akun baru (username, sandi hashed, NIK, nama lengkap, nomor HP, dan alamat) ke basis data.
- **Routing Registrasi (`routes.php`):** Mendaftarkan route `$route['auth/register'] = 'Auth/register';`.
- **Controller Registrasi (`Auth.php`):**
  - **Handling GET Request:** Memeriksa pesan kilat (_flash message_) dan merender view `auth/register`.
  - **Handling POST Request:** Mengambil dan membersihkan input form (username, password, NIK, nama lengkap, no HP, alamat).
  - **Validasi Server-Side:** Memeriksa format username (1–15 karakter), panjang password (minimal 8 karakter), keharmonisan konfirmasi password, format NIK (16 digit angka), nama lengkap, format nomor HP, serta alamat lengkap.
  - **Keamanan Password:** Mengenkripsi kata sandi menggunakan fungsi `password_hash()` sebelum disimpan ke database.
  - **Pencegahan Duplikasi:** Memastikan `user_plg` belum terdaftar di sistem sebelum eksekusi penyimpanan.
- **View Registrasi (`register.php`):**
  - Mengintegrasikan komponen header dan navbar modular dari modul katalog.
  - Menyediakan formulir pendaftaran interaktif lengkap dengan atribut HTML5 validation (`pattern`, `maxlength`, `required`).
  - Menampilkan alert pesan galat (_error_) atau pesan sukses registrasi secara dinamis.
  - Mempertahankan isian data lama (_old input_) saat terjadi galat validasi agar pengguna tidak perlu mengetik ulang dari awal.

---

### 4. Langkah Pengujian yang Dilakukan

1. Membuka peramban (browser) dan mengakses URL `http://localhost/dpwl-[NIM]/index.php/auth/register` atau menekan tombol **Registrasi** pada navbar.
2. **Pengujian Input Tidak Valid (Validasi Error):**
   - Mengisi form dengan password kurang dari 8 karakter atau konfirmasi password tidak cocok.
   - Mengisi NIK tidak sama dengan 16 digit angka.
   - Memastikan pesan kesalahan tampil dengan jelas dan nilai form sebelumnya tidak hilang (_old input_ tetap terisi).
3. **Pengujian Username Duplikat:**
   - Mendaftar kembali menggunakan username `user_plg` yang sudah terdaftar di basis data.
   - Memastikan sistem menolak dan memunculkan pesan galat "Username sudah digunakan."
4. **Pengujian Registrasi Berhasil:**
   - Mengisi seluruh field pendaftaran dengan data valid.
   - Mengirimkan form dan memastikan sistem melakukan pengalihan (_redirect_) kembali ke halaman registrasi dengan menampilkan notifikasi sukses.
   - Memeriksa basis data untuk memastikan data akun baru berhasil masuk dengan password terenkripsi (hashed).

---

### 5. Hasil Aktual Pengujian dan Penanganan Masalah

- **Pemuatan Form:** Form registrasi berhasil tampil rapi terintegrasi dengan layout `header` dan `navbar`.
- **Validasi Server-side:** Seluruh aturan validasi (NIK 16 digit, min password 8 karakter, ketersediaan username) bekerja dengan akurat.
- **Keamanan Data:** Kata sandi tersimpan di database dalam bentuk hash secure (`password_hash`).
- **Kendala & Solusi:**
  - _Kendala:_ Notifikasi sukses registrasi langsung hilang setelah halaman di-refresh.
  - _Solusi:_ Penggunaan `unset($_SESSION['flash_penyewa'])` pada handler `GET` sudah tepat sehingga pesan sukses hanya tampil 1x (_flash message_).

---

### 6. Status Fitur dan Hasil Verifikasi

| Nama Fitur / Komponen                | Status Verifikasi | Catatan                                                   |
| :----------------------------------- | :---------------- | :-------------------------------------------------------- |
| Model `findByUserPlg()` & `create()` | **Berhasil**      | Mampu mengecek ketersediaan akun dan menyimpan data baru. |
| Routing `/auth/register`             | **Berhasil**      | Menghubungkan URL ke handler `Auth::register()`.          |
| Validasi Input Server-Side           | **Berhasil**      | Menolak format NIK/password/username yang tidak sesuai.   |
| Hashing Password                     | **Berhasil**      | Sandi tersimpan dalam format terenkripsi di database.     |
| Pengondisian Flash Message           | **Berhasil**      | Pesan sukses/error tampil sesuai dengan aksi pengguna.    |

---

## E. Mengimplementasikan Autentikasi Penyewa (Login dan Logout)

### 1. Tahap Pengembangan yang Dikerjakan

- Menambahkan method verifikasi kredenisial penyewa (`verifyPenyewa`) pada model `Penyewa_model.php`.
- Menambahkan konfigurasi route baru untuk mengakses alur login dan logout penyewa.
- Menambahkan method `loginPenyewa()` dan `logoutPenyewa()` pada Controller `Auth.php`.
- Membuat komponen View form login penyewa (`login.php`) di dalam direktori `application/views/auth/`.

---

### 2. Berkas / Komponen yang Ditambahkan, Diubah, atau Dihapus

- **Diubah:** `application/models/Penyewa_model.php` (menambahkan method `verifyPenyewa()`).
- **Diubah:** `application/config/routes.php` (menambahkan route `'auth/login'` dan `'auth/logout'`).
- **Diubah:** `application/controllers/Auth.php` (menambahkan method `loginPenyewa()` dan `logoutPenyewa()`).
- **Ditambahkan:** `application/views/auth/login.php`.

---

### 3. Perubahan dan Fitur yang Diimplementasikan

- **Model Penyewa (`Penyewa_model.php`):** Method `verifyPenyewa(string $userPlg, string $password)` mengeksekusi pencarian data akun berdasarkan username dan memvalidasi kata sandi menggunakan `password_verify()`. Jika valid, mengembalikan data profil penyewa tanpa menyertakan kolom `sandi`.
- **Routing Autentikasi (`routes.php`):**
  - Mendaftarkan route `$route['auth/login'] = 'Auth/loginPenyewa';`.
  - Mendaftarkan route `$route['auth/logout'] = 'Auth/logoutPenyewa';`.
- **Controller Autentikasi (`Auth.php`):**
  - **Handling `loginPenyewa()` (GET):** Menampilkan form login penyewa dan menangkap _flash message_ sukses atau galat.
  - **Handling `loginPenyewa()` (POST):** Memvalidasi kelengkapan username dan password, lalu memanggil `Penyewa_model->verifyPenyewa()`. Jika autentikasi berhasil, regenerasi ID sesi (`session_regenerate_id(true)`), menyimpan sesi penyewa (`$_SESSION['penyewa_user_plg']`), dan mengarahkan pengguna ke halaman katalog.
  - **Handling `logoutPenyewa()`:** Menghapus data sesi penyewa (`unset($_SESSION['penyewa_user_plg'])`) dan mengarahkan kembali ke halaman login dengan pesan notifikasi.
- **View Login (`login.php`):**
  - Mengintegrasikan header dan navbar modular dari katalog.
  - Menyediakan form interaktif untuk memasukkan `Username` dan `Password`.
  - Menampilkan alert _error_ jika login gagal serta tombol navigasi bagi pengguna baru yang belum memiliki akun untuk menuju halaman registrasi.

---

### 4. Langkah Pengujian yang Dilakukan

1. Membuka peramban (browser) dan mengakses URL `http://localhost/dpwl-[NIM]/index.php/auth/login` atau menekan menu **Login** pada navbar.
2. **Pengujian Kredensial Salah:**
   - Memasukkan username yang belum terdaftar atau kata sandi yang salah.
   - Memastikan sistem menolak autentikasi dan menampilkan pesan galat "Username atau password tidak valid."
3. **Pengujian Login Berhasil:**
   - Memasukkan username dan password penyewa yang valid/sesuai.
   - Memastikan sistem berhasil login, mengarahkan pengguna ke halaman katalog, dan komponen navbar berubah menampilkan nama/menu penyewa beserta tombol **Logout**.
4. **Pengujian Logout:**
   - Menekan tombol **Logout** pada navbar.
   - Memastikan sesi penyewa dihancurkan dan halaman dialihkan kembali ke form login dengan pesan "Anda telah berhasil logout."

---

### 5. Hasil Aktual Pengujian dan Penanganan Masalah

- **Pemuatan Form & Autentikasi:** Form login berhasil dimuat dengan baik dan verifikasi `password_verify()` bekerja secara akurat.
- **Manajemen Sesi:** Sesi login penyewa tersimpan aman di `$_SESSION['penyewa_user_plg']` dan proses regenerasi ID sesi berhasil mencegah ancaman _session fixation_.
- **Kendala & Solusi:**
  - _Kendala:_ Setelah login, nama penyewa pada navbar tidak berubah.
  - _Solusi:_ Memastikan variabel `$_SESSION['penyewa_user_plg']` telah di-set pada controller dan diperiksa secara tepat pada file view `navbar.php`.

---

### 6. Status Fitur dan Hasil Verifikasi

| Nama Fitur / Komponen                  | Status Verifikasi | Catatan                                                   |
| :------------------------------------- | :---------------- | :-------------------------------------------------------- |
| Model `verifyPenyewa()`                | **Berhasil**      | Memverifikasi kata sandi terenkripsi secara tepat.        |
| Routing `/auth/login` & `/auth/logout` | **Berhasil**      | Meneruskan permintaan ke method controller yang sesuai.   |
| Regenerasi & Penyimpanan Sesi          | **Berhasil**      | Sesi terdaftar dengan aman dan terlindungi dari fixation. |
| Penanganan Logout                      | **Berhasil**      | Sesi dihapus dan pengguna dialihkan ke halaman login.     |

---

## F. Mengimplementasikan Profil dan Pembaruan Data Penyewa

### 1. Tahap Pengembangan yang Dikerjakan

- Menambahkan method retrieval data profil (`getProfil`) dan pembaruan data profil (`updateProfil`) pada model `Penyewa_model.php`.
- Menambahkan fungsi pembantu penanganan unggah berkas (`uploadFotoPenyewa`) serta pembersihan berkas lama (`hapusFotoLama`).
- Menambahkan konfigurasi route baru untuk mengakses halaman profil penyewa.
- Menambahkan method `profil()` pada Controller `Penyewa_controller.php` untuk mengelola logika validasi, perubahan kata sandi, serta pembaruan data dan berkas media penyewa.
- Membuat komponen View profil penyewa (`profil.php`) di dalam direktori `application/views/penyewa/`.

---

### 2. Berkas / Komponen yang Ditambahkan, Diubah, atau Dihapus

- **Diubah:** `application/models/Penyewa_model.php` (menambahkan method `getProfil()` dan `updateProfil()`).
- **Diubah:** `application/config/routes.php` (menambahkan route `'penyewa/profil'`).
- **Ditambahkan:** `application/controllers/Penyewa_controller.php`.
- **Ditambahkan:** `application/views/penyewa/profil.php`.

---

### 3. Perubahan dan Fitur yang Diimplementasikan

- **Model Profil (`Penyewa_model.php`):**
  - `getProfil(string $userPlg)`: Mengambil data profil lengkap penyewa dari tabel `t_penyewa` tanpa mengembalikan kolom sandi terenkripsi.
  - `updateProfil(string $userPlg, array $data)`: Memperbarui bidang data profil (`nik`, `nm_lngkp`, `no_hp`, `almt_lngkp`), opsional kata sandi baru (`sandi`), serta path berkas `ft_ktp` dan `ft_sim_a`.
- **Routing Profil (`routes.php`):** Mendaftarkan route `$route['penyewa/profil'] = 'Penyewa_controller/profil';`.
- **Controller Profil (`Penyewa_controller.php`):**
  - **Otorisasi Sesi:** Memastikan hanya pengguna yang telah login (`$_SESSION['penyewa_user_plg']`) yang dapat mengakses halaman profil; mengalihkan ke form login jika sesi tidak ditemukan.
  - **Validasi Server-Side:** Memvalidasi perubahan NIK (16 digit), nama lengkap, nomor HP, dan alamat.
  - **Pembaruan Password Safe:** Memverifikasi `password_saat_ini` sebelum mengizinkan penggantian ke `password_baru` yang di-hash.
  - **Manajemen Unggah Berkas:** Memvalidasi format gambar (JPG, PNG, WEBP) dan ukuran berkas (maksimal 5 MB) untuk KTP/SIM A, serta menghapus foto lama secara otomatis dari server setelah pembaruan basis data berhasil.
- **View Profil (`profil.php`):**
  - Menampilkan bidang `Username` secara read-only.
  - Menyediakan formulir pembaruan data identitas, ganti kata sandi, dan pratinjau (_preview_) foto KTP serta SIM A yang sudah diunggah.

---

### 4. Langkah Pengujian yang Dilakukan

1. Membuka peramban (browser) dan mengakses URL `http://localhost/dpwl-[NIM]/index.php/penyewa/profil` saat dalam keadaan belum login (memastikan dialihkan otomatis ke halaman login).
2. Login sebagai penyewa, kemudian mengakses menu **Profil Saya** atau URL `http://localhost/dpwl-[NIM]/index.php/penyewa/profil`.
3. **Pengujian Pembaruan Data Teks:** Mengubah NIK, Nama Lengkap, Nomor HP, atau Alamat, lalu menekan **Simpan Perubahan** untuk memastikan data berhasil diperbarui di basis data.
4. **Pengujian Ganti Password:**
   - Mengisi password saat ini yang salah (memastikan sistem menolak).
   - Mengisi password saat ini yang benar dan password baru, lalu menguji login ulang menggunakan password baru.
5. **Pengujian Unggah Foto KTP & SIM A:**
   - Mengunggah berkas gambar dengan format valid (< 5 MB) dan memastikan pratinjau gambar tampil di halaman.
   - Mengunggah berkas non-gambar atau ukuran > 5 MB (memastikan muncul alert pesan kesalahan validasi).

---

### 5. Hasil Aktual Pengujian dan Penanganan Masalah

- **Otorisasi Sesi:** Halaman profil terlindungi dengan aman dari akses tanpa autentikasi.
- **Pembaruan Profil & Media:** Pembaruan data teks dan proses unggah foto KTP/SIM A berjalan lancar. Berkas lama di folder `uploads/penyewa/` berhasil terhapus otomatis saat diganti berkas baru.
- **Kendala & Solusi:**
  - _Kendala:_ Foto KTP/SIM A tidak terunggah dan mengembalikan galat folder.
  - _Solusi:_ Memastikan direktori `uploads/penyewa/` telah dibuat dan memiliki izin akses tulis (_write permission_).

---

### 6. Status Fitur dan Hasil Verifikasi

| Nama Fitur / Komponen                  | Status Verifikasi | Catatan                                                          |
| :------------------------------------- | :---------------- | :--------------------------------------------------------------- |
| Protection Route & Session Check       | **Berhasil**      | Pengguna belum login dialihkan otomatis ke `/auth/login`.        |
| Model `getProfil()` & `updateProfil()` | **Berhasil**      | Menarik dan memperbarui data akun penyewa secara presisi.        |
| Pengubahan Password Secure             | **Berhasil**      | Memeriksa sandi lama sebelum menyimpan hash sandi baru.          |
| Unggah Foto KTP & SIM A                | **Berhasil**      | Mendukung validasi jenis/ukuran berkas dan autoremove foto lama. |

---

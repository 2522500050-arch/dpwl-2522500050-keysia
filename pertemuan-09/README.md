# pertemuan-09

# Tahap 1: Analisis Source Code P7 / UTS

**Tujuan:** memahami aplikasi yang ada sebelum menulis kode P9, dan menyimpan kondisi awal (baseline).
**Perubahan kode pada tahap ini:** tidak ada. Tahap ini hanya analisis, pengecekan database, dan commit baseline.

> Hasil di bawah diperoleh dengan membaca source code pada `dpwl-2522500050.zip`.
> Skema database **tidak ada di ZIP** (tidak ada file `.sql`), jadi struktur tabel di bawah adalah
> kesimpulan dari query pada Model. Buktikan sendiri dengan perintah SQL pada bagian C.

---

## A. Temuan analisis

| Pertanyaan tugas                      | Temuan                                                                                                                                                                                                                                                                                                                                                                         | File                                              |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------- |
| Bagaimana checkout membuat record DP? | `Transaksi_model::checkout()` membuka transaksi DB, memvalidasi keranjang, lalu `INSERT INTO t_bayar` dengan `kode_bayar = PAY/YYYYMMDD/NN`, `ft_byr = NULL`, `mtode_byr = 'transfer'`, `jns_byr = 'dp'`, `nominal = round(total × 30 / 100)`, `sttus_byr = 'pending'`, `tgl_wkt_ver = NULL`. Dalam transaksi yang sama `t_sewa.status_sewa` berubah `keranjang` → `diajukan`. | `models/Transaksi_model.php`                      |
| Alur Model & Controller transaksi     | Controller memeriksa login & method, memanggil Model, lalu **redirect + flash** (`$_SESSION['flash_transaksi']`). Model memakai `prepare()`/`bind_param()` dan melempar `RuntimeException`/`InvalidArgumentException`.                                                                                                                                                         | `controllers/Transaksi_controller.php`            |
| Sesi penyewa                          | `$_SESSION['penyewa_user_plg']` (diisi `Auth::loginPenyewa()` setelah `session_regenerate_id(true)`).                                                                                                                                                                                                                                                                          | `controllers/Auth.php`                            |
| Sesi admin                            | `$_SESSION['admin_username']` dan `$_SESSION['admin_status'] = 'aktif'`. Helper: `is_admin_logged_in()` dan `require_admin_login()`.                                                                                                                                                                                                                                           | `controllers/Auth.php`, `helpers/auth_helper.php` |
| Pembeda hak akses                     | Penyewa dicek lewat `penyewa_user_plg`, admin lewat `is_admin_logged_in()`. Keduanya memakai sesi PHP yang sama, jadi **P9 mengecek kedua sisi secara eksplisit**.                                                                                                                                                                                                             | –                                                 |
| Mekanisme upload                      | `Penyewa_controller::uploadFotoPenyewa()`: cek `UPLOAD_ERR_*`, `is_uploaded_file`, ukuran (5 MB), MIME dengan `finfo`, `getimagesize`, nama `field_YmdHis_<random_bytes>.ext`, `move_uploaded_file` ke `uploads/penyewa/`. **Ini referensi untuk bukti pembayaran.**                                                                                                           | `controllers/Penyewa_controller.php`              |
| Routing                               | `index.php` membaca `?route=` (dari `.htaccess`), mencocokkan `$route[...]` lalu memanggil `Controller/method`. Pola `(:any)` = `([^/]+)`.                                                                                                                                                                                                                                     | `index.php`, `config/routes.php`                  |
| Tata letak View                       | Admin: `templates/header.php` + `sidebar.php` + `footer.php` (butuh `$username`). Penyewa: `katalog/header.php` + `navbar.php` + `footer.php`.                                                                                                                                                                                                                                 | `views/`                                          |

### Keputusan desain yang diambil dari temuan ini

1. **`kode_bayar` mengandung tanda `/`** (`PAY/20261028/01`). Pola `(:any)` tidak cocok untuk nilai berisi `/`,
   maka kode dikirim lewat **query string** `?kode_bayar=...` (sama seperti `?kode_sewa=` pada P7).
2. **Bukti pembayaran bersifat privat.** Foto KTP/SIM dibuka langsung lewat URL; untuk bukti transfer
   dibuat route khusus yang memeriksa hak akses dulu, dan folder `uploads/bayar/` ditutup dari akses langsung.
3. Record DP **diperbarui**, tidak dibuat baru (`UPDATE t_bayar ... WHERE kode_bayar = ?`).

### Catatan temuan lain (bukan bagian tugas, cukup dicatat)

- `Penyewa_controller::pastikanAdmin()` memakai kunci sesi `admin_user`, berbeda dari `admin_username`. Route admin penyewa
  memakai `Mpenyewa_controller`, jadi metode itu tampaknya tidak terpakai. **P9 tidak memakainya.**
- `views/templates/sidebar.php` punya `</ul>` ganda (tidak merusak tampilan).
- `index.php` mengaktifkan `display_errors`. Aman untuk praktikum, matikan jika dipakai di luar lokal.
- `checkout` tidak mengubah `t_mobil.status_unit`, sehingga penolakan pembayaran tidak perlu mengembalikan status kendaraan.
- Nama database di `config/database.php` adalah `db_dpwl_nama123`. Pastikan sama dengan database Anda.

---

## B. Persiapan & baseline

1. Salin/siapkan folder aplikasi di Laragon: `C:\laragon\www\dpwl-2522500050\` (sesuaikan NIM Anda).
   `RewriteBase` di `.htaccess` dan `base_url` di `config/config.php` harus sama dengan nama folder.
2. Jalankan Laragon (Apache + MySQL), buka `http://localhost/dpwl-2522500050/`.
3. Uji ulang fitur P7 (centang bila berhasil):
   - [ ] Login admin dan login penyewa
   - [ ] Katalog dan detail kendaraan
   - [ ] Profil penyewa
   - [ ] Tambah ke keranjang, ubah periode, hapus item
   - [ ] Checkout → muncul record baru di `t_bayar` (`pending`, `ft_byr` NULL, nominal 30%)
4. Jika ada galat dari P7, perbaiki dulu dan catat di tabel _Catatan Galat_ pada `pertemuan-09/README.md`.

## C. Periksa database (jalankan di HeidiSQL / phpMyAdmin)

```sql
SHOW CREATE TABLE t_sewa;
SHOW CREATE TABLE t_bayar;
SELECT kode_bayar, kode_sewa, nominal, sttus_byr, ft_byr, tgl_wkt_byr, tgl_wkt_ver FROM t_bayar;
SHOW TABLE STATUS WHERE Name IN ('t_sewa','t_bayar','t_pilih');  -- kolom Engine harus InnoDB
```

Yang harus Anda pastikan:

| Cek                                                                    | Mengapa penting                                                              |
| ---------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| Engine `t_sewa`, `t_bayar`, `t_pilih` = **InnoDB**                     | `begin_transaction`, `rollback`, dan `FOR UPDATE` hanya bekerja pada InnoDB. |
| `t_sewa.status_sewa` menerima nilai `disetujui` dan `ditolak`          | P9 mengisi kedua nilai ini.                                                  |
| `t_bayar.sttus_byr` menerima `valid` dan `invalid`                     | Sama.                                                                        |
| `t_bayar.ft_byr` boleh NULL dan cukup panjang (VARCHAR(50) atau lebih) | Nama berkas: `bayar_YYYYMMDDHHMMSS_<16 hex>.jpg` (41 karakter).              |

Jika kolom bertipe `ENUM` dan **belum** memuat nilai di atas, ubah tipe kolomnya. Salin daftar nilai lama dari hasil
`SHOW CREATE TABLE`, lalu **tambahkan** nilai baru (jangan menghapus nilai yang sudah ada). Contoh bentuk perintahnya
(sesuaikan daftar nilai!):

```sql
ALTER TABLE t_sewa  MODIFY status_sewa ENUM('keranjang','diajukan','disetujui','ditolak' /* + nilai lain milik Anda */) NOT NULL;
ALTER TABLE t_bayar MODIFY sttus_byr   ENUM('pending','valid','invalid') NOT NULL DEFAULT 'pending';
```

Ketentuan tugas melarang mengubah struktur database tanpa alasan. Alasan teknis yang sah: _nilai status yang diwajibkan
aturan bisnis P9 belum tersedia pada kolom_. Catat alasan ini di README bila Anda harus menjalankan `ALTER`.

## D. Cek pengaturan PHP (untuk upload)

Di Laragon: _Menu → PHP → php.ini_, lalu cari:

- `file_uploads = On`
- `upload_max_filesize` (batas aplikasi P9 adalah 2 MB; jika nilai ini lebih kecil, PHP menolak lebih dulu)
- `post_max_size` (harus ≥ `upload_max_filesize`)
- `extension=fileinfo` aktif (sudah dipakai upload KTP/SIM, jadi seharusnya aktif)Tahap 1: Analisis Source Code P7 / UTS

**Tujuan:** memahami aplikasi yang ada sebelum menulis kode P9, dan menyimpan kondisi awal (baseline).
**Perubahan kode pada tahap ini:** tidak ada. Tahap ini hanya analisis, pengecekan database, dan commit baseline.

> Hasil di bawah diperoleh dengan membaca source code pada `dpwl-2522500050.zip`.
> Skema database **tidak ada di ZIP** (tidak ada file `.sql`), jadi struktur tabel di bawah adalah
> kesimpulan dari query pada Model. Buktikan sendiri dengan perintah SQL pada bagian C.

---

## A. Temuan analisis

| Pertanyaan tugas                      | Temuan                                                                                                                                                                                                                                                                                                                                                                         | File                                              |
| ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------- |
| Bagaimana checkout membuat record DP? | `Transaksi_model::checkout()` membuka transaksi DB, memvalidasi keranjang, lalu `INSERT INTO t_bayar` dengan `kode_bayar = PAY/YYYYMMDD/NN`, `ft_byr = NULL`, `mtode_byr = 'transfer'`, `jns_byr = 'dp'`, `nominal = round(total × 30 / 100)`, `sttus_byr = 'pending'`, `tgl_wkt_ver = NULL`. Dalam transaksi yang sama `t_sewa.status_sewa` berubah `keranjang` → `diajukan`. | `models/Transaksi_model.php`                      |
| Alur Model & Controller transaksi     | Controller memeriksa login & method, memanggil Model, lalu **redirect + flash** (`$_SESSION['flash_transaksi']`). Model memakai `prepare()`/`bind_param()` dan melempar `RuntimeException`/`InvalidArgumentException`.                                                                                                                                                         | `controllers/Transaksi_controller.php`            |
| Sesi penyewa                          | `$_SESSION['penyewa_user_plg']` (diisi `Auth::loginPenyewa()` setelah `session_regenerate_id(true)`).                                                                                                                                                                                                                                                                          | `controllers/Auth.php`                            |
| Sesi admin                            | `$_SESSION['admin_username']` dan `$_SESSION['admin_status'] = 'aktif'`. Helper: `is_admin_logged_in()` dan `require_admin_login()`.                                                                                                                                                                                                                                           | `controllers/Auth.php`, `helpers/auth_helper.php` |
| Pembeda hak akses                     | Penyewa dicek lewat `penyewa_user_plg`, admin lewat `is_admin_logged_in()`. Keduanya memakai sesi PHP yang sama, jadi **P9 mengecek kedua sisi secara eksplisit**.                                                                                                                                                                                                             | –                                                 |
| Mekanisme upload                      | `Penyewa_controller::uploadFotoPenyewa()`: cek `UPLOAD_ERR_*`, `is_uploaded_file`, ukuran (5 MB), MIME dengan `finfo`, `getimagesize`, nama `field_YmdHis_<random_bytes>.ext`, `move_uploaded_file` ke `uploads/penyewa/`. **Ini referensi untuk bukti pembayaran.**                                                                                                           | `controllers/Penyewa_controller.php`              |
| Routing                               | `index.php` membaca `?route=` (dari `.htaccess`), mencocokkan `$route[...]` lalu memanggil `Controller/method`. Pola `(:any)` = `([^/]+)`.                                                                                                                                                                                                                                     | `index.php`, `config/routes.php`                  |
| Tata letak View                       | Admin: `templates/header.php` + `sidebar.php` + `footer.php` (butuh `$username`). Penyewa: `katalog/header.php` + `navbar.php` + `footer.php`.                                                                                                                                                                                                                                 | `views/`                                          |

### Keputusan desain yang diambil dari temuan ini

1. **`kode_bayar` mengandung tanda `/`** (`PAY/20261028/01`). Pola `(:any)` tidak cocok untuk nilai berisi `/`,
   maka kode dikirim lewat **query string** `?kode_bayar=...` (sama seperti `?kode_sewa=` pada P7).
2. **Bukti pembayaran bersifat privat.** Foto KTP/SIM dibuka langsung lewat URL; untuk bukti transfer
   dibuat route khusus yang memeriksa hak akses dulu, dan folder `uploads/bayar/` ditutup dari akses langsung.
3. Record DP **diperbarui**, tidak dibuat baru (`UPDATE t_bayar ... WHERE kode_bayar = ?`).

### Catatan temuan lain (bukan bagian tugas, cukup dicatat)

- `Penyewa_controller::pastikanAdmin()` memakai kunci sesi `admin_user`, berbeda dari `admin_username`. Route admin penyewa
  memakai `Mpenyewa_controller`, jadi metode itu tampaknya tidak terpakai. **P9 tidak memakainya.**
- `views/templates/sidebar.php` punya `</ul>` ganda (tidak merusak tampilan).
- `index.php` mengaktifkan `display_errors`. Aman untuk praktikum, matikan jika dipakai di luar lokal.
- `checkout` tidak mengubah `t_mobil.status_unit`, sehingga penolakan pembayaran tidak perlu mengembalikan status kendaraan.
- Nama database di `config/database.php` adalah `db_dpwl_nama123`. Pastikan sama dengan database Anda.

---

## B. Persiapan & baseline

1. Salin/siapkan folder aplikasi di Laragon: `C:\laragon\www\dpwl-2522500050\` (sesuaikan NIM Anda).
   `RewriteBase` di `.htaccess` dan `base_url` di `config/config.php` harus sama dengan nama folder.
2. Jalankan Laragon (Apache + MySQL), buka `http://localhost/dpwl-2522500050/`.
3. Uji ulang fitur P7 (centang bila berhasil):
   - [ ] Login admin dan login penyewa
   - [ ] Katalog dan detail kendaraan
   - [ ] Profil penyewa
   - [ ] Tambah ke keranjang, ubah periode, hapus item
   - [ ] Checkout → muncul record baru di `t_bayar` (`pending`, `ft_byr` NULL, nominal 30%)
4. Jika ada galat dari P7, perbaiki dulu dan catat di tabel _Catatan Galat_ pada `pertemuan-09/README.md`.

## C. Periksa database (jalankan di HeidiSQL / phpMyAdmin)

```sql
SHOW CREATE TABLE t_sewa;
SHOW CREATE TABLE t_bayar;
SELECT kode_bayar, kode_sewa, nominal, sttus_byr, ft_byr, tgl_wkt_byr, tgl_wkt_ver FROM t_bayar;
SHOW TABLE STATUS WHERE Name IN ('t_sewa','t_bayar','t_pilih');  -- kolom Engine harus InnoDB
```

Yang harus Anda pastikan:

| Cek                                                                    | Mengapa penting                                                              |
| ---------------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| Engine `t_sewa`, `t_bayar`, `t_pilih` = **InnoDB**                     | `begin_transaction`, `rollback`, dan `FOR UPDATE` hanya bekerja pada InnoDB. |
| `t_sewa.status_sewa` menerima nilai `disetujui` dan `ditolak`          | P9 mengisi kedua nilai ini.                                                  |
| `t_bayar.sttus_byr` menerima `valid` dan `invalid`                     | Sama.                                                                        |
| `t_bayar.ft_byr` boleh NULL dan cukup panjang (VARCHAR(50) atau lebih) | Nama berkas: `bayar_YYYYMMDDHHMMSS_<16 hex>.jpg` (41 karakter).              |

Jika kolom bertipe `ENUM` dan **belum** memuat nilai di atas, ubah tipe kolomnya. Salin daftar nilai lama dari hasil
`SHOW CREATE TABLE`, lalu **tambahkan** nilai baru (jangan menghapus nilai yang sudah ada). Contoh bentuk perintahnya
(sesuaikan daftar nilai!):

```sql
ALTER TABLE t_sewa  MODIFY status_sewa ENUM('keranjang','diajukan','disetujui','ditolak' /* + nilai lain milik Anda */) NOT NULL;
ALTER TABLE t_bayar MODIFY sttus_byr   ENUM('pending','valid','invalid') NOT NULL DEFAULT 'pending';
```

Ketentuan tugas melarang mengubah struktur database tanpa alasan. Alasan teknis yang sah: _nilai status yang diwajibkan
aturan bisnis P9 belum tersedia pada kolom_. Catat alasan ini di README bila Anda harus menjalankan `ALTER`.

## D. Cek pengaturan PHP (untuk upload)

Di Laragon: _Menu → PHP → php.ini_, lalu cari:

- `file_uploads = On`
- `upload_max_filesize` (batas aplikasi P9 adalah 2 MB; jika nilai ini lebih kecil, PHP menolak lebih dulu)
- `post_max_size` (harus ≥ `upload_max_filesize`)
- `extension=fileinfo` aktif (sudah dipakai upload KTP/SIM, jadi seharusnya aktif)
  satu satu.

  # Tahap 2: Pembayaran Awal oleh Penyewa

  **Tujuan:** penyewa dapat melihat rincian DP dan mengunggah bukti transfer. Status pembayaran tetap `pending`.

## A. File pada tahap ini

Struktur folder di sini **sama dengan struktur aplikasi**. Salin ke folder proyek Anda dan timpa bila diminta.

| Status | File                                                | Peran (MVC)                                                             |
| ------ | --------------------------------------------------- | ----------------------------------------------------------------------- |
| BARU   | `application/models/Bayar_model.php`                | **Model**: baca & ubah `t_bayar` milik penyewa                          |
| BARU   | `application/controllers/Pembayaran_controller.php` | **Controller**: login, kepemilikan, validasi berkas, aturan bisnis, PRG |
| BARU   | `application/views/pembayaran/index.php`            | **View**: daftar pembayaran saya                                        |
| BARU   | `application/views/pembayaran/detail.php`           | **View**: rincian transaksi, DP, status, formulir unggah                |
| BARU   | `application/helpers/bukti_helper.php`              | Helper berkas bukti (lokasi, nama valid, hapus, kirim)                  |
| BARU   | `application/helpers/csrf_helper.php`               | Helper token CSRF untuk formulir POST baru                              |
| BARU   | `uploads/bayar/.htaccess`                           | Menutup akses langsung ke bukti pembayaran                              |
| UBAH   | `application/config/routes.php`                     | Menambah 4 route `pembayaran/...`                                       |
| UBAH   | `application/views/katalog/navbar.php`              | Menambah ikon _Pembayaran Saya_                                         |
| UBAH   | `application/controllers/Transaksi_controller.php`  | Setelah checkout, arahkan ke halaman pembayaran                         |

> Folder `uploads/bayar/` akan dibuat otomatis saat unggah pertama. Anda tetap perlu menyalin `.htaccess`-nya.
> Pada Windows, buat dulu folder `uploads\bayar\` lalu letakkan `.htaccess` di dalamnya.

Perubahan pada file lama sangat kecil. Bandingkan dengan `git diff`:

- `routes.php`: hanya penambahan di akhir file.
- `navbar.php`: satu blok `<a>` ikon kartu kredit sebelum "Ikon Profil Penyewa".
- `Transaksi_controller.php`: pada `checkout()` (POST) pesan sukses dan tujuan redirect berubah dari `katalog` ke `pembayaran`.

## B. Route

| URL                                     | Method | Controller::method             | Fungsi                             |
| --------------------------------------- | ------ | ------------------------------ | ---------------------------------- |
| `/pembayaran`                           | GET    | `Pembayaran_controller::index` | Daftar pembayaran DP milik penyewa |
| `/pembayaran/detail?kode_bayar=PAY/...` | GET    | `::detail`                     | Rincian + formulir unggah          |
| `/pembayaran/unggah`                    | POST   | `::unggah`                     | Proses unggah, lalu redirect (PRG) |
| `/pembayaran/bukti?kode_bayar=PAY/...`  | GET    | `::bukti`                      | Menampilkan bukti milik sendiri    |

## C. Aturan bisnis → implementasi

| Aturan                                     | Di mana                                                                                                     |
| ------------------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| Wajib login penyewa                        | `wajibLoginPenyewa()` di setiap method; belum login → redirect ke `auth/login-penyewa`                      |
| Hanya pembayaran miliknya                  | Query `JOIN t_sewa ... WHERE b.kode_bayar = ? AND s.user_plg = ?`; tidak cocok → HTTP 403                   |
| Admin tidak boleh masuk proses penyewa     | Jika hanya admin yang login → HTTP 403                                                                      |
| Nominal dari database, tidak dari formulir | Formulir tidak memiliki field nominal; `simpanBukti()` tidak menyentuh `nominal`                            |
| Memperbarui record DP, bukan membuat baru  | `UPDATE t_bayar SET ft_byr, tgl_wkt_byr WHERE kode_bayar = ?`                                               |
| Status tetap `pending`                     | `UPDATE` tidak mengubah `sttus_byr`                                                                         |
| Yang sudah diverifikasi tidak bisa diubah  | Cek di Controller dan di Model (`FOR UPDATE` + `AND sttus_byr = 'pending'`)                                 |
| Validasi jenis, MIME, ukuran               | `validasiDanSimpanBerkas()`: ekstensi, `finfo` MIME, `getimagesize`, maks. 2 MB, ekstensi harus sesuai MIME |
| Nama berkas aman                           | `bayar_<YmdHis>_<16 hex random>.<ext>`; nama asli dari pengguna tidak dipakai                               |
| Berkas privat                              | `.htaccess` + route `pembayaran/bukti` yang memeriksa kepemilikan                                           |
| PRG                                        | Setiap hasil `POST /pembayaran/unggah` berakhir dengan `header('Location: ...')` + flash                    |
| Prepared statement                         | Semua query berparameter memakai `prepare()` + `bind_param()`                                               |

**Keputusan desain:** selama status masih `pending`, penyewa boleh **mengganti** bukti (berkas lama dihapus
setelah update berhasil). Setelah diverifikasi admin, bukti terkunci.

## D. Cara memasang

1. Pastikan Tahap 1 selesai (nilai ENUM status sudah sesuai).
2. Salin semua file dari folder ini ke proyek dengan path yang sama.
3. Buka `http://localhost/dpwl-2522500050/`, login sebagai penyewa, lakukan checkout.
4. Anda akan diarahkan ke `/pembayaran` dan melihat DP `pending`.

## E. Pengujian Tahap 2

Isi kolom _Hasil_ dan simpan tangkapan layar untuk README utama.

| No  | Skenario                                 | Cara uji                                                                              | Diharapkan                                                                     | Hasil |
| --- | ---------------------------------------- | ------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ | ----- |
| 1   | Buka halaman pembayaran setelah checkout | Checkout, lalu buka `/pembayaran` → _Bayar DP_                                        | Rincian transaksi dan DP tampil                                                |       |
| 2   | Buka pembayaran milik akun lain          | Login penyewa B, buka URL detail milik penyewa A                                      | HTTP 403 "Akses ditolak"                                                       |       |
| 3   | Unggah bukti valid                       | JPG/PNG/WEBP ≤ 2 MB                                                                   | Pesan sukses; `ft_byr` & `tgl_wkt_byr` berubah; berkas ada di `uploads/bayar/` |       |
| 4   | Jenis berkas tidak diizinkan             | Unggah `.pdf`, `.gif`, `.txt`; coba juga file `.txt` yang diganti namanya jadi `.jpg` | Ditolak dengan pesan                                                           |       |
| 5   | Ukuran berlebih                          | Gambar > 2 MB                                                                         | Ditolak dengan pesan ukuran                                                    |       |
| 6   | Kirim tanpa memilih berkas               | Klik _Unggah Bukti_ tanpa berkas                                                      | Pesan "Pilih berkas bukti transfer terlebih dahulu."                           |       |
| 7   | Status setelah unggah                    | Lihat halaman detail                                                                  | Status tetap **Pending**                                                       |       |
| 15  | Belum login                              | Logout, buka `/pembayaran`                                                            | Diarahkan ke login penyewa                                                     |       |

Periksa juga di database setelah uji 3:

```sql
SELECT kode_bayar, ft_byr, tgl_wkt_byr, sttus_byr, nominal, tgl_wkt_ver FROM t_bayar ORDER BY kode_bayar DESC;
```

`nominal` **tidak boleh** berubah, `sttus_byr` tetap `pending`, `tgl_wkt_ver` tetap `NULL`.

# Tahap 3: Verifikasi Pembayaran oleh Admin

**Tujuan:** admin melihat pembayaran DP beserta bukti transfer, lalu menerima atau menolak.
Perubahan `t_bayar` dan `t_sewa` dilakukan dalam **satu transaksi database**.

**Prasyarat:** Tahap 2 sudah terpasang dan lolos uji (file `Bayar_model.php`, `bukti_helper.php`, `csrf_helper.php` dipakai ulang di sini).

## A. File pada tahap ini

| Status | File                                                 | Peran (MVC)                                                          |
| ------ | ---------------------------------------------------- | -------------------------------------------------------------------- |
| BARU   | `application/models/Verifikasi_model.php`            | **Model**: daftar, detail, dan `proses()` (transaksi DB)             |
| BARU   | `application/controllers/Verifikasi_controller.php`  | **Controller**: hak akses admin, validasi, PRG                       |
| BARU   | `application/views/admin/verifikasi_view.php`        | **View**: daftar verifikasi (filter Menunggu/Diterima/Ditolak/Semua) |
| BARU   | `application/views/admin/verifikasi_detail_view.php` | **View**: penyewa, transaksi, nominal, bukti, tombol Terima/Tolak    |
| UBAH   | `application/config/routes.php`                      | **Kumulatif**: berisi route Tahap 2 + Tahap 3                        |
| UBAH   | `application/views/templates/sidebar.php`            | Menu _Verifikasi Pembayaran_                                         |

> `routes.php` di folder ini sudah memuat route Tahap 2. Jika Anda menimpa file Anda, tidak ada route yang hilang.

## B. Route

| URL                                                       | Method | Controller::method                       |
| --------------------------------------------------------- | ------ | ---------------------------------------- |
| `/admin/verifikasi?status=pending\|valid\|invalid\|semua` | GET    | `Verifikasi_controller::index`           |
| `/admin/verifikasi/detail?kode_bayar=PAY/...`             | GET    | `::detail`                               |
| `/admin/verifikasi/bukti?kode_bayar=PAY/...`              | GET    | `::bukti` (menampilkan gambar bukti)     |
| `/admin/verifikasi/proses`                                | POST   | `::proses` (terima/tolak, lalu redirect) |

## C. Aturan perubahan status

| Keputusan | `t_bayar.sttus_byr` | `t_bayar.tgl_wkt_ver` | `t_sewa.status_sewa` |
| --------- | ------------------- | --------------------- | -------------------- |
| Terima    | `valid`             | `NOW()`               | `disetujui`          |
| Tolak     | `invalid`           | `NOW()`               | `ditolak`            |

Alur `Verifikasi_model::proses()`:

```
begin_transaction
  SELECT ... FROM t_bayar JOIN t_sewa WHERE kode_bayar = ? FOR UPDATE   -- kunci baris
  tolak jika: tidak ada | sudah diverifikasi | ft_byr kosong | status_sewa <> 'diajukan'
  UPDATE t_bayar  SET sttus_byr = ?, tgl_wkt_ver = NOW() WHERE ... AND sttus_byr = 'pending' ...   (harus 1 baris)
  UPDATE t_sewa   SET status_sewa = ?                    WHERE ... AND status_sewa = 'diajukan'    (harus 1 baris)
commit        -- bila ada kegagalan di mana pun: rollback, tidak ada data yang berubah
```

## D. Aturan bisnis → implementasi

| Aturan                                      | Di mana                                                                                |
| ------------------------------------------- | -------------------------------------------------------------------------------------- |
| Admin wajib login                           | `wajibAdmin()` memakai `is_admin_logged_in()`                                          |
| Penyewa tidak boleh masuk menu admin        | Penyewa yang login → HTTP 403; yang belum login → redirect ke `auth/login`             |
| Bukti wajib ada sebelum verifikasi          | Tombol Terima/Tolak tidak ditampilkan tanpa bukti **dan** `proses()` menolak di server |
| Yang sudah diverifikasi tidak diproses lagi | Cek status di Model dalam transaksi + `AND sttus_byr = 'pending'` pada `UPDATE`        |
| Admin dapat membuka bukti                   | Route `admin/verifikasi/bukti` (folder upload tertutup untuk akses langsung)           |
| Konsistensi `t_bayar` dan `t_sewa`          | Satu transaksi DB; `rollback` jika salah satu gagal                                    |
| PRG                                         | `proses()` selalu berakhir dengan redirect ke halaman detail + flash                   |
| CSRF                                        | Token pada formulir keputusan diperiksa dengan `hash_equals`                           |

## E. Cara memasang

1. Salin semua file dari folder ini ke proyek (path sama).
2. Login admin; menu **Verifikasi Pembayaran** muncul di sidebar.
3. Siapkan data uji: minimal 3 pembayaran `pending` (2 dengan bukti, 1 tanpa bukti).

## F. Pengujian Tahap 3

| No  | Skenario                | Cara uji                                                                     | Diharapkan                                                           | Hasil |
| --- | ----------------------- | ---------------------------------------------------------------------------- | -------------------------------------------------------------------- | ----- |
| 8   | Daftar verifikasi       | Buka `/admin/verifikasi`                                                     | Pembayaran `pending` tampil                                          |       |
| 9   | Lihat bukti             | _Periksa_ → klik gambar / _Buka ukuran penuh_                                | Gambar bukti terbuka                                                 |       |
| 10  | Terima                  | Klik _Terima_ → OK                                                           | `sttus_byr = valid`, `status_sewa = disetujui`, `tgl_wkt_ver` terisi |       |
| 11  | Tolak                   | Klik _Tolak_ → OK (pada pembayaran lain)                                     | `invalid` dan `ditolak`, `tgl_wkt_ver` terisi                        |       |
| 12  | Verifikasi tanpa bukti  | Lihat detail pembayaran tanpa bukti; lalu kirim POST manual (lihat di bawah) | Tombol tidak ada; POST manual ditolak dengan pesan                   |       |
| 13  | Verifikasi ulang        | Kirim POST manual untuk pembayaran yang sudah diproses                       | Ditolak: "sudah diverifikasi"                                        |       |
| 14  | Penyewa buka menu admin | Login penyewa (tanpa login admin), buka `/admin/verifikasi`                  | HTTP 403                                                             |       |

**Cara kirim POST manual (skenario 12 dan 13).** Buka detail satu pembayaran `pending` yang **punya** bukti, buka
_DevTools → Console_, salin nilai token dengan `document.querySelector('[name=csrf_token]').value`, lalu jalankan
(ganti `TOKEN` dan `KODE`; nama folder disesuaikan):

```js
fetch("/dpwl-2522500050/admin/verifikasi/proses", {
  method: "POST",
  headers: { "Content-Type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({
    csrf_token: "TOKEN",
    kode_bayar: "PAY/20261118/02",
    keputusan: "terima",
  }),
  credentials: "same-origin",
})
  .then((r) => r.url)
  .then(console.log);
```

Token berlaku untuk seluruh sesi, jadi bisa dipakai untuk `KODE` lain (yang tanpa bukti, atau yang sudah diproses).
Muat ulang halaman detail `KODE` tersebut untuk melihat pesan penolakan.

Cek konsistensi data setelah setiap uji:

```sql
SELECT b.kode_bayar, b.sttus_byr, b.tgl_wkt_ver, s.status_sewa
FROM t_bayar b JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
ORDER BY b.kode_bayar;
```

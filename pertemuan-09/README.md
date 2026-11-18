# pertemuan-09

Tahap 1: Analisis Source Code P7 / UTS

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

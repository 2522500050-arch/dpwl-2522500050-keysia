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

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

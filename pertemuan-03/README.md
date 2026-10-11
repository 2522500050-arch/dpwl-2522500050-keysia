# pertemuan-03

### Penerapan Post-Redirect-Get (PRG)

Pada tahap ini dilakukan penelusuran dan pengujian pola
Post-Redirect-Get (PRG) pada proses autentikasi.
Penerapan PRG:

- Login berhasil menggunakan POST untuk proses autentikasi,
  kemudian redirect ke /admin dan dilanjutkan dengan GET.
- Logout menggunakan POST untuk mengakhiri session,
  kemudian redirect ke /auth/login dan dilanjutkan dengan GET.
  Login gagal tidak menggunakan redirect karena Controller
  mengembalikan View login beserta pesan kesalahan pada
  request tersebut.
  Pengujian aktual dilakukan terhadap:
  | No. | Skenario Pengujian | Hasil Aktual | Status |
  | --: | ----------------------------------- | ---------------- | ----------------------- |
  | 1 | Login dengan kredensial valid | `[Berhasil login, kemudian redirect ke /admin melalui GET.]` | `[Sesuai]` |
  | 2 | Login dengan kredensial tidak valid | `[Tetap pada halaman login dan menampilkan pesan kesalahan tanpa redirect.]` | `[Sesuai]` |
  | 3 | Logout melalui halaman administrasi | `[	Session berakhir, kemudian redirect ke /auth/login melalui GET.]` | `[Sesuai]` |
  | 4 | Akses halaman admin setelah logout | `[Akses ditolak dan diarahkan kembali ke halaman login.]` | `[Sesuai]` |
  Catat hasil berdasarkan eksekusi yang benar-benar dilakukan. Jika ditemukan masalah, dokumentasikan
  perbaikan yang dilakukan dan ulangi pengujian yang terdampak.

### Perbaikan

| Masalah yang Ditemukan | Perbaikan yang Dilakukan | Hasil Setelah Perbaikan |
| ---------------------- | ------------------------ | ----------------------- |
| `[jika ada]`           | `[perbaikan aktual]`     | `[hasil aktual]`        |

Jika tidak ditemukan masalah, tuliskan:

> Tidak ditemukan masalah pada saat pengujian M.3.

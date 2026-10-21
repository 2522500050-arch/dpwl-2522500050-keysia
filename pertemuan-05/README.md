# pertemuan-05
Berikut draft penjelasan untuk poin **A.5** yang dapat dimasukkan ke dalam berkas `README.md`:

---

### Tahap A: Integrasi Menu Navigasi Admin dan Konfigurasi Route Awal

**Deskripsi Perubahan:**
Mengintegrasikan menu baru **Manajemen Kendaraan** ke dalam sidebar panel admin serta menambahkan konfigurasi *route* dasar untuk mengarahkan navigasi tersebut ke *controller* kendaraan.

**Berkas yang Terdampak:**

* `application/views/templates/sidebar.php` (Penambahan struktur menu Manajemen Kendaraan)
* `application/config/routes.php` (Penambahan route `$route['admin/mobil'] = 'Mobil_controller/index';`)

**Hasil Pengujian:**

* Menu **Manajemen Kendaraan** berhasil muncul di bawah kelompok menu Manajemen Admin pada *sidebar*.
* Ketika menu diklik, URL pada peramban berhasil berubah menjadi `admin/mobil`.
* Tampilan sistem mengeluarkan pesan *"Controller tidak ditemukan."*. Hal ini merupakan kondisi yang diharapkan (normal) karena file `Mobil_controller.php` belum dibuat pada tahap ini.
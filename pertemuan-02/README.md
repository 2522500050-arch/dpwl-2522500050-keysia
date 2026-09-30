# pertemuan-02
## 1. Tujuan Praktikum 
Jawaban:Tujuan utama praktikum Pertemuan 2 (P2) adalah membangun dan memahami fondasi arsitektur custom MVC tanpa framework. Praktikum ini berfokus pada alur kerja front controller (index.php) dan routing URL, pemisahan peran antara Controller dan View, pembuatan fungsi pembantu URL, serta pembiasaan struktur konvensi mirip CodeIgniter 3 sekaligus penerapan dokumentasi proyek melalui Git dan README.md.
 
## 2. Struktur Direktori 
Jawaban:dpwl-2522500050/
├── application/
│   ├── config/
│   │   ├── config.php
│   │   └── routes.php
│   ├── controllers/
│   │   └── Home.php
│   ├── helpers/
│   │   └── url_helper.php
│   └── views/
│       └── home/
│           ├── index.php
│           └── info.php
├── assets/
│   └── css/
│       └── app.css
├── system/
│   └── core/
│       ├── Controller.php
│       └── Router.php
└── index.php
    Penjelasan:1. Root Repository (dpwl-2522500050/)
            -README.md: Berkas dokumentasi utama repositori yang berisi penjelasan umum proyek, petunjuk   instalasi, cara menjalankan aplikasi, serta informasi pemilik repositori (NIM) .
            -pertemuan-01/: Direktori khusus yang menyimpan tugas, materi, atau kode program hasil pengerjaan pada Pertemuan 1.
            -pertemuan-01/README.md:Catatan atau rincian laporan pengerjaan khusus untuk modul Pertemuan 1.
            2. Modul Utama (pertemuan-02/)
               Direktori yang berisi implementasi arsitektur MVC (Model-View-Controller) buatan sendiri (custom/native) untuk Pertemuan 2.
            A. Berkas Inti Root Modul
             -index.php (Front Controller): Titik masuk utama (single entry point) bagi seluruh permintaan (request) ke aplikasi. Berkas ini memuat konfigurasi awal, helper, class core, serta memicu Router untuk mengeksekusi controller yang sesuai.   
             -README.md: Dokumentasi khusus untuk materi dan tugas Pertemuan 2.
             -dokumentasi/: Direktori untuk menyimpan berkas pendukung dokumentasi proyek, seperti screenshot hasil pengujian, diagram alur, atau Laporan Praktikum.
             B. Direktori application/Menyimpan seluruh logika, konfigurasi, dan tampilan spesifik dari aplikasi.   
             *config/
             -config.php: Berkas konfigurasi utama aplikasi, seperti pengaturan alamat dasar (base URL) dan variabel global.   
             -routes.php: Mengatur pemetaan URL ke Controller dan method tertentu, serta menentukan default controller.  
             *controllers/
             -Home.php: Class Controller yang menangani alur logika permintaan, berinteraksi dengan data, dan menentukan View yang akan ditampilkan kepada pengguna.   
             *helpers/
             -url_helper.php: Berisi fungsi-fungsi pembantu routing dan navigasi, seperti base_url() untuk memanggil aset statis dan site_url() untuk pembentukan tautan URL.   
             *views/
             -home/index.php: Tampilan HTML/PHP utama untuk Controller Home.home/routes.php (atau info.php): Halaman tampilan tambahan di dalam direktori home untuk menyajikan informasi tertentu kepada pengguna.  
             C. Direktori assets/
             Menyimpan berkas-berkas aset statis yang diakses langsung oleh browser tanpa melalui proses routing PHP.   
             -css/app.css: Berkas lembar gaya (CSS) untuk mengatur tampilan visual, tata letak, dan gaya desain halaman web.
             D. Direktori system/
             Menyimpan komponen inti (core framework) yang mendasari jalannya aplikasi MVC.   
             core/
             -Controller.php (Base Controller): Class induk yang menyediakan pustaka dan metode standar (seperti method pemanggil view) yang diwarisi oleh semua Controller aplikasi.   
             -Router.php: Menguraikan URI dari browser, mencocokkannya dengan aturan di routes.php, dan memanggil Controller serta method target. 
 
## 3. Front controller 
Jawaban:Index.php adalah pengontrol utama di aplikasi. Ini menjadi pintu masuk utama, tempat semua permintaan halaman dinamis dari browser pertama kali diarahkan dan diproses.
Index.php bertugas menyiapkan lingkungan awal aplikasi. Sebagai contoh, ia menetapkan jalur direktori utama, seperti FCPATH, APPPATH, dan SYSPATH. Ia juga memuat file konfigurasi, misalnya file config/config.php, dan file helper, seperti helpers/url_helper.php.
Selain itu, index.php memuat kelas inti aplikasi, seperti Base Controller yang berada di system/core/Controller.php dan Router yang berada di system/core/Router.php. Ia juga memuat aturan pemetaan route yang ada di config/routes.php.
Setelah semua persiapan selesai, index.php menerima alamat URI dari browser dan menyerahkannya kepada Router. Router kemudian memetakan URI tersebut dan menentukan controller, method, serta parameter yang akan dijalankan.
 
## 4. Routing dan Pemetaan URL 
| URL/Route | Controller | Method | Parameter | View | 
|---|---|---|---|---| 
| / | Home | index | - | home/index.php | 
| home/index | Home | index | - | home/index.php | 
| home/info/mvc | Home | info | mvc | home/info.php | 
| info/routing | Home | info | routing | home/info.php | |mahasiswa/detail/2522500050| |
|Mahasiswa | detail |2522500050 |produk/detail.php
 
Penjelasan:-URL / Route (materi/detail/p2)Segmen alamat URL yang diakses oleh pengguna pada browser (misalnya: http://localhost/dpwl-NIM/materi/detail/p2).  
- Controller (Materi)Router membaca segmen pertama (materi) dan mengarahkannya ke kelas controller Materi.php (application/controllers/Materi.php).  
  -Method (detail)Router membaca segmen kedua (detail) dan mengeksekusi method/fungsi detail() yang berada di dalam controller Materi.  
   -Parameter (p2)Router menangkap segmen ketiga (p2) sebagai argumen/parameter nilai yang dikirimkan ke dalam method detail($id) untuk menentukan materi spesifik yang ingin diproses/ditampilkan.   
   -View (materi/detail.php)Method detail() pada controller mengolah parameter p2 tersebut, lalu memuat dan menyuntikkan datanya ke berkas tampilan detail.php yang berada di dalam folder application/views/materi/ untuk disajikan ke browser pengguna.
 
## 5. Base URL dan Helper 
Penjelasan Fungsi:
*base_url()Fungsi: Mengembalikan alamat URL dasar (root URL) dari aplikasi sesuai dengan yang dikonfigurasi pada file config.php (misalnya: http://localhost/dpwl-NIM/).   
-Kegunaan Utama: Digunakan untuk memanggil aset-aset statis seperti berkas CSS, JavaScript, gambar, atau media lain yang tersimpan di direktori utama aplikasi tanpa melalui routing.   
*site_url()Fungsi: Mengembalikan URL lengkap aplikasi yang mencakup alamat dasar (base URL), berkas titik masuk (index page seperti index.php), serta jalur/route yang dituju.   
-Kegunaan Utama: Digunakan untuk membuat tautan navigasi internal (antar-controller atau method) dalam aplikasi. 
* Contoh penggunaan memanggil CSS:
<link rel="stylesheet" href="<?= base_url('assets/css/app.css'); ?>">

site_url(): Mengembalikan URL lengkap yang menyertakan rute aplikasi/index.php untuk navigasi antar-halaman.   

* Contoh penggunaan navigasi route:
<a href="<?= site_url('info/routing'); ?>">Lihat Info Routing</a>
 
## 6. Alur Request-response 
Jelaskan dua alur berikut: 
 
1. Alur eksekusi aktual P2:-Browser: Pengguna mengakses URL (mengirim HTTP Request).   
                          -index.php: Menerima request, memuat konfigurasi, helper, dan core class.   
                          -Router: Membaca URI dan mencocokkannya dengan aturan routing untuk menentukan Controller & Method.  
                           -Controller: Menjalankan logika bisnis dan menyiapkan data statis/lokal (tanpa basis data). 
                           -View: Menerima data dari Controller dan menyusun tampilan HTML/UI.  
                        -Response: Hasil render HTML dikirim kembali ke Browser pengguna.
Browser → index.php → Router → Controller → View → Response. 
2. Posisi Model dalam arsitektur MVC lengkap:-Browser → index.php → Router → Controller: Proses awal sama seperti P2.  
                                             -Controller → Model: Controller meminta Model untuk mengelola/mengambil data dinamis.  
                                             - Model → Basis Data → Model: Model mengeksekusi query (SELECT/INSERT/UPDATE/DELETE) ke basis data dan menerima hasilnya kembali.   
                                             -Model → Controller: Model menyerahkan hasil olahan data ke Controller.   
                                             -Controller → View → Response: Controller menyuntikkan data dinamis tersebut ke View untuk disajikan sebagai Response HTML ke Browser
Browser → index.php → Router → Controller → Model → basis data/data → Model → Controller → View → 
Response. 
 
Pada implementasi P2, Model belum digunakan karena akses dan pengelolaan basis data mulai 
diimplementasikan pada P3. 
 
## 7. Hasil Pengujian dan Debugging 
Jawaban:ditemukan kesalahan selama implementasi.
Gejala:
Pas ngejalanin perintah php -1 index. php di terminal VS Code, muncul error php
The term
'php is not recognized as the name of a cmdlet...
(CommandotFoundException) •
Penyebab:
Windows belum kenal sama perintah php karena path folder PHP dari Laragon belum didaftarin ke System Environment Variables (PATH).
Perbaikan:
Nambahin path
folder PHP Laragon ( C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64°) ke
PATH Windows, terus restart VS Code supaya jalurnya terbaca.
Hasil Uji Ulang:
Perintah php -1 buat semua berkas berhasil dijalanin dan keluar respon
• No syntax
errors detected in [nama_filel].
 
## 8. Bukti Tangkapan Layar 
Sisipkan gambar yang relevan dari folder dokumentasi/ dengan perintah: 
 
### Gambar 1. Hasil Pengujian Halaman Utama  
![Gambar 1 - Halaman Utama](pertemuan-02/dokumentasi/dokumentasigambar1.png)  
 
### Gambar 2. Hasil Pengujian Custom Route  
![Gambar 2 - Custom Route](pertemuan-02/dokumentasi/dokumentasigambar2.png) 
 
## 9. Kesimpulan P2 
Jelaskan apa yang sudah dapat dilakukan kerangka MVC dan apa yang baru akan ditambahkan pada P3. 
Jawaban:1. Yang Sudah Jadi di P2:-Front Controller (index.php): Semua request masuk lewat satu pintu utama.   
-Routing Clean URL: Router.php & routes.php berhasil memetakan URL ke Controller, method, dan parameter (termasuk handle error 404).   
-Base Controller & View: Controller.php siap memanggil berkas View lewat fungsi view().  
-Helper URL: Fungsi base_url() dan site_url() sudah bisa dipakai buat panggil aset CSS/JS dan navigasi link tanpa hardcode.  
-Alur Request: Jalur dari $\text{Browser} \rightarrow \text{Router} \rightarrow \text{Controller} \rightarrow \text{View}$ sudah jalan lancar (tanpa database). 

2.Yang Akan Dikerjakan di P3:-Model Layer: Mulai pakai folder models/ buat olah data.  
 -Koneksi Database & Keamanan: Connect ke MySQL pake MySQLi + prepared statement biar aman dari SQL Injection.   
 -Autentikasi: Bikin fitur Login/Logout, Session, dan hak akses pengguna.   
 -Integrasi UI: Pasang template AdminLTE 2.4.18 buat tampilan dashboard admin. 
# pertemuan-01
1. kesinambungan PWD–DPW–DPWL;
Jawaban:-PWD: Pondasi dasar front-end (HTML, CSS, JS) dan back-end (PHP & MySQL dasar).   
        -DPW: Lanjutan integrasi logika web, penanganan form, dan manipulasi database.   
        -DPWL: Pengembangan skala besar dengan framework MVC, arsitektur modular, dan keamanan.   
2. perbedaan PHP terstruktur dan MVC;
Jawaban:Terstruktur: Kode logika, database, dan HTML tercampur dalam satu file (spaghetti code), sulit dirawat.   Sedangkan MVC: Kode dipisah berdasarkan fungsi, lebih rapi, modular, dan mudah dikembangkan.   
3. fungsi Model, View, dan Controller;
Jawaban:-Model: Mengelola logika data, basis data, dan aturan bisnis (CRUD).
        -View: Menampilkan antarmuka pengguna (UI/HTML).
        -Controller: Menghubungkan Model dan View serta memproses input dari request pengguna.
4. alur request–response MVC;
Jawaban: 1.Alur Request–Response MVC
         2.Routing & Controller Menerima Request
         3.Komunikasi dengan Model (Data)
         4.Menyiapkan View
         5.Response ke User
5. pemetaan satu atau beberapa bagian/fitur aplikasi DPW ke Model, Controller, dan View disertai alasan; dan
Jawaban:-Model (MahasiswaModel.php): Menyimpan query SQL (SELECT, INSERT) dan validasi data.
Alasan: Model mengelola logika data, berkomunikasi dengan database, dan memastikan data memenuhi aturan sebelum tersimpan.
-Controller (MahasiswaController.php): Menerima input form dan menentukan alur tampilan.
Alasan: Controller memproses request pengguna, memanggil fungsi Model, lalu memilih View yang ditampilkan.
-View (mahasiswa_index.php): Menampilkan tabel data dan tombol aksi di layar.
Alasan: View merender HTML dan CSS saja — tanpa logika bisnis, tanpa koneksi database.
6. kesimpulan P1.
Jawaban:Kesimpulan P1 ini fokus pada dasar alur kerja pengembangan web modern dan penggunaan Git serta GitHub.    Mahasiswa belajar perintah dasar seperti git add, git commit, git push, dan git pull. Membuat README.md dan GitHub Pages membantu mereka dokumentasi proyek dan mulai membangun portofolio digital. Pengetahuan ini penting sebelum melanjutkan ke arsitektur MVC, MySQL, dan pengembangan aplikasi berbasis framework.
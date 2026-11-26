<?php

require_once APPPATH . 'models/Pembayaran_model.php';
require_once APPPATH . 'models/Transaksi_model.php';

class Pembayaran_controller extends Controller
{
  private Pembayaran_model $pembayaranModel;
  private Transaksi_model $transaksiModel;

  public function __construct()
  {
    require_once APPPATH . 'helpers/auth_helper.php';

    $this->pembayaranModel = new Pembayaran_model();
    $this->transaksiModel = new Transaksi_model();
  }

  /**
   * Menampilkan daftar pembayaran DP yang memiliki bukti
   * dan masih menunggu verifikasi admin.
   */
  public function verifikasiIndex(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    try {
      $daftarVerifikasi = $this->pembayaranModel
        ->getDaftarVerifikasi();

      $flash = $_SESSION['flash_pembayaran'] ?? null;
      unset($_SESSION['flash_pembayaran']);

      $this->view('admin/verifikasi_pembayaran', [
        'username' => $_SESSION['admin_username'],
        'daftarVerifikasi' => $daftarVerifikasi,
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log(
        'Daftar verifikasi pembayaran gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Daftar verifikasi pembayaran belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * Menampilkan detail pembayaran dan bukti transfer untuk admin.
   */
  public function verifikasiDetail(string $kodeBayar = ''): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeBayar = trim(str_replace('~', '/', $kodeBayar));

    if ($kodeBayar === '') {
      http_response_code(400);
      exit('Kode pembayaran wajib tersedia.');
    }

    try {
      $pembayaran = $this->pembayaranModel
        ->getDetailVerifikasi($kodeBayar);

      if ($pembayaran === null) {
        http_response_code(404);
        exit('Data pembayaran tidak ditemukan.');
      }

      $daftarKendaraan = $this->pembayaranModel
        ->getDaftarKendaraanSewa((string) $pembayaran['kode_sewa']);

      $flash = $_SESSION['flash_pembayaran'] ?? null;
      unset($_SESSION['flash_pembayaran']);

      $this->view('admin/verifikasi_pembayaran_detail', [
        'username' => $_SESSION['admin_username'],
        'pembayaran' => $pembayaran,
        'daftarKendaraan' => $daftarKendaraan,
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log(
        'Detail verifikasi pembayaran gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Detail verifikasi pembayaran belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * Memproses keputusan admin: valid atau invalid.
   * Model memastikan bukti tersedia, status masih pending,
   * dan perubahan t_bayar serta t_sewa berlangsung atomik.
   */
  public function prosesVerifikasi(string $kodeBayar = ''): void
  {
    require_admin_login();
    $this->wajibMetode('POST');

    $kodeBayar = trim(str_replace('~', '/', $kodeBayar));
    $statusBaru = trim((string) ($_POST['sttus_byr'] ?? ''));

    if ($kodeBayar === '') {
      http_response_code(400);
      exit('Kode pembayaran wajib tersedia.');
    }

    if (!in_array($statusBaru, ['valid', 'invalid'], true)) {
      $this->redirectDenganPesan(
        'admin/pembayaran/verifikasi/' . str_replace('/', '~', $kodeBayar),
        'error',
        'Keputusan verifikasi tidak valid.'
      );
    }

    try {
      $this->pembayaranModel->verifikasiPembayaran(
        $kodeBayar,
        $statusBaru
      );

      $pesan = $statusBaru === 'valid'
        ? 'Pembayaran berhasil diterima dan transaksi disetujui.'
        : 'Pembayaran ditolak dan transaksi diperbarui menjadi ditolak.';

      $this->redirectDenganPesan(
        'admin/pembayaran',
        'success',
        $pesan
      );
    } catch (Throwable $e) {
      error_log(
        'Proses verifikasi pembayaran gagal: '
          . $e->getMessage()
      );

      $this->redirectDenganPesan(
        'admin/pembayaran/verifikasi/' . str_replace('/', '~', $kodeBayar),
        'error',
        'Pembayaran tidak dapat diverifikasi. Pastikan status masih menunggu verifikasi dan bukti tersedia.'
      );
    }
  }

  /**
   * P10: Menampilkan daftar transaksi yang masih memiliki kewajiban pelunasan.
   */
  public function pelunasanIndex(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    try {
      $daftarPelunasan = $this->pembayaranModel
        ->getDaftarPelunasan();

      $flash = $_SESSION['flash_pembayaran'] ?? null;
      unset($_SESSION['flash_pembayaran']);

      $this->view('admin/pelunasan', [
        'username' => $_SESSION['admin_username'],
        'daftarPelunasan' => $daftarPelunasan,
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log(
        'Daftar pelunasan gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Daftar transaksi pelunasan belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * P10: Menampilkan detail transaksi, riwayat pembayaran, dan sisa kewajiban.
   */
  public function pelunasanDetail(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    try {
      $detailPelunasan = $this->pembayaranModel
        ->getDetailPelunasan($kodeSewa);

      if ($detailPelunasan === null) {
        http_response_code(404);
        exit('Transaksi sewa tidak ditemukan.');
      }

      $flash = $_SESSION['flash_pembayaran'] ?? null;
      unset($_SESSION['flash_pembayaran']);

      $this->view('admin/pelunasan_detail', [
        'username' => $_SESSION['admin_username'],
        'detailPelunasan' => $detailPelunasan,
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log(
        'Detail pelunasan gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Detail transaksi pelunasan belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * P10: Mencatat pelunasan yang diterima langsung oleh admin.
   * Tunai: tidak memerlukan unggahan bukti.
   * Transfer: admin mengunggah bukti yang diterima dari penyewa.
   * Model menghitung nominal sisa kewajiban dan menyimpan pembayaran
   * dengan status valid dalam satu transaksi database.
   */
  public function prosesPelunasan(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('POST');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));
    $metodeBayar = trim((string) ($_POST['mtode_byr'] ?? ''));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    if (!in_array($metodeBayar, ['tunai', 'transfer'], true)) {
      $this->redirectDenganPesan(
        'admin/pembayaran/pelunasan/' . str_replace('/', '~', $kodeSewa),
        'error',
        'Metode pembayaran harus tunai atau transfer.'
      );
    }

    $namaFileBukti = null;

    if ($metodeBayar === 'transfer') {
      $hasilUpload = $this->simpanFileBukti('ft_byr');

      if (!$hasilUpload['success']) {
        $this->redirectDenganPesan(
          'admin/pembayaran/pelunasan/' . str_replace('/', '~', $kodeSewa),
          'error',
          $hasilUpload['error']
        );
      }

      $namaFileBukti = (string) $hasilUpload['filename'];
    }

    try {
      $kodeBayar = $this->pembayaranModel->simpanPelunasan(
        $kodeSewa,
        $metodeBayar,
        $namaFileBukti
      );

      $this->redirectDenganPesan(
        'admin/pembayaran/pelunasan/' . str_replace('/', '~', $kodeSewa),
        'success',
        "Pelunasan berhasil dicatat dengan kode pembayaran {$kodeBayar}. "
          . 'Status pembayaran: valid; status transaksi: lunas.'
      );
    } catch (InvalidArgumentException $e) {
      if ($namaFileBukti !== null) {
        $this->hapusBuktiBaru($namaFileBukti);
      }

      $this->redirectDenganPesan(
        'admin/pembayaran/pelunasan/' . str_replace('/', '~', $kodeSewa),
        'error',
        $e->getMessage()
      );
    } catch (Throwable $e) {
      error_log(
        'Proses pelunasan gagal: '
          . $e->getMessage()
      );

      if ($namaFileBukti !== null) {
        $this->hapusBuktiBaru($namaFileBukti);
      }

      $this->redirectDenganPesan(
        'admin/pembayaran/pelunasan/' . str_replace('/', '~', $kodeSewa),
        'error',
        'Pelunasan belum berhasil dicatat. Pastikan transaksi masih memiliki '
          . 'sisa kewajiban dan belum pernah dilunasi.'
      );
    }
  }

  /**
   * P10: Menampilkan dokumen transaksi yang siap dicetak.
   * Data disusun Model dari t_sewa, t_penyewa, t_pilih, t_mobil,
   * dan t_bayar. Method ini hanya menyajikan data.
   */
  public function dokumenTransaksi(string $kodeSewa = ''): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeSewa = trim(str_replace('~', '/', $kodeSewa));

    if ($kodeSewa === '') {
      http_response_code(400);
      exit('Kode transaksi sewa wajib tersedia.');
    }

    try {
      $detailPelunasan = $this->pembayaranModel
        ->getDetailPelunasan($kodeSewa);

      if ($detailPelunasan === null) {
        http_response_code(404);
        exit('Data transaksi tidak ditemukan.');
      }

      $this->view('admin/dokumen_transaksi', [
        'detailPelunasan' => $detailPelunasan
      ]);
    } catch (Throwable $e) {
      error_log(
        'Dokumen transaksi gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Dokumen transaksi belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * Menampilkan daftar pembayaran DP milik penyewa yang sedang login.
   */
  public function index(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    try {
      $daftarPembayaran = $this->pembayaranModel
        ->getDaftarPembayaranPenyewa($userPlg);

      $flash = $_SESSION['flash_pembayaran'] ?? null;
      unset($_SESSION['flash_pembayaran']);

      $jumlahKeranjang = $this->transaksiModel
        ->getJumlahMobilKeranjang($userPlg);

      $this->view('penyewa/pembayaran', [
        'daftarPembayaran' => $daftarPembayaran,
        'flash' => $flash,
        'jumlahKeranjang' => $jumlahKeranjang
      ]);
    } catch (Throwable $e) {
      error_log(
        'Daftar pembayaran penyewa gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Daftar pembayaran belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * Menampilkan detail pembayaran DP milik penyewa yang login.
   */
  public function detail(string $kodeBayar = ''): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    $kodeBayar = trim(str_replace('~', '/', $kodeBayar));

    if ($kodeBayar === '') {
      http_response_code(400);
      exit('Kode pembayaran wajib tersedia.');
    }

    try {
      $pembayaran = $this->pembayaranModel
        ->getPembayaranPenyewa($kodeBayar, $userPlg);

      if ($pembayaran === null) {
        http_response_code(404);
        exit('Pembayaran tidak ditemukan.');
      }

      $daftarKendaraan = $this->pembayaranModel
        ->getDaftarKendaraanSewa((string) $pembayaran['kode_sewa']);

      $flash = $_SESSION['flash_pembayaran'] ?? null;
      unset($_SESSION['flash_pembayaran']);

      $jumlahKeranjang = $this->transaksiModel
        ->getJumlahMobilKeranjang($userPlg);

      $this->view('penyewa/pembayaran_detail', [
        'pembayaran' => $pembayaran,
        'daftarKendaraan' => $daftarKendaraan,
        'flash' => $flash,
        'jumlahKeranjang' => $jumlahKeranjang
      ]);
    } catch (Throwable $e) {
      error_log(
        'Detail pembayaran penyewa gagal ditampilkan: '
          . $e->getMessage()
      );

      http_response_code(500);
      exit('Detail pembayaran belum dapat ditampilkan. '
        . 'Silakan coba kembali.');
    }
  }

  /**
   * Menerima unggahan bukti transfer dari penyewa.
   *
   * Route yang direncanakan:
   * pembayaran/upload-bukti/{kode_bayar}
   *
   * Nama field formulir:
   * ft_byr
   */
  public function uploadBukti(string $kodeBayar = ''): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('POST');

    $kodeBayar = trim(str_replace('~', '/', $kodeBayar));

    if ($kodeBayar === '') {
      http_response_code(400);
      exit('Kode pembayaran wajib tersedia.');
    }

    $hasilUpload = $this->simpanFileBukti('ft_byr');

    if (!$hasilUpload['success']) {
      $this->redirectDenganPesan(
        'pembayaran/detail/' . str_replace('/', '~', $kodeBayar),
        'error',
        $hasilUpload['error']
      );
    }

    $namaFileBaru = $hasilUpload['filename'];
    $namaFileLama = null;

    try {
      /*
       * Model memeriksa kepemilikan penyewa dan status pembayaran
       * sebelum memperbarui record DP yang sudah dibuat saat checkout.
       */
      $namaFileLama = $this->pembayaranModel
        ->simpanBuktiPembayaran(
          $kodeBayar,
          $userPlg,
          $namaFileBaru
        );

      // Hapus bukti lama hanya setelah transaksi database berhasil commit.
      if ($namaFileLama !== null) {
        $this->hapusBuktiLama($namaFileLama);
      }

      $this->redirectDenganPesan(
        'pembayaran/detail/' . str_replace('/', '~', $kodeBayar),
        'success',
        'Bukti pembayaran berhasil diunggah. Pembayaran menunggu verifikasi admin.'
      );
    } catch (InvalidArgumentException $e) {
      $this->hapusBuktiBaru($namaFileBaru);

      $this->redirectDenganPesan(
        'pembayaran/detail/' . str_replace('/', '~', $kodeBayar),
        'error',
        $e->getMessage()
      );
    } catch (Throwable $e) {
      error_log(
        'Unggah bukti pembayaran gagal: ' . $e->getMessage()
      );

      // Jika pembaruan database gagal, file baru tidak boleh tertinggal.
      $this->hapusBuktiBaru($namaFileBaru);

      $this->redirectDenganPesan(
        'pembayaran/detail/' . str_replace('/', '~', $kodeBayar),
        'error',
        'Bukti pembayaran belum berhasil disimpan. Silakan coba kembali.'
      );
    }
  }

  /**
   * Memvalidasi dan menyimpan satu file gambar bukti pembayaran.
   *
   * Validasi menggunakan ukuran aktual, MIME hasil pemeriksaan server,
   * dan pemeriksaan struktur gambar. Ekstensi dari nama file pengguna
   * tidak digunakan.
   */
  private function simpanFileBukti(string $field): array
  {
    if ($field !== 'ft_byr') {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Field unggahan tidak valid.'
      ];
    }

    if (
      !isset($_FILES[$field]) ||
      !is_array($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Pilih bukti transfer terlebih dahulu.'
      ];
    }

    $file = $_FILES[$field];
    $uploadError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($uploadError !== UPLOAD_ERR_OK) {
      $pesan = match ($uploadError) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
        'Ukuran bukti pembayaran melebihi batas unggahan.',
        UPLOAD_ERR_PARTIAL =>
        'Bukti pembayaran hanya terunggah sebagian. Silakan ulangi.',
        UPLOAD_ERR_NO_TMP_DIR =>
        'Folder sementara unggahan tidak tersedia.',
        UPLOAD_ERR_CANT_WRITE =>
        'Server gagal menulis berkas unggahan.',
        UPLOAD_ERR_EXTENSION =>
        'Unggahan dihentikan oleh konfigurasi server.',
        default =>
        'Unggah bukti pembayaran gagal. Silakan ulangi.'
      };

      return [
        'success' => false,
        'filename' => null,
        'error' => $pesan
      ];
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $ukuran = (int) ($file['size'] ?? 0);

    if (
      $tmpName === '' ||
      !is_uploaded_file($tmpName) ||
      $ukuran < 1 ||
      $ukuran > 5 * 1024 * 1024
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Bukti pembayaran harus berupa gambar berukuran maksimal 5 MB.'
      ];
    }

    if (!class_exists('finfo')) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Ekstensi Fileinfo PHP belum tersedia pada server.'
      ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmpName);

    $formatDiizinkan = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp'
    ];

    if (
      !isset($formatDiizinkan[$mime]) ||
      @getimagesize($tmpName) === false
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Format bukti pembayaran harus JPEG, PNG, atau WEBP yang valid.'
      ];
    }

    $uploadDir = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'bayar'
      . DIRECTORY_SEPARATOR;

    if (
      !is_dir($uploadDir) &&
      !mkdir($uploadDir, 0755, true) &&
      !is_dir($uploadDir)
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Folder penyimpanan bukti pembayaran tidak dapat dibuat.'
      ];
    }

    if (!is_writable($uploadDir)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Folder penyimpanan bukti pembayaran tidak dapat ditulis.'
      ];
    }

    try {
      $namaFile = 'bukti_' . date('YmdHis') . '_'
        . bin2hex(random_bytes(8)) . '.'
        . $formatDiizinkan[$mime];
    } catch (Throwable $e) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Nama file bukti pembayaran gagal dibuat.'
      ];
    }

    if (!move_uploaded_file($tmpName, $uploadDir . $namaFile)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Bukti pembayaran gagal dipindahkan ke folder penyimpanan.'
      ];
    }

    return [
      'success' => true,
      'filename' => $namaFile,
      'error' => null
    ];
  }

  /**
   * Menghapus file baru apabila pembaruan database gagal.
   * Nama file dibatasi pada pola yang dibuat oleh simpanFileBukti().
   */
  private function hapusBuktiBaru(string $namaFile): void
  {
    if (
      !preg_match(
        '/^bukti_\d{14}_[a-f0-9]{16}\.(jpg|png|webp)$/',
        $namaFile
      )
    ) {
      return;
    }

    $path = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'bayar'
      . DIRECTORY_SEPARATOR . $namaFile;

    if (is_file($path)) {
      @unlink($path);
    }
  }

  /**
   * Menghapus bukti lama setelah bukti baru berhasil dicatat di database.
   */
  private function hapusBuktiLama(string $namaFile): void
  {
    if (
      !preg_match(
        '/^bukti_\d{14}_[a-f0-9]{16}\.(jpg|png|webp)$/',
        $namaFile
      )
    ) {
      return;
    }

    $path = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'bayar'
      . DIRECTORY_SEPARATOR . $namaFile;

    if (is_file($path)) {
      @unlink($path);
    }
  }

  /**
   * Memastikan sesi penyewa tersedia.
   */
  private function wajibLoginPenyewa(): string
  {
    $userPlg = trim(
      (string) ($_SESSION['penyewa_user_plg'] ?? '')
    );

    if ($userPlg === '') {
      $_SESSION['flash_penyewa_nav'] = [
        'type' => 'info',
        'message' => 'Silakan login terlebih dahulu untuk mengakses pembayaran.'
      ];

      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    return $userPlg;
  }

  /**
   * Membatasi method HTTP untuk setiap action.
   */
  private function wajibMetode(string $metodeDiizinkan): void
  {
    $metodeAktif = strtoupper(
      (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')
    );

    if ($metodeAktif !== $metodeDiizinkan) {
      http_response_code(405);
      header('Allow: ' . $metodeDiizinkan);
      exit('Method Not Allowed');
    }
  }

  /**
   * Menyimpan pesan dan menerapkan pola Post-Redirect-Get (PRG).
   */
  private function redirectDenganPesan(
    string $uri,
    string $jenis,
    string $pesan
  ): never {
    $_SESSION['flash_pembayaran'] = [
      'type' => $jenis,
      'message' => $pesan
    ];

    header('Location: ' . site_url($uri));
    exit;
  }
}

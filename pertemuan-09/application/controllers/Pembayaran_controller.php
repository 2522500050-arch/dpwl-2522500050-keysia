<?php
// P9 Tahap 2 - Controller pembayaran awal (DP) untuk PENYEWA.
require_once APPPATH . 'models/Bayar_model.php';
require_once APPPATH . 'models/Transaksi_model.php';
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';
require_once APPPATH . 'helpers/bukti_helper.php';

class Pembayaran_controller extends Controller
{
  private const UKURAN_MAKS = 2 * 1024 * 1024; // 2 MB

  private const MIME_DIIZINKAN = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
  ];

  private const EKSTENSI_SESUAI = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png'  => ['png'],
    'image/webp' => ['webp'],
  ];

  private Bayar_model $model;
  private Transaksi_model $transaksiModel;

  public function __construct()
  {
    $this->model = new Bayar_model();
    $this->transaksiModel = new Transaksi_model();
  }

  private function wajibLoginPenyewa(): string
  {
    $userPlg = trim((string) ($_SESSION['penyewa_user_plg'] ?? ''));

    if ($userPlg === '') {
      if (is_admin_logged_in()) {
        http_response_code(403);
        exit('Akses ditolak. Halaman pembayaran ini khusus penyewa.');
      }

      $_SESSION['flash_penyewa_nav'] =
        'Silakan login terlebih dahulu untuk mengakses pembayaran.';
      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    return $userPlg;
  }

  private function wajibMetode(string $diizinkan): void
  {
    $aktif = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($aktif !== $diizinkan) {
      http_response_code(405);
      header('Allow: ' . $diizinkan);
      exit('Method Not Allowed');
    }
  }

  private function kodeBayarDariRequest(array $sumber): string
  {
    $kode = trim((string) ($sumber['kode_bayar'] ?? ''));
    if (preg_match('#\APAY/\d{8}/\d{2}\z#', $kode) !== 1) {
      return '';
    }
    return $kode;
  }

  private function tolakAkses(): never
  {
    http_response_code(403);
    exit('Akses ditolak. Pembayaran tidak ditemukan atau bukan milik akun ini.');
  }

  private function redirectDetail(string $kodeBayar, string $jenis, string $pesan): never
  {
    $_SESSION['flash_pembayaran'] = ['type' => $jenis, 'message' => $pesan];
    header('Location: ' . site_url('pembayaran/detail?kode_bayar=' . rawurlencode($kodeBayar)));
    exit;
  }

  private function redirectDaftar(string $jenis, string $pesan): never
  {
    $_SESSION['flash_pembayaran'] = ['type' => $jenis, 'message' => $pesan];
    header('Location: ' . site_url('pembayaran'));
    exit;
  }

  private function ambilFlash(): ?array
  {
    $flash = $_SESSION['flash_pembayaran'] ?? null;
    unset($_SESSION['flash_pembayaran']);
    return is_array($flash) ? $flash : null;
  }

  public function index(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    try {
      $this->view('pembayaran/index', [
        'daftar' => $this->model->getDaftarByPenyewa($userPlg),
        'flash' => $this->ambilFlash(),
        'jumlahKeranjang' => $this->transaksiModel->getJumlahMobilKeranjang($userPlg),
      ]);
    } catch (Throwable $e) {
      error_log('Daftar pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Daftar pembayaran belum dapat ditampilkan. Silakan coba kembali.');
    }
  }

  public function detail(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    $kodeBayar = $this->kodeBayarDariRequest($_GET);
    if ($kodeBayar === '') {
      $this->tolakAkses();
    }

    try {
      $bayar = $this->model->getDetailMilikPenyewa($kodeBayar, $userPlg);
      if ($bayar === null) {
        $this->tolakAkses();
      }

      $this->view('pembayaran/detail', [
        'bayar' => $bayar,
        'flash' => $this->ambilFlash(),
        'jumlahKeranjang' => $this->transaksiModel->getJumlahMobilKeranjang($userPlg),
      ]);
    } catch (Throwable $e) {
      error_log('Detail pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Detail pembayaran belum dapat ditampilkan. Silakan coba kembali.');
    }
  }

  public function bukti(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('GET');

    $kodeBayar = $this->kodeBayarDariRequest($_GET);
    if ($kodeBayar === '') {
      $this->tolakAkses();
    }

    try {
      $bayar = $this->model->getDetailMilikPenyewa($kodeBayar, $userPlg);
    } catch (Throwable $e) {
      error_log('Bukti pembayaran gagal dibaca: ' . $e->getMessage());
      http_response_code(500);
      exit('Bukti pembayaran belum dapat ditampilkan.');
    }

    if ($bayar === null) {
      $this->tolakAkses();
    }

    bukti_bayar_kirim($bayar['ft_byr'] ?? null);
  }

  public function unggah(): void
  {
    $userPlg = $this->wajibLoginPenyewa();
    $this->wajibMetode('POST');

    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
      $this->redirectDaftar(
        'error',
        'Ukuran berkas melebihi batas yang diizinkan server (maksimal 2 MB).'
      );
    }

    $kodeBayar = $this->kodeBayarDariRequest($_POST);
    if ($kodeBayar === '') {
      $this->tolakAkses();
    }

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
      $this->redirectDetail(
        $kodeBayar,
        'error',
        'Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.'
      );
    }

    try {
      $bayar = $this->model->getDetailMilikPenyewa($kodeBayar, $userPlg);
    } catch (Throwable $e) {
      error_log('Pemeriksaan pembayaran gagal: ' . $e->getMessage());
      $this->redirectDaftar('error', 'Pembayaran belum dapat diproses. Silakan coba kembali.');
    }

    if ($bayar === null) {
      $this->tolakAkses();
    }

    if ($bayar['sttus_byr'] !== 'pending') {
      $this->redirectDetail(
        $kodeBayar,
        'error',
        'Pembayaran ini sudah diverifikasi dan tidak dapat diubah lagi.'
      );
    }

    $hasil = $this->validasiDanSimpanBerkas('bukti');
    if (!$hasil['success']) {
      $this->redirectDetail($kodeBayar, 'error', (string) $hasil['error']);
    }

    $namaBaru = (string) $hasil['filename'];

    try {
      $berkasLama = $this->model->simpanBukti($kodeBayar, $userPlg, $namaBaru);
      bukti_bayar_hapus($berkasLama);

      $this->redirectDetail(
        $kodeBayar,
        'success',
        'Bukti pembayaran berhasil diunggah. Status tetap pending sampai diverifikasi admin.'
      );
    } catch (InvalidArgumentException $e) {
      bukti_bayar_hapus($namaBaru);
      $this->redirectDetail($kodeBayar, 'error', $e->getMessage());
    } catch (Throwable $e) {
      bukti_bayar_hapus($namaBaru);
      error_log('Unggah bukti gagal: ' . $e->getMessage());
      $this->redirectDetail(
        $kodeBayar,
        'error',
        'Bukti pembayaran belum berhasil disimpan. Silakan coba kembali.'
      );
    }
  }

  private function gagal(string $pesan): array
  {
    return ['success' => false, 'filename' => null, 'error' => $pesan];
  }

  private function validasiDanSimpanBerkas(string $field): array
  {
    if (
      !isset($_FILES[$field]) ||
      !is_array($_FILES[$field]) ||
      is_array($_FILES[$field]['name'] ?? null)
    ) {
      return $this->gagal('Pilih berkas bukti transfer terlebih dahulu.');
    }

    $file = $_FILES[$field];
    $kodeError = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($kodeError === UPLOAD_ERR_NO_FILE) {
      return $this->gagal('Pilih berkas bukti transfer terlebih dahulu.');
    }
    if ($kodeError === UPLOAD_ERR_INI_SIZE || $kodeError === UPLOAD_ERR_FORM_SIZE) {
      return $this->gagal('Ukuran berkas melebihi batas maksimal 2 MB.');
    }
    if ($kodeError !== UPLOAD_ERR_OK) {
      return $this->gagal('Unggah berkas gagal. Silakan coba kembali.');
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $ukuran = (int) ($file['size'] ?? 0);

    if ($tmp === '' || !is_uploaded_file($tmp)) {
      return $this->gagal('Berkas unggahan tidak valid.');
    }
    if ($ukuran < 1) {
      return $this->gagal('Berkas tidak boleh kosong.');
    }
    if ($ukuran > self::UKURAN_MAKS) {
      return $this->gagal('Ukuran berkas melebihi batas maksimal 2 MB.');
    }

    $ekstensi = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

    if (
      !is_string($mime) ||
      !isset(self::MIME_DIIZINKAN[$mime]) ||
      !in_array($ekstensi, self::EKSTENSI_SESUAI[$mime], true)
    ) {
      return $this->gagal('Jenis berkas harus gambar JPG, JPEG, PNG, atau WEBP.');
    }

    if (@getimagesize($tmp) === false) {
      return $this->gagal('Isi berkas bukan gambar yang valid.');
    }

    $dir = bukti_bayar_dir();
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
      return $this->gagal('Folder penyimpanan bukti pembayaran tidak dapat dibuat.');
    }
    if (!is_writable($dir)) {
      return $this->gagal('Folder penyimpanan bukti pembayaran tidak dapat ditulis.');
    }

    try {
      $nama = 'bayar_' . date('YmdHis') . '_'
        . bin2hex(random_bytes(8)) . '.' . self::MIME_DIIZINKAN[$mime];
    } catch (Throwable $e) {
      return $this->gagal('Nama berkas gagal dibuat.');
    }

    if (!move_uploaded_file($tmp, $dir . $nama)) {
      return $this->gagal('Berkas gagal dipindahkan ke folder penyimpanan.');
    }

    @chmod($dir . $nama, 0644);

    return ['success' => true, 'filename' => $nama, 'error' => null];
  }
}

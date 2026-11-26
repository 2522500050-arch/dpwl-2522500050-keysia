<?php
// P9 Tahap 3 - Controller verifikasi pembayaran DP oleh ADMIN.
require_once APPPATH . 'models/Bayar_model.php';
require_once APPPATH . 'helpers/auth_helper.php';
require_once APPPATH . 'helpers/csrf_helper.php';
require_once APPPATH . 'helpers/bukti_helper.php';
require_once APPPATH . 'helpers/flash_helper.php';

class Verifikasi_controller extends Controller
{
  private Bayar_model $model;

  public function __construct()
  {
    $this->model = new Bayar_model();
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

  // GET admin/pembayaran
  public function index(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $status = trim((string) ($_GET['status'] ?? ''));
    $filter = in_array($status, ['pending', 'valid', 'invalid'], true) ? $status : null;

    try {
      $this->view('admin/pembayaran_view', [
        'username' => $_SESSION['admin_username'],
        'daftar' => $this->model->getDaftarVerifikasiAdmin($filter),
        'filter_status' => $filter ?? 'semua',
        'flash' => get_flash_message(),
      ]);
    } catch (Throwable $e) {
      error_log('Daftar verifikasi pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Daftar verifikasi pembayaran belum dapat ditampilkan.');
    }
  }

  // GET admin/pembayaran/detail
  public function detail(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeBayar = $this->kodeBayarDariRequest($_GET);
    if ($kodeBayar === '') {
      set_flash_message('danger', 'Kode pembayaran tidak valid.');
      header('Location: ' . site_url('admin/pembayaran'));
      exit;
    }

    try {
      $bayar = $this->model->getDetailAdmin($kodeBayar);
      if ($bayar === null) {
        set_flash_message('danger', 'Data pembayaran tidak ditemukan.');
        header('Location: ' . site_url('admin/pembayaran'));
        exit;
      }

      $this->view('admin/pembayaran_detail', [
        'username' => $_SESSION['admin_username'],
        'bayar' => $bayar,
        'flash' => get_flash_message(),
      ]);
    } catch (Throwable $e) {
      error_log('Detail verifikasi pembayaran gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Detail pembayaran belum dapat ditampilkan.');
    }
  }

  // GET admin/pembayaran/bukti
  public function bukti(): void
  {
    require_admin_login();
    $this->wajibMetode('GET');

    $kodeBayar = $this->kodeBayarDariRequest($_GET);
    if ($kodeBayar === '') {
      http_response_code(400);
      exit('Kode pembayaran tidak valid.');
    }

    try {
      $bayar = $this->model->getDetailAdmin($kodeBayar);
    } catch (Throwable $e) {
      http_response_code(500);
      exit('Bukti pembayaran belum dapat ditampilkan.');
    }

    if ($bayar === null) {
      http_response_code(404);
      exit('Data pembayaran tidak ditemukan.');
    }

    bukti_bayar_kirim($bayar['ft_byr'] ?? null);
  }

  // POST admin/pembayaran/proses
  public function proses(): void
  {
    require_admin_login();
    $this->wajibMetode('POST');

    $kodeBayar = $this->kodeBayarDariRequest($_POST);
    if ($kodeBayar === '') {
      set_flash_message('danger', 'Kode pembayaran tidak valid.');
      header('Location: ' . site_url('admin/pembayaran'));
      exit;
    }

    if (!csrf_valid($_POST['csrf_token'] ?? null)) {
      set_flash_message('danger', 'Token keamanan formulir tidak valid.');
      header('Location: ' . site_url('admin/pembayaran/detail?kode_bayar=' . rawurlencode($kodeBayar)));
      exit;
    }

    $keputusan = trim((string) ($_POST['keputusan'] ?? ''));

    try {
      $this->model->verifikasiPembayaran($kodeBayar, $keputusan);
      $label = $keputusan === 'terima' ? 'diterima (VALID)' : 'ditolak (INVALID)';
      set_flash_message('success', "Pembayaran {$kodeBayar} berhasil {$label}.");
    } catch (InvalidArgumentException $e) {
      set_flash_message('danger', $e->getMessage());
    } catch (Throwable $e) {
      error_log('Proses verifikasi pembayaran gagal: ' . $e->getMessage());
      set_flash_message('danger', 'Proses verifikasi gagal dijalankan.');
    }

    header('Location: ' . site_url('admin/pembayaran/detail?kode_bayar=' . rawurlencode($kodeBayar)));
    exit;
  }
}

<?php
class Auth extends Controller
{
  public function login(): void
  {
    // 1. KODE BAGIAN ATAS: Pengecekan HTTP Method (diambil dari langkah P3 awal / P2)
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
      $this->view('auth/login');
      return;
    }

    if ($method !== 'POST') {
      http_response_code(405);
      header('Allow: GET, POST');
      exit('Method Not Allowed');
    }

    // 2. Mengambil input dari form login
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 3. Validasi Username (Sub-bab J.3.2)
    if (
      $username === '' ||
      strlen($username) > 15 ||
      preg_match('/[\x00-\x1F\x7F]/', $username)
    ) {
      $this->view('auth/login', [
        'error' => 'Username tidak valid.'
      ]);
      return;
    }

    // 4. Validasi Password
    if ($password === '') {
      $this->view('auth/login', [
        'error' => 'Password wajib diisi.'
      ]);
      return;
    }

    require_once APPPATH . 'models/Admin_model.php';

    $model = new Admin_model();
    $admin = $model->findByUsername($username);

    // 1. Cek apakah username tidak terdaftar atau password-nya salah
    if (!$admin || !password_verify($password, $admin['password'])) {
      $this->view('auth/login', [
        'error' => 'Username atau password tidak sesuai.'
      ]);
      return;
    }

    // 2. Cek jika status akun nonaktif (password sudah pasti benar jika lolos dari cek di atas)
    if ($admin['status_akun'] !== 'aktif') {
      $this->view('auth/login', [
        'error' => 'Maaf, akun Anda dinonaktifkan.'
      ]);
      return;
    }

    // PERBAIKAN: Ditambahkan titik koma (;) di akhir baris ini
    session_regenerate_id(true);

    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_status'] = $admin['status_akun'];

    header('Location: ' . site_url('admin'));
    exit;
  }

  public function logout(): void
  {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      header('Allow: POST');
      exit('Method Not Allowed');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();

      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }
    session_destroy();
    header('Location: ' . site_url('auth/login'));
    exit;
  }
}

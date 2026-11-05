<?php
require_once APPPATH . 'models/Penyewa_model.php';
require_once APPPATH . 'models/Transaksi_model.php';

class Penyewa_controller extends Controller
{
  private Penyewa_model $model;
  private Transaksi_model $transaksiModel;

  public function __construct()
  {
    $this->model = new Penyewa_model();
    $this->transaksiModel = new Transaksi_model();
  }

  /*
     * =========================================================
     * CRUD ADMIN PENYEWA
     * =========================================================
     */

  private function pastikanAdmin(): void
  {
    // Sesuaikan 'admin_user' jika nama session login admin
    // di project kamu berbeda.
    if (empty($_SESSION['admin_user'])) {
      $_SESSION['flash_admin_penyewa'] =
        'Silakan login sebagai admin terlebih dahulu.';
      header('Location: ' . site_url('auth/login-admin'));
      exit;
    }
  }

  public function indexAdmin(): void
  {
    $this->pastikanAdmin();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
      http_response_code(405);
      header('Allow: GET');
      exit('Method Not Allowed');
    }

    try {
      $penyewa = $this->model->getAllAdmin();
      $flash = $_SESSION['flash_admin_penyewa'] ?? null;
      unset($_SESSION['flash_admin_penyewa']);

      $this->view('admin/penyewa_view', [
        'penyewa' => $penyewa,
        'flash' => $flash
      ]);
    } catch (Throwable $e) {
      error_log('Pengambilan data penyewa admin gagal: ' . $e->getMessage());
      http_response_code(500);
      exit('Data penyewa belum dapat ditampilkan. Silakan coba kembali.');
    }
  }

  public function tambahAdmin(): void
  {
    $this->pastikanAdmin();

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
      $this->view('admin/penyewa_form', [
        'mode' => 'tambah',
        'penyewa' => [],
        'old' => [],
        'error' => null
      ]);
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      header('Allow: GET, POST');
      exit('Method Not Allowed');
    }

    $userPlg = trim((string) ($_POST['user_plg'] ?? ''));
    $nik = trim((string) ($_POST['nik'] ?? ''));
    $nama = trim((string) ($_POST['nm_lngkp'] ?? ''));
    $noHp = trim((string) ($_POST['no_hp'] ?? ''));
    $alamat = trim((string) ($_POST['almt_lngkp'] ?? ''));
    $password = (string) ($_POST['sandi'] ?? '');

    $old = [
      'user_plg' => $userPlg,
      'nik' => $nik,
      'nm_lngkp' => $nama,
      'no_hp' => $noHp,
      'almt_lngkp' => $alamat
    ];

    $error = $this->validateAdminPenyewaInput(
      $userPlg,
      $nik,
      $nama,
      $noHp,
      $alamat
    );

    if ($error === null && ($password === '' || strlen($password) < 8 || strlen($password) > 72)) {
      $error = 'Password wajib diisi dengan panjang 8 sampai 72 byte.';
    }

    if ($error !== null) {
      $this->renderAdminPenyewaForm('tambah', $old, $error);
      return;
    }

    $uploadedFiles = [];

    try {
      if ($this->model->findAdminByUserPlg($userPlg) !== null) {
        $this->renderAdminPenyewaForm(
          'tambah',
          $old,
          'Username penyewa sudah digunakan.'
        );
        return;
      }

      $hash = password_hash($password, PASSWORD_DEFAULT);
      if ($hash === false) {
        throw new RuntimeException('Pembuatan hash password gagal.');
      }

      $uploadKtp = $this->uploadFotoPenyewa('ft_ktp');
      if (!$uploadKtp['success']) {
        $this->renderAdminPenyewaForm('tambah', $old, $uploadKtp['error']);
        return;
      }
      if ($uploadKtp['filename'] !== null) {
        $uploadedFiles[] = $uploadKtp['filename'];
      }

      $uploadSim = $this->uploadFotoPenyewa('ft_sim_a');
      if (!$uploadSim['success']) {
        $this->hapusFotoPenyewa($uploadedFiles);
        $this->renderAdminPenyewaForm('tambah', $old, $uploadSim['error']);
        return;
      }
      if ($uploadSim['filename'] !== null) {
        $uploadedFiles[] = $uploadSim['filename'];
      }

      $success = $this->model->createLengkap([
        'user_plg' => $userPlg,
        'sandi' => $hash,
        'nik' => $nik,
        'nm_lngkp' => $nama,
        'no_hp' => $noHp,
        'almt_lngkp' => $alamat,
        'ft_ktp' => $uploadKtp['filename'],
        'ft_sim_a' => $uploadSim['filename']
      ]);

      if (!$success) {
        $this->hapusFotoPenyewa($uploadedFiles);
        $this->renderAdminPenyewaForm(
          'tambah',
          $old,
          'Data penyewa gagal ditambahkan. Periksa kembali data yang dimasukkan.'
        );
        return;
      }

      $_SESSION['flash_admin_penyewa'] = 'Data penyewa berhasil ditambahkan.';
      header('Location: ' . site_url('penyewa/indexAdmin'));
      exit;
    } catch (Throwable $e) {
      error_log('Tambah penyewa admin gagal: ' . $e->getMessage());
      if (!empty($uploadedFiles)) {
        $this->hapusFotoPenyewa($uploadedFiles);
      }
      $this->renderAdminPenyewaForm(
        'tambah',
        $old,
        'Data penyewa belum berhasil ditambahkan. Silakan coba kembali.'
      );
    }
  }

  public function ubahAdmin(string $userPlg = ''): void
  {
    $this->pastikanAdmin();

    $userPlg = trim($userPlg !== '' ? $userPlg : (string) (
      $_GET['user_plg'] ?? $_POST['user_plg'] ?? ''
    ));

    if ($userPlg === '') {
      $_SESSION['flash_admin_penyewa'] = 'Username penyewa tidak ditemukan.';
      header('Location: ' . site_url('penyewa/indexAdmin'));
      exit;
    }

    try {
      $penyewa = $this->model->findAdminByUserPlg($userPlg);
      if ($penyewa === null) {
        $_SESSION['flash_admin_penyewa'] = 'Data penyewa tidak ditemukan.';
        header('Location: ' . site_url('penyewa/indexAdmin'));
        exit;
      }

      if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
        $this->view('admin/penyewa_form', [
          'mode' => 'ubah',
          'penyewa' => $penyewa,
          'old' => [],
          'error' => null
        ]);
        return;
      }

      if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: GET, POST');
        exit('Method Not Allowed');
      }

      $nik = trim((string) ($_POST['nik'] ?? ''));
      $nama = trim((string) ($_POST['nm_lngkp'] ?? ''));
      $noHp = trim((string) ($_POST['no_hp'] ?? ''));
      $alamat = trim((string) ($_POST['almt_lngkp'] ?? ''));
      $password = (string) ($_POST['sandi'] ?? '');

      $old = [
        'user_plg' => $userPlg,
        'nik' => $nik,
        'nm_lngkp' => $nama,
        'no_hp' => $noHp,
        'almt_lngkp' => $alamat
      ];

      $error = $this->validateAdminPenyewaInput(
        $userPlg,
        $nik,
        $nama,
        $noHp,
        $alamat
      );

      if (
        $error === null && $password !== '' &&
        (strlen($password) < 8 || strlen($password) > 72)
      ) {
        $error = 'Password baru harus memiliki panjang 8 sampai 72 byte.';
      }

      if ($error !== null) {
        $this->renderAdminPenyewaForm('ubah', $old, $error, $penyewa);
        return;
      }

      $uploadedFiles = [];
      $uploadKtp = $this->uploadFotoPenyewa('ft_ktp');
      if (!$uploadKtp['success']) {
        $this->renderAdminPenyewaForm('ubah', $old, $uploadKtp['error'], $penyewa);
        return;
      }
      if ($uploadKtp['filename'] !== null) {
        $uploadedFiles[] = $uploadKtp['filename'];
      }

      $uploadSim = $this->uploadFotoPenyewa('ft_sim_a');
      if (!$uploadSim['success']) {
        $this->hapusFotoPenyewa($uploadedFiles);
        $this->renderAdminPenyewaForm('ubah', $old, $uploadSim['error'], $penyewa);
        return;
      }
      if ($uploadSim['filename'] !== null) {
        $uploadedFiles[] = $uploadSim['filename'];
      }

      $passwordHash = null;
      if ($password !== '') {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if ($passwordHash === false) {
          $this->hapusFotoPenyewa($uploadedFiles);
          throw new RuntimeException('Pembuatan hash password baru gagal.');
        }
      }

      $data = [
        'nik' => $nik,
        'nm_lngkp' => $nama,
        'no_hp' => $noHp,
        'almt_lngkp' => $alamat,
        'ft_ktp' => $uploadKtp['filename'] ?? ($penyewa['ft_ktp'] ?? null),
        'ft_sim_a' => $uploadSim['filename'] ?? ($penyewa['ft_sim_a'] ?? null)
      ];

      $success = $this->model->updateLengkap($userPlg, $data, $passwordHash);

      if (!$success) {
        $this->hapusFotoPenyewa($uploadedFiles);
        $this->renderAdminPenyewaForm(
          'ubah',
          $old,
          'Data penyewa gagal diperbarui.',
          $penyewa
        );
        return;
      }

      // Hapus foto lama setelah perubahan database berhasil.
      $fotoLama = [];
      if ($uploadKtp['filename'] !== null && !empty($penyewa['ft_ktp'])) {
        $fotoLama[] = ['ft_ktp', $penyewa['ft_ktp']];
      }
      if ($uploadSim['filename'] !== null && !empty($penyewa['ft_sim_a'])) {
        $fotoLama[] = ['ft_sim_a', $penyewa['ft_sim_a']];
      }
      $this->hapusFotoPenyewa($fotoLama);

      $_SESSION['flash_admin_penyewa'] = 'Data penyewa berhasil diperbarui.';
      header('Location: ' . site_url('penyewa/indexAdmin'));
      exit;
    } catch (Throwable $e) {
      error_log('Ubah penyewa admin gagal: ' . $e->getMessage());
      if (!empty($uploadedFiles)) {
        $this->hapusFotoPenyewa($uploadedFiles);
      }
      $this->renderAdminPenyewaForm(
        'ubah',
        $old ?? [],
        'Data penyewa belum berhasil diperbarui. Silakan coba kembali.',
        $penyewa
      );
    }
  }

  public function hapusAdmin(string $userPlg = ''): void
  {
    $this->pastikanAdmin();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
      http_response_code(405);
      header('Allow: POST');
      exit('Method Not Allowed');
    }

    $userPlg = trim($userPlg !== '' ? $userPlg : (string) ($_POST['user_plg'] ?? ''));
    if ($userPlg === '') {
      $_SESSION['flash_admin_penyewa'] = 'Username penyewa tidak valid.';
      header('Location: ' . site_url('penyewa/indexAdmin'));
      exit;
    }

    try {
      $penyewa = $this->model->findAdminByUserPlg($userPlg);
      if ($penyewa === null) {
        $_SESSION['flash_admin_penyewa'] = 'Data penyewa tidak ditemukan.';
        header('Location: ' . site_url('penyewa/indexAdmin'));
        exit;
      }

      if ($this->model->deleteAdmin($userPlg)) {
        $fotoLama = [];
        if (!empty($penyewa['ft_ktp'])) {
          $fotoLama[] = ['ft_ktp', $penyewa['ft_ktp']];
        }
        if (!empty($penyewa['ft_sim_a'])) {
          $fotoLama[] = ['ft_sim_a', $penyewa['ft_sim_a']];
        }
        $this->hapusFotoPenyewa($fotoLama);
        $_SESSION['flash_admin_penyewa'] = 'Data penyewa berhasil dihapus.';
      } else {
        $_SESSION['flash_admin_penyewa'] = 'Data penyewa gagal dihapus.';
      }
    } catch (Throwable $e) {
      error_log('Hapus penyewa admin gagal: ' . $e->getMessage());
      $_SESSION['flash_admin_penyewa'] = 'Data penyewa belum berhasil dihapus.';
    }

    header('Location: ' . site_url('penyewa/indexAdmin'));
    exit;
  }

  private function validateAdminPenyewaInput(
    string $userPlg,
    string $nik,
    string $nama,
    string $noHp,
    string $alamat
  ): ?string {
    if (
      $userPlg === '' || strlen($userPlg) > 50 ||
      !preg_match('/\A[a-zA-Z0-9_.-]+\z/', $userPlg)
    ) {
      return 'Username wajib diisi, maksimal 50 karakter, dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
    }

    if (!preg_match('/\A[0-9]{16}\z/', $nik)) {
      return 'NIK wajib terdiri dari tepat 16 digit angka.';
    }

    if (
      $nama === '' ||
      mb_strlen($nama) > 100 ||
      !preg_match("/\A[\p{L}\p{M}]+(?:[ .'-][\p{L}\p{M}]+)*\z/u", $nama)
    ) {
      return 'Nama lengkap wajib diisi, maksimal 100 karakter, dan hanya berisi huruf serta pemisah nama yang wajar.';
    }

    if (
      $noHp === '' ||
      strlen($noHp) > 15 ||
      !preg_match('/\A[0-9+(). -]+\z/', $noHp) ||
      !preg_match('/[0-9]/', $noHp)
    ) {
      return 'Nomor HP wajib diisi, maksimal 15 karakter, dan hanya boleh berisi angka serta tanda +, titik, kurung, spasi, atau tanda hubung.';
    }

    if (
      $alamat === '' ||
      mb_strlen($alamat) > 300 ||
      preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $alamat)
    ) {
      return 'Alamat wajib diisi, maksimal 300 karakter, dan tidak boleh mengandung karakter kontrol.';
    }

    return null;
  }

  private function renderAdminPenyewaForm(
    string $mode,
    array $old,
    string $error,
    array $penyewa = []
  ): void {
    $this->view('admin/penyewa_form', [
      'mode' => $mode,
      'penyewa' => $penyewa,
      'old' => $old,
      'error' => $error
    ]);
  }

  /*
     * =========================================================
     * PROFIL PENYEWA (KODE LAMA, DIPERTAHANKAN)
     * =========================================================
     */

  public function profil(): void
  {
    if (empty($_SESSION['penyewa_user_plg'])) {
      $_SESSION['flash_penyewa_nav'] =
        'Silakan login terlebih dahulu untuk mengakses profil.';
      header('Location: ' . site_url('auth/login-penyewa'));
      exit;
    }

    $userPlg = (string) $_SESSION['penyewa_user_plg'];

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      try {
        $profil = $this->model->getProfil($userPlg);
        if ($profil === null) {
          unset($_SESSION['penyewa_user_plg']);
          $_SESSION['flash_penyewa_nav'] =
            'Data akun tidak ditemukan. Silakan login kembali.';
          header('Location: ' . site_url('auth/login-penyewa'));
          exit;
        }

        $flash = $_SESSION['flash_profil_penyewa'] ?? null;
        unset($_SESSION['flash_profil_penyewa']);
        $jumlahKeranjang = $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

        $this->view('penyewa/profil', [
          'profil' => $profil,
          'flash' => $flash,
          'error' => null,
          'old' => [],
          'jumlahKeranjang' => $jumlahKeranjang
        ]);
        return;
      } catch (Throwable $e) {
        error_log('Pengambilan profil penyewa gagal: ' . $e->getMessage());
        http_response_code(500);
        exit('Profil belum dapat ditampilkan. Silakan coba kembali.');
      }
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      header('Allow: GET, POST');
      exit('Method Not Allowed');
    }

    $nik = trim((string) ($_POST['nik'] ?? ''));
    $nama = trim((string) ($_POST['nm_lngkp'] ?? ''));
    $noHp = trim((string) ($_POST['no_hp'] ?? ''));
    $alamat = trim((string) ($_POST['almt_lngkp'] ?? ''));
    $passwordSaatIni = (string) ($_POST['password_saat_ini'] ?? '');
    $passwordBaru = (string) ($_POST['password_baru'] ?? '');
    $konfirmasiPassword = (string) ($_POST['konfirmasi_password'] ?? '');

    $old = [
      'nik' => $nik,
      'nm_lngkp' => $nama,
      'no_hp' => $noHp,
      'almt_lngkp' => $alamat
    ];

    $error = $this->validateProfilInput($nik, $nama, $noHp, $alamat);
    $uploadedFiles = [];

    try {
      $profil = $this->model->getProfil($userPlg);
      if ($profil === null) {
        unset($_SESSION['penyewa_user_plg']);
        $_SESSION['flash_penyewa_nav'] =
          'Data akun tidak ditemukan. Silakan login kembali.';
        header('Location: ' . site_url('auth/login-penyewa'));
        exit;
      }

      $passwordHashBaru = null;
      $inginGantiPassword =
        $passwordSaatIni !== '' ||
        $passwordBaru !== '' ||
        $konfirmasiPassword !== '';

      if ($inginGantiPassword) {
        if (
          $passwordSaatIni === '' ||
          $passwordBaru === '' ||
          $konfirmasiPassword === ''
        ) {
          $error = 'Untuk mengganti password, ketiga kolom password wajib diisi.';
        } elseif (strlen($passwordBaru) < 8) {
          $error = 'Password baru minimal 8 karakter.';
        } elseif (strlen($passwordBaru) > 72) {
          $error = 'Password baru maksimal 72 byte.';
        } elseif ($passwordBaru !== $konfirmasiPassword) {
          $error = 'Konfirmasi password baru tidak sama.';
        } elseif (!$this->model->verifikasiPassword($userPlg, $passwordSaatIni)) {
          $error = 'Password saat ini tidak sesuai.';
        } else {
          $passwordHashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
          if ($passwordHashBaru === false) {
            throw new RuntimeException('Pembuatan hash password baru gagal.');
          }
        }
      }

      $jumlahKeranjang = $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

      if ($error !== null) {
        $this->view('penyewa/profil', [
          'profil' => $profil,
          'flash' => null,
          'error' => $error,
          'old' => $old,
          'jumlahKeranjang' => $jumlahKeranjang
        ]);
        return;
      }

      $uploadKtp = $this->uploadFotoPenyewa('ft_ktp');
      if (!$uploadKtp['success']) {
        $this->renderProfilError($profil, $old, $uploadKtp['error']);
        return;
      }
      if ($uploadKtp['filename'] !== null) {
        $uploadedFiles[] = $uploadKtp['filename'];
      }

      $uploadSim = $this->uploadFotoPenyewa('ft_sim_a');
      if (!$uploadSim['success']) {
        $this->hapusFotoPenyewa($uploadedFiles);
        $this->renderProfilError($profil, $old, $uploadSim['error']);
        return;
      }
      if ($uploadSim['filename'] !== null) {
        $uploadedFiles[] = $uploadSim['filename'];
      }

      // Foto lama dipertahankan jika tidak ada file baru.
      $ftKtpBaru = $uploadKtp['filename'] ?? ($profil['ft_ktp'] ?? null);
      $ftSimBaru = $uploadSim['filename'] ?? ($profil['ft_sim_a'] ?? null);

      $success = $this->model->updateProfil(
        $userPlg,
        $nik,
        $nama,
        $noHp,
        $alamat,
        $ftKtpBaru,
        $ftSimBaru,
        $passwordHashBaru
      );

      if (!$success) {
        $this->hapusFotoPenyewa($uploadedFiles);
        $this->renderProfilError(
          $profil,
          $old,
          'Profil gagal diperbarui. Periksa kembali data Anda.'
        );
        return;
      }

      // Hapus foto lama hanya setelah database berhasil diperbarui.
      $fotoLama = [];
      if ($uploadKtp['filename'] !== null && !empty($profil['ft_ktp'])) {
        $fotoLama[] = ['ft_ktp', $profil['ft_ktp']];
      }
      if ($uploadSim['filename'] !== null && !empty($profil['ft_sim_a'])) {
        $fotoLama[] = ['ft_sim_a', $profil['ft_sim_a']];
      }
      $this->hapusFotoPenyewa($fotoLama);

      $_SESSION['flash_profil_penyewa'] = 'Profil berhasil diperbarui.';
      header('Location: ' . site_url('penyewa/profil'));
      exit;
    } catch (Throwable $e) {
      error_log('Update profil penyewa gagal: ' . $e->getMessage());
      if (!empty($uploadedFiles)) {
        $this->hapusFotoPenyewa($uploadedFiles);
      }
      $this->renderProfilError(
        $profil ?? [],
        $old,
        'Profil belum berhasil diperbarui. Silakan coba kembali.'
      );
    }
  }

  private function validateProfilInput(
    string $nik,
    string $nama,
    string $noHp,
    string $alamat
  ): ?string {
    if (!preg_match('/\A[0-9]{16}\z/', $nik)) {
      return 'NIK wajib terdiri dari tepat 16 digit angka.';
    }

    if (
      $nama === '' ||
      mb_strlen($nama) > 100 ||
      !preg_match("/\A[\p{L}\p{M}]+(?:[ .'-][\p{L}\p{M}]+)*\z/u", $nama)
    ) {
      return 'Nama lengkap wajib diisi, maksimal 100 karakter, dan hanya berisi huruf serta pemisah nama yang wajar.';
    }

    if (
      $noHp === '' ||
      strlen($noHp) > 15 ||
      !preg_match('/\A[0-9+(). -]+\z/', $noHp) ||
      !preg_match('/[0-9]/', $noHp)
    ) {
      return 'Nomor HP wajib diisi, maksimal 15 karakter, dan hanya boleh berisi angka serta tanda +, titik, kurung, spasi, atau tanda hubung.';
    }

    if (
      $alamat === '' ||
      mb_strlen($alamat) > 300 ||
      preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $alamat)
    ) {
      return 'Alamat wajib diisi, maksimal 300 karakter, dan tidak boleh mengandung karakter kontrol.';
    }

    return null;
  }

  private function renderProfilError(
    array $profil,
    array $old,
    string $error
  ): void {
    $userPlg = trim((string) ($_SESSION['penyewa_user_plg'] ?? ''));
    $jumlahKeranjang = $userPlg === ''
      ? 0
      : $this->transaksiModel->getJumlahMobilKeranjang($userPlg);

    $this->view('penyewa/profil', [
      'profil' => $profil,
      'flash' => null,
      'error' => $error,
      'old' => $old,
      'jumlahKeranjang' => $jumlahKeranjang
    ]);
  }

  /*
     * =========================================================
     * HELPER UPLOAD DAN HAPUS FOTO PENYEWA
     * Dipakai oleh halaman profil dan CRUD admin.
     * =========================================================
     */

  private function uploadFotoPenyewa(string $field): array
  {
    if (!in_array($field, ['ft_ktp', 'ft_sim_a'], true)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Field foto tidak valid.'
      ];
    }

    if (
      !isset($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
      return [
        'success' => true,
        'filename' => null,
        'error' => null
      ];
    }

    $file = $_FILES[$field];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Unggah foto gagal. Periksa ukuran file dan konfigurasi PHP.'
      ];
    }

    if (
      !isset($file['tmp_name'], $file['size']) ||
      !is_uploaded_file($file['tmp_name']) ||
      (int) $file['size'] < 1 ||
      (int) $file['size'] > 5 * 1024 * 1024
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Foto harus berukuran lebih dari 0 byte dan maksimal 5 MB.'
      ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Format foto harus JPEG, PNG, atau WEBP yang valid.'
      ];
    }

    $projectRoot = dirname(APPPATH);
    $uploadDir = $projectRoot . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa' . DIRECTORY_SEPARATOR;

    if (
      !is_dir($uploadDir) &&
      !mkdir($uploadDir, 0755, true) &&
      !is_dir($uploadDir)
    ) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Folder penyimpanan foto penyewa tidak dapat dibuat.'
      ];
    }

    if (!is_writable($uploadDir)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Folder penyimpanan foto penyewa tidak dapat ditulis.'
      ];
    }

    try {
      $filename = $field . '_' . date('YmdHis') . '_'
        . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    } catch (Throwable $e) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Nama file foto gagal dibuat.'
      ];
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
      return [
        'success' => false,
        'filename' => null,
        'error' => 'Foto gagal dipindahkan ke folder penyimpanan.'
      ];
    }

    return [
      'success' => true,
      'filename' => $filename,
      'error' => null
    ];
  }

  private function hapusFotoPenyewa(array $files): void
  {
    $uploadDir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa' . DIRECTORY_SEPARATOR;

    foreach ($files as $item) {
      if (is_array($item)) {
        [$field, $filename] = $item;
      } else {
        // File baru yang diunggah pada request ini.
        $filename = $item;
        $field = is_string($filename)
          ? (str_starts_with($filename, 'ft_ktp_') ? 'ft_ktp' : 'ft_sim_a')
          : '';
      }

      if (
        !in_array($field, ['ft_ktp', 'ft_sim_a'], true) ||
        !is_string($filename)
      ) {
        continue;
      }

      // Hanya hapus file dengan pola nama yang dibuat aplikasi.
      $pattern = '/^' . preg_quote($field, '/') .
        '_\d{10,14}_[a-f0-9]{12,16}\.(jpg|jpeg|png|webp)$/';

      if (!preg_match($pattern, $filename)) {
        continue;
      }

      $path = $uploadDir . $filename;
      if (is_file($path)) {
        @unlink($path);
      }
    }
  }
}

<?php

// UTS P8: CRUD Penyewa pada sisi Admin (pola sama dengan Mobil_controller).
class Mpenyewa_controller extends Controller
{
  private Penyewa_model $model;

  public function __construct()
  {
    require_once APPPATH . 'models/Penyewa_model.php';
    require_once APPPATH . 'helpers/auth_helper.php';
    require_once APPPATH . 'helpers/flash_helper.php';

    $this->model = new Penyewa_model();
  }

  // READ: daftar penyewa.
  public function index(): void
  {
    require_admin_login();

    $this->view('admin/penyewa_view', [
      'username' => $_SESSION['admin_username'],
      'penyewa' => $this->model->getAll(),
      'flash' => get_flash_message(),
      'mode' => null,
      'penyewa_dipilih' => null,
      'error' => null,
    ]);
  }

  // Menampilkan halaman beserta form dan pesan kesalahan.
  private function renderPenyewa(
    string $mode,
    ?array $penyewaDipilih = null,
    ?string $error = null
  ): void {
    $this->view('admin/penyewa_view', [
      'username' => $_SESSION['admin_username'],
      'penyewa' => $this->model->getAll(),
      'flash' => null,
      'mode' => $mode,
      'penyewa_dipilih' => $penyewaDipilih,
      'error' => $error,
    ]);
  }

  // Validasi dan sanitasi input (aturan disamakan dengan register/profil penyewa).
  private function validatePenyewaInput(
    array $data,
    string $password,
    string $konfirmasi,
    bool $isEdit
  ): ?string {
    if (!preg_match('/\A[A-Za-z0-9._-]{1,15}\z/', $data['user_plg'])) {
      return 'Username harus 1-15 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
    }

    // Tambah: password wajib. Ubah: boleh kosong (hash lama dipertahankan).
    if (!$isEdit || $password !== '' || $konfirmasi !== '') {
      if ($password === '') {
        return 'Password wajib diisi.';
      }
      if (strlen($password) < 8) {
        return 'Password minimal 8 karakter.';
      }
      if (strlen($password) > 72) {
        return 'Password maksimal 72 byte.';
      }
      if ($password !== $konfirmasi) {
        return 'Konfirmasi password tidak sama.';
      }
    }

    if (!preg_match('/\A[0-9]{16}\z/', $data['nik'])) {
      return 'NIK wajib terdiri dari tepat 16 digit angka.';
    }

    if (
      $data['nm_lngkp'] === '' ||
      mb_strlen($data['nm_lngkp']) > 100 ||
      !preg_match("/\A[\p{L}\p{M}]+(?:[ .'-][\p{L}\p{M}]+)*\z/u", $data['nm_lngkp'])
    ) {
      return 'Nama lengkap wajib diisi, maksimal 100 karakter, dan hanya berisi huruf serta pemisah nama yang wajar.';
    }

    if (
      $data['no_hp'] === '' ||
      strlen($data['no_hp']) > 15 ||
      !preg_match('/\A[0-9+(). -]+\z/', $data['no_hp']) ||
      !preg_match('/[0-9]/', $data['no_hp'])
    ) {
      return 'Nomor HP wajib diisi, maksimal 15 karakter, dan hanya berisi angka serta tanda + . ( ) spasi atau -.';
    }

    if (
      $data['almt_lngkp'] === '' ||
      mb_strlen($data['almt_lngkp']) > 300 ||
      preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $data['almt_lngkp'])
    ) {
      return 'Alamat wajib diisi, maksimal 300 karakter, dan tidak boleh mengandung karakter kontrol.';
    }

    return null;
  }

  // Mengunggah foto KTP / SIM A.
  private function uploadFoto(string $field): array
  {
    if (!in_array($field, ['ft_ktp', 'ft_sim_a'], true)) {
      return ['success' => false, 'filename' => null, 'error' => 'Field foto tidak valid.'];
    }

    // Tidak ada file dipilih: dianggap berhasil, foto lama dipertahankan.
    if (
      !isset($_FILES[$field]) ||
      ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE
    ) {
      return ['success' => true, 'filename' => null, 'error' => null];
    }

    $file = $_FILES[$field];
    $label = $field === 'ft_ktp' ? 'Foto KTP' : 'Foto SIM A';

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return ['success' => false, 'filename' => null, 'error' => $label . ': unggah gagal. Periksa ukuran file dan konfigurasi PHP.'];
    }

    if (
      !isset($file['tmp_name'], $file['size']) ||
      !is_uploaded_file($file['tmp_name']) ||
      (int) $file['size'] < 1 ||
      (int) $file['size'] > 5 * 1024 * 1024
    ) {
      return ['success' => false, 'filename' => null, 'error' => $label . ' harus berukuran lebih dari 0 byte dan maksimal 5 MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
      return ['success' => false, 'filename' => null, 'error' => $label . ' harus berformat JPEG, PNG, atau WEBP yang valid.'];
    }

    $uploadDir = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa'
      . DIRECTORY_SEPARATOR;

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
      return ['success' => false, 'filename' => null, 'error' => 'Folder penyimpanan foto tidak dapat dibuat.'];
    }

    if (!is_writable($uploadDir)) {
      return ['success' => false, 'filename' => null, 'error' => 'Folder penyimpanan foto tidak dapat ditulis.'];
    }

    try {
      $filename = $field . '_' . date('YmdHis') . '_'
        . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    } catch (Throwable $e) {
      return ['success' => false, 'filename' => null, 'error' => 'Nama file foto gagal dibuat.'];
    }

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
      return ['success' => false, 'filename' => null, 'error' => 'Foto gagal dipindahkan ke folder penyimpanan.'];
    }

    return ['success' => true, 'filename' => $filename, 'error' => null];
  }

  // Menghapus berkas foto (hanya nama yang cocok pola aman).
  private function hapusFoto(array $filenames): void
  {
    $uploadDir = dirname(APPPATH)
      . DIRECTORY_SEPARATOR . 'uploads'
      . DIRECTORY_SEPARATOR . 'penyewa'
      . DIRECTORY_SEPARATOR;

    foreach ($filenames as $filename) {
      if (
        is_string($filename) &&
        preg_match(
          '/^(ft_ktp|ft_sim_a)_\d{10,14}_[a-f0-9]{12,16}\.(jpg|jpeg|png|webp)$/',
          $filename
        )
      ) {
        $path = $uploadDir . $filename;

        if (is_file($path)) {
          @unlink($path);
        }
      }
    }
  }

  // CREATE
  public function tambah(): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->renderPenyewa('tambah');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $data = [
      'user_plg' => trim((string) ($_POST['user_plg'] ?? '')),
      'nik' => trim((string) ($_POST['nik'] ?? '')),
      'nm_lngkp' => trim((string) ($_POST['nm_lngkp'] ?? '')),
      'no_hp' => trim((string) ($_POST['no_hp'] ?? '')),
      'almt_lngkp' => trim((string) ($_POST['almt_lngkp'] ?? '')),
      'ft_ktp' => null,
      'ft_sim_a' => null,
    ];
    // Password tidak di-trim dan tidak pernah dikirim kembali ke form.
    $password = (string) ($_POST['sandi'] ?? '');
    $konfirmasi = (string) ($_POST['konfirmasi_sandi'] ?? '');

    $error = $this->validatePenyewaInput($data, $password, $konfirmasi, false);
    if ($error !== null) {
      $this->renderPenyewa('tambah', $data, $error);
      return;
    }

    try {
      if ($this->model->findByUserPlg($data['user_plg']) !== null) {
        $this->renderPenyewa('tambah', $data, 'Username sudah digunakan.');
        return;
      }
    } catch (Throwable $e) {
      error_log('Cek username penyewa gagal: ' . $e->getMessage());
      $this->renderPenyewa('tambah', $data, 'Data belum dapat diperiksa. Silakan coba kembali.');
      return;
    }

    $uploadedFiles = [];
    foreach (['ft_ktp', 'ft_sim_a'] as $field) {
      $upload = $this->uploadFoto($field);

      if (!$upload['success']) {
        $this->hapusFoto($uploadedFiles);
        $this->renderPenyewa('tambah', $data, $upload['error']);
        return;
      }

      $data[$field] = $upload['filename'];

      if ($upload['filename'] !== null) {
        $uploadedFiles[] = $upload['filename'];
      }
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) {
      $this->hapusFoto($uploadedFiles);
      $this->renderPenyewa('tambah', $data, 'Pembuatan hash password gagal.');
      return;
    }
    $data['sandi'] = $hash;

    try {
      $success = $this->model->createLengkap($data);
    } catch (Throwable $e) {
      error_log('Tambah penyewa gagal: ' . $e->getMessage());
      $success = false;
    }

    if (!$success) {
      $this->hapusFoto($uploadedFiles);
      $this->renderPenyewa(
        'tambah',
        $data,
        'Data penyewa gagal disimpan. Periksa kembali data yang dimasukkan.'
      );
      return;
    }

    // PRG: POST -> flash -> redirect -> GET.
    set_flash_message('success', 'Data penyewa berhasil ditambahkan.');
    header('Location: ' . site_url('admin/penyewa'));
    exit;
  }

  // UPDATE
  public function ubah(string $userPlg): void
  {
    require_admin_login();

    $userPlg = rawurldecode($userPlg);
    $penyewaLama = $this->model->findLengkap($userPlg);

    if ($penyewaLama === null) {
      set_flash_message('danger', 'Data penyewa tidak ditemukan.');
      header('Location: ' . site_url('admin/penyewa'));
      exit;
    }

    // Hash password tidak pernah dikirim ke View.
    unset($penyewaLama['sandi']);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $this->renderPenyewa('ubah', $penyewaLama);
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $data = [
      'user_plg' => $userPlg, // kunci record, tidak diubah dari form
      'nik' => trim((string) ($_POST['nik'] ?? '')),
      'nm_lngkp' => trim((string) ($_POST['nm_lngkp'] ?? '')),
      'no_hp' => trim((string) ($_POST['no_hp'] ?? '')),
      'almt_lngkp' => trim((string) ($_POST['almt_lngkp'] ?? '')),
      'ft_ktp' => $penyewaLama['ft_ktp'],
      'ft_sim_a' => $penyewaLama['ft_sim_a'],
    ];
    $password = (string) ($_POST['sandi'] ?? '');
    $konfirmasi = (string) ($_POST['konfirmasi_sandi'] ?? '');

    $error = $this->validatePenyewaInput($data, $password, $konfirmasi, true);
    if ($error !== null) {
      $this->renderPenyewa('ubah', array_merge($penyewaLama, $data), $error);
      return;
    }

    $uploadedFiles = [];
    $fotoLamaUntukDihapus = [];
    foreach (['ft_ktp', 'ft_sim_a'] as $field) {
      $upload = $this->uploadFoto($field);

      if (!$upload['success']) {
        $this->hapusFoto($uploadedFiles);
        $this->renderPenyewa('ubah', array_merge($penyewaLama, $data), $upload['error']);
        return;
      }

      if ($upload['filename'] !== null) {
        $uploadedFiles[] = $upload['filename'];

        if (!empty($penyewaLama[$field])) {
          $fotoLamaUntukDihapus[] = $penyewaLama[$field];
        }

        $data[$field] = $upload['filename'];
      }
    }

    // Password kosong = hash lama dipertahankan.
    $hashBaru = null;
    if ($password !== '') {
      $hashBaru = password_hash($password, PASSWORD_DEFAULT);
      if ($hashBaru === false) {
        $this->hapusFoto($uploadedFiles);
        $this->renderPenyewa('ubah', array_merge($penyewaLama, $data), 'Pembuatan hash password gagal.');
        return;
      }
    }

    try {
      $success = $this->model->updateLengkap($userPlg, $data, $hashBaru);
    } catch (Throwable $e) {
      error_log('Ubah penyewa gagal: ' . $e->getMessage());
      $success = false;
    }

    if (!$success) {
      $this->hapusFoto($uploadedFiles);
      $this->renderPenyewa(
        'ubah',
        array_merge($penyewaLama, $data),
        'Data penyewa gagal diperbarui. Periksa kembali data yang dimasukkan.'
      );
      return;
    }

    // Foto lama dihapus hanya setelah database berhasil diperbarui.
    $this->hapusFoto($fotoLamaUntukDihapus);
    set_flash_message('success', 'Data penyewa berhasil diperbarui.');
    header('Location: ' . site_url('admin/penyewa'));
    exit;
  }

  // DELETE (wajib POST)
  public function hapus(string $userPlg): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      http_response_code(405);
      exit('Metode request tidak diizinkan.');
    }

    $userPlg = rawurldecode($userPlg);
    $penyewa = $this->model->findLengkap($userPlg);

    if ($penyewa === null) {
      set_flash_message('danger', 'Data penyewa tidak ditemukan.');
      header('Location: ' . site_url('admin/penyewa'));
      exit;
    }

    try {
      $success = $this->model->delete($userPlg);
    } catch (Throwable $e) {
      // Contoh: penyewa masih terkait data transaksi (foreign key).
      error_log('Hapus penyewa gagal: ' . $e->getMessage());
      $success = false;
    }

    if (!$success) {
      set_flash_message(
        'danger',
        'Data penyewa gagal dihapus. Kemungkinan penyewa masih memiliki data transaksi.'
      );
      header('Location: ' . site_url('admin/penyewa'));
      exit;
    }

    $this->hapusFoto(array_filter([
      $penyewa['ft_ktp'] ?? null,
      $penyewa['ft_sim_a'] ?? null,
    ]));

    set_flash_message('success', 'Data penyewa berhasil dihapus.');
    header('Location: ' . site_url('admin/penyewa'));
    exit;
  }
}


<?php

class Manajemen_penyewa_controller extends Controller
{
  private Manajemen_penyewa_model $penyewaModel;
  private string $uploadDir;

  public function __construct()
  {
    require_once APPPATH . 'helpers/auth_helper.php';
    require_once APPPATH . 'models/Manajemen_penyewa_model.php';

    $this->penyewaModel = new Manajemen_penyewa_model();

    $this->uploadDir =
      dirname(rtrim(APPPATH, '/\\')) .
      DIRECTORY_SEPARATOR . 'uploads' .
      DIRECTORY_SEPARATOR . 'penyewa' .
      DIRECTORY_SEPARATOR;
  }

  // Menampilkan halaman manajemen penyewa
  public function index(): void
  {
    require_admin_login();

    $penyewa = $this->penyewaModel->getAll();

    $this->view('admin/penyewa_view', [
      'judul' => 'Manajemen Data Penyewa',
      'username' => $_SESSION['admin_username'] ?? '',
      'penyewa' => $penyewa,
      'pesan' => $_SESSION['flash_pesan'] ?? '',
      'jenis' => $_SESSION['flash_jenis'] ?? 'info'
    ]);

    unset($_SESSION['flash_pesan'], $_SESSION['flash_jenis']);
  }

  // Menambahkan data penyewa
  public function tambah(): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirectIndex();
    }

    $data = [
      'user_plg' => trim($_POST['user_plg'] ?? ''),
      'nik' => trim($_POST['nik'] ?? ''),
      'nm_lngkp' => trim($_POST['nm_lngkp'] ?? ''),
      'no_hp' => trim($_POST['no_hp'] ?? ''),
      'almt_lngkp' => trim($_POST['almt_lngkp'] ?? '')
    ];

    $password = $_POST['sandi'] ?? '';

    if (
      !preg_match('/^[A-Za-z0-9_.-]{3,15}$/', $data['user_plg']) ||
      !preg_match('/^[0-9]{16}$/', $data['nik']) ||
      $data['nm_lngkp'] === '' ||
      strlen($data['nm_lngkp']) > 100 ||
      $data['no_hp'] === '' ||
      strlen($data['no_hp']) > 15 ||
      $data['almt_lngkp'] === '' ||
      strlen($data['almt_lngkp']) > 300 ||
      strlen($password) < 8
    ) {
      $this->setFlash('Data tidak valid. Periksa kembali formulir.', 'danger');
      $this->redirectIndex();
    }

    if ($this->penyewaModel->findByUserPlg($data['user_plg']) !== null) {
      $this->setFlash('Username sudah digunakan.', 'danger');
      $this->redirectIndex();
    }

    $fotoBaru = [];

    try {
      $fotoBaru['ft_ktp'] = $this->uploadFoto('ft_ktp');
      $fotoBaru['ft_sim_a'] = $this->uploadFoto('ft_sim_a');

      $data['sandi'] = password_hash($password, PASSWORD_DEFAULT);

      if ($data['sandi'] === false) {
        throw new RuntimeException('Password gagal diproses.');
      }

      $data['ft_ktp'] = $fotoBaru['ft_ktp'];
      $data['ft_sim_a'] = $fotoBaru['ft_sim_a'];

      if (!$this->penyewaModel->create($data)) {
        throw new RuntimeException('Data penyewa gagal disimpan.');
      }

      $this->setFlash('Data penyewa berhasil ditambahkan.', 'success');
    } catch (Throwable $e) {
      $this->hapusFile($fotoBaru['ft_ktp'] ?? null);
      $this->hapusFile($fotoBaru['ft_sim_a'] ?? null);

      $this->setFlash(
        'Data gagal ditambahkan. Periksa kembali data dan foto yang diunggah.',
        'danger'
      );
    }

    $this->redirectIndex();
  }

  // Mengubah data penyewa, password, dan foto
  public function ubah(string $userPlg): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirectIndex();
    }

    $userPlg = trim($userPlg);
    $lama = $this->penyewaModel->findByUserPlg($userPlg);

    if ($lama === null) {
      $this->setFlash('Data penyewa tidak ditemukan.', 'danger');
      $this->redirectIndex();
    }

    $data = [
      'nik' => trim($_POST['nik'] ?? ''),
      'nm_lngkp' => trim($_POST['nm_lngkp'] ?? ''),
      'no_hp' => trim($_POST['no_hp'] ?? ''),
      'almt_lngkp' => trim($_POST['almt_lngkp'] ?? ''),
      'ft_ktp' => $lama['ft_ktp'],
      'ft_sim_a' => $lama['ft_sim_a']
    ];

    $passwordBaru = $_POST['sandi_baru'] ?? '';

    if (
      !preg_match('/^[0-9]{16}$/', $data['nik']) ||
      $data['nm_lngkp'] === '' ||
      strlen($data['nm_lngkp']) > 100 ||
      $data['no_hp'] === '' ||
      strlen($data['no_hp']) > 15 ||
      $data['almt_lngkp'] === '' ||
      strlen($data['almt_lngkp']) > 300 ||
      ($passwordBaru !== '' && strlen($passwordBaru) < 8)
    ) {
      $this->setFlash('Data tidak valid. Periksa kembali formulir.', 'danger');
      $this->redirectIndex();
    }

    $fotoBaru = [];

    try {
      $fotoBaru['ft_ktp'] = $this->uploadFoto('ft_ktp');
      $fotoBaru['ft_sim_a'] = $this->uploadFoto('ft_sim_a');

      if ($fotoBaru['ft_ktp'] !== null) {
        $data['ft_ktp'] = $fotoBaru['ft_ktp'];
      }

      if ($fotoBaru['ft_sim_a'] !== null) {
        $data['ft_sim_a'] = $fotoBaru['ft_sim_a'];
      }

      // Password hanya diubah jika kolom diisi
      if ($passwordBaru !== '') {
        $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);

        if ($hash === false) {
          throw new RuntimeException('Password gagal diproses.');
        }

        $data['sandi'] = $hash;
      }

      // Data dan password disimpan dalam satu query
      if (!$this->penyewaModel->update($userPlg, $data)) {
        throw new RuntimeException('Data gagal diperbarui.');
      }

      // Hapus foto lama setelah database berhasil diperbarui
      if ($fotoBaru['ft_ktp'] !== null) {
        $this->hapusFile($lama['ft_ktp'] ?? null);
      }

      if ($fotoBaru['ft_sim_a'] !== null) {
        $this->hapusFile($lama['ft_sim_a'] ?? null);
      }

      $this->setFlash('Data penyewa berhasil diperbarui.', 'success');
    } catch (Throwable $e) {
      $this->hapusFile($fotoBaru['ft_ktp'] ?? null);
      $this->hapusFile($fotoBaru['ft_sim_a'] ?? null);

      $this->setFlash(
        'Data gagal diperbarui. Periksa kembali data dan foto.',
        'danger'
      );
    }

    $this->redirectIndex();
  }

  // Menghapus data penyewa beserta foto
  public function hapus(string $userPlg): void
  {
    require_admin_login();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->redirectIndex();
    }

    $userPlg = trim($userPlg);
    $data = $this->penyewaModel->findByUserPlg($userPlg);

    if ($data === null) {
      $this->setFlash('Data penyewa tidak ditemukan.', 'danger');
      $this->redirectIndex();
    }

    if ($this->penyewaModel->delete($userPlg)) {
      $this->hapusFile($data['ft_ktp'] ?? null);
      $this->hapusFile($data['ft_sim_a'] ?? null);

      $this->setFlash('Data penyewa berhasil dihapus.', 'success');
    } else {
      $this->setFlash('Data penyewa gagal dihapus.', 'danger');
    }

    $this->redirectIndex();
  }

  // Menampilkan foto melalui akses admin
  public function foto(string $userPlg, string $jenis): void
  {
    require_admin_login();

    $data = $this->penyewaModel->findByUserPlg(trim($userPlg));

    if ($data === null) {
      http_response_code(404);
      exit('Foto tidak ditemukan.');
    }

    $kolom = [
      'ktp' => 'ft_ktp',
      'sim' => 'ft_sim_a'
    ];

    if (!isset($kolom[$jenis])) {
      http_response_code(404);
      exit('Jenis foto tidak valid.');
    }

    $namaFile = $data[$kolom[$jenis]] ?? '';

    if (
      $namaFile === '' ||
      basename($namaFile) !== $namaFile
    ) {
      http_response_code(404);
      exit('Foto tidak ditemukan.');
    }

    $lokasi = $this->uploadDir . $namaFile;

    if (!is_file($lokasi)) {
      http_response_code(404);
      exit('File foto tidak ditemukan.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($lokasi);

    $formatDiizinkan = [
      'image/jpeg',
      'image/png',
      'image/webp'
    ];

    if (
      !in_array($mime, $formatDiizinkan, true) ||
      @getimagesize($lokasi) === false
    ) {
      http_response_code(404);
      exit('Format foto tidak valid.');
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($lokasi));
    header('Cache-Control: private, no-store');
    header('X-Content-Type-Options: nosniff');

    readfile($lokasi);
    exit;
  }

  // Proses upload foto
  private function uploadFoto(string $field): ?string
  {
    if (
      !isset($_FILES[$field]) ||
      $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE
    ) {
      return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      throw new RuntimeException('Upload foto gagal.');
    }

    if ($file['size'] > 2 * 1024 * 1024) {
      throw new RuntimeException('Ukuran foto maksimal 2 MB.');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
      throw new RuntimeException('File upload tidak valid.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $format = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp'
    ];

    if (
      !isset($format[$mime]) ||
      @getimagesize($file['tmp_name']) === false
    ) {
      throw new RuntimeException('Format foto tidak didukung.');
    }

    if (!is_dir($this->uploadDir)) {
      if (
        !mkdir($this->uploadDir, 0755, true) &&
        !is_dir($this->uploadDir)
      ) {
        throw new RuntimeException('Folder upload gagal dibuat.');
      }
    }

    if (!is_writable($this->uploadDir)) {
      throw new RuntimeException('Folder upload tidak dapat ditulis.');
    }

    $namaFile = bin2hex(random_bytes(16)) . '.' . $format[$mime];
    $tujuan = $this->uploadDir . $namaFile;

    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
      throw new RuntimeException('Foto gagal dipindahkan.');
    }

    return $namaFile;
  }

  // Menghapus file foto
  private function hapusFile(?string $namaFile): void
  {
    if (
      $namaFile === null ||
      $namaFile === '' ||
      basename($namaFile) !== $namaFile
    ) {
      return;
    }

    $lokasi = $this->uploadDir . $namaFile;

    if (is_file($lokasi)) {
      unlink($lokasi);
    }
  }

  // Menyimpan pesan notifikasi
  private function setFlash(string $pesan, string $jenis): void
  {
    $_SESSION['flash_pesan'] = $pesan;
    $_SESSION['flash_jenis'] = $jenis;
  }

  // Kembali ke halaman utama
  private function redirectIndex(): void
  {
    header('Location: ' . site_url('admin/penyewa'));
    exit;
  }
}

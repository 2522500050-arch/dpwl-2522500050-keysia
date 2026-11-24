<?php
class Penyewa_model
{
  private mysqli $db;
  public function __construct()
  {
    global $db;
    $this->db = $db;
  }
  public function findByUserPlg(string $userPlg): ?array
  {
    $sql = "SELECT user_plg
FROM t_penyewa
WHERE user_plg = ?
LIMIT 1";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException(
        'Pemeriksaan username penyewa gagal disiapkan.'
      );
    }
    $stmt->bind_param('s', $userPlg);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Pemeriksaan username penyewa gagal dijalankan.'
      );
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
  }
  public function create(array $data): bool
  {
    $sql = "INSERT INTO t_penyewa (
user_plg,
sandi,
nik,
nm_lngkp,
no_hp,
almt_lngkp,
ft_ktp,
ft_sim_a
) VALUES (?, ?, ?, ?, ?, ?, NULL, NULL)";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException(
        'Query registrasi penyewa gagal disiapkan.'
      );
    }
    $stmt->bind_param(
      'ssssss',
      $data['user_plg'],
      $data['sandi'],
      $data['nik'],
      $data['nm_lngkp'],
      $data['no_hp'],
      $data['almt_lngkp']
    );
    $success = $stmt->execute();
    $stmt->close();
    return $success;
  }
  // P6: untuk login penyewa
  public function findForLogin(string $userPlg): ?array
  {
    $sql = "SELECT user_plg, sandi, nm_lngkp
FROM t_penyewa
WHERE user_plg = ?
LIMIT 1";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException(
        'Query pencarian akun penyewa gagal disiapkan.'
      );
    }
    $stmt->bind_param('s', $userPlg);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Pencarian akun penyewa gagal dijalankan.'
      );
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
  }
  // P6: mengambil profil penyewa.
  public function getProfil(string $userPlg): ?array
  {
    $sql = "SELECT
user_plg,
nik,
nm_lngkp,
no_hp,
almt_lngkp,
ft_ktp,
ft_sim_a
FROM t_penyewa
WHERE user_plg = ?
LIMIT 1";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException(
        'Query profil penyewa gagal disiapkan.'
      );
    }
    $stmt->bind_param('s', $userPlg);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Pengambilan profil penyewa gagal dijalankan.'
      );
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
  }
  // P6: verifikasi password
  public function verifikasiPassword(
    string $userPlg,
    string $passwordSaatIni
  ): bool {
    $sql = "SELECT sandi
FROM t_penyewa
WHERE user_plg = ?
LIMIT 1";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException(
        'Query verifikasi password gagal disiapkan.'
      );
    }
    $stmt->bind_param('s', $userPlg);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Verifikasi password gagal dijalankan.'
      );
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row !== null
      && password_verify($passwordSaatIni, $row['sandi']);
  }
  // P6: memperbarui data profil dan referensi foto.
  public function updateProfil(
    string $userPlg,
    string $nik,
    string $nama,
    string $noHp,
    string $alamat,
    ?string $ftKtp,
    ?string $ftSimA,
    ?string $passwordHash = null
  ): bool {
    if ($passwordHash !== null) {
      $sql = "UPDATE t_penyewa
SET nik = ?,
nm_lngkp = ?,
no_hp = ?,
almt_lngkp = ?,
ft_ktp = ?,
ft_sim_a = ?,
sandi = ?
WHERE user_plg = ?";
      $stmt = $this->db->prepare($sql);
      if (!$stmt) {
        throw new RuntimeException(
          'Query update profil dan password gagal disiapkan.'
        );
      }
      $stmt->bind_param(
        'ssssssss',
        $nik,
        $nama,
        $noHp,
        $alamat,
        $ftKtp,
        $ftSimA,
        $passwordHash,
        $userPlg
      );
    } else {
      $sql = "UPDATE t_penyewa
SET nik = ?,
nm_lngkp = ?,
no_hp = ?,
almt_lngkp = ?,
ft_ktp = ?,
ft_sim_a = ?
WHERE user_plg = ?";
      $stmt = $this->db->prepare($sql);
      if (!$stmt) {
        throw new RuntimeException(
          'Query update profil gagal disiapkan.'
        );
      }
      $stmt->bind_param(
        'sssssss',
        $nik,
        $nama,
        $noHp,
        $alamat,
        $ftKtp,
        $ftSimA,
        $userPlg
      );
    }
    $success = $stmt->execute();
    $stmt->close();
    return $success;
  }

  // UTS P8 (Admin): seluruh data penyewa tanpa hash password.
  public function getAll(): array
  {
    $sql = "SELECT user_plg, nik, nm_lngkp, no_hp, almt_lngkp, ft_ktp, ft_sim_a
FROM t_penyewa
ORDER BY user_plg ASC";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException($this->db->error);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
  }

  // UTS P8 (Admin): satu penyewa lengkap, termasuk sandi (hash) untuk keperluan Controller.
  public function findLengkap(string $userPlg): ?array
  {
    $sql = "SELECT user_plg, sandi, nik, nm_lngkp, no_hp, almt_lngkp, ft_ktp, ft_sim_a
FROM t_penyewa
WHERE user_plg = ?
LIMIT 1";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException($this->db->error);
    }
    $stmt->bind_param('s', $userPlg);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
  }

  // UTS P8 (Admin): INSERT penyewa beserta foto (sandi sudah berupa hash).
  public function createLengkap(array $data): bool
  {
    $sql = "INSERT INTO t_penyewa (
user_plg, sandi, nik, nm_lngkp, no_hp, almt_lngkp, ft_ktp, ft_sim_a
) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException($this->db->error);
    }
    $stmt->bind_param(
      'ssssssss',
      $data['user_plg'],
      $data['sandi'],
      $data['nik'],
      $data['nm_lngkp'],
      $data['no_hp'],
      $data['almt_lngkp'],
      $data['ft_ktp'],
      $data['ft_sim_a']
    );
    $success = $stmt->execute();
    $stmt->close();
    return $success;
  }

  // UTS P8 (Admin): UPDATE penyewa; sandi hanya diganti bila hash baru diberikan.
  public function updateLengkap(string $userPlg, array $data, ?string $passwordHash): bool
  {
    if ($passwordHash !== null) {
      $sql = "UPDATE t_penyewa
SET sandi = ?, nik = ?, nm_lngkp = ?, no_hp = ?, almt_lngkp = ?, ft_ktp = ?, ft_sim_a = ?
WHERE user_plg = ?";
      $stmt = $this->db->prepare($sql);
      if (!$stmt) {
        throw new RuntimeException($this->db->error);
      }
      $stmt->bind_param(
        'ssssssss',
        $passwordHash,
        $data['nik'],
        $data['nm_lngkp'],
        $data['no_hp'],
        $data['almt_lngkp'],
        $data['ft_ktp'],
        $data['ft_sim_a'],
        $userPlg
      );
    } else {
      $sql = "UPDATE t_penyewa
SET nik = ?, nm_lngkp = ?, no_hp = ?, almt_lngkp = ?, ft_ktp = ?, ft_sim_a = ?
WHERE user_plg = ?";
      $stmt = $this->db->prepare($sql);
      if (!$stmt) {
        throw new RuntimeException($this->db->error);
      }
      $stmt->bind_param(
        'sssssss',
        $data['nik'],
        $data['nm_lngkp'],
        $data['no_hp'],
        $data['almt_lngkp'],
        $data['ft_ktp'],
        $data['ft_sim_a'],
        $userPlg
      );
    }
    $success = $stmt->execute();
    $stmt->close();
    return $success;
  }

  // UTS P8 (Admin): DELETE berdasarkan primary key user_plg.
  public function delete(string $userPlg): bool
  {
    $sql = "DELETE FROM t_penyewa WHERE user_plg = ?";
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException($this->db->error);
    }
    $stmt->bind_param('s', $userPlg);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected > 0;
  }
  // =====================================================
  // P8 - CRUD ADMIN PENYEWA
  // =====================================================

  public function getAllAdmin(): array
  {
    $sql = "
        SELECT
            user_plg,
            nik,
            nm_lngkp,
            no_hp,
            almt_lngkp
        FROM t_penyewa
        ORDER BY nm_lngkp ASC
    ";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
      throw new RuntimeException(
        'Query data penyewa gagal disiapkan.'
      );
    }

    if (!$stmt->execute()) {
      $stmt->close();

      throw new RuntimeException(
        'Query data penyewa gagal dijalankan.'
      );
    }

    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $rows;
  }

  public function findAdminByUserPlg(string $userPlg): ?array
  {
    $sql = "
        SELECT
            user_plg,
            sandi,
            nik,
            nm_lngkp,
            no_hp,
            almt_lngkp,
            ft_ktp,
            ft_sim_a
        FROM t_penyewa
        WHERE user_plg = ?
        LIMIT 1
    ";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
      throw new RuntimeException(
        'Query pencarian penyewa gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $userPlg);

    if (!$stmt->execute()) {
      $stmt->close();

      throw new RuntimeException(
        'Query pencarian penyewa gagal dijalankan.'
      );
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return $row ?: null;
  }

  public function createAdmin(array $data): bool
  {
    $sql = "
        INSERT INTO t_penyewa
        (
            user_plg,
            sandi,
            nik,
            nm_lngkp,
            no_hp,
            almt_lngkp,
            ft_ktp,
            ft_sim_a
        )
        VALUES (?, ?, ?, ?, ?, ?, NULL, NULL)
    ";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
      throw new RuntimeException(
        'Query tambah penyewa gagal disiapkan.'
      );
    }

    $stmt->bind_param(
      'ssssss',
      $data['user_plg'],
      $data['sandi'],
      $data['nik'],
      $data['nm_lngkp'],
      $data['no_hp'],
      $data['almt_lngkp']
    );

    $success = $stmt->execute();

    $stmt->close();

    return $success;
  }

  public function updateAdmin(
    string $userPlg,
    array $data,
    ?string $passwordHash = null
  ): bool {

    if ($passwordHash !== null) {

      $sql = "
            UPDATE t_penyewa
            SET
                sandi = ?,
                nik = ?,
                nm_lngkp = ?,
                no_hp = ?,
                almt_lngkp = ?
            WHERE user_plg = ?
        ";

      $stmt = $this->db->prepare($sql);

      if (!$stmt) {
        throw new RuntimeException(
          'Query ubah penyewa gagal disiapkan.'
        );
      }

      $stmt->bind_param(
        'ssssss',
        $passwordHash,
        $data['nik'],
        $data['nm_lngkp'],
        $data['no_hp'],
        $data['almt_lngkp'],
        $userPlg
      );
    } else {

      $sql = "
            UPDATE t_penyewa
            SET
                nik = ?,
                nm_lngkp = ?,
                no_hp = ?,
                almt_lngkp = ?
            WHERE user_plg = ?
        ";

      $stmt = $this->db->prepare($sql);

      if (!$stmt) {
        throw new RuntimeException(
          'Query ubah penyewa gagal disiapkan.'
        );
      }

      $stmt->bind_param(
        'sssss',
        $data['nik'],
        $data['nm_lngkp'],
        $data['no_hp'],
        $data['almt_lngkp'],
        $userPlg
      );
    }

    $success = $stmt->execute();

    $stmt->close();

    return $success;
  }

  public function deleteAdmin(string $userPlg): bool
  {
    $sql = "
        DELETE FROM t_penyewa
        WHERE user_plg = ?
    ";

    $stmt = $this->db->prepare($sql);

    if (!$stmt) {
      throw new RuntimeException(
        'Query hapus penyewa gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $userPlg);

    $stmt->execute();

    $affectedRows = $stmt->affected_rows;

    $stmt->close();

    return $affectedRows > 0;
  }
}

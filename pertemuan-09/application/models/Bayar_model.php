<?php
class Bayar_model
{
  private mysqli $db;

  public function __construct()
  {
    global $db;
    $this->db = $db;
  }

  /* ================= PENYEWA ================= */

  public function getDaftarByPenyewa(string $userPlg): array
  {
    $sql = "SELECT
              b.kode_bayar, b.tgl_wkt_byr, b.ft_byr, b.mtode_byr,
              b.jns_byr, b.nominal, b.sttus_byr, b.tgl_wkt_ver, b.kode_sewa,
              s.tgl_mulai, s.tgl_selesai, s.status_sewa
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            WHERE s.user_plg = ? AND b.jns_byr = 'dp'
            ORDER BY b.kode_bayar DESC";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException('Query daftar pembayaran gagal disiapkan.');
    }
    $stmt->bind_param('s', $userPlg);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
  }

  public function getDetailMilikPenyewa(string $kodeBayar, string $userPlg): ?array
  {
    $sql = "SELECT
              b.kode_bayar, b.tgl_wkt_byr, b.ft_byr, b.mtode_byr,
              b.jns_byr, b.nominal, b.sttus_byr, b.tgl_wkt_ver, b.kode_sewa,
              s.user_plg, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil, s.status_sewa
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            WHERE b.kode_bayar = ? AND s.user_plg = ? AND b.jns_byr = 'dp'
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException('Query detail pembayaran gagal disiapkan.');
    }
    $stmt->bind_param('ss', $kodeBayar, $userPlg);
    $stmt->execute();
    $bayar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bayar) {
      return null;
    }

    $bayar['detail_mobil'] = $this->getDetailMobilSewa((string) $bayar['kode_sewa']);
    return $bayar;
  }

  public function getDetailMobilSewa(string $kodeSewa): array
  {
    $sql = "SELECT
              p.no_plat, p.hrg_sewa, p.n_hari,
              m.nm_mobil, m.jns_mobil, m.transmisi
            FROM t_pilih p
            INNER JOIN t_mobil m ON m.no_plat = p.no_plat
            WHERE p.kode_sewa = ?
            ORDER BY m.nm_mobil ASC";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException('Query rincian mobil sewa gagal disiapkan.');
    }
    $stmt->bind_param('s', $kodeSewa);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
  }

  public function simpanBukti(string $kodeBayar, string $userPlg, string $namaBerkas): ?string
  {
    $lama = $this->getDetailMilikPenyewa($kodeBayar, $userPlg);
    if ($lama === null) {
      throw new InvalidArgumentException('Pembayaran tidak ditemukan atau bukan milik akun ini.');
    }
    if ($lama['sttus_byr'] !== 'pending') {
      throw new InvalidArgumentException('Pembayaran yang sudah diverifikasi tidak dapat diubah.');
    }

    $waktuSekarang = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))
      ->format('Y-m-d H:i:s');

    $sql = "UPDATE t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            SET b.ft_byr = ?, b.tgl_wkt_byr = ?
            WHERE b.kode_bayar = ? AND s.user_plg = ? AND b.sttus_byr = 'pending'";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException('Pembaruan bukti pembayaran gagal disiapkan.');
    }
    $stmt->bind_param('ssss', $namaBerkas, $waktuSekarang, $kodeBayar, $userPlg);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException('Pembaruan bukti pembayaran gagal dijalankan.');
    }
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected < 1) {
      throw new RuntimeException('Data pembayaran tidak berubah.');
    }

    return $lama['ft_byr'] ?? null;
  }

  /* ================= ADMIN ================= */

  public function getDaftarVerifikasiAdmin(?string $filterStatus = null): array
  {
    $where = "WHERE b.jns_byr = 'dp'";
    $types = '';
    $params = [];

    if ($filterStatus !== null && in_array($filterStatus, ['pending', 'valid', 'invalid'], true)) {
      $where .= " AND b.sttus_byr = ?";
      $types .= 's';
      $params[] = $filterStatus;
    }

    // Menggunakan t_penyewa sesuai database lokal Anda
    $sql = "SELECT
              b.kode_bayar, b.tgl_wkt_byr, b.ft_byr, b.mtode_byr,
              b.jns_byr, b.nominal, b.sttus_byr, b.tgl_wkt_ver, b.kode_sewa,
              s.user_plg, s.tgl_mulai, s.tgl_selesai, s.status_sewa,
              plg.nm_lngkp, plg.no_hp
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            INNER JOIN t_penyewa plg ON plg.user_plg = s.user_plg
            $where
            ORDER BY
              CASE b.sttus_byr WHEN 'pending' THEN 0 ELSE 1 END,
              b.kode_bayar DESC";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException('Query verifikasi pembayaran gagal disiapkan.');
    }
    if ($types !== '') {
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
  }

  public function getDetailAdmin(string $kodeBayar): ?array
  {
    // Menggunakan t_penyewa sesuai database lokal Anda
    $sql = "SELECT
              b.kode_bayar, b.tgl_wkt_byr, b.ft_byr, b.mtode_byr,
              b.jns_byr, b.nominal, b.sttus_byr, b.tgl_wkt_ver, b.kode_sewa,
              s.user_plg, s.tgl_mulai, s.tgl_selesai, s.wkt_ambil, s.status_sewa,
              plg.nm_lngkp, plg.nik, plg.no_hp, plg.almt_lngkp
            FROM t_bayar b
            INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
            INNER JOIN t_penyewa plg ON plg.user_plg = s.user_plg
            WHERE b.kode_bayar = ? AND b.jns_byr = 'dp'
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new RuntimeException('Query detail pembayaran admin gagal disiapkan.');
    }
    $stmt->bind_param('s', $kodeBayar);
    $stmt->execute();
    $bayar = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bayar) {
      return null;
    }

    $bayar['detail_mobil'] = $this->getDetailMobilSewa((string) $bayar['kode_sewa']);
    return $bayar;
  }

  public function verifikasiPembayaran(string $kodeBayar, string $keputusan): void
  {
    if (!in_array($keputusan, ['terima', 'tolak'], true)) {
      throw new InvalidArgumentException('Keputusan verifikasi tidak valid.');
    }

    $statusBayarBaru = $keputusan === 'terima' ? 'valid' : 'invalid';
    $statusSewaBaru  = $keputusan === 'terima' ? 'disetujui' : 'ditolak';

    $this->db->begin_transaction();

    try {
      $stmt = $this->db->prepare(
        "SELECT b.kode_bayar, b.ft_byr, b.sttus_byr, b.kode_sewa, s.status_sewa
         FROM t_bayar b
         INNER JOIN t_sewa s ON s.kode_sewa = b.kode_sewa
         WHERE b.kode_bayar = ? AND b.jns_byr = 'dp'
         FOR UPDATE"
      );
      if (!$stmt) {
        throw new RuntimeException('Penguncian data pembayaran gagal.');
      }
      $stmt->bind_param('s', $kodeBayar);
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if (!$row) {
        throw new InvalidArgumentException('Data pembayaran tidak ditemukan.');
      }
      if ($row['sttus_byr'] !== 'pending') {
        throw new InvalidArgumentException('Pembayaran ini sudah diverifikasi sebelumnya.');
      }
      if (empty($row['ft_byr'])) {
        throw new InvalidArgumentException('Bukti pembayaran belum diunggah oleh penyewa.');
      }

      $waktuVer = (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))
        ->format('Y-m-d H:i:s');

      $stmtBayar = $this->db->prepare(
        "UPDATE t_bayar
         SET sttus_byr = ?, tgl_wkt_ver = ?
         WHERE kode_bayar = ? AND sttus_byr = 'pending'"
      );
      if (!$stmtBayar) {
        throw new RuntimeException('Query pembaruan status bayar gagal disiapkan.');
      }
      $stmtBayar->bind_param('sss', $statusBayarBaru, $waktuVer, $kodeBayar);
      if (!$stmtBayar->execute() || $stmtBayar->affected_rows !== 1) {
        $stmtBayar->close();
        throw new RuntimeException('Gagal memperbarui status pembayaran.');
      }
      $stmtBayar->close();

      $kodeSewa = (string) $row['kode_sewa'];
      $stmtSewa = $this->db->prepare(
        "UPDATE t_sewa
         SET status_sewa = ?
         WHERE kode_sewa = ?"
      );
      if (!$stmtSewa) {
        throw new RuntimeException('Query pembaruan status sewa gagal disiapkan.');
      }
      $stmtSewa->bind_param('ss', $statusSewaBaru, $kodeSewa);
      if (!$stmtSewa->execute()) {
        $stmtSewa->close();
        throw new RuntimeException('Gagal memperbarui status transaksi sewa.');
      }
      $stmtSewa->close();

      if ($keputusan === 'tolak') {
        $stmtMobil = $this->db->prepare(
          "UPDATE t_mobil m
           INNER JOIN t_pilih p ON p.no_plat = m.no_plat
           SET m.status_unit = 'tersedia'
           WHERE p.kode_sewa = ?"
        );
        if ($stmtMobil) {
          $stmtMobil->bind_param('s', $kodeSewa);
          $stmtMobil->execute();
          $stmtMobil->close();
        }
      }

      $this->db->commit();
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }
  }
}

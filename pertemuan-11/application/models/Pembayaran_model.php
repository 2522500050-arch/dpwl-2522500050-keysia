<?php

class Pembayaran_model
{
  private mysqli $db;

  public function __construct()
  {
    global $db;

    if (!$db instanceof mysqli) {
      throw new RuntimeException(
        'Koneksi basis data tidak tersedia.'
      );
    }

    $this->db = $db;
  }

  /**
   * Mengambil seluruh pembayaran DP milik penyewa untuk halaman daftar.
   * Data ini memungkinkan penyewa menemukan pembayaran yang dibuat saat checkout.
   */
  public function getDaftarPembayaranPenyewa(string $userPlg): array
  {
    $stmt = $this->db->prepare(
      "SELECT
        b.kode_bayar,
        b.tgl_wkt_byr,
        b.ft_byr,
        b.mtode_byr,
        b.jns_byr,
        b.nominal,
        b.sttus_byr,
        b.tgl_wkt_ver,
        b.kode_sewa,
        s.tgl_mulai,
        s.tgl_selesai,
        s.status_sewa
      FROM t_bayar b
      INNER JOIN t_sewa s
        ON s.kode_sewa = b.kode_sewa
      WHERE s.user_plg = ?
        AND b.jns_byr = 'dp'
      ORDER BY b.tgl_wkt_byr DESC, b.kode_bayar DESC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query daftar pembayaran penyewa gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $userPlg);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Daftar pembayaran penyewa gagal diambil.'
      );
    }

    $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $daftar;
  }

  /**
   * Mengambil satu record pembayaran DP milik penyewa tertentu.
   * Kepemilikan diperiksa melalui relasi t_bayar -> t_sewa -> t_penyewa.
   */
  public function getPembayaranPenyewa(
    string $kodeBayar,
    string $userPlg
  ): ?array {
    $stmt = $this->db->prepare(
      "SELECT
        b.kode_bayar,
        b.tgl_wkt_byr,
        b.ft_byr,
        b.mtode_byr,
        b.jns_byr,
        b.nominal,
        b.sttus_byr,
        b.tgl_wkt_ver,
        b.kode_sewa,
        s.tgl_mulai,
        s.tgl_selesai,
        s.wkt_ambil,
        s.status_sewa,
        p.user_plg,
        p.nm_lngkp,
        p.no_hp
      FROM t_bayar b
      INNER JOIN t_sewa s
        ON s.kode_sewa = b.kode_sewa
      INNER JOIN t_penyewa p
        ON p.user_plg = s.user_plg
      WHERE b.kode_bayar = ?
        AND s.user_plg = ?
        AND b.jns_byr = 'dp'
      LIMIT 1"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query pembayaran penyewa gagal disiapkan.'
      );
    }

    $stmt->bind_param('ss', $kodeBayar, $userPlg);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Data pembayaran penyewa gagal diambil.'
      );
    }

    $pembayaran = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $pembayaran ?: null;
  }

  /**
   * Mengambil daftar kendaraan yang tercatat pada transaksi sewa.
   */
  public function getDaftarKendaraanSewa(string $kodeSewa): array
  {
    $kodeSewa = trim($kodeSewa);

    if ($kodeSewa === '') {
      throw new InvalidArgumentException(
        'Kode transaksi sewa wajib diisi.'
      );
    }

    $stmt = $this->db->prepare(
      "SELECT
        m.no_plat,
        m.nm_mobil
      FROM t_pilih p
      INNER JOIN t_mobil m
        ON m.no_plat = p.no_plat
      WHERE p.kode_sewa = ?
      ORDER BY m.nm_mobil ASC, m.no_plat ASC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query daftar kendaraan transaksi gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Daftar kendaraan transaksi gagal diambil.'
      );
    }

    $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $daftar;
  }

  /**
   * Mengambil pembayaran DP transfer yang sudah memiliki bukti
   * dan masih menunggu verifikasi admin.
   */
  public function getDaftarVerifikasi(): array
  {
    $stmt = $this->db->prepare(
      "SELECT
        b.kode_bayar,
        b.tgl_wkt_byr,
        b.ft_byr,
        b.mtode_byr,
        b.jns_byr,
        b.nominal,
        b.sttus_byr,
        b.kode_sewa,
        s.tgl_mulai,
        s.tgl_selesai,
        s.status_sewa,
        p.user_plg,
        p.nm_lngkp,
        p.no_hp
      FROM t_bayar b
      INNER JOIN t_sewa s
        ON s.kode_sewa = b.kode_sewa
      INNER JOIN t_penyewa p
        ON p.user_plg = s.user_plg
      WHERE b.jns_byr = 'dp'
        AND b.mtode_byr = 'transfer'
        AND b.sttus_byr = 'pending'
        AND b.ft_byr IS NOT NULL
        AND b.ft_byr <> ''
      ORDER BY b.tgl_wkt_byr ASC, b.kode_bayar ASC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query daftar verifikasi gagal disiapkan.'
      );
    }

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Daftar verifikasi pembayaran gagal diambil.'
      );
    }

    $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $daftar;
  }

  /**
   * Mengambil detail pembayaran DP untuk pemeriksaan admin.
   */
  public function getDetailVerifikasi(string $kodeBayar): ?array
  {
    $stmt = $this->db->prepare(
      "SELECT
        b.kode_bayar,
        b.tgl_wkt_byr,
        b.ft_byr,
        b.mtode_byr,
        b.jns_byr,
        b.nominal,
        b.sttus_byr,
        b.tgl_wkt_ver,
        b.kode_sewa,
        s.tgl_mulai,
        s.tgl_selesai,
        s.wkt_ambil,
        s.status_sewa,
        p.user_plg,
        p.nm_lngkp,
        p.no_hp
      FROM t_bayar b
      INNER JOIN t_sewa s
        ON s.kode_sewa = b.kode_sewa
      INNER JOIN t_penyewa p
        ON p.user_plg = s.user_plg
      WHERE b.kode_bayar = ?
        AND b.jns_byr = 'dp'
        AND b.mtode_byr = 'transfer'
      LIMIT 1"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query detail verifikasi gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeBayar);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Detail verifikasi pembayaran gagal diambil.'
      );
    }

    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $detail ?: null;
  }

  /**
   * Memperbarui bukti pada record DP yang dibuat saat checkout.
   * Mengembalikan nama file lama agar Controller dapat menghapusnya
   * setelah file baru berhasil disimpan dan transaksi database commit.
   */
  public function simpanBuktiPembayaran(
    string $kodeBayar,
    string $userPlg,
    string $namaFile
  ): ?string {
    $kodeBayar = trim($kodeBayar);
    $userPlg = trim($userPlg);
    $namaFile = trim($namaFile);

    if ($kodeBayar === '' || $userPlg === '' || $namaFile === '') {
      throw new InvalidArgumentException(
        'Kode pembayaran, penyewa, dan nama berkas wajib tersedia.'
      );
    }

    $this->db->begin_transaction();

    try {
      $stmt = $this->db->prepare(
        "SELECT
          b.ft_byr,
          b.sttus_byr,
          b.jns_byr,
          b.mtode_byr,
          s.status_sewa
        FROM t_bayar b
        INNER JOIN t_sewa s
          ON s.kode_sewa = b.kode_sewa
        WHERE b.kode_bayar = ?
          AND s.user_plg = ?
        LIMIT 1
        FOR UPDATE"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Penguncian pembayaran penyewa gagal disiapkan.'
        );
      }

      $stmt->bind_param('ss', $kodeBayar, $userPlg);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Penguncian pembayaran penyewa gagal dijalankan.'
        );
      }

      $baris = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($baris === null) {
        throw new RuntimeException(
          'Pembayaran tidak ditemukan atau bukan milik akun Anda.'
        );
      }

      if (
        $baris['jns_byr'] !== 'dp' ||
        $baris['mtode_byr'] !== 'transfer' ||
        $baris['sttus_byr'] !== 'pending' ||
        $baris['status_sewa'] !== 'diajukan'
      ) {
        throw new RuntimeException(
          'Pembayaran tidak berada pada status yang dapat diunggah.'
        );
      }

      $fileLama = $baris['ft_byr'] !== null
        ? (string) $baris['ft_byr']
        : null;

      $stmt = $this->db->prepare(
        "UPDATE t_bayar
        SET ft_byr = ?,
            tgl_wkt_byr = NOW()
        WHERE kode_bayar = ?
          AND sttus_byr = 'pending'
          AND jns_byr = 'dp'
          AND mtode_byr = 'transfer'"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pembaruan bukti pembayaran gagal disiapkan.'
        );
      }

      $stmt->bind_param('ss', $namaFile, $kodeBayar);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Bukti pembayaran gagal dicatat.'
        );
      }

      if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new RuntimeException(
          'Data pembayaran berubah atau tidak dapat diperbarui.'
        );
      }

      $stmt->close();
      $this->db->commit();

      return $fileLama;
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }
  }

  /**
   * Memverifikasi pembayaran DP transfer.
   *
   * Persetujuan:
   * - Memastikan seluruh kendaraan transaksi target masih tersedia.
   * - Membatalkan seluruh transaksi lain yang masih diajukan dan memiliki
   *   minimal satu kendaraan yang sama dengan transaksi target.
   * - Mengubah seluruh kendaraan transaksi target menjadi disewa.
   *
   * Penolakan:
   * - Mengubah pembayaran menjadi invalid dan transaksi menjadi ditolak.
   * - Tidak mengubah status kendaraan.
   *
   * Seluruh perubahan dilakukan secara atomik dalam satu transaksi database.
   */
  public function verifikasiPembayaran(
    string $kodeBayar,
    string $statusBaru
  ): bool {
    $kodeBayar = trim($kodeBayar);

    if ($kodeBayar === '') {
      throw new InvalidArgumentException(
        'Kode pembayaran wajib diisi.'
      );
    }

    if (!in_array($statusBaru, ['valid', 'invalid'], true)) {
      throw new InvalidArgumentException(
        'Keputusan verifikasi tidak valid.'
      );
    }

    $statusSewaBaru = $statusBaru === 'valid'
      ? 'disetujui'
      : 'ditolak';

    $this->db->begin_transaction();

    try {
      // Kunci pembayaran dan transaksi target agar tidak diverifikasi
      // lebih dari satu kali.
      $stmt = $this->db->prepare(
        "SELECT
          b.kode_sewa,
          b.ft_byr,
          b.sttus_byr,
          b.jns_byr,
          b.mtode_byr,
          b.nominal,
          s.tgl_mulai,
          s.tgl_selesai,
          s.status_sewa
        FROM t_bayar b
        INNER JOIN t_sewa s
          ON s.kode_sewa = b.kode_sewa
        WHERE b.kode_bayar = ?
        LIMIT 1
        FOR UPDATE"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Penguncian data verifikasi gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $kodeBayar);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Penguncian data verifikasi gagal dijalankan.'
        );
      }

      $baris = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($baris === null) {
        throw new RuntimeException(
          'Data pembayaran tidak ditemukan.'
        );
      }

      if (
        $baris['jns_byr'] !== 'dp' ||
        $baris['mtode_byr'] !== 'transfer' ||
        $baris['sttus_byr'] !== 'pending' ||
        $baris['status_sewa'] !== 'diajukan' ||
        $baris['ft_byr'] === null ||
        trim((string) $baris['ft_byr']) === ''
      ) {
        throw new RuntimeException(
          'Pembayaran tidak memenuhi syarat verifikasi atau sudah diproses.'
        );
      }

      $kodeSewa = (string) $baris['kode_sewa'];

      // Persetujuan hanya boleh dilakukan jika nominal DP cocok.
      // Penolakan tetap dapat dilakukan meskipun nominal tidak sesuai.
      if ($statusBaru === 'valid') {
        $tanggalMulai = \DateTimeImmutable::createFromFormat(
          '!Y-m-d',
          (string) $baris['tgl_mulai']
        );
        $errorMulai = \DateTimeImmutable::getLastErrors();

        $tanggalSelesai = \DateTimeImmutable::createFromFormat(
          '!Y-m-d',
          (string) $baris['tgl_selesai']
        );
        $errorSelesai = \DateTimeImmutable::getLastErrors();

        if (
          $tanggalMulai === false ||
          (
            $errorMulai !== false &&
            (
              $errorMulai['warning_count'] > 0 ||
              $errorMulai['error_count'] > 0
            )
          ) ||
          $tanggalMulai->format('Y-m-d') !== $baris['tgl_mulai'] ||
          $tanggalSelesai === false ||
          (
            $errorSelesai !== false &&
            (
              $errorSelesai['warning_count'] > 0 ||
              $errorSelesai['error_count'] > 0
            )
          ) ||
          $tanggalSelesai->format('Y-m-d') !== $baris['tgl_selesai'] ||
          $tanggalSelesai <= $tanggalMulai
        ) {
          throw new RuntimeException(
            'Periode sewa tidak valid sehingga nominal DP tidak dapat diverifikasi.'
          );
        }

        $hariSeharusnya = (int) $tanggalMulai->diff($tanggalSelesai)->days;

        if ($hariSeharusnya < 1 || $hariSeharusnya > 127) {
          throw new RuntimeException(
            'Lama sewa tidak valid sehingga nominal DP tidak dapat diverifikasi.'
          );
        }

        // Kunci detail transaksi dan hitung ulang nominal berdasarkan
        // snapshot harga yang tersimpan saat checkout.
        $stmt = $this->db->prepare(
          "SELECT
            no_plat,
            hrg_sewa,
            n_hari
          FROM t_pilih
          WHERE kode_sewa = ?
          ORDER BY no_plat ASC
          FOR UPDATE"
        );

        if (!$stmt) {
          throw new RuntimeException(
            'Pemeriksaan detail nominal DP gagal disiapkan.'
          );
        }

        $stmt->bind_param('s', $kodeSewa);

        if (!$stmt->execute()) {
          $stmt->close();
          throw new RuntimeException(
            'Pemeriksaan detail nominal DP gagal dijalankan.'
          );
        }

        $detailSewa = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($detailSewa) === 0) {
          throw new RuntimeException(
            'Transaksi tidak memiliki detail kendaraan untuk menghitung nominal DP.'
          );
        }

        $totalSewa = 0;

        foreach ($detailSewa as $item) {
          $hargaSewa = (int) $item['hrg_sewa'];
          $jumlahHari = (int) $item['n_hari'];

          if ($hargaSewa <= 0 || $jumlahHari !== $hariSeharusnya) {
            throw new RuntimeException(
              'Data harga atau lama sewa tidak konsisten. Verifikasi pembayaran dibatalkan.'
            );
          }

          $totalSewa += $hargaSewa * $jumlahHari;
        }

        $nominalDpSeharusnya = (int) round($totalSewa * 30 / 100);
        $nominalDpTersimpan = (int) $baris['nominal'];

        if (
          $nominalDpSeharusnya <= 0 ||
          $nominalDpTersimpan !== $nominalDpSeharusnya
        ) {
          throw new RuntimeException(
            'Nominal DP pada data pembayaran tidak sesuai dengan hasil perhitungan transaksi. Verifikasi dibatalkan.'
          );
        }

        // Kunci seluruh kendaraan target secara berurutan. Penguncian ini
        // juga menyerialkan persetujuan transaksi yang berebut kendaraan sama.
        $stmt = $this->db->prepare(
          "SELECT
            p.no_plat,
            m.status_unit
          FROM t_pilih p
          INNER JOIN t_mobil m
            ON m.no_plat = p.no_plat
          WHERE p.kode_sewa = ?
          ORDER BY p.no_plat ASC
          FOR UPDATE"
        );

        if (!$stmt) {
          throw new RuntimeException(
            'Penguncian kendaraan transaksi gagal disiapkan.'
          );
        }

        $stmt->bind_param('s', $kodeSewa);

        if (!$stmt->execute()) {
          $stmt->close();
          throw new RuntimeException(
            'Penguncian kendaraan transaksi gagal dijalankan.'
          );
        }

        $daftarKendaraan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (count($daftarKendaraan) === 0) {
          throw new RuntimeException(
            'Transaksi tidak memiliki kendaraan yang dapat diverifikasi.'
          );
        }

        foreach ($daftarKendaraan as $kendaraan) {
          if ($kendaraan['status_unit'] !== 'tersedia') {
            throw new RuntimeException(
              'Status salah satu kendaraan bukan tersedia. Verifikasi dibatalkan.'
            );
          }
        }

        /*
         * Cari transaksi pesaing yang masih diajukan dan memesan setidaknya
         * satu kendaraan target. Daftar diurutkan dan dideduplikasi agar
         * setiap transaksi pesaing hanya diproses satu kali.
         *
         * Bukti pembayaran pesaing tidak disyaratkan: transaksi yang belum
         * mengunggah bukti pun harus dibatalkan jika berebut kendaraan.
         */
        $stmt = $this->db->prepare(
          "SELECT DISTINCT s.kode_sewa
          FROM t_sewa s
          INNER JOIN t_bayar b
            ON b.kode_sewa = s.kode_sewa
          INNER JOIN t_pilih p_pesaing
            ON p_pesaing.kode_sewa = s.kode_sewa
          INNER JOIN t_pilih p_target
            ON p_target.no_plat = p_pesaing.no_plat
          WHERE p_target.kode_sewa = ?
            AND s.kode_sewa <> ?
            AND s.status_sewa = 'diajukan'
            AND b.jns_byr = 'dp'
            AND b.mtode_byr = 'transfer'
            AND b.sttus_byr = 'pending'
          ORDER BY s.kode_sewa ASC"
        );

        if (!$stmt) {
          throw new RuntimeException(
            'Pencarian transaksi sewa yang bersaing gagal disiapkan.'
          );
        }

        $stmt->bind_param('ss', $kodeSewa, $kodeSewa);

        if (!$stmt->execute()) {
          $stmt->close();
          throw new RuntimeException(
            'Pencarian transaksi sewa yang bersaing gagal dijalankan.'
          );
        }

        $daftarPesaing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Kunci dan batalkan setiap transaksi pesaing secara berurutan.
        foreach ($daftarPesaing as $pesaing) {
          $kodeSewaPesaing = (string) $pesaing['kode_sewa'];

          $stmt = $this->db->prepare(
            "SELECT status_sewa
            FROM t_sewa
            WHERE kode_sewa = ?
            LIMIT 1
            FOR UPDATE"
          );

          if (!$stmt) {
            throw new RuntimeException(
              'Penguncian transaksi pesaing gagal disiapkan.'
            );
          }

          $stmt->bind_param('s', $kodeSewaPesaing);

          if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
              'Penguncian transaksi pesaing gagal dijalankan.'
            );
          }

          $sewaPesaing = $stmt->get_result()->fetch_assoc();
          $stmt->close();

          // Status dapat berubah ketika menunggu penguncian.
          if (
            $sewaPesaing === null ||
            $sewaPesaing['status_sewa'] !== 'diajukan'
          ) {
            continue;
          }

          $stmt = $this->db->prepare(
            "SELECT kode_bayar
            FROM t_bayar
            WHERE kode_sewa = ?
              AND jns_byr = 'dp'
              AND mtode_byr = 'transfer'
              AND sttus_byr = 'pending'
            ORDER BY kode_bayar ASC
            FOR UPDATE"
          );

          if (!$stmt) {
            throw new RuntimeException(
              'Penguncian pembayaran transaksi pesaing gagal disiapkan.'
            );
          }

          $stmt->bind_param('s', $kodeSewaPesaing);

          if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
              'Penguncian pembayaran transaksi pesaing gagal dijalankan.'
            );
          }

          $pembayaranPesaing = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
          $stmt->close();

          if (count($pembayaranPesaing) === 0) {
            continue;
          }

          $stmt = $this->db->prepare(
            "UPDATE t_bayar
            SET sttus_byr = 'invalid',
                tgl_wkt_ver = NOW()
            WHERE kode_sewa = ?
              AND jns_byr = 'dp'
              AND mtode_byr = 'transfer'
              AND sttus_byr = 'pending'"
          );

          if (!$stmt) {
            throw new RuntimeException(
              'Pembatalan pembayaran transaksi pesaing gagal disiapkan.'
            );
          }

          $stmt->bind_param('s', $kodeSewaPesaing);

          if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
              'Pembayaran transaksi pesaing gagal dibatalkan.'
            );
          }

          if ($stmt->affected_rows !== count($pembayaranPesaing)) {
            $stmt->close();
            throw new RuntimeException(
              'Status pembayaran transaksi pesaing berubah. Seluruh proses dibatalkan.'
            );
          }

          $stmt->close();

          $stmt = $this->db->prepare(
            "UPDATE t_sewa
            SET status_sewa = 'ditolak'
            WHERE kode_sewa = ?
              AND status_sewa = 'diajukan'"
          );

          if (!$stmt) {
            throw new RuntimeException(
              'Pembatalan transaksi pesaing gagal disiapkan.'
            );
          }

          $stmt->bind_param('s', $kodeSewaPesaing);

          if (!$stmt->execute()) {
            $stmt->close();
            throw new RuntimeException(
              'Transaksi pesaing gagal dibatalkan.'
            );
          }

          if ($stmt->affected_rows !== 1) {
            $stmt->close();
            throw new RuntimeException(
              'Status transaksi pesaing berubah. Seluruh proses dibatalkan.'
            );
          }

          $stmt->close();
        }
      }

      // Perbarui status pembayaran transaksi target.
      $stmt = $this->db->prepare(
        "UPDATE t_bayar
        SET sttus_byr = ?,
            tgl_wkt_ver = NOW()
        WHERE kode_bayar = ?
          AND sttus_byr = 'pending'
          AND jns_byr = 'dp'
          AND mtode_byr = 'transfer'
          AND ft_byr IS NOT NULL
          AND ft_byr <> ''"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pembaruan status pembayaran gagal disiapkan.'
        );
      }

      $stmt->bind_param('ss', $statusBaru, $kodeBayar);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Status pembayaran gagal diperbarui.'
        );
      }

      if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new RuntimeException(
          'Status pembayaran berubah atau sudah diverifikasi.'
        );
      }

      $stmt->close();

      // Perbarui status transaksi target.
      $stmt = $this->db->prepare(
        "UPDATE t_sewa
        SET status_sewa = ?
        WHERE kode_sewa = ?
          AND status_sewa = 'diajukan'"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pembaruan status transaksi gagal disiapkan.'
        );
      }

      $stmt->bind_param('ss', $statusSewaBaru, $kodeSewa);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Status transaksi gagal diperbarui.'
        );
      }

      if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new RuntimeException(
          'Status transaksi berubah atau tidak memenuhi syarat verifikasi.'
        );
      }

      $stmt->close();

      // Hanya persetujuan yang mengubah status kendaraan menjadi disewa.
      // Penolakan manual tidak mengubah status kendaraan apa pun.
      if ($statusBaru === 'valid') {
        $stmt = $this->db->prepare(
          "UPDATE t_mobil m
          INNER JOIN t_pilih p
            ON p.no_plat = m.no_plat
          SET m.status_unit = 'disewa'
          WHERE p.kode_sewa = ?"
        );

        if (!$stmt) {
          throw new RuntimeException(
            'Pembaruan status kendaraan gagal disiapkan.'
          );
        }

        $stmt->bind_param('s', $kodeSewa);

        if (!$stmt->execute()) {
          $stmt->close();
          throw new RuntimeException(
            'Status kendaraan gagal diperbarui.'
          );
        }

        if ($stmt->affected_rows !== count($daftarKendaraan)) {
          $stmt->close();
          throw new RuntimeException(
            'Tidak seluruh status kendaraan transaksi target berhasil diperbarui.'
          );
        }

        $stmt->close();
      }

      $this->db->commit();

      return true;
    } catch (Throwable $e) {
      $this->db->rollback();
      throw $e;
    }
  }

  // =========================================================================
  // METHOD PERTEMUAN 10 (P10) - PELUNASAN TRANSAKSI
  // =========================================================================

  /**
   * P10: Mengambil daftar transaksi yang dapat diproses pelunasannya.
   * Total transaksi menggunakan snapshot tarif pada t_pilih.
   * Pembayaran yang mengurangi kewajiban hanya yang berstatus valid.
   */
  public function getDaftarPelunasan(): array
  {
    $stmt = $this->db->prepare(
      "SELECT
        s.kode_sewa,
        s.tgl_mulai,
        s.tgl_selesai,
        s.status_sewa,
        p.user_plg,
        p.nm_lngkp,
        p.no_hp,
        COALESCE((
          SELECT SUM(tp.hrg_sewa * tp.n_hari)
          FROM t_pilih tp
          WHERE tp.kode_sewa = s.kode_sewa
        ), 0) AS total_transaksi,
        COALESCE((
          SELECT SUM(b.nominal)
          FROM t_bayar b
          WHERE b.kode_sewa = s.kode_sewa
            AND b.sttus_byr = 'valid'
        ), 0) AS total_terbayar
      FROM t_sewa s
      INNER JOIN t_penyewa p
        ON p.user_plg = s.user_plg
      WHERE s.status_sewa IN ('disetujui', 'lunas')
        AND EXISTS (
          SELECT 1
          FROM t_pilih tp
          WHERE tp.kode_sewa = s.kode_sewa
        )
      ORDER BY s.tgl_mulai DESC, s.kode_sewa DESC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query daftar pelunasan gagal disiapkan.'
      );
    }

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Daftar pelunasan gagal diambil.'
      );
    }

    $daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($daftar as &$item) {
      $item['total_transaksi'] = (int) $item['total_transaksi'];
      $item['total_terbayar'] = (int) $item['total_terbayar'];
      $item['sisa_kewajiban'] = max(
        0,
        $item['total_transaksi'] - $item['total_terbayar']
      );
    }
    unset($item);

    return array_values(array_filter(
      $daftar,
      static fn(array $item): bool =>
      $item['sisa_kewajiban'] > 0 ||
        $item['status_sewa'] === 'lunas'
    ));
  }

  /**
   * P10: Mengambil detail transaksi, penyewa, dan riwayat pembayaran
   * untuk halaman pelunasan.
   */
  public function getDetailPelunasan(string $kodeSewa): ?array
  {
    $kodeSewa = trim($kodeSewa);
    if ($kodeSewa === '') {
      throw new InvalidArgumentException(
        'Kode transaksi sewa wajib diisi.'
      );
    }

    $stmt = $this->db->prepare(
      "SELECT
        s.kode_sewa,
        s.tgl_mulai,
        s.tgl_selesai,
        s.wkt_ambil,
        s.status_sewa,
        p.user_plg,
        p.nm_lngkp,
        p.no_hp,
        p.nik,
        p.almt_lngkp,
        COALESCE((
          SELECT SUM(tp.hrg_sewa * tp.n_hari)
          FROM t_pilih tp
          WHERE tp.kode_sewa = s.kode_sewa
        ), 0) AS total_transaksi,
        COALESCE((
          SELECT SUM(b.nominal)
          FROM t_bayar b
          WHERE b.kode_sewa = s.kode_sewa
            AND b.sttus_byr = 'valid'
        ), 0) AS total_terbayar
      FROM t_sewa s
      INNER JOIN t_penyewa p
        ON p.user_plg = s.user_plg
      WHERE s.kode_sewa = ?
      LIMIT 1"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query detail pelunasan gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Detail transaksi pelunasan gagal diambil.'
      );
    }

    $detail = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($detail === null) {
      return null;
    }

    $detail['total_transaksi'] = (int) $detail['total_transaksi'];
    $detail['total_terbayar'] = (int) $detail['total_terbayar'];
    $detail['sisa_kewajiban'] = max(
      0,
      $detail['total_transaksi'] - $detail['total_terbayar']
    );

    $stmt = $this->db->prepare(
      "SELECT
        kode_bayar,
        tgl_wkt_byr,
        mtode_byr,
        jns_byr,
        nominal,
        sttus_byr,
        ft_byr,
        tgl_wkt_ver
      FROM t_bayar
      WHERE kode_sewa = ?
      ORDER BY tgl_wkt_byr ASC, kode_bayar ASC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query riwayat pembayaran gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Riwayat pembayaran gagal diambil.'
      );
    }

    $detail['riwayat_pembayaran'] =
      $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $this->db->prepare(
      "SELECT
        m.no_plat,
        m.nm_mobil,
        tp.hrg_sewa,
        tp.n_hari,
        (tp.hrg_sewa * tp.n_hari) AS subtotal
      FROM t_pilih tp
      INNER JOIN t_mobil m
        ON m.no_plat = tp.no_plat
      WHERE tp.kode_sewa = ?
      ORDER BY m.nm_mobil ASC, m.no_plat ASC"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Query detail kendaraan pelunasan gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $kodeSewa);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Detail kendaraan pelunasan gagal diambil.'
      );
    }

    $detail['daftar_kendaraan'] =
      $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $detail;
  }

  /**
   * P10: Membuat kode pembayaran dengan format PAY/YYYYMMDD/NN.
   * Pemanggil harus memegang named lock pembayaran harian.
   */
  private function buatKodeBayarPelunasan(string $tanggalKode): string
  {
    $pola = 'PAY/' . $tanggalKode . '/%';

    $stmt = $this->db->prepare(
      "SELECT kode_bayar
      FROM t_bayar
      WHERE kode_bayar LIKE ?
      ORDER BY kode_bayar DESC
      LIMIT 1"
    );

    if (!$stmt) {
      throw new RuntimeException(
        'Pembuatan kode pembayaran gagal disiapkan.'
      );
    }

    $stmt->bind_param('s', $pola);

    if (!$stmt->execute()) {
      $stmt->close();
      throw new RuntimeException(
        'Pembuatan kode pembayaran gagal dijalankan.'
      );
    }

    $terakhir = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $nomorBerikutnya = 1;

    if ($terakhir !== null) {
      $bagianKode = explode('/', (string) $terakhir['kode_bayar']);
      $nomorBerikutnya = ((int) end($bagianKode)) + 1;

      if ($nomorBerikutnya > 99) {
        throw new RuntimeException(
          'Nomor pembayaran harian telah mencapai batas 99.'
        );
      }
    }

    return sprintf('PAY/%s/%02d', $tanggalKode, $nomorBerikutnya);
  }

  /**
   * P10: Mencatat pelunasan langsung oleh admin.
   * Pembayaran langsung dicatat sebagai valid.
   * Status transaksi diperbarui menjadi lunas dalam transaksi database
   * yang sama. Status kendaraan tidak diubah pada P10.
   */
  public function simpanPelunasan(
    string $kodeSewa,
    string $metodeBayar,
    ?string $namaFileBukti = null
  ): string {
    $kodeSewa = trim($kodeSewa);
    $metodeBayar = trim($metodeBayar);

    if ($kodeSewa === '') {
      throw new InvalidArgumentException(
        'Kode transaksi sewa wajib diisi.'
      );
    }

    if (!in_array($metodeBayar, ['tunai', 'transfer'], true)) {
      throw new InvalidArgumentException(
        'Metode pembayaran tidak valid.'
      );
    }

    if ($metodeBayar === 'transfer') {
      $namaFileBukti = trim((string) $namaFileBukti);

      if ($namaFileBukti === '') {
        throw new InvalidArgumentException(
          'Bukti transfer wajib tersedia.'
        );
      }
    } else {
      $namaFileBukti = null;
    }

    // Harus sama dengan kunci yang digunakan Transaksi_model::checkout()
    // ketika membuat kode pembayaran DP.
    $namaKunci = 'dpwl_payment_code_sequence';
    $kunciDiperoleh = false;
    $transaksiDimulai = false;

    try {
      $stmt = $this->db->prepare(
        'SELECT GET_LOCK(?, 10) AS lock_status'
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Kunci pembuatan kode pembayaran gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $namaKunci);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Kunci pembuatan kode pembayaran gagal dijalankan.'
        );
      }

      $hasilKunci = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ((int) ($hasilKunci['lock_status'] ?? 0) !== 1) {
        throw new RuntimeException(
          'Proses pembayaran sedang digunakan. Silakan coba kembali.'
        );
      }

      $kunciDiperoleh = true;

      $this->db->begin_transaction();
      $transaksiDimulai = true;

      // Kunci transaksi agar dua permintaan pelunasan tidak berjalan
      // bersamaan untuk transaksi yang sama.
      $stmt = $this->db->prepare(
        "SELECT
          s.kode_sewa,
          s.status_sewa,
          COALESCE((
            SELECT SUM(tp.hrg_sewa * tp.n_hari)
            FROM t_pilih tp
            WHERE tp.kode_sewa = s.kode_sewa
          ), 0) AS total_transaksi,
          COALESCE((
            SELECT SUM(b.nominal)
            FROM t_bayar b
            WHERE b.kode_sewa = s.kode_sewa
              AND b.sttus_byr = 'valid'
          ), 0) AS total_terbayar
        FROM t_sewa s
        WHERE s.kode_sewa = ?
        LIMIT 1
        FOR UPDATE"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pemeriksaan transaksi pelunasan gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $kodeSewa);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Pemeriksaan transaksi pelunasan gagal dijalankan.'
        );
      }

      $transaksi = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($transaksi === null) {
        throw new RuntimeException(
          'Transaksi sewa tidak ditemukan.'
        );
      }

      if (!in_array(
        $transaksi['status_sewa'],
        ['disetujui', 'lunas'],
        true
      )) {
        throw new RuntimeException(
          'Status transaksi belum memenuhi syarat pelunasan.'
        );
      }

      $totalTransaksi = (int) $transaksi['total_transaksi'];
      $totalTerbayar = (int) $transaksi['total_terbayar'];
      $sisaKewajiban = $totalTransaksi - $totalTerbayar;

      if ($totalTransaksi <= 0 || $sisaKewajiban <= 0) {
        throw new RuntimeException(
          'Transaksi tidak memiliki sisa kewajiban untuk dilunasi.'
        );
      }

      if ($sisaKewajiban > 8388607) {
        throw new RuntimeException(
          'Sisa kewajiban melebihi kapasitas kolom nominal pada database.'
        );
      }

      // Pastikan transaksi belum memiliki record pelunasan.
      $stmt = $this->db->prepare(
        "SELECT kode_bayar
        FROM t_bayar
        WHERE kode_sewa = ?
          AND jns_byr = 'pelunasan'
        LIMIT 1
        FOR UPDATE"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pemeriksaan pelunasan sebelumnya gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $kodeSewa);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Pemeriksaan pelunasan sebelumnya gagal dijalankan.'
        );
      }

      $pelunasanAda = $stmt->get_result()->fetch_assoc() !== null;
      $stmt->close();

      if ($pelunasanAda) {
        throw new RuntimeException(
          'Transaksi ini sudah memiliki record pelunasan.'
        );
      }

      $hasilWaktu = $this->db->query(
        "SELECT
          DATE_FORMAT(NOW(), '%Y%m%d') AS tanggal_kode,
          NOW() AS waktu_pembayaran"
      );

      if (!$hasilWaktu) {
        throw new RuntimeException(
          'Waktu pembayaran gagal dibaca.'
        );
      }

      $waktu = $hasilWaktu->fetch_assoc();
      $hasilWaktu->free();

      $tanggalKode = (string) ($waktu['tanggal_kode'] ?? '');
      $waktuPembayaran = (string) ($waktu['waktu_pembayaran'] ?? '');

      if (
        !preg_match('/^\d{8}$/', $tanggalKode) ||
        $waktuPembayaran === ''
      ) {
        throw new RuntimeException(
          'Waktu pembayaran tidak valid.'
        );
      }

      $kodeBayar = $this->buatKodeBayarPelunasan($tanggalKode);

      $stmt = $this->db->prepare(
        "INSERT INTO t_bayar (
          kode_bayar,
          tgl_wkt_byr,
          ft_byr,
          mtode_byr,
          jns_byr,
          nominal,
          sttus_byr,
          tgl_wkt_ver,
          kode_sewa
        ) VALUES (?, ?, ?, ?, 'pelunasan', ?, 'valid', NOW(), ?)"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pencatatan pelunasan gagal disiapkan.'
        );
      }

      $stmt->bind_param(
        'ssssis',
        $kodeBayar,
        $waktuPembayaran,
        $namaFileBukti,
        $metodeBayar,
        $sisaKewajiban,
        $kodeSewa
      );

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Pelunasan gagal disimpan.'
        );
      }

      $stmt->close();

      // Pelunasan berhasil dicatat; perbarui status transaksi
      // dalam transaksi database yang sama.
      $stmt = $this->db->prepare(
        "UPDATE t_sewa
        SET status_sewa = 'lunas'
        WHERE kode_sewa = ?
          AND status_sewa IN ('disetujui', 'lunas')"
      );

      if (!$stmt) {
        throw new RuntimeException(
          'Pembaruan status transaksi menjadi lunas gagal disiapkan.'
        );
      }

      $stmt->bind_param('s', $kodeSewa);

      if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException(
          'Status transaksi gagal diperbarui menjadi lunas.'
        );
      }

      if ($stmt->affected_rows !== 1) {
        $stmt->close();
        throw new RuntimeException(
          'Status transaksi tidak berubah menjadi lunas. ' .
            'Periksa kembali status transaksi.'
        );
      }

      $stmt->close();

      $this->db->commit();
      $transaksiDimulai = false;

      return $kodeBayar;
    } catch (Throwable $e) {
      if ($transaksiDimulai) {
        $this->db->rollback();
      }

      throw $e;
    } finally {
      if ($kunciDiperoleh) {
        $stmt = $this->db->prepare(
          'SELECT RELEASE_LOCK(?)'
        );

        if ($stmt) {
          $stmt->bind_param('s', $namaKunci);
          $stmt->execute();
          $stmt->close();
        }
      }
    }
  }
}

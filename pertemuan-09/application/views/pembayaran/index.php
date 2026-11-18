<?php
require APPPATH . 'views/katalog/header.php';

$e = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($nilai): string {
  return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};

require APPPATH . 'views/katalog/navbar.php';
$daftar = is_array($daftar ?? null) ? $daftar : [];
?>

<section class="bg-success py-4">
  <div class="container">
    <h1 class="h2 text-white mt-2">Pembayaran Awal (DP)</h1>
    <p class="text-white mb-0">Daftar tagihan uang muka transaksi penyewaan kendaraan Anda.</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <?php if (is_array($flash ?? null) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= ($flash['type'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= $e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?php if (empty($daftar)): ?>
      <div class="alert alert-info">
        Belum ada data pembayaran DP. Silakan lakukan checkout kendaraan terlebih dahulu.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
          <thead class="table-light">
            <tr>
              <th>Kode Bayar</th>
              <th>Kode Transaksi</th>
              <th>Periode Sewa</th>
              <th class="text-end">Nominal DP (30%)</th>
              <th>Bukti Transfer</th>
              <th>Status Bayar</th>
              <th>Status Sewa</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($daftar as $row): ?>
              <?php
              $stBayar = (string) ($row['sttus_byr'] ?? 'pending');
              $badgeBayar = $stBayar === 'valid' ? 'bg-success' : ($stBayar === 'invalid' ? 'bg-danger' : 'bg-warning text-dark');
              ?>
              <tr>
                <td><strong><?= $e($row['kode_bayar']) ?></strong></td>
                <td><?= $e($row['kode_sewa']) ?></td>
                <td><?= $e($row['tgl_mulai']) ?> s/d <?= $e($row['tgl_selesai']) ?></td>
                <td class="text-end text-nowrap"><?= $e($formatRupiah($row['nominal'])) ?></td>
                <td>
                  <?php if (!empty($row['ft_byr'])): ?>
                    <span class="badge bg-info text-dark">Sudah diunggah</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Belum ada</span>
                  <?php endif; ?>
                </td>
                <td><span class="badge <?= $badgeBayar ?>"><?= $e(strtoupper($stBayar)) ?></span></td>
                <td><?= $e(ucfirst((string) ($row['status_sewa'] ?? '-'))) ?></td>
                <td class="text-center">
                  <a href="<?= $e(site_url('pembayaran/detail?kode_bayar=' . rawurlencode((string) $row['kode_bayar']))) ?>"
                    class="btn btn-sm btn-success">
                    Detail & Unggah
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require APPPATH . 'views/katalog/footer.php'; ?>
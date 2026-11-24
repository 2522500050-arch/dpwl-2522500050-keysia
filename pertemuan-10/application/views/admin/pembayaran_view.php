<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$e = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($nilai): string {
  return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};
$daftar = is_array($daftar ?? null) ? $daftar : [];
$filterStatus = (string) ($filter_status ?? 'semua');
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Verifikasi Pembayaran DP</h1>
  </section>

  <section class="content">
    <?php if (is_array($flash ?? null) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= $e($flash['type'] ?? 'info') ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?= $e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Daftar Pembayaran Awal (DP 30%)</h3>
        <div class="box-tools">
          <a href="<?= $e(site_url('admin/pembayaran')) ?>" class="btn btn-xs <?= $filterStatus === 'semua' ? 'btn-primary' : 'btn-default' ?>">Semua</a>
          <a href="<?= $e(site_url('admin/pembayaran?status=pending')) ?>" class="btn btn-xs <?= $filterStatus === 'pending' ? 'btn-warning' : 'btn-default' ?>">Pending</a>
          <a href="<?= $e(site_url('admin/pembayaran?status=valid')) ?>" class="btn btn-xs <?= $filterStatus === 'valid' ? 'btn-success' : 'btn-default' ?>">Valid</a>
          <a href="<?= $e(site_url('admin/pembayaran?status=invalid')) ?>" class="btn btn-xs <?= $filterStatus === 'invalid' ? 'btn-danger' : 'btn-default' ?>">Invalid</a>
        </div>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Kode Bayar</th>
              <th>Kode Transaksi</th>
              <th>Penyewa</th>
              <th>Nominal DP</th>
              <th>Waktu Unggah</th>
              <th>Bukti</th>
              <th>Status Bayar</th>
              <th>Status Sewa</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($daftar)): ?>
              <tr>
                <td colspan="9" class="text-center">Tidak ada data pembayaran.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftar as $row): ?>
                <?php
                $st = (string) ($row['sttus_byr'] ?? 'pending');
                $labelClass = $st === 'valid' ? 'label-success' : ($st === 'invalid' ? 'label-danger' : 'label-warning');
                ?>
                <tr>
                  <td><strong><?= $e($row['kode_bayar']) ?></strong></td>
                  <td><?= $e($row['kode_sewa']) ?></td>
                  <td><?= $e($row['nm_lngkp']) ?> (<?= $e($row['user_plg']) ?>)</td>
                  <td><?= $e($formatRupiah($row['nominal'])) ?></td>
                  <td><?= $e($row['tgl_wkt_byr'] ?? '-') ?></td>
                  <td>
                    <?php if (!empty($row['ft_byr'])): ?>
                      <span class="label label-info">Ada Bukti</span>
                    <?php else: ?>
                      <span class="label label-default">Belum Unggah</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="label <?= $labelClass ?>"><?= $e(strtoupper($st)) ?></span></td>
                  <td><?= $e(ucfirst((string) $row['status_sewa'])) ?></td>
                  <td>
                    <a href="<?= $e(site_url('admin/pembayaran/detail?kode_bayar=' . rawurlencode((string) $row['kode_bayar']))) ?>"
                      class="btn btn-xs btn-primary">
                      Periksa & Verifikasi
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>
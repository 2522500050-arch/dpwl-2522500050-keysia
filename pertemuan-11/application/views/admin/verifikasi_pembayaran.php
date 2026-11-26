<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$daftarVerifikasi = $daftarVerifikasi ?? [];
$flash = $flash ?? null;
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Verifikasi Pembayaran <small>Pembayaran DP menunggu pemeriksaan</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
      <li class="active">Verifikasi Pembayaran</li>
    </ol>
  </section>
  <section class="content">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= $e(($flash['type'] ?? '') === 'success' ? 'success' : 'danger') ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button><?= $e($flash['message']) ?>
      </div>
    <?php endif; ?>
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Daftar Pembayaran Menunggu Verifikasi</h3>
      </div>
      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No.</th>
              <th>Kode Bayar</th>
              <th>Penyewa</th>
              <th>No. HP</th>
              <th>Kode Sewa</th>
              <th>Periode</th>
              <th>Nominal DP</th>
              <th>Waktu Unggah</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$daftarVerifikasi): ?>
              <tr>
                <td colspan="9" class="text-center">Tidak ada pembayaran yang menunggu verifikasi.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($daftarVerifikasi as $i => $item): ?>
                <tr>
                  <td><?= (int) $i + 1 ?></td>
                  <td><?= $e($item['kode_bayar'] ?? '') ?></td>
                  <td><?= $e($item['nm_lngkp'] ?? '') ?><br><small><?= $e($item['user_plg'] ?? '') ?></small></td>
                  <td><?= $e($item['no_hp'] ?? '') ?></td>
                  <td><?= $e($item['kode_sewa'] ?? '') ?></td>
                  <td><?= $e($item['tgl_mulai'] ?? '') ?> s.d. <?= $e($item['tgl_selesai'] ?? '') ?></td>
                  <td>Rp <?= number_format((int) ($item['nominal'] ?? 0), 0, ',', '.') ?></td>
                  <td><?= $e($item['tgl_wkt_byr'] ?? '') ?></td>
                  <td><a class="btn btn-primary btn-xs" href="<?= $e(site_url('admin/pembayaran/verifikasi/' . str_replace('/', '~', (string) $item['kode_bayar']))) ?>"><i class="fa fa-search"></i> Periksa</a></td>
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
<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$pembayaran = $pembayaran ?? [];
$daftarKendaraan = $daftarKendaraan ?? [];
$flash = $flash ?? null;
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$statusBayar = (string) ($pembayaran['sttus_byr'] ?? '');
$statusSewa = (string) ($pembayaran['status_sewa'] ?? '');
$namaBukti = trim((string) ($pembayaran['ft_byr'] ?? ''));
$bolehVerifikasi = $statusBayar === 'pending'
  && $statusSewa === 'diajukan'
  && $namaBukti !== ''
  && empty($pembayaran['tgl_wkt_ver']);
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Detail Verifikasi Pembayaran <small>Pemeriksaan bukti transfer DP</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= $e(site_url('admin')) ?>">Dashboard</a></li>
      <li><a href="<?= $e(site_url('admin/pembayaran')) ?>">Verifikasi Pembayaran</a></li>
      <li class="active">Detail</li>
    </ol>
  </section>
  <section class="content">
    <?php if (is_array($flash) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= $e(($flash['type'] ?? '') === 'success' ? 'success' : 'danger') ?>"><?= $e($flash['message']) ?></div>
    <?php endif; ?>
    <div class="box">
      <div class="box-header with-border">
        <h3 class="box-title">Informasi Pembayaran</h3>
      </div>
      <div class="box-body">
        <dl class="dl-horizontal">
          <dt>Kode pembayaran</dt>
          <dd><?= $e($pembayaran['kode_bayar'] ?? '') ?></dd>
          <dt>Kode transaksi</dt>
          <dd><?= $e($pembayaran['kode_sewa'] ?? '') ?></dd>
          <dt>Nama penyewa</dt>
          <dd><?= $e($pembayaran['nm_lngkp'] ?? '') ?></dd>
          <dt>Username penyewa</dt>
          <dd><?= $e($pembayaran['user_plg'] ?? '') ?></dd>
          <dt>Nomor HP</dt>
          <dd><?= $e($pembayaran['no_hp'] ?? '') ?></dd>
          <dt>Periode sewa</dt>
          <dd><?= $e($pembayaran['tgl_mulai'] ?? '') ?> s.d. <?= $e($pembayaran['tgl_selesai'] ?? '') ?></dd>
          <dt>Waktu pengambilan</dt>
          <dd><?= $e($pembayaran['wkt_ambil'] ?? '') ?></dd>
          <dt>Nominal DP</dt>
          <dd><strong>Rp <?= number_format((int) ($pembayaran['nominal'] ?? 0), 0, ',', '.') ?></strong></dd>
          <dt>Metode</dt>
          <dd><?= $e($pembayaran['mtode_byr'] ?? '') ?></dd>
          <dt>Status pembayaran</dt>
          <dd><?= $e($statusBayar) ?></dd>
          <dt>Status transaksi</dt>
          <dd><?= $e($statusSewa) ?></dd>
          <dt>Waktu unggah</dt>
          <dd><?= $e($pembayaran['tgl_wkt_byr'] ?? '-') ?></dd>
          <dt>Waktu verifikasi</dt>
          <dd><?= $e($pembayaran['tgl_wkt_ver'] ?? '-') ?></dd>
        </dl>
        <h4>Kendaraan yang dipesan</h4>
        <?php if (!$daftarKendaraan): ?>
          <div class="alert alert-warning">Data kendaraan pada transaksi ini tidak ditemukan.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>No. Polisi</th>
                  <th>Nama Kendaraan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($daftarKendaraan as $i => $kendaraan): ?>
                  <tr>
                    <td><?= (int) $i + 1 ?></td>
                    <td><?= $e($kendaraan['no_plat'] ?? '') ?></td>
                    <td><?= $e($kendaraan['nm_mobil'] ?? '') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <?php if ($namaBukti !== ''): ?>
          <h4>Bukti transfer</h4>
          <a href="<?= $e(base_url('uploads/bayar/' . rawurlencode($namaBukti))) ?>" target="_blank" rel="noopener">
            <img src="<?= $e(base_url('uploads/bayar/' . rawurlencode($namaBukti))) ?>" alt="Bukti transfer" class="img-responsive img-thumbnail" style="max-width:600px">
          </a>
        <?php else: ?>
          <div class="alert alert-warning">Bukti pembayaran belum tersedia.</div>
        <?php endif; ?>
      </div>
      <?php if ($bolehVerifikasi): ?>
        <div class="box-footer">
          <form method="post" action="<?= $e(site_url('admin/pembayaran/proses/' . str_replace('/', '~', (string) $pembayaran['kode_bayar']))) ?>" class="form-inline">
            <button type="submit" name="sttus_byr" value="valid" class="btn btn-success" onclick="return confirm('Terima pembayaran ini dan setujui transaksi?')"><i class="fa fa-check"></i> Terima Pembayaran</button>
            <button type="submit" name="sttus_byr" value="invalid" class="btn btn-danger" onclick="return confirm('Tolak pembayaran ini dan tolak transaksi?')"><i class="fa fa-times"></i> Tolak Pembayaran</button>
          </form>
        </div>
      <?php else: ?>
        <div class="box-footer">
          <div class="alert alert-info mb-0">Pembayaran tidak dapat diverifikasi ulang. Pastikan status dan bukti pembayaran.</div>
        </div>
      <?php endif; ?>
    </div>
    <a href="<?= $e(site_url('admin/pembayaran')) ?>" class="btn btn-default">Kembali ke daftar</a>
  </section>
</div>
<?php require APPPATH . 'views/templates/footer.php'; ?>
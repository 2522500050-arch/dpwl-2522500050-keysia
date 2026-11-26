<?php
require APPPATH . 'views/katalog/header.php';
require APPPATH . 'views/katalog/navbar.php';

$pembayaran = $pembayaran ?? [];
$daftarKendaraan = $daftarKendaraan ?? [];
$flash = $flash ?? null;
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$status = (string) ($pembayaran['sttus_byr'] ?? '');
$bolehUnggah = $status === 'pending' && empty($pembayaran['tgl_wkt_ver']);
$namaBukti = trim((string) ($pembayaran['ft_byr'] ?? ''));
?>
<section class="container py-5">
  <h1 class="h2 mb-3">Detail Pembayaran DP</h1>
  <?php if (is_array($flash) && !empty($flash['message'])): ?>
    <div class="alert alert-<?= $e(($flash['type'] ?? '') === 'success' ? 'success' : 'danger') ?>"><?= $e($flash['message']) ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-4">Kode pembayaran</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['kode_bayar'] ?? '') ?></dd>
        <dt class="col-sm-4">Kode transaksi sewa</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['kode_sewa'] ?? '') ?></dd>
        <dt class="col-sm-4">Periode sewa</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['tgl_mulai'] ?? '') ?> s.d. <?= $e($pembayaran['tgl_selesai'] ?? '') ?></dd>
        <dt class="col-sm-4">Waktu rencana pengambilan</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['wkt_ambil'] ?? '-') ?></dd>
        <dt class="col-sm-4">Nominal DP</dt>
        <dd class="col-sm-8">Rp <?= number_format((int) ($pembayaran['nominal'] ?? 0), 0, ',', '.') ?></dd>
        <dt class="col-sm-4">Metode</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['mtode_byr'] ?? '') ?></dd>
        <dt class="col-sm-4">Status pembayaran</dt>
        <dd class="col-sm-8"><?= $e($status) ?></dd>
        <dt class="col-sm-4">Status transaksi</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['status_sewa'] ?? '') ?></dd>
        <dt class="col-sm-4">Waktu unggah</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['tgl_wkt_byr'] ?? '-') ?></dd>
        <dt class="col-sm-4">Waktu verifikasi</dt>
        <dd class="col-sm-8"><?= $e($pembayaran['tgl_wkt_ver'] ?? '-') ?></dd>
      </dl>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><strong>Kendaraan yang dipesan</strong></div>
    <div class="card-body">
      <?php if (!$daftarKendaraan): ?>
        <p class="text-muted mb-0">Data kendaraan pada transaksi ini tidak ditemukan.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped mb-0">
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
    </div>
  </div>

  <?php if ($namaBukti !== ''): ?>
    <div class="mb-4">
      <h2 class="h5">Bukti transfer</h2>
      <img class="img-fluid img-thumbnail" style="max-width:520px"
        src="<?= $e(base_url('uploads/bayar/' . rawurlencode($namaBukti))) ?>"
        alt="Bukti transfer pembayaran">
    </div>
  <?php else: ?>
    <div class="alert alert-warning">Bukti pembayaran belum diunggah.</div>
  <?php endif; ?>

  <?php if ($bolehUnggah): ?>
    <div class="card mb-4">
      <div class="card-body">
        <h2 class="h5">Unggah atau ganti bukti transfer</h2>
        <p class="text-muted">Format JPEG, PNG, atau WEBP; ukuran maksimal 5 MB.</p>
        <form action="<?= $e(site_url('pembayaran/upload/' . str_replace('/', '~', (string) $pembayaran['kode_bayar']))) ?>"
          method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="ft_byr" class="form-label">Berkas bukti pembayaran</label>
            <input class="form-control" type="file" id="ft_byr" name="ft_byr" accept="image/jpeg,image/png,image/webp" required>
          </div>
          <button type="submit" class="btn btn-success">Unggah bukti pembayaran</button>
        </form>
      </div>
    </div>
  <?php elseif ($status !== 'pending'): ?>
    <div class="alert alert-info">Pembayaran telah diproses dan tidak dapat diubah.</div>
  <?php endif; ?>

  <a class="btn btn-outline-secondary" href="<?= $e(site_url('pembayaran')) ?>">Kembali ke daftar pembayaran</a>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>
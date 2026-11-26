<?php
require APPPATH . 'views/katalog/header.php';

$e = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($nilai): string {
  return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};

require APPPATH . 'views/katalog/navbar.php';

$mobil = is_array($bayar['detail_mobil'] ?? null) ? $bayar['detail_mobil'] : [];
$kodeBayar = (string) ($bayar['kode_bayar'] ?? '');
$stBayar = (string) ($bayar['sttus_byr'] ?? 'pending');
$badgeBayar = $stBayar === 'valid' ? 'bg-success' : ($stBayar === 'invalid' ? 'bg-danger' : 'bg-warning text-dark');
?>

<section class="bg-success py-4">
  <div class="container">
    <h1 class="h2 text-white mt-2">Rincian Pembayaran DP</h1>
    <p class="text-white mb-0">Kode Pembayaran: <strong><?= $e($kodeBayar) ?></strong></p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="mb-3">
      <a href="<?= $e(site_url('pembayaran')) ?>" class="btn btn-outline-secondary btn-sm">
        ← Kembali ke Daftar Pembayaran
      </a>
    </div>

    <?php if (is_array($flash ?? null) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= ($flash['type'] ?? '') === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show">
        <?= $e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h2 class="h5 mb-0">Informasi Transaksi & Tagihan</h2>
          </div>
          <div class="card-body">
            <dl class="row mb-0">
              <dt class="col-sm-5">Kode Pembayaran</dt>
              <dd class="col-sm-7"><strong><?= $e($kodeBayar) ?></strong></dd>

              <dt class="col-sm-5">Kode Transaksi</dt>
              <dd class="col-sm-7"><?= $e($bayar['kode_sewa'] ?? '-') ?></dd>

              <dt class="col-sm-5">Periode Sewa</dt>
              <dd class="col-sm-7"><?= $e($bayar['tgl_mulai'] ?? '-') ?> s/d <?= $e($bayar['tgl_selesai'] ?? '-') ?></dd>

              <dt class="col-sm-5">Metode Pembayaran</dt>
              <dd class="col-sm-7"><?= $e(strtoupper((string) ($bayar['mtode_byr'] ?? 'transfer'))) ?></dd>

              <dt class="col-sm-5">Nominal DP (30%)</dt>
              <dd class="col-sm-7"><strong class="text-success fs-5"><?= $e($formatRupiah($bayar['nominal'] ?? 0)) ?></strong></dd>

              <dt class="col-sm-5">Status Pembayaran</dt>
              <dd class="col-sm-7"><span class="badge <?= $badgeBayar ?>"><?= $e(strtoupper($stBayar)) ?></span></dd>

              <dt class="col-sm-5">Waktu Unggah Terakhir</dt>
              <dd class="col-sm-7"><?= $e($bayar['tgl_wkt_byr'] ?? '-') ?></dd>

              <dt class="col-sm-5">Waktu Verifikasi</dt>
              <dd class="col-sm-7"><?= $e($bayar['tgl_wkt_ver'] ?? '-') ?></dd>
            </dl>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header bg-light">
            <h2 class="h5 mb-0">Rincian Kendaraan Disewa</h2>
          </div>
          <div class="table-responsive">
            <table class="table table-bordered mb-0">
              <thead class="table-light">
                <tr>
                  <th>Kendaraan</th>
                  <th>No. Plat</th>
                  <th class="text-end">Harga/Hari</th>
                  <th class="text-center">Durasi</th>
                  <th class="text-end">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mobil as $m): ?>
                  <tr>
                    <td><?= $e($m['nm_mobil']) ?></td>
                    <td><?= $e($m['no_plat']) ?></td>
                    <td class="text-end"><?= $e($formatRupiah($m['hrg_sewa'])) ?></td>
                    <td class="text-center"><?= $e($m['n_hari']) ?> hari</td>
                    <td class="text-end"><?= $e($formatRupiah((int) $m['hrg_sewa'] * (int) $m['n_hari'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card shadow-sm mb-4">
          <div class="card-header bg-light">
            <h2 class="h5 mb-0">Bukti Transfer</h2>
          </div>
          <div class="card-body">
            <?php if (!empty($bayar['ft_byr'])): ?>
              <p class="small text-muted mb-2">Bukti pembayaran yang tersimpan saat ini:</p>
              <div class="border rounded p-2 text-center bg-light mb-3">
                <img src="<?= $e(site_url('pembayaran/bukti?kode_bayar=' . rawurlencode($kodeBayar))) ?>"
                  alt="Bukti Transfer"
                  class="img-fluid rounded"
                  style="max-height: 280px;">
              </div>
              <a href="<?= $e(site_url('pembayaran/bukti?kode_bayar=' . rawurlencode($kodeBayar))) ?>"
                target="_blank"
                class="btn btn-outline-primary btn-sm w-100 mb-3">
                Buka Ukuran Penuh
              </a>
            <?php else: ?>
              <div class="alert alert-warning mb-3">
                Anda belum mengunggah bukti transfer. Silakan transfer sesuai nominal DP dan unggah buktinya di bawah ini.
              </div>
            <?php endif; ?>

            <?php if ($stBayar === 'pending'): ?>
              <form action="<?= $e(site_url('pembayaran/unggah')) ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                <input type="hidden" name="kode_bayar" value="<?= $e($kodeBayar) ?>">

                <div class="mb-3">
                  <label for="bukti" class="form-label">Pilih Berkas Gambar (JPG, PNG, WEBP, Maks. 2 MB)</label>
                  <input type="file" name="bukti" id="bukti" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                </div>

                <button type="submit" class="btn btn-success w-100">
                  <?= !empty($bayar['ft_byr']) ? 'Ganti Bukti Pembayaran' : 'Unggah Bukti Pembayaran' ?>
                </button>
              </form>
            <?php else: ?>
              <div class="alert alert-info mb-0">
                Pembayaran ini telah diverifikasi admin dengan status <strong><?= $e(strtoupper($stBayar)) ?></strong> dan tidak dapat diubah lagi.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require APPPATH . 'views/katalog/footer.php'; ?>
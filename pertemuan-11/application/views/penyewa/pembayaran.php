<?php
require APPPATH . 'views/katalog/header.php';
require APPPATH . 'views/katalog/navbar.php';

$daftarPembayaran = $daftarPembayaran ?? [];
$flash = $flash ?? null;
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<section class="container py-5">
  <div class="row mb-3">
    <div class="col">
      <h1 class="h2">Pembayaran Saya</h1>
      <p class="text-muted">Daftar pembayaran uang muka (DP) transaksi sewa Anda.</p>
    </div>
  </div>

  <?php if (is_array($flash) && !empty($flash['message'])): ?>
    <div class="alert alert-<?= $e(($flash['type'] ?? '') === 'success' ? 'success' : 'danger') ?>">
      <?= $e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>No.</th>
          <th>Kode Pembayaran</th>
          <th>Kode Sewa</th>
          <th>Periode Sewa</th>
          <th>Nominal DP</th>
          <th>Status Pembayaran</th>
          <th>Bukti</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$daftarPembayaran): ?>
          <tr>
            <td colspan="8" class="text-center">Belum ada data pembayaran DP.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($daftarPembayaran as $i => $item): ?>
            <?php
            $status = (string) ($item['sttus_byr'] ?? '');
            $label = ['pending' => 'Menunggu verifikasi', 'valid' => 'Valid', 'invalid' => 'Ditolak'][$status] ?? $status;
            ?>
            <tr>
              <td><?= (int) $i + 1 ?></td>
              <td><?= $e($item['kode_bayar'] ?? '') ?></td>
              <td><?= $e($item['kode_sewa'] ?? '') ?></td>
              <td><?= $e($item['tgl_mulai'] ?? '') ?> s.d. <?= $e($item['tgl_selesai'] ?? '') ?></td>
              <td>Rp <?= number_format((int) ($item['nominal'] ?? 0), 0, ',', '.') ?></td>
              <td><?= $e($label) ?></td>
              <td><?= !empty($item['ft_byr']) ? 'Sudah diunggah' : 'Belum diunggah' ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary"
                  href="<?= $e(site_url('pembayaran/detail/' . str_replace('/', '~', (string) $item['kode_bayar']))) ?>">Detail</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <a class="btn btn-success" href="<?= $e(site_url('katalog')) ?>">Kembali ke katalog</a>
</section>
<?php require APPPATH . 'views/katalog/footer.php'; ?>
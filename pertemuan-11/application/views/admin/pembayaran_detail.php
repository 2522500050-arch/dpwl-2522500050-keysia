<?php
require APPPATH . 'views/templates/header.php';
require APPPATH . 'views/templates/sidebar.php';

$e = static function ($value): string {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
$formatRupiah = static function ($nilai): string {
  return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
};

$kodeBayar = (string) ($bayar['kode_bayar'] ?? '');
$st = (string) ($bayar['sttus_byr'] ?? 'pending');
$mobil = is_array($bayar['detail_mobil'] ?? null) ? $bayar['detail_mobil'] : [];
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Detail Verifikasi Pembayaran <small>Pemeriksaan bukti pembayaran penyewa</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= $e(site_url('admin')) ?>"><i class="fa fa-dashboard"></i> Admin</a></li>
      <li><a href="<?= $e(site_url('admin/pembayaran')) ?>">Verifikasi Pembayaran</a></li>
      <li class="active">Detail</li>
    </ol>
  </section>

  <section class="content">
    <p>
      <a href="<?= $e(site_url('admin/pembayaran')) ?>" class="btn btn-default btn-sm">← Kembali ke Daftar</a>
    </p>

    <?php if (is_array($flash ?? null) && !empty($flash['message'])): ?>
      <div class="alert alert-<?= $e($flash['type'] ?? 'info') ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?= $e($flash['message']) ?>
      </div>
    <?php endif; ?>

    <div class="row">
      <!-- Kolom Kiri: Informasi Pembayaran & Penyewa -->
      <div class="col-md-7">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Informasi Pembayaran</h3>
          </div>
          <div class="box-body">
            <table class="table table-bordered">
              <tr>
                <th style="width: 35%">Kode Pembayaran</th>
                <td><strong><?= $e($kodeBayar) ?></strong></td>
              </tr>
              <tr>
                <th>Kode Sewa</th>
                <td><?= $e($bayar['kode_sewa'] ?? '-') ?></td>
              </tr>
              <tr>
                <th>Jenis Pembayaran</th>
                <td><?= $e(strtoupper((string) ($bayar['jns_byr'] ?? 'dp'))) ?></td>
              </tr>
              <tr>
                <th>Nominal</th>
                <td><strong class="text-success"><?= $e($formatRupiah($bayar['nominal'] ?? 0)) ?></strong></td>
              </tr>
              <tr>
                <th>Metode Pembayaran</th>
                <td><?= $e(strtoupper((string) ($bayar['mtode_byr'] ?? 'transfer'))) ?></td>
              </tr>
              <tr>
                <th>Tanggal Pembayaran</th>
                <td><?= $e($bayar['tgl_wkt_byr'] ?? '-') ?></td>
              </tr>
              <tr>
                <th>Status Pembayaran</th>
                <td>
                  <?php
                  $labelClass = $st === 'valid' ? 'label-success' : ($st === 'invalid' ? 'label-danger' : 'label-warning');
                  ?>
                  <span class="label <?= $labelClass ?>"><?= $e(strtoupper($st)) ?></span>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Informasi Penyewa</h3>
          </div>
          <div class="box-body">
            <table class="table table-bordered">
              <tr>
                <th style="width: 35%">Username</th>
                <td><?= $e($bayar['user_plg'] ?? '-') ?></td>
              </tr>
              <tr>
                <th>Nama Lengkap</th>
                <td><?= $e($bayar['nm_lngkp'] ?? '-') ?></td>
              </tr>
              <tr>
                <th>NIK</th>
                <td><?= $e($bayar['nik'] ?? '-') ?></td>
              </tr>
              <tr>
                <th>No. HP</th>
                <td><?= $e($bayar['no_hp'] ?? '-') ?></td>
              </tr>
              <tr>
                <th>Alamat</th>
                <td><?= $e($bayar['almt_lngkp'] ?? '-') ?></td>
              </tr>
            </table>

            <h4 style="margin-top: 20px;">Rincian Kendaraan Disewa</h4>
            <table class="table table-condensed table-bordered">
              <thead>
                <tr>
                  <th>Kendaraan</th>
                  <th>No. Plat</th>
                  <th>Harga/Hari</th>
                  <th>Durasi</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mobil as $m): ?>
                  <tr>
                    <td><?= $e($m['nm_mobil']) ?></td>
                    <td><?= $e($m['no_plat']) ?></td>
                    <td><?= $e($formatRupiah($m['hrg_sewa'])) ?></td>
                    <td><?= $e($m['n_hari']) ?> hari</td>
                    <td><?= $e($formatRupiah((int) $m['hrg_sewa'] * (int) $m['n_hari'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Bukti Transfer & Tombol Verifikasi -->
      <div class="col-md-5">
        <div class="box box-warning">
          <div class="box-header with-border">
            <h3 class="box-title">Bukti Transfer & Aksi Verifikasi</h3>
          </div>
          <div class="box-body text-center">
            <?php if (!empty($bayar['ft_byr'])): ?>
              <div style="margin-bottom: 15px;">
                <img src="<?= $e(site_url('admin/pembayaran/bukti?kode_bayar=' . rawurlencode($kodeBayar))) ?>"
                  alt="Bukti Transfer"
                  class="img-responsive img-thumbnail"
                  style="max-height: 350px; margin: 0 auto;">
              </div>
              <p>
                <a href="<?= $e(site_url('admin/pembayaran/bukti?kode_bayar=' . rawurlencode($kodeBayar))) ?>"
                  target="_blank"
                  class="btn btn-default btn-block">
                  <i class="fa fa-search-plus"></i> Buka Ukuran Penuh
                </a>
              </p>
            <?php else: ?>
              <div class="alert alert-warning">
                Penyewa belum mengunggah bukti transfer.
              </div>
            <?php endif; ?>

            <?php if ($st === 'pending' && !empty($bayar['ft_byr'])): ?>
              <hr>
              <div class="row">
                <div class="col-xs-6">
                  <form action="<?= $e(site_url('admin/pembayaran/proses')) ?>" method="post" onsubmit="return confirm('Terima pembayaran ini? Status transaksi akan disetujui.');">
                    <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                    <input type="hidden" name="kode_bayar" value="<?= $e($kodeBayar) ?>">
                    <input type="hidden" name="keputusan" value="terima">
                    <button type="submit" class="btn btn-success btn-block btn-flat">
                      <i class="fa fa-check"></i> Terima
                    </button>
                  </form>
                </div>
                <div class="col-xs-6">
                  <form action="<?= $e(site_url('admin/pembayaran/proses')) ?>" method="post" onsubmit="return confirm('Tolak pembayaran ini? Status transaksi akan ditolak.');">
                    <input type="hidden" name="csrf_token" value="<?= $e(csrf_token()) ?>">
                    <input type="hidden" name="kode_bayar" value="<?= $e($kodeBayar) ?>">
                    <input type="hidden" name="keputusan" value="tolak">
                    <button type="submit" class="btn btn-danger btn-block btn-flat">
                      <i class="fa fa-times"></i> Tolak
                    </button>
                  </form>
                </div>
              </div>
            <?php elseif ($st !== 'pending'): ?>
              <div class="alert alert-info" style="margin-top: 15px;">
                Pembayaran ini sudah diverifikasi dengan status <strong><?= $e(strtoupper($st)) ?></strong>.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php require APPPATH . 'views/templates/footer.php'; ?>
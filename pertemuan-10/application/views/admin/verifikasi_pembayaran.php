<?php
$pembayaran = $pembayaran ?? [];
$flash = $flash ?? null;
?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>
      Verifikasi Pembayaran
      <small>Daftar pembayaran penyewa</small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?= site_url('admin') ?>">
          <i class="fa fa-dashboard"></i> Admin
        </a></li>
      <li class="active">Verifikasi Pembayaran</li>
    </ol>
  </section>

  <section class="content">
    <?php if ($flash): ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8') ?>">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Daftar Pembayaran DP</h3>
      </div>

      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>No.</th>
              <th>Kode Pembayaran</th>
              <th>Nama Penyewa</th>
              <th>Tanggal Pembayaran</th>
              <th>Nominal</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($pembayaran)): ?>
              <tr>
                <td colspan="7" class="text-center">
                  Belum ada pembayaran yang perlu diverifikasi.
                </td>
              </tr>
            <?php else: ?>
              <?php $no = 1; ?>
              <?php foreach ($pembayaran as $item): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($item['kode_bayar'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($item['nm_lngkp'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($item['tgl_wkt_byr'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    Rp <?= number_format((float) $item['nominal'], 0, ',', '.') ?>
                  </td>
                  <td>
                    <span class="label label-warning">
                      <?= htmlspecialchars($item['sttus_byr'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </td>
                  <td>
                    <a
                      href="<?= htmlspecialchars(
                              site_url(
                                'admin/pembayaran/detail/' .
                                  str_replace('/', '-', $item['kode_bayar'])
                              ),
                              ENT_QUOTES,
                              'UTF-8'
                            ) ?>"
                      class="btn btn-sm btn-success">
                      Detail
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
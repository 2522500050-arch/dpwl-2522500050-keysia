<?php
require APPPATH . '/views/templates/header.php';
require APPPATH . '/views/templates/sidebar.php';

$penyewa = $penyewa ?? [];
$judul = $judul ?? 'Manajemen Data Penyewa';

$aman = static function ($nilai): string {
  return htmlspecialchars(
    (string) ($nilai ?? ''),
    ENT_QUOTES,
    'UTF-8'
  );
};

$urlFoto = static function ($userPlg, $jenis): string {
  return site_url(
    'admin/penyewa/foto/' .
      rawurlencode($userPlg) . '/' .
      rawurlencode($jenis)
  );
};
?>

<div class="content-wrapper">

  <section class="content-header">
    <h1>
      <?= $aman($judul) ?>
      <small>Kelola data penyewa</small>
    </h1>

    <ol class="breadcrumb">
      <li>
        <a href="<?= $aman(site_url('admin')) ?>">
          <i class="fa fa-dashboard"></i> Dashboard
        </a>
      </li>
      <li class="active">Manajemen Penyewa</li>
    </ol>
  </section>

  <section class="content">

    <?php if (!empty($pesan)): ?>
      <div class="alert alert-<?= $aman($jenis ?? 'info') ?> alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">
          &times;
        </button>
        <?= $aman($pesan) ?>
      </div>
    <?php endif; ?>

    <!-- Form Tambah -->
    <div class="box box-primary">

      <div class="box-header with-border">
        <h3 class="box-title">Form Tambah Data Penyewa</h3>
      </div>

      <form method="POST"
        action="<?= $aman(site_url('admin/penyewa/tambah')) ?>"
        enctype="multipart/form-data">

        <div class="box-body">

          <div class="form-group">
            <label>Username Penyewa</label>
            <input type="text"
              name="user_plg"
              class="form-control"
              maxlength="15"
              pattern="[A-Za-z0-9_.-]{3,15}"
              placeholder="Masukkan username"
              required>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password"
              name="sandi"
              class="form-control"
              minlength="8"
              autocomplete="new-password"
              placeholder="Minimal 8 karakter"
              required>
          </div>

          <div class="form-group">
            <label>NIK</label>
            <input type="text"
              name="nik"
              class="form-control"
              maxlength="16"
              pattern="[0-9]{16}"
              placeholder="Masukkan 16 digit NIK"
              required>
          </div>

          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text"
              name="nm_lngkp"
              class="form-control"
              maxlength="100"
              required>
          </div>

          <div class="form-group">
            <label>Nomor HP</label>
            <input type="tel"
              name="no_hp"
              class="form-control"
              maxlength="15"
              required>
          </div>

          <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea name="almt_lngkp"
              class="form-control"
              rows="3"
              maxlength="300"
              required></textarea>
          </div>

          <div class="form-group">
            <label>Foto KTP</label>
            <input type="file"
              name="ft_ktp"
              class="form-control"
              accept=".jpg,.jpeg,.png,.webp">
            <small class="text-muted">
              JPG, PNG, atau WEBP. Maksimal 2 MB.
            </small>
          </div>

          <div class="form-group">
            <label>Foto SIM A</label>
            <input type="file"
              name="ft_sim_a"
              class="form-control"
              accept=".jpg,.jpeg,.png,.webp">
            <small class="text-muted">
              JPG, PNG, atau WEBP. Maksimal 2 MB.
            </small>
          </div>

        </div>

        <div class="box-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> Simpan Data
          </button>
        </div>

      </form>
    </div>

    <!-- Tabel Data Penyewa -->
    <div class="box">

      <div class="box-header">
        <h3 class="box-title">Daftar Data Penyewa</h3>
      </div>

      <div class="box-body table-responsive">

        <table class="table table-bordered table-striped js-data-table">

          <thead>
            <tr>
              <th>No</th>
              <th>Username</th>
              <th>NIK</th>
              <th>Nama Lengkap</th>
              <th>Nomor HP</th>
              <th>Alamat</th>
              <th>Foto KTP</th>
              <th>Foto SIM A</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>

            <?php if (!empty($penyewa)): ?>

              <?php $no = 1; ?>

              <?php foreach ($penyewa as $p): ?>

                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= $aman($p['user_plg']) ?></td>
                  <td><?= $aman($p['nik']) ?></td>
                  <td><?= $aman($p['nm_lngkp']) ?></td>
                  <td><?= $aman($p['no_hp']) ?></td>
                  <td><?= $aman($p['almt_lngkp']) ?></td>

                  <!-- Foto KTP -->
                  <td>
                    <?php if (!empty($p['ft_ktp'])): ?>
                      <a href="<?= $aman($urlFoto($p['user_plg'], 'ktp')) ?>"
                        target="_blank"
                        rel="noopener">
                        <img src="<?= $aman($urlFoto($p['user_plg'], 'ktp')) ?>"
                          alt="Foto KTP"
                          style="width:80px;height:60px;object-fit:cover;">
                      </a>
                    <?php else: ?>
                      <span class="text-muted">Belum ada foto</span>
                    <?php endif; ?>
                  </td>

                  <!-- Foto SIM A -->
                  <td>
                    <?php if (!empty($p['ft_sim_a'])): ?>
                      <a href="<?= $aman($urlFoto($p['user_plg'], 'sim')) ?>"
                        target="_blank"
                        rel="noopener">
                        <img src="<?= $aman($urlFoto($p['user_plg'], 'sim')) ?>"
                          alt="Foto SIM A"
                          style="width:80px;height:60px;object-fit:cover;">
                      </a>
                    <?php else: ?>
                      <span class="text-muted">Belum ada foto</span>
                    <?php endif; ?>
                  </td>

                  <td style="min-width:180px;">

                    <!-- Edit -->
                    <details>
                      <summary class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit
                      </summary>

                      <div style="min-width:250px;padding:10px;background:#f4f4f4;margin-top:10px;">

                        <form method="POST"
                          enctype="multipart/form-data"
                          action="<?= $aman(site_url(
                                    'admin/penyewa/ubah/' .
                                      rawurlencode($p['user_plg'])
                                  )) ?>">

                          <div class="form-group">
                            <label>NIK</label>
                            <input type="text"
                              name="nik"
                              class="form-control"
                              maxlength="16"
                              pattern="[0-9]{16}"
                              value="<?= $aman($p['nik']) ?>"
                              required>
                          </div>

                          <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text"
                              name="nm_lngkp"
                              class="form-control"
                              maxlength="100"
                              value="<?= $aman($p['nm_lngkp']) ?>"
                              required>
                          </div>

                          <div class="form-group">
                            <label>Nomor HP</label>
                            <input type="tel"
                              name="no_hp"
                              class="form-control"
                              maxlength="15"
                              value="<?= $aman($p['no_hp']) ?>"
                              required>
                          </div>

                          <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="almt_lngkp"
                              class="form-control"
                              maxlength="300"
                              required><?= $aman($p['almt_lngkp']) ?></textarea>
                          </div>

                          <div class="form-group">
                            <label>Password Baru (Opsional)</label>
                            <input type="password"
                              name="sandi_baru"
                              class="form-control"
                              minlength="8"
                              autocomplete="new-password"
                              placeholder="Kosongkan jika tidak diubah">
                          </div>

                          <div class="form-group">
                            <label>Ganti Foto KTP</label>
                            <input type="file"
                              name="ft_ktp"
                              class="form-control"
                              accept=".jpg,.jpeg,.png,.webp">
                          </div>

                          <div class="form-group">
                            <label>Ganti Foto SIM A</label>
                            <input type="file"
                              name="ft_sim_a"
                              class="form-control"
                              accept=".jpg,.jpeg,.png,.webp">
                          </div>

                          <button type="submit" class="btn btn-success btn-sm">
                            <i class="fa fa-save"></i> Simpan Perubahan
                          </button>

                        </form>

                      </div>
                    </details>

                    <br><br>

                    <!-- Hapus -->
                    <form method="POST"
                      action="<?= $aman(site_url(
                                'admin/penyewa/hapus/' .
                                  rawurlencode($p['user_plg'])
                              )) ?>"
                      onsubmit="return confirm('Yakin ingin menghapus data penyewa ini?')">

                      <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fa fa-trash"></i> Hapus
                      </button>

                    </form>

                  </td>
                </tr>

              <?php endforeach; ?>

            <?php else: ?>

              <tr>
                <td colspan="9" class="text-center">
                  Data penyewa belum tersedia.
                </td>
              </tr>

            <?php endif; ?>

          </tbody>

        </table>

      </div>
    </div>

  </section>
</div>

<?php
require APPPATH . '/views/templates/footer.php';
?>
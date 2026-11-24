<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <title><?php echo htmlspecialchars($judul ?? '', ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="<?php echo function_exists('base_url') ? htmlspecialchars(base_url('assets/css/app.css'), ENT_QUOTES, 'UTF-8') : 'assets/css/app.css'; ?>">
</head>

<body>
  <h1><?php echo htmlspecialchars($judul ?? 'Judul Default', ENT_QUOTES, 'UTF-8'); ?></h1>
  <p><?php echo htmlspecialchars($pesan ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
  <p>
    <a href="<?php echo function_exists('site_url') ? htmlspecialchars(site_url('info/routing'), ENT_QUOTES, 'UTF-8') : '#'; ?>">
      Uji custom route dan parameter
    </a>
  </p>
</body>

</html>
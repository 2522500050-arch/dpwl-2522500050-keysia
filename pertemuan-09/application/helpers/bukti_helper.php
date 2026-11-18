<?php
if (!function_exists('bukti_bayar_dir')) {
  function bukti_bayar_dir(): string
  {
    return rtrim(dirname(APPPATH), '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'bayar' . DIRECTORY_SEPARATOR;
  }
}

if (!function_exists('bukti_bayar_nama_aman')) {
  function bukti_bayar_nama_aman(?string $filename): ?string
  {
    if ($filename === null || $filename === '') {
      return null;
    }
    $base = basename($filename);
    if (preg_match('/\Abayar_\d{14}_[a-f0-9]{16}\.(jpg|png|webp)\z/', $base) !== 1) {
      return null;
    }
    return $base;
  }
}

if (!function_exists('bukti_bayar_hapus')) {
  function bukti_bayar_hapus(?string $filename): void
  {
    $aman = bukti_bayar_nama_aman($filename);
    if ($aman === null) {
      return;
    }
    $path = bukti_bayar_dir() . $aman;
    if (is_file($path)) {
      @unlink($path);
    }
  }
}

if (!function_exists('bukti_bayar_kirim')) {
  function bukti_bayar_kirim(?string $filename): never
  {
    $aman = bukti_bayar_nama_aman($filename);
    if ($aman === null) {
      http_response_code(404);
      exit('Bukti pembayaran tidak ditemukan.');
    }

    $path = bukti_bayar_dir() . $aman;
    if (!is_file($path)) {
      http_response_code(404);
      exit('Berkas bukti pembayaran tidak ditemukan.');
    }

    $ext = strtolower(pathinfo($aman, PATHINFO_EXTENSION));
    $mimeMap = [
      'jpg'  => 'image/jpeg',
      'png'  => 'image/png',
      'webp' => 'image/webp',
    ];
    $mime = $mimeMap[$ext] ?? 'application/octet-stream';

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . (string) filesize($path));
    header('Content-Disposition: inline; filename="' . $aman . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
  }
}

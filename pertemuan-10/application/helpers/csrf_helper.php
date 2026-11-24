<?php
if (!function_exists('csrf_token')) {
  function csrf_token(): string
  {
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
      $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
  }
}

if (!function_exists('csrf_valid')) {
  function csrf_valid(?string $token): bool
  {
    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    if (!is_string($token) || $token === '' || !is_string($sessionToken) || $sessionToken === '') {
      return false;
    }
    return hash_equals($sessionToken, $token);
  }
}

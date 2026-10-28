<?php
session_start();

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('APPPATH', FCPATH . 'application' . DIRECTORY_SEPARATOR);
define('SYSPATH', FCPATH . 'system' . DIRECTORY_SEPARATOR);
require_once APPPATH . 'config/config.php';
require_once APPPATH . 'config/database.php';
require_once APPPATH . 'helpers/url_helper.php';
require_once SYSPATH . 'core/Controller.php';
require_once SYSPATH . 'core/Router.php';

// if ($db->query('SELECT 1')) { 
// echo 'Koneksi basis data berhasil.'; 
// } else { 
// echo 'Koneksi basis data gagal.'; 
// }

// require_once APPPATH . 'models/Admin_model.php';
// $model = new Admin_model();
// echo '<pre>';
// // Uji 1: username yang ada 
// echo "UJI 1 - Username admin\n";
// $dataAdmin = $model->findByUsername('admin');
// var_dump($dataAdmin);
// // Uji 2: username yang tidak ada 
// echo "\nUJI 2 - Username tidak ada\n";
// $dataTidakAda = $model->findByUsername('admin_tidak_ada');
// var_dump($dataTidakAda);
// echo '</pre>';
// exit;

$route = [];
require APPPATH . 'config/routes.php';
$uri = $_SERVER['PATH_INFO'] ?? '';
if ($uri === '') {
  $requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
  ) ?? '/';
  $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
  if (str_starts_with($requestPath, $scriptName)) {
    $uri = substr($requestPath, strlen($scriptName));
  }
}
$router = new Router($route);
$router->dispatch($uri);

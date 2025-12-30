<?php
// track.php — versión básica y robusta

date_default_timezone_set('UTC');

$file = __DIR__ . '/qr-tracking.json';

// Obtener datos simples
$entry = [
  "url"       => $_SERVER['REQUEST_URI'],
  "full_url"  => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
  "ip"        => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
  "userAgent" => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
  "time"      => date('c')
];

// Leer JSON actual
$data = [];
if (file_exists($file)) {
  $content = file_get_contents($file);
  $decoded = json_decode($content, true);
  if (is_array($decoded)) {
    $data = $decoded;
  }
}

// Agregar entrada
$data[] = $entry;

// Guardar
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "TRACK OK";
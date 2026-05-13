<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../includes/permissoes.php';

if (!podeFazer('editar')) {
  http_response_code(403);
  exit('Sem permissão.');
}

$f = $_GET['f'] ?? '';
$f = basename($f); // evita path traversal

$path = __DIR__ . '/../../backups/' . $f;

if ($f === '' || !is_file($path)) {
  http_response_code(404);
  exit('Arquivo não encontrado.');
}

header('Content-Type: application/sql; charset=utf-8');
header('Content-Disposition: attachment; filename="'.$f.'"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
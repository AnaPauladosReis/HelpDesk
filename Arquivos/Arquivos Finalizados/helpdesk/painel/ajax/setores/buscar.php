<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'setores';

function jexit($ok, $msg = '', $data = null) {
  echo json_encode([
    'ok' => (bool)$ok,
    'msg' => (string)$msg,
    'data' => $data
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) jexit(false, 'ID inválido.');

  // Não SaaS: sem filtro por empresa
  $stmt = $pdo->prepare("SELECT id, nome FROM {$tabela} WHERE id = :id LIMIT 1");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $s = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$s) jexit(false, 'Setor não encontrado.');

  // defaults (mantém padrão do front e evita undefined)
  $defaults = [
    'id'   => 0,
    'nome' => ''
  ];

  $data = array_merge($defaults, $s);

  jexit(true, 'OK', $data);

} catch (Throwable $e) {
  jexit(false, 'Erro ao buscar: ' . $e->getMessage());
}
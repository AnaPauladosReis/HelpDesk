<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados_status'; // ✅ status de abertura

function jexit($ok, $msg = '', $data = null) {
  echo json_encode([
    'ok'   => (bool)$ok,
    'msg'  => (string)$msg,
    'data' => $data
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) jexit(false, 'ID inválido.');

  // Não SaaS: sem filtro por empresa
  $stmt = $pdo->prepare("
    SELECT
      id,
      nome,
      cor,
      ordem,
      ativo,
      padrao,
      fechado
    FROM {$tabela}
    WHERE id = :id
    LIMIT 1
  ");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $s = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$s) jexit(false, 'Status não encontrado.');

  // defaults (mantém padrão do front e evita undefined)
  $defaults = [
    'id'      => 0,
    'nome'    => '',
    'cor'     => '#6c757d',
    'ordem'   => 0,
    'ativo'   => 'Sim',
    'padrao'  => 'Não',
    'fechado' => 'Não',
  ];

  $data = array_merge($defaults, $s);

  // normaliza cor (garante #)
  $cor = trim((string)($data['cor'] ?? ''));
  if ($cor !== '' && $cor[0] !== '#') $cor = '#'.$cor;
  if ($cor === '' || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $cor)) {
    $cor = '#6c757d';
  }
  $data['cor'] = $cor;

  // normaliza enums
  foreach (['ativo','padrao','fechado'] as $k) {
    $v = (string)($data[$k] ?? 'Não');
    if ($v === '1') $v = 'Sim';
    if ($v === '0') $v = 'Não';
    if (!in_array($v, ['Sim','Não'], true)) $v = ($k === 'ativo' ? 'Sim' : 'Não');
    $data[$k] = $v;
  }

  $data['ordem'] = (int)($data['ordem'] ?? 0);

  jexit(true, 'OK', $data);

} catch (Throwable $e) {
  jexit(false, 'Erro ao buscar: ' . $e->getMessage());
}

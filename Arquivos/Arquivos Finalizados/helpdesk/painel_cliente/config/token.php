<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';


function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // aceita POST normal e JSON
  $data = $_POST;
  if (!$data) {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $j = json_decode($raw, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($j)) $data = $j;
    }
  }

  $id = isset($data['id']) ? (int)$data['id'] : 0;
  if ($id <= 0) out(false, 'ID inválido.');

  // "escopo" opcional (ex: "chamados", "clientes", "vendas"...)
  // serve pra você impedir que um token de "clientes" seja usado em "chamados"
  $scope = trim((string)($data['scope'] ?? ''));
  if ($scope === '') $scope = 'default';

  // timestamp opcional (pra expiração futura)
  $ts = time();

  // CHAVE SECRETA (ideal: mover pro config.php do sistema)
  $secret = $GLOBALS['token_secret'] ?? 'hugocursos_token_2026';

  // payload: id|scope|ts
  $payload = $id . '|' . $scope . '|' . $ts;

  // assinatura HMAC
  $sig = hash_hmac('sha256', $payload, $secret);

  // token final
  $token = base64_encode($payload . '|' . $sig);

  out(true, 'OK', ['token' => $token]);

} catch (Throwable $e) {
  out(false, 'Erro: ' . $e->getMessage());
}

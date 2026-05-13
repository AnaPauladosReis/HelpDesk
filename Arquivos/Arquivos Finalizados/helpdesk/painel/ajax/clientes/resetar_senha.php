<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';

function out($ok, $msg) {
  echo json_encode(['ok'=>(bool)$ok,'msg'=>(string)$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

// Permissão
if (!podeFazer('editar')) {
  out(false, 'Sem permissão para resetar senha.');
}

// ID
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) out(false, 'ID inválido.');

try {

  // Verifica se cliente existe
  $st = $pdo->prepare("SELECT id, nome FROM clientes WHERE id = :id LIMIT 1");
  $st->execute([':id' => $id]);
  $cliente = $st->fetch(PDO::FETCH_ASSOC);

  if (!$cliente) {
    out(false, 'Cliente não encontrado.');
  }

  // 🔐 senha padrão do sistema (definida no conexao.php)
  $novaSenhaHash = password_hash($senha_padrao, PASSWORD_DEFAULT);

  $up = $pdo->prepare("UPDATE clientes SET senha = :senha WHERE id = :id LIMIT 1");
  $up->execute([
    ':senha' => $novaSenhaHash,
    ':id'    => $id
  ]);

  registrarLog(
    $pdo,
    'editar',
    'clientes',
    $id,
    "Senha do cliente '{$cliente['nome']}' resetada para padrão"
  );

  out(true, 'Senha do cliente resetada com sucesso!');

} catch (Throwable $e) {
  out(false, 'Erro ao resetar senha: ' . $e->getMessage());
}
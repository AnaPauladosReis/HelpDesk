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

if (!podeFazer('editar')) {
  out(false, 'Sem permissão para resetar senha.');
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) out(false, 'ID inválido.');

try {

  // impede resetar a si mesmo (opcional)
  $id_logado = (int)($_SESSION['id'] ?? 0);
  if ($id === $id_logado) {
    out(false, 'Você não pode resetar sua própria senha.');
  }

  // senha padrão do sistema
  require_once __DIR__ . '/../../../conexao.php';
  $novaSenha = password_hash($senha_padrao, PASSWORD_DEFAULT);

  $st = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id LIMIT 1");
  $st->execute([
    ':senha' => $novaSenha,
    ':id' => $id
  ]);

  if ($st->rowCount() <= 0) {
    out(false, 'Usuário não encontrado.');
  }

  registrarLog(
    $pdo,
    'editar',
    'usuarios',
    $id,
    'Senha do usuário resetada para padrão'
  );

  out(true, 'Senha resetada com sucesso!');

} catch (Throwable $e) {
  out(false, 'Erro ao resetar senha: ' . $e->getMessage());
}
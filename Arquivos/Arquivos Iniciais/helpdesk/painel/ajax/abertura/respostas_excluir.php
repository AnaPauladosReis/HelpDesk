<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../includes/permissoes.php';

header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

if (!podeFazer('excluir')) {
  out(false, 'Sem permissão para excluir.');
}

try {
  $id = 0;

  if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
  } else {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $json = json_decode($raw, true);
      if (json_last_error() === JSON_ERROR_NONE && isset($json['id'])) {
        $id = (int)$json['id'];
      }
    }
  }

  if ($id <= 0) out(false, 'ID inválido.');

  $uidSess   = (int)($_SESSION['id'] ?? 0);
  $nivelSess = (string)($_SESSION['nivel'] ?? '');
  $isAdmin   = (trim($nivelSess) === 'Administrador');

  if ($uidSess <= 0) out(false, 'Sessão inválida. Faça login novamente.');

  // Busca resposta e valida regra
  $st = $pdo->prepare("
    SELECT id, chamado_id, usuario_id, tipo_autor
    FROM chamados_respostas
    WHERE id = :id
    LIMIT 1
  ");
  $st->execute([':id' => $id]);
  $r = $st->fetch(PDO::FETCH_ASSOC);

  if (!$r) out(false, 'Mensagem não encontrada.');

  $tipo = (string)($r['tipo_autor'] ?? 'usuario');
  $autorUsuarioId = (int)($r['usuario_id'] ?? 0);

  $podeExcluir = false;
  if ($isAdmin) {
    $podeExcluir = true;
  } else {
    // usuário comum: só remove se a msg for do tipo usuario e for dele
    $podeExcluir = ($tipo === 'usuario' && $autorUsuarioId === $uidSess);
  }

  if (!$podeExcluir) out(false, 'Você não pode excluir esta mensagem.');

  // Exclui (hard delete)
  $del = $pdo->prepare("DELETE FROM chamados_respostas WHERE id = :id LIMIT 1");
  $del->execute([':id' => $id]);

  registrarLog(
    $pdo,
    'excluir',
    'chamados_respostas',
    $id,
    'Resposta do chamado excluída'
  );

  out(true, 'Mensagem excluída com sucesso!', ['id' => $id]);

} catch (Throwable $e) {
  out(false, 'Erro ao excluir: ' . $e->getMessage());
}
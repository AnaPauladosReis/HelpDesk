<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados';

require_once __DIR__ . '/../../includes/permissoes.php';
if (!podeFazer('excluir')) {
  echo json_encode(['ok'=>false,'msg'=>'Sem permissão para excluir.'], JSON_UNESCAPED_UNICODE);
  exit;
}

function jexit($ok, $msg = '') {
  echo json_encode(['ok' => (bool)$ok, 'msg' => (string)$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // aceita POST padrão e também JSON
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

  if ($id <= 0) jexit(false, 'ID inválido.');

  // Busca chamado (pra log e validação)
  $st = $pdo->prepare("
    SELECT id, protocolo, assunto, status_id
    FROM chamados
    WHERE id = :id
    LIMIT 1
  ");
  $st->execute([':id' => $id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);

  if (!$row) jexit(false, 'Chamado não encontrado.');

  $protocolo = (string)($row['protocolo'] ?? '');
  $assunto   = (string)($row['assunto'] ?? '');

  // (Opcional) Se quiser impedir excluir chamado encerrado:
  // $status_id = (int)($row['status_id'] ?? 0);
  // ... aqui você poderia checar em chamados_status se fechado='Sim' e bloquear.

  $pdo->beginTransaction();

  // Movimentos serão deletados automaticamente (FK ON DELETE CASCADE)
  $del = $pdo->prepare("DELETE FROM chamados WHERE id = :id LIMIT 1");
  $del->execute([':id' => $id]);

  $pdo->commit();

  // Log do sistema
  registrarLog(
    $pdo,
    'excluir',
    $tabela,
    $id,
    "Chamado excluído: {$protocolo} - {$assunto}"
  );

  jexit(true, 'Chamado excluído com sucesso!');

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  jexit(false, 'Erro ao excluir: ' . $e->getMessage());
}

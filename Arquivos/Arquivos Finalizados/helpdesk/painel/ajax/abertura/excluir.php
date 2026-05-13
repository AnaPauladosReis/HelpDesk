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

  // =========================
  // Busca anexos (para apagar do disco depois)
  // =========================
  $anexos = [];
  try {
    $stAx = $pdo->prepare("SELECT arquivo FROM chamados_anexos WHERE chamado_id = :id");
    $stAx->execute([':id' => $id]);
    $anexos = $stAx->fetchAll(PDO::FETCH_COLUMN) ?: [];
  } catch (Throwable $ignore) {
    $anexos = [];
  }

  // =========================
  // TRANSAÇÃO: remove tudo relacionado
  // =========================
  $pdo->beginTransaction();

  // 1) anexos (registros)
  $pdo->prepare("DELETE FROM chamados_anexos WHERE chamado_id = :id")->execute([':id' => $id]);

  // 2) respostas
  $pdo->prepare("DELETE FROM chamados_respostas WHERE chamado_id = :id")->execute([':id' => $id]);

  // 3) movimentos
  $pdo->prepare("DELETE FROM chamados_movimentos WHERE chamado_id = :id")->execute([':id' => $id]);

  // 4) chamado
  $del = $pdo->prepare("DELETE FROM chamados WHERE id = :id LIMIT 1");
  $del->execute([':id' => $id]);

  if ($del->rowCount() <= 0) {
    // se por algum motivo não deletou
    $pdo->rollBack();
    jexit(false, 'Não foi possível excluir o chamado.');
  }

  $pdo->commit();

  // =========================
  // LOG
  // =========================
  registrarLog(
    $pdo,
    'excluir',
    $tabela,
    $id,
    "Chamado excluído: {$protocolo} - {$assunto} (com dados relacionados)"
  );

  // =========================
  // Remove anexos do disco (depois do commit)
  // =========================
  // ⚠️ Ajuste aqui se sua pasta for outra
  $dirAnexos = __DIR__ . '/../../../uploads/chamados/';

  foreach ($anexos as $arq) {
    $arq = trim((string)$arq);
    if ($arq === '') continue;

    // proteção contra path traversal
    $safe = str_replace(['..', '/', '\\'], '', $arq);

    $path = $dirAnexos . $safe;
    if (is_file($path)) {
      @unlink($path);
    }
  }

  jexit(true, 'Chamado excluído com sucesso!');

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  jexit(false, 'Erro ao excluir: ' . $e->getMessage());
}
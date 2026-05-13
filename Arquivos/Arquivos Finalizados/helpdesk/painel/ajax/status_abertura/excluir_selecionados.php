<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados_status'; // ✅ status_abertura

require_once __DIR__ . '/../../includes/permissoes.php';
if (!podeFazer('excluir')) {
  echo json_encode(['ok'=>false,'msg'=>'Sem permissão para excluir.'], JSON_UNESCAPED_UNICODE);
  exit;
}

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>(bool)$ok,'msg'=>(string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // aceita POST padrão (x-www-form-urlencoded) e também JSON
  $idsStr = '';

  if (isset($_POST['ids'])) {
    $idsStr = trim((string)$_POST['ids']);
  } else {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $json = json_decode($raw, true);
      if (json_last_error() === JSON_ERROR_NONE && isset($json['ids'])) {
        $idsStr = trim((string)$json['ids']);
      }
    }
  }

  if ($idsStr === '') out(false, 'Nenhum registro selecionado.');

  $ids = array_filter(array_map('intval', explode(',', $idsStr)));
  $ids = array_values(array_unique($ids));

  if (count($ids) === 0) out(false, 'Nenhum ID válido.');

  // busca registros existentes
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare("SELECT id, nome, padrao FROM {$tabela} WHERE id IN ($placeholders)");
  $st->execute($ids);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  if (!$rows) out(false, 'Nenhum status encontrado.');

  $naoExcluidos = [];
  $excluidos = 0;

  foreach ($rows as $r) {
    $id = (int)($r['id'] ?? 0);
    $nome = (string)($r['nome'] ?? '');
    $padrao = (string)($r['padrao'] ?? 'Não');

    if ($id <= 0) continue;

    // ✅ impede excluir status padrão
    if ($padrao === 'Sim') {
      $naoExcluidos[] = "{$nome} (padrão)";
      continue;
    }

    // ✅ bloqueia exclusão se estiver em uso por chamados
    $emUso = 0;
    try {
      $stUso = $pdo->prepare("SELECT COUNT(*) FROM chamados WHERE status_id = :id");
      $stUso->execute([':id' => $id]);
      $emUso = (int)$stUso->fetchColumn();
    } catch (Throwable $ignore) {
      $emUso = 0; // se a tabela não existir, não bloqueia
    }

    if ($emUso > 0) {
      $naoExcluidos[] = "{$nome} (em uso: {$emUso})";
      continue;
    }

    // exclui
    $del = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id LIMIT 1");
    $del->execute([':id' => $id]);

    if ($del->rowCount() > 0) {
      $excluidos++;

      registrarLog(
        $pdo,
        'excluir',
        $tabela,
        $id,
        "Status '{$nome}' excluído (seleção múltipla)"
      );
    }
  }

  if ($excluidos <= 0) {
    // nada foi excluído
    $det = $naoExcluidos ? " Não excluídos: " . implode(', ', $naoExcluidos) : "";
    out(false, "Nenhum status pôde ser excluído." . $det);
  }

  $msg = "{$excluidos} status excluído(s) com sucesso!";
  if ($naoExcluidos) {
    $msg .= " Não excluídos: " . implode(', ', $naoExcluidos);
  }

  out(true, $msg);

} catch (Throwable $e) {
  out(false, 'Erro ao excluir: ' . $e->getMessage());
}
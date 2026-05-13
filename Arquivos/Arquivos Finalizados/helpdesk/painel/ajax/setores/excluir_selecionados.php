<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'setores';

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

  // busca registros existentes (para log e para confirmar que existem)
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare("SELECT id, nome FROM {$tabela} WHERE id IN ($placeholders)");
  $st->execute($ids);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  if (!$rows) out(false, 'Nenhum setor encontrado.');

  $excluidos = 0;
  $naoEncontrados = [];

  // ids que realmente existem
  $idsExistentes = [];
  foreach ($rows as $r) {
    $idsExistentes[] = (int)($r['id'] ?? 0);
  }

  // identifica ids que vieram mas não existem
  foreach ($ids as $idIn) {
    if (!in_array($idIn, $idsExistentes, true)) {
      $naoEncontrados[] = $idIn;
    }
  }

  // exclui 1 a 1 (mantém log detalhado por registro)
  foreach ($rows as $r) {
    $id = (int)($r['id'] ?? 0);
    $nome = (string)($r['nome'] ?? '');

    if ($id <= 0) continue;

    $del = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id LIMIT 1");
    $del->execute([':id' => $id]);

    if ($del->rowCount() > 0) {
      $excluidos++;

      registrarLog(
        $pdo,
        'excluir',
        $tabela,
        $id,
        "Setor '{$nome}' excluído (seleção múltipla)"
      );
    }
  }

  if ($excluidos <= 0) {
    out(false, 'Nenhum setor pôde ser excluído.');
  }

  $msg = "{$excluidos} setor(es) excluído(s) com sucesso!";
  if (!empty($naoEncontrados)) {
    $msg .= " IDs não encontrados: " . implode(', ', $naoEncontrados);
  }

  out(true, $msg);

} catch (Throwable $e) {
  out(false, 'Erro ao excluir: ' . $e->getMessage());
}
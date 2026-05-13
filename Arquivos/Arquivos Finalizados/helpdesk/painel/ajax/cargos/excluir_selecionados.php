<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>(bool)$ok,'msg'=>(string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

if (!podeFazer('excluir')) out(false, "Sem permissão para excluir.");

try {
  $idsStr = trim($_POST['ids'] ?? '');
  if ($idsStr === '') out(false, "Nenhum registro selecionado.");

  $ids = array_filter(array_map('intval', explode(',', $idsStr)));
  $ids = array_values(array_unique($ids));

  if (count($ids) === 0) out(false, "Nenhum registro válido.");

  // empresa (mesmo padrão do seu cargos.php)
  $id_empresa = (int)($usuario_empresa ?? ($_SESSION['empresa'] ?? 0));

  // monta IN seguro
  $placeholders = implode(',', array_fill(0, count($ids), '?'));

  // garante que são da empresa antes (opcional, mas recomendado)
  $stCheck = $pdo->prepare("SELECT id, nome FROM cargos WHERE empresa = ? AND id IN ($placeholders)");
  $stCheck->execute(array_merge([$id_empresa], $ids));
  $rows = $stCheck->fetchAll(PDO::FETCH_ASSOC);

  if (!$rows) out(false, "Nenhum registro encontrado para excluir.");

  // exclui
  $stDel = $pdo->prepare("DELETE FROM cargos WHERE empresa = ? AND id IN ($placeholders)");
  $stDel->execute(array_merge([$id_empresa], $ids));

  $qtd = $stDel->rowCount();

  // logs (um por registro, igual seu padrão detalhado)
  foreach ($rows as $r) {
    $rid = (int)($r['id'] ?? 0);
    $nome = (string)($r['nome'] ?? '');
    if ($rid > 0) {
      registrarLog($pdo, 'excluir', 'cargos', $rid, "Cargo '{$nome}' excluído (seleção múltipla)");
    }
  }

  out(true, "{$qtd} registro(s) excluído(s) com sucesso!");

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}
<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'usuarios';

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

  // impede excluir a si mesmo (se estiver na seleção)
  $id_logado = (int)($usuario_id ?? ($_SESSION['id'] ?? 0));

  // busca usuários existentes (para foto)
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $st = $pdo->prepare("SELECT id, foto FROM {$tabela} WHERE id IN ($placeholders)");
  $st->execute($ids);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  if (!$rows) out(false, 'Nenhum usuário encontrado.');

  // ids que realmente existem
  $idsExistentes = [];
  foreach ($rows as $r) $idsExistentes[] = (int)($r['id'] ?? 0);

  $naoEncontrados = [];
  foreach ($ids as $idIn) {
    if (!in_array($idIn, $idsExistentes, true)) $naoEncontrados[] = $idIn;
  }

  $pulados = [];     // ex: "ID 3 (usuário logado)"
  $naoExcluidos = []; // ex: "ID 5 (erro)"
  $excluidos = 0;

  foreach ($rows as $r) {
    $id = (int)($r['id'] ?? 0);
    if ($id <= 0) continue;

    // não exclui o usuário logado
    if ($id_logado > 0 && $id === $id_logado) {
      $pulados[] = "{$id} (usuário logado)";
      continue;
    }

    $foto = trim((string)($r['foto'] ?? ''));

    try {
      $pdo->beginTransaction();

      // 1) remove permissões
      $pdo->prepare("DELETE FROM usuarios_permissoes WHERE usuario_id = :id")
          ->execute([':id' => $id]);

      // 2) remove ações
      $pdo->prepare("DELETE FROM usuarios_acoes WHERE usuario_id = :id")
          ->execute([':id' => $id]);

      // 3) remove usuário
      $delUser = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id LIMIT 1");
      $delUser->execute([':id' => $id]);

      $pdo->commit();

      if ($delUser->rowCount() > 0) {
        $excluidos++;

        registrarLog(
          $pdo,
          'excluir',
          $tabela,
          $id,
          'Usuário excluído do sistema (seleção múltipla)'
        );

        // remove foto do disco (após commit) - não remove sem_foto.webp
        if ($foto !== '' && $foto !== 'sem_foto.webp') {
          $fotoSafe = str_replace(['..', '/', '\\'], '', $foto);
          $path = __DIR__ . '/../../../uploads/perfil/' . $fotoSafe;
          if (is_file($path)) @unlink($path);
        }
      } else {
        $naoExcluidos[] = "{$id} (não removido)";
      }

    } catch (Throwable $e2) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $naoExcluidos[] = "{$id} (erro)";
    }
  }

  if ($excluidos <= 0) {
    $det = [];
    if ($pulados) $det[] = "Pulados: " . implode(', ', $pulados);
    if ($naoExcluidos) $det[] = "Não excluídos: " . implode(', ', $naoExcluidos);
    if ($naoEncontrados) $det[] = "IDs não encontrados: " . implode(', ', $naoEncontrados);
    out(false, "Nenhum usuário pôde ser excluído." . ($det ? " " . implode(" | ", $det) : ""));
  }

  $msg = "{$excluidos} usuário(s) excluído(s) com sucesso!";
  $extraInfo = [];

  if ($pulados) $extraInfo[] = "Pulados: " . implode(', ', $pulados);
  if ($naoExcluidos) $extraInfo[] = "Não excluídos: " . implode(', ', $naoExcluidos);
  if ($naoEncontrados) $extraInfo[] = "IDs não encontrados: " . implode(', ', $naoEncontrados);

  if ($extraInfo) $msg .= " " . implode(" | ", $extraInfo);

  out(true, $msg);

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  out(false, 'Erro ao excluir: ' . $e->getMessage());
}
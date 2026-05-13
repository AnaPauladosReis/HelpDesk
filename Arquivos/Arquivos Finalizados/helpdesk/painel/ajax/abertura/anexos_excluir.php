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

try {

  // painel: precisa ter permissão de excluir
  if (!podeFazer('excluir')) out(false, "Sem permissão para excluir anexos.");

  // aceita POST json ou form
  $id = 0;

  if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
  } else {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $j = json_decode($raw, true);
      if (json_last_error() === JSON_ERROR_NONE && isset($j['id'])) {
        $id = (int)$j['id'];
      }
    }
  }

  if ($id <= 0) out(false, "ID inválido.");

  $usuario_logado = (int)($_SESSION['id'] ?? 0);
  $cliente_logado = (int)($_SESSION['cliente_id'] ?? 0); // futuro painel do cliente
  $nivel          = (string)($_SESSION['nivel'] ?? '');
  $isAdmin        = (mb_strtolower(trim($nivel)) === 'administrador');

  // busca anexo
  $st = $pdo->prepare("
    SELECT a.id, a.chamado_id, a.usuario_id, a.cliente_id, a.arquivo, a.nome,
           ch.protocolo
      FROM chamados_anexos a
      LEFT JOIN chamados ch ON ch.id = a.chamado_id
     WHERE a.id = :id
     LIMIT 1
  ");
  $st->execute([':id' => $id]);
  $a = $st->fetch(PDO::FETCH_ASSOC);
  if (!$a) out(false, "Anexo não encontrado.");

  $anexo_user_id    = ($a['usuario_id'] !== null) ? (int)$a['usuario_id'] : 0;
  $anexo_cliente_id = ($a['cliente_id'] !== null) ? (int)$a['cliente_id'] : 0;

  // regra de exclusão
  $podeExcluir = false;

  if ($isAdmin) {
    $podeExcluir = true;
  } else {
    if ($usuario_logado > 0 && $anexo_user_id > 0 && $usuario_logado === $anexo_user_id) $podeExcluir = true;
    if ($cliente_logado > 0 && $anexo_cliente_id > 0 && $cliente_logado === $anexo_cliente_id) $podeExcluir = true;
  }

  if (!$podeExcluir) out(false, "Você não pode excluir este arquivo.");

  // arquivo físico
  $arquivo = (string)($a['arquivo'] ?? '');
  if ($arquivo === '') out(false, "Arquivo inválido.");

  $uploadsAbs = realpath(__DIR__ . '/../../../uploads/arquivos');
  if (!$uploadsAbs) out(false, "Pasta de uploads não encontrada.");

  // segurança: não deixa path traversal
  $arquivo = basename($arquivo);
  $path = $uploadsAbs . DIRECTORY_SEPARATOR . $arquivo;

  if (is_file($path)) {
    if (!unlink($path)) {
      // se falhar, não remove do banco pra não deixar “fantasma”
      out(false, "Não foi possível excluir o arquivo do servidor.");
    }
  } // se não existe, seguimos para limpar o banco

  // remove do banco
  $del = $pdo->prepare("DELETE FROM chamados_anexos WHERE id = :id LIMIT 1");
  $del->execute([':id' => $id]);

  // log
  $protocolo = (string)($a['protocolo'] ?? '');
  $nome = (string)($a['nome'] ?? '');
  registrarLog($pdo, 'excluir', 'chamados_anexos', $id, "Anexo excluído (Chamado {$protocolo}) {$arquivo} {$nome}");

  out(true, "Arquivo excluído com sucesso!");

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}
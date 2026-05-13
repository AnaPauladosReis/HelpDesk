<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../funcoes/email.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  

  $uid = 0;

  // aceita JSON
  $raw = file_get_contents('php://input');
  $json = $raw ? json_decode($raw, true) : null;
  if (!$json || json_last_error() !== JSON_ERROR_NONE) out(false, 'JSON inválido.');

  $chamado_id = (int)($json['chamado_id'] ?? 0);
  $mensagem   = trim((string)($json['mensagem'] ?? ''));

  if ($chamado_id <= 0) out(false, 'Chamado inválido.');
  if ($mensagem === '') out(false, 'Digite uma resposta.');
  if (mb_strlen($mensagem) > 5000) out(false, 'Mensagem muito grande (máx. 5000).');

  // confere chamado e pega protocolo + cliente_id + assunto
  $stC = $pdo->prepare("
    SELECT id, protocolo, cliente_id, assunto
    FROM chamados
    WHERE id = :id
    LIMIT 1
  ");
  $stC->execute([':id' => $chamado_id]);
  $ch = $stC->fetch(PDO::FETCH_ASSOC);
  if (!$ch) out(false, 'Chamado não encontrado.');

  $protocolo  = (string)($ch['protocolo'] ?? '');
  $cliente_id = (int)($ch['cliente_id'] ?? 0);
  $assuntoChamado = (string)($ch['assunto'] ?? '');

  /**
   * Autor:
   * - agora: usuário
   * - futuro: cliente (quando tiver painel do cliente)
   */
  $tipo_autor = 'cliente'; // usuario | cliente | sistema
  $usuario_id = (int)($_SESSION['cliente_id'] ?? 0);

  // insere resposta
  $st = $pdo->prepare("
    INSERT INTO chamados_respostas
      (chamado_id, usuario_id, cliente_id, tipo_autor, mensagem, criado_em)
    VALUES
      (:chamado_id, :usuario_id, :cliente_id, :tipo_autor, :mensagem, NOW())
  ");
  $st->execute([
    ':chamado_id' => $chamado_id,
    ':usuario_id' => $usuario_id,
    ':cliente_id' => ($cliente_id > 0 ? $cliente_id : null), // cliente do chamado (não o autor)
    ':tipo_autor' => $tipo_autor,
    ':mensagem'   => $mensagem
  ]);

  $respId = (int)$pdo->lastInsertId();

 
  out(true, 'Resposta enviada!', ['id' => $respId]);

} catch (Throwable $e) {
  out(false, 'Erro: ' . $e->getMessage());
}
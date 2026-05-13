<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

function out($ok, $msg) {
  echo json_encode(['ok' => (bool)$ok, 'msg' => (string)$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  // Permissão: usar a mesma de editar (ou crie uma específica depois)
  if (!podeFazer('editar')) out(false, "Sem permissão.");

  $raw = file_get_contents('php://input');
  $j = json_decode($raw, true);

  $id = (int)($j['id'] ?? 0);
  $telefone = preg_replace('/\D+/', '', (string)($j['telefone'] ?? ''));
  $mensagem = trim((string)($j['mensagem'] ?? ''));

  if ($id <= 0) out(false, "ID inválido.");
  if ($telefone === '') out(false, "Telefone inválido.");
  if ($mensagem === '') out(false, "Mensagem vazia.");

  // valida chamado existe
  $st = $pdo->prepare("SELECT id, protocolo FROM chamados WHERE id = :id LIMIT 1");
  $st->execute([':id' => $id]);
  $ch = $st->fetch(PDO::FETCH_ASSOC);
  if (!$ch) out(false, "Chamado não encontrado.");

  // se sua config usa $api_whatsapp e for "Nenhuma", bloqueia
  if (($api_whatsapp ?? 'Nenhuma') === 'Nenhuma') {
    out(false, "API de WhatsApp não configurada.");
  }

  // Essas variáveis são usadas no seu texto_whatsapp.php
  $telefone_disparo = $telefone;
  $mensagem_whatsapp = $mensagem;

  // Reaproveita seu disparador
  require __DIR__ . '/../../apis/texto_whatsapp.php';

  out(true, "Mensagem enviada para o cliente com sucesso!");

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}

<?php
/**
 * ajax/abertura/whats_responsavel.php
 * Envia mensagem WhatsApp para o RESPONSÁVEL do chamado.
 * Recebe JSON:
 *  - id (int) chamado_id
 *  - telefone (string) telefone do responsável (somente números ou com máscara)
 *  - mensagem (string)
 */

@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

function jexit($ok, $msg = '', $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // Lê JSON
  $raw = file_get_contents('php://input');
  $json = $raw ? json_decode($raw, true) : null;

  if (!is_array($json)) jexit(false, 'Requisição inválida.');

  $id = (int)($json['id'] ?? 0);
  $telefone = (string)($json['telefone'] ?? '');
  $mensagem = trim((string)($json['mensagem'] ?? ''));

  if ($id <= 0) jexit(false, 'ID inválido.');
  if ($mensagem === '') jexit(false, 'Mensagem vazia.');

  // Normaliza telefone (somente dígitos)
  $telDigits = preg_replace('/\D+/', '', $telefone);
  if ($telDigits === '' || strlen($telDigits) < 10) {
    jexit(false, 'Telefone inválido.');
  }

  // Confere sessão
  $usuario_logado = (int)($_SESSION['id'] ?? 0);
  if ($usuario_logado <= 0) jexit(false, 'Sessão inválida. Faça login novamente.');

  // ⚠️ Se você tiver empresa no sistema, mantenha. Se não tiver, pode remover.
  $empresa = (int)($_SESSION['empresa'] ?? 0);

  // Busca dados do chamado + responsável (valida que existe)
  $st = $pdo->prepare("
    SELECT
      c.id,
      c.protocolo,
      c.usuario_responsavel_id,
      ur.nome AS resp_nome,
      ur.telefone AS resp_tel_db
    FROM chamados c
    INNER JOIN usuarios ur ON ur.id = c.usuario_responsavel_id
    WHERE c.id = :id
    LIMIT 1
  ");
  $st->execute([':id' => $id]);
  $row = $st->fetch(PDO::FETCH_ASSOC);

  if (!$row) jexit(false, 'Chamado não encontrado ou sem responsável.');

  // Se quiser garantir que o telefone enviado é o do responsável do cadastro:
  // (mais seguro, evita enviar pra número "injetado" no POST)
  $telDb = preg_replace('/\D+/', '', (string)($row['resp_tel_db'] ?? ''));
  if ($telDb !== '' && $telDb !== $telDigits) {
    // usa o do banco como verdade (recomendado)
    $telDigits = $telDb;
  }

  // Verifica se API WhatsApp está configurada (puxada do config/conexao.php)
  // Você usa $api_whatsapp em outros pontos (salvar.php), então mantive padrão.
  if (($api_whatsapp ?? 'Nenhuma') === 'Nenhuma') {
    jexit(false, 'API do WhatsApp não configurada.');
  }

  // Monta variáveis esperadas pelo seu texto_whatsapp.php
  // (no seu salvar.php você usa exatamente essas)
  $telefone_disparo = $telDigits;
  $mensagem_whatsapp = $mensagem;

  // Disparo
  require __DIR__ . '/../../apis/texto_whatsapp.php';

  // Se seu texto_whatsapp.php retornar algo em variável, adapte aqui.
  // Como no seu salvar.php você não checa retorno, vamos considerar OK se não deu fatal.
  jexit(true, 'Mensagem enviada para o responsável com sucesso!');

} catch (Throwable $e) {
  jexit(false, 'Erro ao enviar: ' . $e->getMessage());
}

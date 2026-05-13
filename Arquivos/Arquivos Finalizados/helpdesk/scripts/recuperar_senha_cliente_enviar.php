<?php
/**
 * scripts/recuperar_senha_cliente_enviar.php
 * Processa solicitação de recuperação de senha do CLIENTE (AJAX)
 * - Busca por CPF ou Telefone (comparando só dígitos)
 * - Gera token (30 min), grava em recuperacao_senha (cliente_id)
 * - Envia e-mail (se existir) + WhatsApp (se configurado)
 * - Resposta sempre genérica (não enumera cadastro)
 */

session_start();
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../painel/funcoes/email.php';

// Evita qualquer saída quebrando o JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

header('Content-Type: application/json; charset=utf-8');

function resposta(bool $ok, string $msg): void {
  if (ob_get_length()) { ob_clean(); }
  echo json_encode(['ok' => $ok, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
  exit;
}

function onlyDigits(string $v): string {
  return preg_replace('/\D+/', '', $v) ?? '';
}

// Mensagem genérica (não vaza se existe ou não)
$MSG_OK = 'Se encontrarmos seu cadastro, você receberá as instruções em instantes.';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  resposta(false, 'Requisição inválida.');
}

// Rate limit simples por sessão (20s)
$agora  = time();
$ultimo = $_SESSION['recuperar_senha_cliente_ts'] ?? 0;
if (($agora - (int)$ultimo) < 20) {
  resposta(true, $MSG_OK);
}
$_SESSION['recuperar_senha_cliente_ts'] = $agora;

// Modo teste: não dispara nada, mas responde “ok”
if (($modo_teste ?? 'Não') === 'Sim') {
  resposta(true, $MSG_OK);
}

// Entrada (aceita vários nomes para você plugar fácil no JS)
$raw = trim((string)(
  $_POST['cpf_telefone'] ??
  $_POST['usuario_recuperar'] ??
  $_POST['usuario'] ??
  ''
));

$u = onlyDigits($raw);

// CPF(11) / Telefone(10-11) / com DDI(12-13) se você quiser permitir
if ($u === '' || strlen($u) < 10 || strlen($u) > 13) {
  resposta(true, $MSG_OK);
}

// Busca cliente por cpf_cnpj ou telefone (comparando só dígitos)
$stmt = $pdo->prepare("
  SELECT id, nome, email, telefone, ativo
  FROM clientes
  WHERE
    REPLACE(REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', ''), ' ', '') = :u
    OR
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', ''), '+', '') = :u
  LIMIT 1
");
$stmt->execute([':u' => $u]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);

// Sempre resposta genérica
if (!$cli) {
  resposta(true, $MSG_OK);
}

// Verifica ativo (sem revelar)
if (strtoupper(trim((string)($cli['ativo'] ?? ''))) !== 'SIM') {
  resposta(true, $MSG_OK);
}

$cliente_id   = (int)($cli['id'] ?? 0);
$nomeCliente  = trim((string)($cli['nome'] ?? ''));
$emailCliente = trim((string)($cli['email'] ?? ''));
$foneCliente  = trim((string)($cli['telefone'] ?? ''));

if ($cliente_id <= 0) {
  resposta(true, $MSG_OK);
}

if ($nomeCliente === '') {
  $nomeCliente = ($emailCliente !== '' ? $emailCliente : 'Cliente');
}

// Gera token + grava no banco
$token = '';
$link  = '';

try {
  $token      = bin2hex(random_bytes(32));
  $token_hash = password_hash($token, PASSWORD_DEFAULT);
  $expira_em  = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

  $ip = $_SERVER['REMOTE_ADDR'] ?? null;
  $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
  if ($ua && strlen($ua) > 255) $ua = substr($ua, 0, 255);

  // Invalida tokens antigos desse cliente
  $pdo->prepare("
    UPDATE recuperacao_senha
       SET usado_em = NOW()
     WHERE cliente_id = :cid
       AND usado_em IS NULL
  ")->execute([':cid' => $cliente_id]);

  // Insere novo token (cliente)
  $pdo->prepare("
    INSERT INTO recuperacao_senha
      (cliente_id, token_hash, expira_em, ip, user_agent)
    VALUES
      (:cid, :hash, :expira, :ip, :ua)
  ")->execute([
    ':cid'    => $cliente_id,
    ':hash'   => $token_hash,
    ':expira' => $expira_em,
    ':ip'     => $ip,
    ':ua'     => $ua
  ]);

  // Monta link para scripts/resetar_senha.php
  $protocolo  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host       = $_SERVER['HTTP_HOST'] ?? 'localhost';

  $scriptName = $_SERVER['SCRIPT_NAME'] ?? ''; // /helpdesk/scripts/recuperar_senha_cliente_enviar.php
  $baseDir    = rtrim(str_replace('\\', '/', dirname($scriptName)), '/'); // /helpdesk/scripts
  $baseRaiz   = preg_replace('#/scripts$#', '', $baseDir);               // /helpdesk

  $link = "{$protocolo}://{$host}{$baseRaiz}/scripts/resetar_senha_cliente.php?token=" . urlencode($token);

} catch (Throwable $e) {
  // Não vaza erro
  resposta(true, $MSG_OK);
}

// =========================
// DISPARO E-MAIL (se tiver e-mail)
// =========================
try {
  if ($emailCliente !== '' && filter_var($emailCliente, FILTER_VALIDATE_EMAIL)) {

    $assunto = "Recuperação de senha - {$nome_sistema}";
    $titulo  = "Recuperação de senha";

    $mensagemHtml = "
      <p>Olá <strong>" . htmlspecialchars($nomeCliente, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
      <p>Recebemos uma solicitação para redefinir sua senha no <strong>" . htmlspecialchars($nome_sistema, ENT_QUOTES, 'UTF-8') . "</strong>.</p>
      <p>Clique no botão abaixo para continuar. Este link é válido por <strong>30 minutos</strong>.</p>
    ";

    $htmlEmail = emailTemplatePadrao(
      $titulo,
      $mensagemHtml,
      'Redefinir senha',
      $link,
      "Se o botão não funcionar, copie e cole o link abaixo no navegador:<br>
      <span style='word-break:break-all;'>" . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . "</span>"
    );

    $env = enviarEmailPadrao($emailCliente, $nomeCliente, $assunto, $htmlEmail);

    // Se quiser logar falhas (sem quebrar):
    // if (!(bool)($env['ok'] ?? false)) { error_log('Falha email recuperar cliente: ' . ($env['erro'] ?? '') . ' | ' . ($env['debug'] ?? '')); }
  }
} catch (Throwable $e) {
  // não vaza erro
}

// =========================
// DISPARO WHATSAPP (se configurado e tiver telefone)
// =========================
try {
  $api = (string)($api_whatsapp ?? 'Nenhuma');
  if ($api !== 'Nenhuma' && $foneCliente !== '') {

    $telefone_disparo = onlyDigits($foneCliente);

    if ($telefone_disparo !== '') {
      $mensagem_whatsapp =
        "Olá, {$nomeCliente}! ✅\n\n" .
        "Recebemos uma solicitação para redefinir sua senha no *{$nome_sistema}*.\n\n" .
        "Para continuar, acesse o link abaixo (válido por 30 minutos):\n" .
        "{$link}\n\n" .
        "Se você não solicitou, ignore esta mensagem.";

      // usa seu mesmo padrão de envio
      require_once __DIR__ . '/../painel/apis/texto_whatsapp.php';
    }
  }
} catch (Throwable $e) {
  // não vaza erro
}

// Resposta final (sempre genérica)
resposta(true, $MSG_OK);
<?php
@session_start();
require_once __DIR__ . '/../../conexao.php';

/**
 * IMPORTANTE:
 * Este arquivo é incluído via require dentro de endpoints JSON.
 * Então ele NÃO pode dar echo/print/var_dump e NÃO pode usar exit/die.
 * Se falhar, só registra em log e retorna.
 */

// Se quiser depurar, deixe false por padrão e ligue manualmente quando acessar direto
$DEBUG_ZAP = false;

if (!$DEBUG_ZAP) {
  ini_set('display_errors', 0);
  error_reporting(0);
}

/**
 * helper: retorna sem matar o script pai
 */
if (!function_exists('zap_fail')) {
  function zap_fail($msg) {
    error_log('[WHATSAPP] ' . $msg);
    return false;
  }
}

if (!function_exists('zap_ok')) {
  function zap_ok($msg = '') {
    if ($msg) error_log('[WHATSAPP] ' . $msg);
    return true;
  }
}

// Garantias (evita notices)
$api_whatsapp        = $api_whatsapp        ?? '';
$telefone_disparo    = $telefone_disparo    ?? '';
$mensagem_whatsapp   = $mensagem_whatsapp   ?? '';
$ddi_telefone        = $ddi_telefone        ?? '55';
$token_whatsapp      = $token_whatsapp      ?? '';
$instancia_whatsapp  = $instancia_whatsapp  ?? '';
$url_api             = $url_api             ?? '';

/* =========================
   MENUIA
========================= */
if ($api_whatsapp == 'menuia') {

  $url = "https://chatbot.menuia.com/api/create-message";

  $dados = [
    "appkey"   => $token_whatsapp,
    "authkey"  => $instancia_whatsapp,
    "to"       => $ddi_telefone . $telefone_disparo,
    "message"  => $mensagem_whatsapp
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => json_encode($dados, JSON_UNESCAPED_UNICODE),
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 30,
  ]);

  $resposta = curl_exec($ch);
  $curlErro = curl_error($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  // Erro de transporte
  if ($resposta === false || $curlErro) {
    return zap_fail("MenuIA cURL falhou (HTTP {$httpCode}): " . ($curlErro ?: 'sem detalhes'));
  }

  $retorno = json_decode($resposta, true);
  if (!is_array($retorno)) {
    return zap_fail("MenuIA retorno não-JSON (HTTP {$httpCode}): " . substr((string)$resposta, 0, 500));
  }

  $statusApi = $retorno['status'] ?? null;
  $msgApi    = $retorno['message'] ?? 'Sem mensagem no retorno da API.';

  if ((int)$statusApi !== 200) {
    return zap_fail("MenuIA status != 200 (HTTP {$httpCode} | status {$statusApi}): {$msgApi}");
  }

  return zap_ok("MenuIA enviado ok: {$msgApi}");
}

/* =========================
   EVOLUTION
========================= */
if ($api_whatsapp === 'evolution') {

  $url_base = rtrim(trim($url_api ?? ''), '/');
  $instance = trim($instancia_whatsapp ?? '');
  $api_key  = trim($token_whatsapp ?? '');

  if ($url_base === '' || $instance === '' || $api_key === '') {
    $_SESSION['whats_evolution_ok']   = false;
    $_SESSION['whats_evolution_msg']  = 'WhatsApp (Evolution): URL, Instance ou ApiKey não configurados.';
    $_SESSION['whats_evolution_http'] = null;
    return zap_fail($_SESSION['whats_evolution_msg']);
  }

  $to  = preg_replace('/\D+/', '', $ddi_telefone . $telefone_disparo);
  $url = "{$url_base}/message/sendText/{$instance}";

  $payload = [
    "number" => $to,
    "text"   => $mensagem_whatsapp
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      "Content-Type: application/json",
      "apikey: {$api_key}"
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 30,
  ]);

  $response   = curl_exec($ch);
  $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err        = curl_error($ch);
  curl_close($ch);

  $_SESSION['whats_evolution_http'] = $httpCode;

  if ($response === false || $err) {
    $_SESSION['whats_evolution_ok']  = false;
    $_SESSION['whats_evolution_msg'] = "WhatsApp (Evolution) cURL: " . ($err ?: 'sem detalhes');
    return zap_fail($_SESSION['whats_evolution_msg']);
  }

  if ($httpCode < 200 || $httpCode >= 300) {
    $_SESSION['whats_evolution_ok']  = false;
    $_SESSION['whats_evolution_msg'] = "WhatsApp (Evolution) FALHOU: HTTP {$httpCode} | " . substr((string)$response, 0, 500);
    return zap_fail($_SESSION['whats_evolution_msg']);
  }

  $_SESSION['whats_evolution_ok']  = true;
  $_SESSION['whats_evolution_msg'] = 'WhatsApp (Evolution): enviado com sucesso.';
  return zap_ok($_SESSION['whats_evolution_msg']);
}

/* =========================
   META
========================= */
if ($api_whatsapp === 'meta') {

  $phoneNumberId = trim((string)$instancia_whatsapp);
  $meta_token    = preg_replace('/\s+/', '', trim((string)$token_whatsapp));
  $to = preg_replace('/\D+/', '', $ddi_telefone . $telefone_disparo);

  if ($phoneNumberId === '' || $meta_token === '') {
    $_SESSION['whats_meta_ok']  = false;
    $_SESSION['whats_meta_msg'] = 'WhatsApp (Meta): PhoneNumberID ou Token vazio.';
    return zap_fail($_SESSION['whats_meta_msg']);
  }

  if ($to === '' || strlen($to) < 10) {
    $_SESSION['whats_meta_ok']  = false;
    $_SESSION['whats_meta_msg'] = "WhatsApp (Meta): telefone inválido ({$to}).";
    return zap_fail($_SESSION['whats_meta_msg']);
  }

  $url = "https://graph.facebook.com/v22.0/{$phoneNumberId}/messages";

  $template_name = 'hello_world';
  $template_lang = 'en_US';

  $payload = [
    "messaging_product" => "whatsapp",
    "to" => $to,
    "type" => "template",
    "template" => [
      "name" => $template_name,
      "language" => ["code" => $template_lang]
    ]
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      "Authorization: Bearer {$meta_token}",
      "Content-Type: application/json",
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 30,
  ]);

  $response   = curl_exec($ch);
  $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err        = curl_error($ch);
  curl_close($ch);

  $_SESSION['whats_meta_http'] = $httpCode;

  if ($response === false || $err) {
    $_SESSION['whats_meta_ok']  = false;
    $_SESSION['whats_meta_msg'] = "WhatsApp (Meta) cURL: " . ($err ?: 'sem detalhes');
    return zap_fail($_SESSION['whats_meta_msg']);
  }

  $ret = json_decode((string)$response, true);
  if (!is_array($ret)) {
    $_SESSION['whats_meta_ok']  = false;
    $_SESSION['whats_meta_msg'] = "WhatsApp (Meta): retorno não-JSON. HTTP {$httpCode}.";
    return zap_fail($_SESSION['whats_meta_msg']);
  }

  if ($httpCode < 200 || $httpCode >= 300) {
    $metaErrorMsg  = $ret['error']['message'] ?? $response;
    $metaErrorType = $ret['error']['type'] ?? '';
    $metaErrorCode = (int)($ret['error']['code'] ?? 0);
    $fbtrace_id    = $ret['error']['fbtrace_id'] ?? null;

    $_SESSION['whats_meta_ok']  = false;
    $_SESSION['whats_meta_msg'] = "WhatsApp (Meta) FALHOU: HTTP {$httpCode} | {$metaErrorType} {$metaErrorCode} | {$metaErrorMsg}" .
                                  ($fbtrace_id ? " fbtrace_id={$fbtrace_id}" : "");

    return zap_fail($_SESSION['whats_meta_msg']);
  }

  $_SESSION['whats_meta_ok']  = true;
  $_SESSION['whats_meta_msg'] = 'WhatsApp (Meta): enviado com sucesso.';
  $_SESSION['whats_meta_id']  = $ret['messages'][0]['id'] ?? null;

  return zap_ok($_SESSION['whats_meta_msg']);
}

// Se não bateu em nenhum provedor, só retorna
return true;
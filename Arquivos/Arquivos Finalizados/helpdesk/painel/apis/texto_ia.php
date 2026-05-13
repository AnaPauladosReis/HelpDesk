<?php
@session_start();

/**
 * painel/apis/texto_ia.php
 *
 * Uso:
 *   $prompt_ia = "Seu prompt aqui...";
 *   require_once __DIR__ . '/texto_ia.php';
 *   if ($ia_ok) echo $ia_text; else echo $ia_msg;
 */

// Saídas padronizadas
$ia_ok   = false;
$ia_msg  = 'IA: não executada.';
$ia_http = null;
$ia_text = '';
$ia_raw  = null;

// Prompt obrigatório (defina antes de incluir este arquivo)
$prompt_ia = (string)($prompt_ia ?? '');
if (trim($prompt_ia) === '') {
    $ia_msg = 'IA: informe um prompt.';
    return;
}

// Provider escolhido no config: Nenhuma | chatgpt | gemini
$api_ia = (string)($api_ia ?? 'Nenhuma');
$api_ia = strtolower(trim($api_ia));

if ($api_ia === 'nenhuma') {
    $ia_msg = 'IA: nenhuma API selecionada.';
    return;
}

// Token único (você decidiu assim)
$token_ia = trim((string)($token_ia ?? ''));
$token_ia = preg_replace('/\s+/', '', $token_ia); // remove quebras/espacos

if ($token_ia === '') {
    $ia_msg = 'IA: token não configurado.';
    return;
}

// Timeout padrão
$timeout = 60;

// =========================================================
// ChatGPT (OpenAI) - Responses API
// =========================================================
if ($api_ia === 'chatgpt') {

    $token = preg_replace('/\s+/', '', (string)($token_ia ?? ''));
    $prompt = trim((string)($prompt_ia ?? ''));

    if ($token === '') {
        $ia_msg = 'ChatGPT: token vazio.';
        return;
    }
    if ($prompt === '') {
        $ia_msg = 'ChatGPT: prompt vazio.';
        return;
    }

    // endpoint novo (Responses API)
    $url = "https://api.openai.com/v1/responses";

    $payload = [
        "model" => "gpt-5.2-2025-12-11", // pode trocar depois
        "input" => $prompt
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$token}",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => 45,
    ]);

    $response = curl_exec($ch);
    $ia_http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    $ia_raw = $response;

    if ($response === false || $err) {
        $ia_msg = "ChatGPT: erro de conexão (cURL) - " . ($err ?: 'sem detalhes');
        return;
    }

    $json = json_decode($response, true);

    if (!is_array($json)) {
        $ia_msg = "ChatGPT: retorno inválido (HTTP {$ia_http}).";
        return;
    }

    // Se deu erro na API
    if ($ia_http < 200 || $ia_http >= 300) {
        $apiErr = $json['error']['message'] ?? 'Erro desconhecido.';
        $ia_msg = "ChatGPT: falhou (HTTP {$ia_http}) - {$apiErr}";
        return;
    }

    // ===== extrair texto final (Responses API) =====
    // caminho mais comum:
    // $json['output'][0]['content'][0]['text']
    $text = '';

    if (!empty($json['output'][0]['content'][0]['text'])) {
        $text = (string)$json['output'][0]['content'][0]['text'];
    }

    // fallback extra (caso venha formato diferente)
    if ($text === '' && isset($json['output']) && is_array($json['output'])) {
        foreach ($json['output'] as $out) {
            if (!empty($out['content']) && is_array($out['content'])) {
                foreach ($out['content'] as $c) {
                    if (!empty($c['text'])) {
                        $text .= ($text ? "\n" : "") . $c['text'];
                    }
                }
            }
        }
    }

    $text = trim($text);

    if ($text === '') {
        $ia_msg = "ChatGPT: ok, mas não retornou texto.";
        return;
    }

    $ia_ok   = true;
    $ia_text = $text;
    $ia_msg  = "ChatGPT: ok.";
}

// =========================================================
// Gemini - generateContent
// =========================================================
if ($api_ia === 'gemini') {

    // modelo padrão (você pode trocar depois)
    $model = "gemini-2.5-flash";
    $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

    $payload = [
        "contents" => [[
            "role"  => "user",
            "parts" => [
                ["text" => $prompt_ia]
            ]
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            "x-goog-api-key: {$token_ia}",
            "Content-Type: application/json",
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $timeout,
    ]);

    $response = curl_exec($ch);
    $ia_http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    $ia_raw = $response;

    if ($response === false || $err) {
        $ia_msg = "Gemini cURL: " . ($err ?: 'sem detalhes');
        return;
    }

    $ret = json_decode((string)$response, true);
    if (!is_array($ret)) {
        $ia_msg = "Gemini: retorno não-JSON. HTTP {$ia_http}.";
        return;
    }

    if ($ia_http < 200 || $ia_http >= 300) {
        $msgErr = $ret['error']['message'] ?? ($ret['message'] ?? 'Erro desconhecido');
        $ia_msg = "Gemini FALHOU: HTTP {$ia_http} | {$msgErr}";
        return;
    }

    $ia_text = $ret['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if (!$ia_text) {
        $ia_msg = "Gemini: resposta vazia.";
        return;
    }

    $ia_ok  = true;
    $ia_msg = 'Gemini: ok.';
    return;
}

// Provider inválido
$ia_msg = "IA: api_ia inválida ('{$api_ia}'). Use 'Nenhuma', 'chatgpt' ou 'gemini'.";
return;

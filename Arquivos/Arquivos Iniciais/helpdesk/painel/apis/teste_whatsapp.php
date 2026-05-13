<?php
@session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../conexao.php'; // ajuste se necessário

// Primeiro tenta vir do POST (modal), se não vier cai no banco/config
$api_whatsapp       = $_POST['api_whatsapp']       ?? ($config['api_whatsapp']       ?? 'Nenhuma');
$token_whatsapp     = $_POST['token_whatsapp']     ?? ($config['token_whatsapp']     ?? '');
$instancia_whatsapp = $_POST['instancia_whatsapp'] ?? ($config['instancia_whatsapp'] ?? '');
$url_api            = $_POST['url_api']            ?? ($config['url_api']            ?? '');






/*
|--------------------------------------------------------------------------
| DADOS DE TESTE
|--------------------------------------------------------------------------
*/
$telefone_disparo = preg_replace('/\D+/', '', $telefone_sistema);
$telefone_teste   = $telefone_disparo; // coloque SEU número para teste
$ddi              = $ddi_telefone;
$mensagem_teste   = 'Teste de envio WhatsApp - API';



/*
|--------------------------------------------------------------------------
| RETORNO PADRÃO
|--------------------------------------------------------------------------
*/
$retorno = [
  'api'  => $api_whatsapp,
  'ok'   => false,
  'msg'  => 'Não executado',
  'http' => null,
  'raw'  => null
];

/*
|--------------------------------------------------------------------------
| MENUIA
|--------------------------------------------------------------------------
*/
if ($api_whatsapp === 'menuia') {

  $url = "https://chatbot.menuia.com/api/create-message";

  $payload = [
    "appkey"  => $token_whatsapp,
    "authkey" => $instancia_whatsapp,
    "to"      => $ddi . $telefone_teste,
    "message" => $mensagem_teste
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 30,
  ]);

  $response = curl_exec($ch);
  $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err      = curl_error($ch);
  curl_close($ch);

  $retorno['http'] = $http;
  $retorno['raw']  = $response;

  if ($err) {
    $retorno['msg'] = "Erro cURL: {$err}";
  } else {
    $json = json_decode($response, true);
    if (($json['status'] ?? 0) == 200) {
      $retorno['ok']  = true;
      $retorno['msg'] = $json['message'] ?? 'Enviado com sucesso';
    } else {
      $retorno['msg'] = $json['message'] ?? 'Erro desconhecido';
    }
  }
}

/*
|--------------------------------------------------------------------------
| META (API OFICIAL)
|--------------------------------------------------------------------------
*/
if ($api_whatsapp === 'meta') {

  $phoneNumberId = trim($instancia_whatsapp);
  $token         = preg_replace('/\s+/', '', trim($token_whatsapp));

  $to = preg_replace('/\D+/', '', $ddi . $telefone_teste);

  $url = "https://graph.facebook.com/v22.0/{$phoneNumberId}/messages";

  // ⚠️ Template obrigatório se fora da janela de 24h
  $payload = [
    "messaging_product" => "whatsapp",
    "to"   => $to,
    "type" => "template",
    "template" => [
      "name" => "hello_world", // precisa existir na sua conta
      "language" => [
        "code" => "en_US"
      ]
    ]
  ];

  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      "Authorization: Bearer {$token}",
      "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 30,
  ]);

  $response = curl_exec($ch);
  $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err      = curl_error($ch);
  curl_close($ch);

  $retorno['http'] = $http;
  $retorno['raw']  = $response;

  if ($err) {
    $retorno['msg'] = "Erro cURL: {$err}";
  } else {
    $json = json_decode($response, true);
    if ($http >= 200 && $http < 300) {
      $retorno['ok']  = true;
      $retorno['msg'] = 'Mensagem enviada com sucesso';
    } else {
      $retorno['msg'] = $json['error']['message'] ?? 'Erro desconhecido';
    }
  }
}

/*
|--------------------------------------------------------------------------
| EVOLUTION API
|--------------------------------------------------------------------------
*/
if ($api_whatsapp === 'evolution') {

    $retorno = [
        'api'  => 'evolution',
        'ok'   => false,
        'msg'  => 'WhatsApp (Evolution): não enviado.',
        'http' => null,
        'raw'  => null,
        'url'  => null
    ];

    // campos obrigatórios
    $url_base = rtrim(trim($url_api ?? ''), '/');
    $instance = trim($instancia_whatsapp ?? '');
    $api_key  = trim($token_whatsapp ?? '');

    if ($url_base === '' || $instance === '' || $api_key === '') {
        $retorno['msg'] = 'URL, Instance ou ApiKey não configurados.';
    } else {

        // telefone em E.164 (somente números)
        $to = preg_replace('/\D+/', '', $ddi . $telefone_teste);

        // 🔥 endpoint CORRETO da Evolution
        $url = "{$url_base}/message/sendText/{$instance}";
        $retorno['url'] = $url;

        $payload = [
            "number" => $to,
            "text"   => $mensagem_teste
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

        $response = curl_exec($ch);
        $http     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        $retorno['http'] = $http;
        $retorno['raw']  = $response;

        if ($response === false || $err) {
            $retorno['msg'] = "Erro cURL: " . ($err ?: 'sem detalhes');
        } else {
            if ($http >= 200 && $http < 300) {
                $retorno['ok']  = true;
                $retorno['msg'] = 'WhatsApp (Evolution): enviado com sucesso.';
            } else {
                $retorno['msg'] = "Erro HTTP {$http}";
            }
        }
    }

    
}


/*
|--------------------------------------------------------------------------
| SAÍDA DE DEBUG
|--------------------------------------------------------------------------
*/
function retornoVisual($dados)
{
    $ok   = $dados['ok'] ?? false;
    $msg  = $dados['msg'] ?? 'Sem mensagem';
    $http = $dados['http'] ?? null;
    $raw  = $dados['raw'] ?? null;
    $api  = $dados['api'] ?? '';

    $cor   = $ok ? 'success' : 'danger';
    $icone = $ok ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    $titulo = $ok ? 'Teste realizado com sucesso' : 'Falha no teste';

    ob_start(); ?>
    
    <div class="alert alert-<?= $cor ?> shadow-sm rounded-3 mb-0">
  <div class="d-flex align-items-start gap-2">
    <i class="bi <?= $icone ?> fs-4"></i>

    <div class="flex-grow-1">
      <div class="fw-bold"><?= $titulo ?></div>

      <div class="small mt-1">
        <?= htmlspecialchars($msg) ?>
      </div>

      <div class="small text-muted mt-2">
        <strong>API:</strong> <?= strtoupper($api) ?>
        <?php if ($http): ?> | <strong>HTTP:</strong> <?= $http ?><?php endif; ?>
      </div>

      <!-- 🔧 DETALHES TÉCNICOS (SEMPRE VISÍVEL) -->
      <div class="mt-2">
        <div class="small fw-semibold text-muted mb-1">
          Detalhes técnicos
        </div>

        <pre class="small bg-light p-2 rounded mb-0" style="white-space: pre-wrap">
<?= htmlspecialchars($raw ?: 'Sem retorno técnico da API.') ?>
        </pre>
      </div>

    </div>
  </div>
</div>


    <?php
    return ob_get_clean();
}


echo retornoVisual($retorno);
exit;

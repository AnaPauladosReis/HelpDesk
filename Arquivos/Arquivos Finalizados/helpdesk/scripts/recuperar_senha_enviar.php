<?php
/**
 * scripts/recuperar_senha_enviar.php
 * Processa solicitação de recuperação de senha (AJAX)
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

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    resposta(false, 'Requisição inválida.');
}

// Rate limit simples por sessão (20s)
$agora = time();
$ultimo = $_SESSION['recuperar_senha_ts'] ?? 0;
if (($agora - (int)$ultimo) < 20) {
    resposta(true, 'Se o e-mail existir no sistema, você receberá as instruções em instantes.');
}
$_SESSION['recuperar_senha_ts'] = $agora;

// Entrada
$email = trim((string)($_POST['email_recuperar'] ?? ''));

// Validação básica
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    resposta(true, 'Se o e-mail existir no sistema, você receberá as instruções em instantes.');
}

// Busca usuário (agora traz NOME)
$stmt = $pdo->prepare("
    SELECT id, nome, ativo
    FROM usuarios
    WHERE email = :email
    LIMIT 1
");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

// Sempre resposta genérica
if (!$user) {
    resposta(true, 'Se o e-mail existir no sistema, você receberá as instruções em instantes.');
}

// Verifica ativo
if (strtoupper(trim((string)$user['ativo'])) !== 'SIM') {
    resposta(true, 'Se o e-mail existir no sistema, você receberá as instruções em instantes.');
}

// ✅ Define nome do usuário (evita Undefined variable)
$nomeUsuario = trim((string)($user['nome'] ?? ''));
if ($nomeUsuario === '') {
    $nomeUsuario = $email; // fallback
}

// Geração do token + gravação
try {
    $usuario_id = (int)$user['id'];

    $token = bin2hex(random_bytes(32));
    $token_hash = password_hash($token, PASSWORD_DEFAULT);
    $expira_em = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
    if ($ua && strlen($ua) > 255) {
        $ua = substr($ua, 0, 255);
    }

    // Invalida tokens antigos
    $pdo->prepare("
        UPDATE recuperacao_senha
        SET usado_em = NOW()
        WHERE usuario_id = :uid
          AND usado_em IS NULL
    ")->execute([':uid' => $usuario_id]);

    // Insere novo token
    $pdo->prepare("
        INSERT INTO recuperacao_senha
        (usuario_id, token_hash, expira_em, ip, user_agent)
        VALUES
        (:uid, :hash, :expira, :ip, :ua)
    ")->execute([
        ':uid'    => $usuario_id,
        ':hash'   => $token_hash,
        ':expira' => $expira_em,
        ':ip'     => $ip,
        ':ua'     => $ua
    ]);

} catch (Exception $e) {
    resposta(true, 'Se o e-mail existir no sistema, você receberá as instruções em instantes.');
}

// Monta link para scripts/resetar_senha.php
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/'); // /helpdesk/scripts
$baseRaiz = preg_replace('#/scripts$#', '', $baseDir);               // /helpdesk

$link = "{$protocolo}://{$host}{$baseRaiz}/scripts/resetar_senha.php?token=" . urlencode($token);

// ✅ Disparo do e-mail (com template)
try {
    $assunto = "Recuperação de senha - {$nome_sistema}";

    $titulo = 'Recuperação de senha';

    $mensagemHtml = "
        <p>Olá <strong>" . htmlspecialchars($nomeUsuario, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
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

    $env = enviarEmailPadrao($email, $nomeUsuario, $assunto, $htmlEmail);

    // Se quiser logar falhas:
    // if (!$env['ok']) { error_log('Falha email: ' . $env['erro'] . ' | ' . $env['debug']); }

} catch (Exception $e) {
    // não vaza erro
}

// Resposta final (sempre genérica)
resposta(true, 'Se o e-mail existir no sistema, você receberá as instruções em instantes.');

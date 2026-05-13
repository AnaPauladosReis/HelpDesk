<?php
/**
 * painel/funcoes/teste_email.php
 * Teste direto de envio de e-mail (SEM AJAX)
 *
 * Acesse no navegador:
 * http://localhost/helpdesk/painel/funcoes/teste_email.php?para=SEUEMAIL@DOMINIO.COM
 */

session_start();

// sobe dois níveis: painel/funcoes -> painel -> raiz
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/email.php';

// Destinatário do teste
$para = trim((string)($_GET['para'] ?? $email_sistema));

// Conteúdo do e-mail
$titulo = 'Teste de envio de e-mail';
$mensagem = "
    <p>Olá!</p>
    <p>Este é um <strong>teste de envio</strong> do <strong>{$nome_sistema}</strong>.</p>
    <p>Se este e-mail chegou, o SMTP está funcionando corretamente ✅</p>
";

// HTML usando o template padrão
$html = emailTemplatePadrao(
    $titulo,
    $mensagem,
    'Abrir sistema',
    (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'
    ) . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/helpdesk/',
    'Enviado em: ' . date('d/m/Y H:i:s')
);

// Assunto
$assunto = "Teste SMTP - {$nome_sistema}";

// Envio
$resultado = enviarEmailPadrao($para, 'Teste', $assunto, $html);

// Mostra resultado na tela (simples e claro)
header('Content-Type: text/plain; charset=utf-8');

echo "===== TESTE DE ENVIO =====\n\n";
echo "Para: {$para}\n";
echo "Assunto: {$assunto}\n\n";

echo "Resultado:\n";
echo "OK: " . ($resultado['ok'] ? 'SIM' : 'NÃO') . "\n";
echo "Erro: " . ($resultado['erro'] ?: '-') . "\n";
echo "Debug: " . ($resultado['debug'] ?: '-') . "\n";

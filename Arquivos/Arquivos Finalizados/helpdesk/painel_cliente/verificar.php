<?php
/**
 * Verificação de sessão do painel
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Segurança básica
if (empty($_SESSION['cliente_id']) || $_SESSION['token_login_cliente'] != $_SESSION['cliente_id'] ) {
    header('Location: ../acesso');
    exit;
}

// Dados do usuário disponíveis globalmente
$usuario_id      = (int) $_SESSION['cliente_id'];
$usuario_nome    = htmlspecialchars($_SESSION['cliente_nome'] ?? '', ENT_QUOTES, 'UTF-8');
$usuario_email   = htmlspecialchars($_SESSION['cliente_email'] ?? '', ENT_QUOTES, 'UTF-8');
$usuario_nivel   = htmlspecialchars($_SESSION['nivel'] ?? 'Cliente', ENT_QUOTES, 'UTF-8');
$usuario_empresa = (int) ($_SESSION['id_empresa'] ?? 0);

?>
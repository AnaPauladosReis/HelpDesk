<?php
/**
 * Verificação de sessão do painel
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Segurança básica
if (empty($_SESSION['id']) || empty($_SESSION['email']) || $_SESSION['token_login'] != $_SESSION['id'] ) {
    header('Location: ../index.php');
    exit;
}

// Dados do usuário disponíveis globalmente
$usuario_id      = (int) $_SESSION['id'];
$usuario_nome    = htmlspecialchars($_SESSION['nome'] ?? '', ENT_QUOTES, 'UTF-8');
$usuario_email   = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
$usuario_nivel   = htmlspecialchars($_SESSION['nivel'] ?? '', ENT_QUOTES, 'UTF-8');
$usuario_empresa = (int) ($_SESSION['id_empresa'] ?? 0);

?>
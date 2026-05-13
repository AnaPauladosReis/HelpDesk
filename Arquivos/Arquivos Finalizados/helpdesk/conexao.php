<?php
/**
 * Arquivo de Configuração do Sistema Helpdesk
 * Contém variáveis de conexão, cores e nome do sistema
 */

$modo_teste = 'Não';
// Credenciais padrão para o modo teste (apenas DEV)
$teste_email = 'contato@hugocursos.com.br';
$teste_senha = '123';
$ddi_telefone = '+55';


// =========================
// URL BASE FIXA DA RAIZ DO SISTEMA
// =========================
// AJUSTE APENAS ESTA LINHA SE MUDAR A PASTA DO SISTEMA
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host  = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detecta ambiente local
$ehLocal = in_array($host, ['localhost', '127.0.0.1']) 
           || str_contains($host, ':'); // pega localhost:8080 etc

// Define pasta raiz
$pasta_raiz_sistema = $ehLocal ? '/helpdesk' : '';

// Monta URL final
$url_sistema = rtrim($proto . '://' . $host . $pasta_raiz_sistema, '/') . '/';

// conexao.php (na raiz do projeto)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__)); // /.../helpdesk
  }


// Fuso horário de Brasília
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$servidor = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'helpdesk';

try {
    $pdo = new PDO(
        "mysql:host=$servidor;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die('Erro ao conectar: ' . $e->getMessage());
}

// Senha padrão para criação de usuários (usar ao criar novos usuários no sistema)
$senha_padrao = '123';

// Variáveis do sistema (valores padrão)
$nome_sistema      = 'Sistema HelpDesk';
$telefone_sistema  = '(31) 97527-5084';
$email_sistema     = 'contato@hugocursos.com.br';
$cor_primaria      = '#667eea';
$cor_secundaria    = '#764ba2';
$id_empresa        = 0;

// ✅ NOVAS VARIÁVEIS (fallbacks)
$logo              = 'sem_foto.webp';
$icone             = 'sem_foto.webp';
$endereco          = '';

// === SMTP (valores padrão) ===
$smtp_host       = 'mail.hugocursos.com.br';
$smtp_senha      = '';
$smtp_porta      = 587;
$smtp_seguranca  = 'tls';
$api_whatsapp  = 'Nenhuma';
$token_whatsapp  = '';
$instancia_whatsapp  = '';
$url_api  = '';
$token_ia  = '';
$api_ia  = 'Nenhuma';
$dias_excluir_logs  = '180';

// Carregar config da tabela (empresa = 0). Se não existir registro, insere o primeiro.
$stmt = $pdo->query("SELECT * FROM config WHERE empresa = 0 LIMIT 1");
$config = $stmt->fetch() ?: [];

if (!$config) {
    $stmt = $pdo->prepare("
        INSERT INTO config 
        (nome_sistema, telefone_sistema, email_sistema, cor_primaria, cor_secundaria, empresa,
         smtp_host, smtp_senha, smtp_porta, smtp_seguranca, logo, icone, endereco, dias_excluir_logs)
        VALUES 
        (:nome_sistema, :telefone_sistema, :email_sistema, :cor_primaria, :cor_secundaria, :empresa,
         :smtp_host, :smtp_senha, :smtp_porta, :smtp_seguranca, :logo, :icone, :endereco, :dias_excluir_logs)
    ");
    $stmt->execute([
        ':nome_sistema'     => $nome_sistema,
        ':telefone_sistema' => $telefone_sistema,
        ':email_sistema'    => $email_sistema,
        ':cor_primaria'     => $cor_primaria,
        ':cor_secundaria'   => $cor_secundaria,
        ':empresa'          => $id_empresa,
        ':smtp_host'        => $smtp_host,
        ':smtp_senha'       => $smtp_senha,
        ':smtp_porta'       => $smtp_porta,
        ':smtp_seguranca'   => $smtp_seguranca,
        ':logo'             => $logo,
        ':icone'            => $icone,
        ':endereco'         => $endereco,
        ':dias_excluir_logs'         => $dias_excluir_logs
    ]);

    $stmt = $pdo->query("SELECT * FROM config WHERE empresa = 0 LIMIT 1");
    $config = $stmt->fetch() ?: [];
}

// Sobrescrever com os valores do banco
if ($config) {
    $nome_sistema     = $config['nome_sistema']     ?? $nome_sistema;
    $telefone_sistema = $config['telefone_sistema'] ?? $telefone_sistema;
    $email_sistema    = $config['email_sistema']    ?? $email_sistema;
    $cor_primaria     = $config['cor_primaria']     ?? $cor_primaria;
    $cor_secundaria   = $config['cor_secundaria']   ?? $cor_secundaria;
    $id_empresa       = (int)($config['empresa']    ?? $id_empresa);

    // === SMTP vindo do banco ===
    $smtp_host      = $config['smtp_host']       ?? $smtp_host;
    $smtp_senha     = $config['smtp_senha']      ?? $smtp_senha;
    $smtp_porta     = (int)($config['smtp_porta'] ?? $smtp_porta);
    $smtp_seguranca = $config['smtp_seguranca']  ?? $smtp_seguranca;

    // ✅ NOVOS CAMPOS vindo do banco (com fallback)
    $logo     = !empty($config['logo'])     ? $config['logo']     : $logo;
    $icone    = !empty($config['icone'])    ? $config['icone']    : $icone;
    $endereco = $config['endereco']         ?? $endereco;
    $api_whatsapp = $config['api_whatsapp']         ?? $api_whatsapp;
    $token_whatsapp = $config['token_whatsapp']         ?? $token_whatsapp;
    $instancia_whatsapp = $config['instancia_whatsapp']         ?? $instancia_whatsapp;
    $url_api = $config['url_api']         ?? $url_api;
    $token_ia = $config['token_ia']         ?? $token_ia;
    $api_ia = $config['api_ia']         ?? $api_ia;
    $dias_excluir_logs = $config['dias_excluir_logs']         ?? $dias_excluir_logs;
}
?>

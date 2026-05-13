<?php
/**
 * Autenticação do Cliente
 * Login por CPF ou Telefone
 * Medidas: prepared statements, password_verify, validação, CSRF, regeneração de sessão
 */

session_start();

require_once 'conexao.php';
require_once __DIR__ . '/painel/includes/logs.php';

// default flash
$_SESSION['flash'] = [
  'tipo' => 'erro',
  'codigo' => 'login_invalido'
];

// Só processa se for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: acesso.php'); // ajuste se seu arquivo tiver outro nome
  exit;
}

// Proteção CSRF
$token_post   = $_POST['csrf_token'] ?? '';
$token_sessao = $_SESSION['csrf_token_login_cliente'] ?? '';
if (empty($token_post) || !hash_equals((string)$token_sessao, (string)$token_post)) {
  $_SESSION['flash'] = ['tipo' => 'erro', 'codigo' => 'csrf_invalido'];
  header('Location: acesso.php');
  exit;
}

// Entrada
$usuarioRaw = trim((string)($_POST['usuario'] ?? ''));
$senha      = (string)($_POST['senha'] ?? '');
$lembrar    = isset($_POST['lembrar']) ? 'Sim' : 'Não';
$_SESSION['lembrar_login_cliente'] = $lembrar;

// Validações básicas
// remove máscara -> só números
$usuarioNum = preg_replace('/\D+/', '', $usuarioRaw);

// CPF (11) ou telefone (10/11). (se você quiser aceitar DDI, pode aceitar 12/13 também)
if ($usuarioNum === '' || strlen($usuarioNum) < 10 || strlen($usuarioNum) > 13) {
  $_SESSION['flash'] = ['tipo'=>'erro','codigo'=>'login_invalido'];
  header('Location: acesso.php');
  exit;
}

// senha
if ($senha === '' || strlen($senha) > 256) {
  $_SESSION['flash'] = ['tipo'=>'erro','codigo'=>'login_invalido'];
  header('Location: acesso.php');
  exit;
}

// Busca cliente por cpf_cnpj ou telefone (comparando só dígitos)
// Observação: REPLACE em cascata remove máscara do banco
$stmt = $pdo->prepare("
  SELECT id, nome, email, telefone, cpf_cnpj, senha, ativo, empresa
  FROM clientes
  WHERE
    REPLACE(REPLACE(REPLACE(REPLACE(cpf_cnpj, '.', ''), '-', ''), '/', ''), ' ', '') = :u
    OR
    REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', ''), '+', '') = :u
  LIMIT 1
");
$stmt->execute([':u' => $usuarioNum]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);

// Mesmo retorno para não existir e senha errada
if (!$cli) {
  $_SESSION['flash'] = ['tipo'=>'erro','codigo'=>'login_invalido'];
  header('Location: acesso.php');
  exit;
}

// Ativo?
if (strtoupper(trim((string)($cli['ativo'] ?? ''))) !== 'SIM') {
  $_SESSION['flash'] = ['tipo'=>'aviso','codigo'=>'usuario_inativo'];
  header('Location: acesso.php');
  exit;
}

// Senha confere?
if (!password_verify($senha, (string)($cli['senha'] ?? ''))) {
  $_SESSION['flash'] = ['tipo'=>'erro','codigo'=>'login_invalido'];
  header('Location: acesso.php');
  exit;
}

// Regenera ID da sessão após login
session_regenerate_id(true);

// Salva sessão do cliente (use nomes diferentes para não conflitar com admin/painel)
$_SESSION['cliente_id']      = (int)($cli['id'] ?? 0);
$_SESSION['cliente_nome']    = strip_tags($cli['nome'] ?? '');
$_SESSION['cliente_email']   = filter_var((string)($cli['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$_SESSION['cliente_empresa'] = (int)($cli['empresa'] ?? 0);
$_SESSION['token_login_cliente'] = (int)($_SESSION['cliente_id'] ?? 0);

// Atualiza último acesso + IP
try {
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $up = $pdo->prepare("
    UPDATE clientes
       SET ultimo_acesso = NOW(),
           ip_ultimo_acesso = :ip
     WHERE id = :id
     LIMIT 1
  ");
  $up->execute([
    ':ip' => $ip,
    ':id' => (int)$_SESSION['cliente_id']
  ]);
} catch (Throwable $ignore) {
  // não bloqueia login por isso
}

// LOG
registrarLog(
  $pdo,
  'login',
  'clientes',
  (int)$_SESSION['cliente_id'],
  'Login do cliente realizado com sucesso'
);

// Remove token CSRF (uso único)
unset($_SESSION['csrf_token_login_cliente']);

// Redireciona para o painel do cliente
header('Location: painel_cliente/'); // ajuste: para onde seu painel do cliente vai apontar
exit;
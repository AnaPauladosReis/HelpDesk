<?php
/**
 * Autenticação de login
 * Valida usuário/senha, inicia sessão e redireciona
 * Medidas: prepared statements, password_verify, validação de entrada, CSRF, regeneração de sessão
 */

session_start();

$_SESSION['flash'] = [
    'tipo' => 'erro', // erro | aviso | sucesso
    'codigo' => 'login_invalido'
];


require_once 'conexao.php';
require_once __DIR__ . '/painel/includes/logs.php';


// Só processa se for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Proteção CSRF: valida token do formulário
$token_post = $_POST['csrf_token'] ?? '';
$token_sessao = $_SESSION['csrf_token_login'] ?? '';
if (empty($token_post) || !hash_equals((string) $token_sessao, (string) $token_post)) {
    $_SESSION['flash'] = [
        'tipo' => 'erro',
        'codigo' => 'csrf_invalido'
    ];
    header('Location: index.php');
    exit;
}

// Entrada: normaliza e valida (não altera a senha – não usar trim em senha)
$usuario = trim((string) ($_POST['usuario'] ?? ''));
$senha = (string) ($_POST['senha'] ?? '');
$lembrar = isset($_POST['lembrar']) ? 'Sim' : 'Não';
$_SESSION['lembrar_login'] = $lembrar;

// Validação de formato: login é email
if (!filter_var($usuario, FILTER_VALIDATE_EMAIL) || strlen($usuario) > 100) {
    $_SESSION['flash'] = [
        'tipo' => 'erro',
        'codigo' => 'login_invalido'
    ];
    header('Location: index.php');
    exit;
}
// Limite de tamanho na senha (bcrypt usa até 72 bytes; evita payload gigante)
if (strlen($senha) > 256 || $senha === '') {
    $_SESSION['flash'] = [
        'tipo' => 'erro',
        'codigo' => 'login_invalido'
    ];
    header('Location: index.php');
    exit;
}

// Busca usuário por email (sempre com prepared statement – proteção contra SQL injection)
$stmt = $pdo->prepare("
    SELECT id, nome, email, senha, nivel, ativo, empresa, foto
    FROM usuarios
    WHERE email = :usuario
    LIMIT 1
");
$stmt->execute([':usuario' => $usuario]);
$user = $stmt->fetch();

// Mesmo retorno para "não existe" e "senha errada" (evita enumerar usuários)
if (!$user) {
    $_SESSION['flash'] = [
        'tipo' => 'erro',
        'codigo' => 'login_invalido'
    ];
    header('Location: index.php');
    exit;
}

if (strtoupper(trim((string) ($user['ativo'] ?? ''))) !== 'SIM') {
    $_SESSION['flash'] = [
        'tipo' => 'aviso',
        'codigo' => 'usuario_inativo'
    ];
    header('Location: index.php');
    exit;
    
}

if (!password_verify($senha, $user['senha'])) {
    $_SESSION['flash'] = [
        'tipo' => 'erro',
        'codigo' => 'login_invalido'
    ];
    header('Location: index.php');
    exit;
}

// Regenera ID da sessão após login (mitiga session fixation)
session_regenerate_id(true);

// Grava na sessão apenas o necessário; nome sem tags para reduzir risco de XSS ao exibir
$_SESSION['id'] = (int) $user['id'];
$_SESSION['nome'] = strip_tags($user['nome'] ?? '');
$_SESSION['email'] = filter_var($user['email'], FILTER_SANITIZE_EMAIL);
$_SESSION['nivel'] = strip_tags($user['nivel'] ?? '');
$_SESSION['id_empresa'] = (int) $user['empresa'];
$_SESSION['foto'] = $user['foto'] ?? 'sem_foto.webp';
$_SESSION['token_login'] = (int) $user['id'];

// 🔥 LOG DE LOGIN (ACESSO)
registrarLog(
    $pdo,
    'login',
    'usuarios',
    (int)$_SESSION['id'],
    'Login realizado com sucesso'
);



// =========================
// AÇÕES (CRUD GERAL)
// =========================
$_SESSION['acoes'] = [];

// Admin sempre tem tudo
if (trim((string)$_SESSION['nivel']) === 'Administrador') {
    $_SESSION['acoes'] = [
        'criar'  => true,
        'editar' => true,
        'excluir'=> true,
    ];
} else {
    // Usuário comum: busca no banco
    $stmtA = $pdo->prepare("SELECT acao FROM usuarios_acoes WHERE usuario_id = :id");
    $stmtA->execute([':id' => (int)$_SESSION['id']]);
    $acoes = $stmtA->fetchAll(PDO::FETCH_COLUMN);

    foreach (($acoes ?: []) as $a) {
        $a = trim((string)$a);
        if ($a !== '') $_SESSION['acoes'][$a] = true;
    }
}




// Remove token CSRF de uso único
unset($_SESSION['csrf_token_login']);

header('Location: painel/');
exit;

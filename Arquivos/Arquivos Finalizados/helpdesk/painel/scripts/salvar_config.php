<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
  echo json_encode(['ok' => false, 'msg' => 'Sessão expirada.']);
  exit;
}

require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../funcoes/crypto.php';



function resp($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>$ok, 'msg'=>$msg], $extra));
  exit;
}


if($modo_teste == 'Sim'){
  resp(false, 'Você está em modo teste, alguns recursos ficam desabilitados!');
}

/**
 * Remove imagem antiga (exceto sem_foto.webp)
 */
function excluirImagemAntiga(?string $arquivoAntigo, string $uploadsDirAbs): void {
  $arquivoAntigo = trim((string)$arquivoAntigo);
  if ($arquivoAntigo === '' || $arquivoAntigo === 'sem_foto.webp') return;

  $path = rtrim($uploadsDirAbs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $arquivoAntigo;
  if (is_file($path)) @unlink($path);
}

/**
 * Salva imagem enviada e retorna o nome do arquivo salvo (somente o nome).
 * Aceita: png/jpg/jpeg/webp | até 2MB
 */
function salvarImagem(string $campo, string $uploadsDirAbs, string $prefixo): ?string {
  if (!isset($_FILES[$campo]) || empty($_FILES[$campo]['name'])) return null;

  $f = $_FILES[$campo];
  if (!empty($f['error'])) {
    throw new RuntimeException("Falha no upload ({$campo}).");
  }

  if ($f['size'] > 2 * 1024 * 1024) {
    throw new RuntimeException("Arquivo muito grande ({$campo}).");
  }

  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  $permitidas = ['png','jpg','jpeg','webp'];
  if (!in_array($ext, $permitidas, true)) {
    throw new RuntimeException("Formato inválido ({$campo}).");
  }

  $nome = $prefixo . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = rtrim($uploadsDirAbs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nome;

  if (!move_uploaded_file($f['tmp_name'], $dest)) {
    throw new RuntimeException("Não foi possível salvar ({$campo}).");
  }

  return $nome;
}

// ===== POST =====
$id      = (int)($_POST['id'] ?? 0);
$empresa = (int)($_POST['empresa'] ?? 0);

// Campos
$nome_sistema     = trim($_POST['nome_sistema'] ?? '');
$telefone_sistema = trim($_POST['telefone_sistema'] ?? '');
$email_sistema    = trim($_POST['email_sistema'] ?? '');
$endereco         = trim($_POST['endereco'] ?? '');

$cor_primaria     = trim($_POST['cor_primaria'] ?? '');
$cor_secundaria   = trim($_POST['cor_secundaria'] ?? '');

$smtp_host        = trim($_POST['smtp_host'] ?? '');
$smtp_porta       = (int)($_POST['smtp_porta'] ?? 0);
$smtp_senha_nova  = trim($_POST['smtp_senha'] ?? '');
$smtp_seguranca   = trim($_POST['smtp_seguranca'] ?? '');
$api_whatsapp   = trim($_POST['api_whatsapp'] ?? '');
$instancia_whatsapp   = trim($_POST['instancia_whatsapp'] ?? '');
$token_whatsapp   = trim($_POST['token_whatsapp'] ?? '');
$url_api   = trim($_POST['url_api'] ?? '');
$token_ia   = trim($_POST['token_ia'] ?? '');
$api_ia   = trim($_POST['api_ia'] ?? '');
$dias_excluir_logs   = trim($_POST['dias_excluir_logs'] ?? '');

// nomes atuais (hidden na modal)
$logo_atual  = trim($_POST['logo_atual'] ?? '');
$icone_atual = trim($_POST['icone_atual'] ?? '');

$fallback = 'sem_foto.webp';

// ===== Validações =====
if ($nome_sistema === '') resp(false, 'Informe o nome do sistema.');
if ($email_sistema !== '' && !filter_var($email_sistema, FILTER_VALIDATE_EMAIL)) resp(false, 'E-mail do sistema inválido.');
if ($smtp_porta && ($smtp_porta < 1 || $smtp_porta > 65535)) resp(false, 'Porta SMTP inválida.');

if ($cor_primaria !== '' && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $cor_primaria)) resp(false, 'Cor primária inválida.');
if ($cor_secundaria !== '' && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $cor_secundaria)) resp(false, 'Cor secundária inválida.');

// ===== Criptografa SMTP senha se veio preenchida =====
$smtp_senha_cript = '';
if ($smtp_senha_nova !== '') {
  try {
    $smtp_senha_cript = smtp_encrypt($smtp_senha_nova);
  } catch (Throwable $e) {
    resp(false, 'Erro ao criptografar a senha SMTP.', ['debug' => $e->getMessage()]);
  }
}

try {
  // pasta uploads absoluta
  $uploadsDirAbs = realpath(__DIR__ . '/../../uploads');
  if ($uploadsDirAbs === false) {
    throw new RuntimeException('Pasta uploads não encontrada.');
  }

  // garante fallback existe
  if (!is_file($uploadsDirAbs . DIRECTORY_SEPARATOR . $fallback)) {
    // não vamos quebrar por isso, mas te dá um debug bom
    // (se quiser quebrar, troque por throw)
  }

  // pega config atual do banco (para deletar arquivo antigo com segurança)
  $st0 = $pdo->prepare("SELECT logo, icone FROM config WHERE id = :id AND empresa = :empresa LIMIT 1");
  $st0->execute([':id' => $id, ':empresa' => $empresa]);
  $cfgAtual = $st0->fetch() ?: ['logo' => '', 'icone' => ''];

  // ===== Upload Logo / Ícone =====
  $novoLogo  = null;
  $novoIcone = null;

  if (!empty($_FILES['logo']['name'] ?? '')) {
    // salva novo
    $novoLogo = salvarImagem('logo', $uploadsDirAbs, 'logo');
    // apaga antigo (do banco)
    excluirImagemAntiga($cfgAtual['logo'] ?? '', $uploadsDirAbs);
  }

  if (!empty($_FILES['icone']['name'] ?? '')) {
    $novoIcone = salvarImagem('icone', $uploadsDirAbs, 'icone');
    excluirImagemAntiga($cfgAtual['icone'] ?? '', $uploadsDirAbs);
  }

  // se não veio upload, mantém o atual (hidden) ou fallback
  $logo_final  = $novoLogo  ?? ($logo_atual  !== '' ? $logo_atual  : ($cfgAtual['logo']  ?? $fallback));
  $icone_final = $novoIcone ?? ($icone_atual !== '' ? $icone_atual : ($cfgAtual['icone'] ?? $fallback));

  // fallback definitivo se ainda ficou vazio
  if ($logo_final === '')  $logo_final  = $fallback;
  if ($icone_final === '') $icone_final = $fallback;

  // ===== UPDATE único (dinâmico) =====
  $set = [
    "nome_sistema = :nome_sistema",
    "telefone_sistema = :telefone_sistema",
    "email_sistema = :email_sistema",
    "endereco = :endereco",
    "cor_primaria = :cor_primaria",
    "cor_secundaria = :cor_secundaria",
    "smtp_host = :smtp_host",
    "smtp_porta = :smtp_porta",
    "smtp_seguranca = :smtp_seguranca",
    "logo = :logo",
    "icone = :icone",
    "api_whatsapp = :api_whatsapp",
    "token_whatsapp = :token_whatsapp",
    "instancia_whatsapp = :instancia_whatsapp",
    "url_api = :url_api",
    "token_ia = :token_ia",
    "api_ia = :api_ia",
    "dias_excluir_logs = :dias_excluir_logs"
  ];

  $params = [
    ':nome_sistema' => $nome_sistema,
    ':telefone_sistema' => $telefone_sistema,
    ':email_sistema' => $email_sistema,
    ':endereco' => $endereco,
    ':cor_primaria' => $cor_primaria,
    ':cor_secundaria' => $cor_secundaria,
    ':smtp_host' => $smtp_host,
    ':smtp_porta' => $smtp_porta,
    ':smtp_seguranca' => $smtp_seguranca,
    ':logo' => $logo_final,
    ':icone' => $icone_final,
    ':id' => $id,
    ':empresa' => $empresa,
    ':api_whatsapp' => $api_whatsapp,
    ':token_whatsapp' => $token_whatsapp,
    ':instancia_whatsapp' => $instancia_whatsapp,
    ':url_api' => $url_api,
    ':token_ia' => $token_ia,
    ':api_ia' => $api_ia,
    ':dias_excluir_logs' => $dias_excluir_logs
  ];

  // só atualiza senha se veio nova
  if ($smtp_senha_nova !== '') {
    $set[] = "smtp_senha = :smtp_senha";
    $params[':smtp_senha'] = $smtp_senha_cript;
  }

  $sql = "UPDATE config SET " . implode(", ", $set) . " WHERE id = :id AND empresa = :empresa LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute($params);

  // recarrega config para uso imediato
  $stmt = $pdo->query("SELECT * FROM config WHERE empresa = 0 LIMIT 1");
  $_SESSION['config'] = $stmt->fetch() ?: [];
  $_SESSION['config_empresa'] = 0;

  resp(true, 'Configurações salvas com sucesso!', [
    'logo' => $logo_final,
    'icone' => $icone_final,
  ]);

} catch (Throwable $e) {
  resp(false, 'Erro ao salvar configurações.', ['debug' => $e->getMessage()]);
}

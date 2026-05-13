<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../conexao.php';

function resp($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

if (empty($_SESSION['cliente_id'])) {
  resp(false, 'Sessão expirada.');
}

if (($modo_teste ?? 'Não') === 'Sim') {
  resp(false, 'Você está em modo teste, alguns recursos ficam desabilitados!');
}

function onlyDigits($s) {
  return preg_replace('/\D+/', '', (string)$s);
}

$cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
$id_post    = (int)($_POST['id'] ?? 0);

// segurança: só edita o próprio cliente
if ($id_post <= 0 || $id_post !== $cliente_id) {
  resp(false, 'Ação não permitida.');
}

// inputs
$nome        = trim($_POST['nome'] ?? '');
$telefone    = trim($_POST['telefone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$cpf_cnpj    = trim($_POST['cpf_cnpj'] ?? '');
$endereco    = trim($_POST['endereco'] ?? '');
$numero      = trim($_POST['numero'] ?? '');
$cep         = trim($_POST['cep'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');
$bairro      = trim($_POST['bairro'] ?? '');
$cidade      = trim($_POST['cidade'] ?? '');
$estado      = strtoupper(trim($_POST['estado'] ?? ''));
$obs         = trim($_POST['observacoes'] ?? '');
$senhaNova   = (string)($_POST['senha'] ?? '');

// validações
if ($nome === '') resp(false, 'Informe o nome.');

if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120)) {
  resp(false, 'E-mail inválido.');
}

$cpf_cnpj_dig = onlyDigits($cpf_cnpj);
if ($cpf_cnpj_dig !== '') {
  if (strlen($cpf_cnpj_dig) !== 11 && strlen($cpf_cnpj_dig) !== 14) {
    resp(false, 'CPF/CNPJ inválido.');
  }
}

$telefone_dig = onlyDigits($telefone);
if ($telefone_dig !== '') {
  if (strlen($telefone_dig) < 10 || strlen($telefone_dig) > 13) {
    resp(false, 'Telefone inválido.');
  }
}

$cep_dig = onlyDigits($cep);
if ($cep_dig !== '' && strlen($cep_dig) !== 8) {
  resp(false, 'CEP inválido.');
}

$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
if ($estado !== '' && !in_array($estado, $ufs, true)) {
  resp(false, 'UF inválida.');
}

// senha opcional
$senhaHash = null;
if (trim($senhaNova) !== '') {
  if (mb_strlen($senhaNova) < 3) resp(false, 'A senha deve ter pelo menos 3 caracteres.');
  if (mb_strlen($senhaNova) > 256) resp(false, 'Senha muito longa.');
  $senhaHash = password_hash($senhaNova, PASSWORD_DEFAULT);
  if (!$senhaHash) resp(false, 'Erro ao gerar hash da senha.');
}

// ========================
// FOTO (upload + remove antiga)
// ========================
$novoNomeFoto = null;

try {
  // pega foto atual do banco (pra remover depois)
  $stOld = $pdo->prepare("SELECT foto FROM clientes WHERE id = :id LIMIT 1");
  $stOld->execute([':id' => $cliente_id]);
  $oldRow = $stOld->fetch(PDO::FETCH_ASSOC);
  $fotoAntiga = trim((string)($oldRow['foto'] ?? ''));
} catch (Throwable $e) {
  $fotoAntiga = '';
}

// se enviou arquivo
if (!empty($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {

  if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    resp(false, 'Erro no upload da foto.');
  }

  $tmp  = $_FILES['foto']['tmp_name'];
  $size = (int)$_FILES['foto']['size'];

  if ($size > 2 * 1024 * 1024) resp(false, 'A foto deve ter no máximo 2MB.');

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $tmp);
  finfo_close($finfo);

  $ext = '';
  if ($mime === 'image/jpeg') $ext = 'jpg';
  if ($mime === 'image/png')  $ext = 'png';
  if ($mime === 'image/webp') $ext = 'webp';
  if ($ext === '') resp(false, 'Formato de imagem inválido. Use JPG, PNG ou WEBP.');

  // pasta uploads/clientes
  $dir = realpath(__DIR__ . '/../../uploads/clientes');
  if (!$dir) {
    @mkdir(__DIR__ . '/../../uploads/clientes', 0775, true);
    $dir = realpath(__DIR__ . '/../../uploads/clientes');
  }
  if (!$dir) resp(false, 'Pasta de upload não encontrada: uploads/clientes.');

  // nome seguro
  $novoNomeFoto = 'c' . $cliente_id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = $dir . DIRECTORY_SEPARATOR . $novoNomeFoto;

  if (!move_uploaded_file($tmp, $dest)) {
    resp(false, 'Não foi possível salvar a foto.');
  }

  // remove antiga (após salvar nova)
  if ($fotoAntiga !== '' && $fotoAntiga !== 'sem_foto.webp') {
    $safeOld = str_replace(['..', '/', '\\'], '', $fotoAntiga);
    $oldPath = $dir . DIRECTORY_SEPARATOR . $safeOld;
    if (is_file($oldPath)) @unlink($oldPath);
  }
}

try {
  // evita e-mail duplicado entre clientes
  if ($email !== '') {
    $stDup = $pdo->prepare("
      SELECT id
      FROM clientes
      WHERE email = :email
        AND id <> :id
      LIMIT 1
    ");
    $stDup->execute([
      ':email' => $email,
      ':id'    => $cliente_id
    ]);
    if ($stDup->fetch()) {
      resp(false, 'Este e-mail já está cadastrado para outro cliente.');
    }
  }

  $fields = [
    'nome'        => $nome,
    'telefone'    => ($telefone !== '' ? $telefone : null),
    'email'       => ($email !== '' ? $email : null),
    'cpf_cnpj'    => ($cpf_cnpj !== '' ? $cpf_cnpj : null),
    'endereco'    => ($endereco !== '' ? $endereco : null),
    'numero'      => ($numero !== '' ? $numero : null),
    'cep'         => ($cep !== '' ? $cep : null),
    'complemento' => ($complemento !== '' ? $complemento : null),
    'bairro'      => ($bairro !== '' ? $bairro : null),
    'cidade'      => ($cidade !== '' ? $cidade : null),
    'estado'      => ($estado !== '' ? $estado : null),
    'observacoes' => ($obs !== '' ? $obs : null),
  ];

  if ($senhaHash) {
    $fields['senha'] = $senhaHash;
  }

  if ($novoNomeFoto) {
    $fields['foto'] = $novoNomeFoto;
  }

  $setParts = [];
  $params = [];
  foreach ($fields as $k => $v) {
    $setParts[] = "{$k} = :{$k}";
    $params[":{$k}"] = $v;
  }
  $params[':id'] = $cliente_id;

  $sql = "UPDATE clientes SET " . implode(', ', $setParts) . " WHERE id = :id LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute($params);

  // atualiza sessão do cliente
  $_SESSION['cliente_nome']  = $nome;
  $_SESSION['cliente_email'] = $email;
  if ($novoNomeFoto) {
    $_SESSION['cliente_foto'] = $novoNomeFoto;
  }

  resp(true, 'Perfil salvo com sucesso!', [
    'foto' => $novoNomeFoto ?: null
  ]);

} catch (Throwable $e) {
  resp(false, 'Erro ao salvar perfil: ' . $e->getMessage());
}
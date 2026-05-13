<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['id'])) {
  echo json_encode(['ok' => false, 'msg' => 'Sessão expirada.']);
  exit;
}

require_once __DIR__ . '/../../conexao.php';



function resp($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>$ok, 'msg'=>$msg], $extra));
  exit;
}

if($modo_teste == 'Sim'){
  resp(false, 'Você está em modo teste, alguns recursos ficam desabilitados!');
}

function onlyDigits($s) {
  return preg_replace('/\D+/', '', (string)$s);
}

$usuario_id = (int)($_SESSION['id']);
$id_post    = (int)($_POST['id'] ?? 0);

// segurança: só edita o próprio usuário (por enquanto)
if ($id_post !== $usuario_id) resp(false, 'Ação não permitida.');

$nome        = trim($_POST['nome'] ?? '');
$telefone    = trim($_POST['telefone'] ?? '');
$email       = trim($_POST['email'] ?? '');
$cpf         = trim($_POST['cpf'] ?? '');
$endereco    = trim($_POST['endereco'] ?? '');
$numero      = trim($_POST['numero'] ?? '');
$cep         = trim($_POST['cep'] ?? '');
$complemento = trim($_POST['complemento'] ?? '');
$bairro      = trim($_POST['bairro'] ?? '');
$cidade      = trim($_POST['cidade'] ?? '');
$estado      = strtoupper(trim($_POST['estado'] ?? ''));
$senhaNova   = trim($_POST['senha'] ?? '');

if ($nome === '') resp(false, 'Informe o nome.');
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) resp(false, 'E-mail inválido.');

$cpf = onlyDigits($cpf);
if ($cpf !== '' && strlen($cpf) !== 11) resp(false, 'CPF inválido.');

$cep = onlyDigits($cep);
if ($cep !== '' && strlen($cep) !== 8) resp(false, 'CEP inválido.');

// UF (select)
$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
if ($estado !== '' && !in_array($estado, $ufs, true)) resp(false, 'UF inválida.');

// ===== Upload foto =====
$novoNomeFoto = null;

if (!empty($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

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

  $dir = realpath(__DIR__ . '/../../uploads/perfil');
  if (!$dir) {
    // tenta criar
    @mkdir(__DIR__ . '/../../uploads/perfil', 0775, true);
    $dir = realpath(__DIR__ . '/../../uploads/perfil');
  }
  if (!$dir) resp(false, 'Pasta de upload não encontrada.');

  $novoNomeFoto = 'u' . $usuario_id . '_' . time() . '.' . $ext;
  $dest = $dir . DIRECTORY_SEPARATOR . $novoNomeFoto;

  if (!move_uploaded_file($tmp, $dest)) {
    resp(false, 'Não foi possível salvar a foto.');
  }

  // opcional: remover foto antiga
  $stOld = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id LIMIT 1");
  $stOld->execute([':id'=>$usuario_id]);
  $old = $stOld->fetch();
  if ($old && !empty($old['foto']) && $old['foto'] !== 'sem_foto.webp') {
    $oldPath = $dir . DIRECTORY_SEPARATOR . $old['foto'];
    if (is_file($oldPath)) {
        @unlink($oldPath);
    }
}

}

try {
  // monta update dinâmico
  $fields = [
    'nome' => $nome,
    'telefone' => $telefone,
    'email' => $email,
    'cpf' => $cpf,
    'endereco' => $endereco,
    'numero' => $numero,
    'cep' => $cep,
    'complemento' => $complemento,
    'bairro' => $bairro,
    'cidade' => $cidade,
    'estado' => $estado,
  ];

  if ($novoNomeFoto) {
    $fields['foto'] = $novoNomeFoto;
  }

  // senha opcional
  $setSenha = '';
  if ($senhaNova !== '') {
    if (strlen($senhaNova) < 3) resp(false, 'A senha deve ter pelo menos 3 caracteres.');
    $hash = password_hash($senhaNova, PASSWORD_DEFAULT);
    $fields['senha'] = $hash;
  }

  $setParts = [];
  $params = [];
  foreach ($fields as $k => $v) {
    $setParts[] = "$k = :$k";
    $params[":$k"] = $v;
  }
  $params[':id'] = $usuario_id;

  $sql = "UPDATE usuarios SET " . implode(', ', $setParts) . " WHERE id = :id LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute($params);

  // atualiza sessão (nome/email) para refletir na topbar
  $_SESSION['nome']  = $nome;
  $_SESSION['email'] = $email;

  resp(true, 'Perfil salvo com sucesso!');
} catch (Exception $e) {
  resp(false, 'Erro ao salvar perfil: ' . $e->getMessage());
}

<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>(bool)$ok, 'msg'=>(string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {

 

  $uid = 0;


  $chamado_id = (int)($_POST['chamado_id'] ?? 0);
  if ($chamado_id <= 0) out(false, 'Chamado inválido.');

  // Confere chamado + pega cliente_id (se existir)
  $stC = $pdo->prepare("SELECT id, cliente_id, protocolo FROM chamados WHERE id = :id LIMIT 1");
  $stC->execute([':id' => $chamado_id]);
  $ch = $stC->fetch(PDO::FETCH_ASSOC);
  if (!$ch) out(false, 'Chamado não encontrado.');

  $cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
  $protocolo  = (string)($ch['protocolo'] ?? '');

  // Arquivo
  if (!isset($_FILES['arquivo']) || !is_array($_FILES['arquivo'])) {
    out(false, 'Envie um arquivo.');
  }

  $f = $_FILES['arquivo'];

  if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    out(false, 'Falha no upload. Código: ' . (int)($f['error'] ?? -1));
  }

  $tmp  = $f['tmp_name'] ?? '';
  $name = (string)($f['name'] ?? '');
  $size = (int)($f['size'] ?? 0);

  if (!is_uploaded_file($tmp)) out(false, 'Arquivo inválido.');
  if ($size <= 0) out(false, 'Arquivo vazio.');

  // Limite (ajuste)
  $MAX = 12 * 1024 * 1024; // 12MB
  if ($size > $MAX) out(false, 'Arquivo muito grande. Máx: 12MB.');

  // Sanitiza nome + extensão
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $base = pathinfo($name, PATHINFO_FILENAME);

  // extensões permitidas (ajuste)
  $permitidas = [
    'pdf','doc','docx','xls','xlsx','csv','txt',
    'png','jpg','jpeg','webp','gif',
    'zip','rar'
  ];
  if (!$ext || !in_array($ext, $permitidas, true)) {
    out(false, 'Tipo de arquivo não permitido.');
  }

  // Pasta destino: /helpdesk/uploads/arquivos/
  $dir = realpath(__DIR__ . '/../../../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'arquivos';
  if (!$dir) out(false, 'Pasta uploads não encontrada.');

  if (!is_dir($dir)) {
    if (!mkdir($dir, 0775, true)) out(false, 'Não foi possível criar a pasta de uploads.');
  }

  // nome final único (sem confiar no original)
  $token = bin2hex(random_bytes(8));
  $arquivoFinal = 'ch_' . $chamado_id . '_' . date('Ymd_His') . '_' . $token . '.' . $ext;

  $dest = $dir . DIRECTORY_SEPARATOR . $arquivoFinal;

  if (!move_uploaded_file($tmp, $dest)) {
    out(false, 'Não foi possível salvar o arquivo.');
  }

// dados do anexo
$nome = trim($_POST['nome'] ?? '');

// se não informar nome, usa nome original (sem extensão)
if ($nome === '') {
  $nome = pathinfo($name, PATHINFO_FILENAME);
}

// limita tamanho
if (mb_strlen($nome) > 255) {
  $nome = mb_substr($nome, 0, 255);
}

// Insere no banco
$st = $pdo->prepare("
  INSERT INTO chamados_anexos 
  (chamado_id, cliente_id, arquivo, nome, criado_em, enviado_por)
  VALUES 
  (:chamado_id, :cliente_id, :arquivo, :nome, NOW(), 'cliente')
");

$st->execute([
  ':chamado_id' => $chamado_id,
  
  ':cliente_id' => ($cliente_id > 0 ? $cliente_id : null),
  ':arquivo'    => $arquivoFinal,
  ':nome'       => $nome
]);

  $anexoId = (int)$pdo->lastInsertId();

  out(true, 'Anexo enviado com sucesso.', [
    'id' => $anexoId,
    'arquivo' => $arquivoFinal,
    'nome' => $nome,
    'criado_em' => date('Y-m-d H:i:s')
  ]);

  

} catch (Throwable $e) {
  out(false, 'Erro: ' . $e->getMessage());
}
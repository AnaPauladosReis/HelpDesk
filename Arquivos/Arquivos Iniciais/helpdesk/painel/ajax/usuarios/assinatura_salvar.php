<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>(bool)$ok,'msg'=>(string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

function slugNomeArquivo(string $nome): string {
  $nome = trim($nome);
  if ($nome === '') return 'usuario';

  // remove acentos (se falhar, continua com o original)
  $conv = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome);
  if ($conv !== false) $nome = $conv;

  $nome = strtolower($nome);
  $nome = preg_replace('/[^a-z0-9]+/', '_', $nome);
  $nome = trim($nome, '_');

  // evita nomes gigantes
  if (strlen($nome) > 40) $nome = substr($nome, 0, 40);

  return $nome ?: 'usuario';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Requisição inválida.');

$id = (int)($_POST['id'] ?? 0);
$dataUrl = (string)($_POST['assinatura'] ?? '');

if ($id <= 0) out(false, 'ID inválido.');
if ($dataUrl === '') out(false, 'Assinatura vazia.');

if (!preg_match('#^data:image/(png|jpeg);base64,#', $dataUrl, $m)) {
  out(false, 'Formato inválido (envie PNG/JPEG base64).');
}
$ext = ($m[1] === 'jpeg') ? 'jpg' : 'png';

$base64 = preg_replace('#^data:image/(png|jpeg);base64,#', '', $dataUrl);
$bin = base64_decode($base64, true);
if ($bin === false) out(false, 'Base64 inválido.');

if (strlen($bin) > 1024 * 1024 * 2) { // 2MB
  out(false, 'Assinatura muito grande (máx 2MB).');
}

// busca nome + assinatura antiga
$st = $pdo->prepare("SELECT nome, assinatura FROM usuarios WHERE id = :id LIMIT 1");
$st->execute([':id'=>$id]);
$row = $st->fetch(PDO::FETCH_ASSOC);
if (!$row) out(false, 'Usuário não encontrado.');

$nomeUsuario = (string)($row['nome'] ?? '');
$old = (string)($row['assinatura'] ?? '');

// caminho físico: raiz/uploads/assinaturas
$root = dirname(__DIR__, 3); // painel/ajax/usuarios -> raiz do projeto
$dir = $root . '/uploads/assinaturas';

if (!is_dir($dir)) {
  if (!mkdir($dir, 0775, true)) out(false, 'Não foi possível criar a pasta de assinaturas.');
}

// nome do arquivo com nome + id
$slug = slugNomeArquivo($nomeUsuario);
$nomeArquivo = "assinatura_{$slug}_{$id}_" . date('Ymd_His') . "." . $ext;
$path = $dir . '/' . $nomeArquivo;

if (file_put_contents($path, $bin) === false) {
  out(false, 'Falha ao salvar arquivo no servidor.');
}

// remove arquivo antigo (se existir)
if ($old) {
  $oldPath = $dir . '/' . basename($old);
  if (is_file($oldPath)) @unlink($oldPath);
}

$up = $pdo->prepare("UPDATE usuarios SET assinatura = :a WHERE id = :id LIMIT 1");
$up->execute([':a' => $nomeArquivo, ':id' => $id]);

// URL pública pro painel
$url = '../uploads/assinaturas/' . rawurlencode($nomeArquivo);
out(true, 'Assinatura salva!', ['arquivo' => $nomeArquivo, 'url' => $url]);

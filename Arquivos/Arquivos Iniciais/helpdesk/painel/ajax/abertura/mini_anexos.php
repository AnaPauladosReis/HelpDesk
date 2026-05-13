<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: text/html; charset=utf-8');

function esc($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmtData($dt){
  $dt = (string)$dt;
  if ($dt === '' || $dt === '0000-00-00 00:00:00') return '';
  $ts = strtotime($dt);
  if (!$ts) return '';
  return date('d/m/Y H:i', $ts);
}

/**
 * Ajuste aqui se sua URL base for diferente.
 * Como seus anexos ficam em /helpdesk/uploads/arquivos/...
 */
$baseUrlUploads = '../uploads/arquivos/'; // relativo ao ajax/abertura
$baseUrlIcons   = '../uploads/arquivos/'; // seus ícones png também estão aí (excel,pdf,word,rar,...)

/** mapeia extensão -> ícone */
function iconFile($ext){
  $ext = strtolower($ext);
  $map = [
    'pdf'  => 'pdf.png',
    'doc'  => 'word.png',
    'docx' => 'word.png',
    'xls'  => 'excel.png',
    'xlsx' => 'excel.png',
    'csv'  => 'excel.png',
    'rar'  => 'rar.png',
    'zip'  => 'rar.png',
    'txt'  => 'xml.png',
    'xml'  => 'xml.png',
  ];
  return $map[$ext] ?? 'sem-foto.png';
}

$chamado_id = (int)($_GET['chamado_id'] ?? 0);
if ($chamado_id <= 0){
  echo '<div class="text-muted small px-2 py-1">Chamado inválido.</div>';
  exit;
}

$st = $pdo->prepare("
  SELECT id, arquivo, nome, criado_em
  FROM chamados_anexos
  WHERE chamado_id = :id
  ORDER BY id DESC
  LIMIT 30
");
$st->execute([':id' => $chamado_id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

if (!count($rows)){
  echo '<div class="text-muted small px-2 py-1">Sem anexos.</div>';
  exit;
}

foreach ($rows as $r){

  $arquivo = (string)($r['arquivo'] ?? '');
  if ($arquivo === '') continue;

  $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
  $isImg = in_array($ext, ['png','jpg','jpeg','gif','webp'], true);

  $fileUrl = $baseUrlUploads . rawurlencode($arquivo);
  $data = fmtData($r['criado_em'] ?? '');
  $nome = trim((string)($r['nome'] ?? ''));

  if ($nome === '') {
    $nome = pathinfo($arquivo, PATHINFO_FILENAME);
  }

  // thumb/ícone (clicável)
  if ($isImg) {
    $thumb = "<img src='".esc($fileUrl)."' alt='' style='width:100%;height:100%;object-fit:cover'>";
  } else {
    $ico = $baseUrlIcons . iconFile($ext);
    $thumb = "<img src='".esc($ico)."' alt='' style='width:18px;height:18px;object-fit:contain'>";
  }

  echo '
  <div class="mini-anexo" title="'.esc($nome).'">
    <a href="'.esc($fileUrl).'" target="_blank" rel="noopener" class="mini-ico">
      '.$thumb.'
    </a>
    <div class="mini-meta">
      <a href="'.esc($fileUrl).'" target="_blank" rel="noopener">
        <div class="mini-nome">'.esc($nome).'</div>
      </a>
      <div class="mini-data">'.esc($data ?: '—').'</div>
    </div>
  </div>
  ';
}
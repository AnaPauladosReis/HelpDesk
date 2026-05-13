<?php
@session_start();
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

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

function fmtSize($bytes){
  $bytes = (int)$bytes;
  if ($bytes <= 0) return '';
  $kb = $bytes / 1024;
  if ($kb < 1024) return number_format($kb, 1, ',', '.') . ' KB';
  return number_format($kb/1024, 2, ',', '.') . ' MB';
}

function iconUrl($ext){
  $ext = strtolower($ext);

  // imagens: thumb será o próprio arquivo
  if (in_array($ext, ['png','jpg','jpeg','gif','webp'], true)) return '';

  $map = [
    'pdf'  => 'pdf.png',
    'doc'  => 'word.png',
    'docx' => 'word.png',
    'xls'  => 'excel.png',
    'xlsx' => 'excel.png',
    'csv'  => 'excel.png',
    'xml'  => 'xml.png',
    'zip'  => 'rar.png',
    'rar'  => 'rar.png',
    'txt'  => 'word.png',
  ];

  return $map[$ext] ?? 'sem-foto.png';
}

/**
 * ==========================================================
 * IDENTIFICAÇÃO DO "ATOR" LOGADO (usuário painel / cliente painel)
 * - Hoje você usa painel (usuarios). Amanhã, no painel do cliente,
 *   basta preencher $_SESSION['cliente_id'] ou similar.
 * ==========================================================
 */
$usuario_logado = (int)($_SESSION['id'] ?? 0);                 // painel admin/suporte
$cliente_logado = (int)($_SESSION['cliente_id'] ?? 0);         // futuro painel do cliente (ajuste depois)
$nivel          = (string)($_SESSION['nivel'] ?? '');
$isAdmin        = (mb_strtolower(trim($nivel)) === 'administrador');

/**
 * ==========================================================
 * CHAMADO
 * ==========================================================
 */
$chamado_id = (int)($_GET['chamado_id'] ?? 0);
if ($chamado_id <= 0) {
  echo "<div class='text-muted small p-3'>Chamado inválido.</div>";
  exit;
}

/**
 * ==========================================================
 * LISTA ANEXOS
 * ==========================================================
 */
$st = $pdo->prepare("
  SELECT
    a.id,
    a.chamado_id,
    a.usuario_id,
    a.cliente_id,
    a.arquivo,
    a.nome,
    a.criado_em,
    a.enviado_por,
    u.nome AS usuario_nome,
    c.nome AS cliente_nome
  FROM chamados_anexos a
  LEFT JOIN usuarios u ON u.id = a.usuario_id
  LEFT JOIN clientes c ON c.id = a.cliente_id
  WHERE a.chamado_id = :chamado_id
  ORDER BY a.id DESC
");
$st->execute([':chamado_id' => $chamado_id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

/**
 * URL base do arquivo
 * (saindo de painel/ajax/abertura -> ../uploads/arquivos)
 */
$baseUrl = "../uploads/arquivos/";

$uploadsAbs = realpath(__DIR__ . '/../../../uploads/arquivos'); // caminho físico

if (!count($rows)) {
  echo "<div class='text-muted small p-3'>Nenhum anexo enviado ainda.</div>";
  exit;
}

echo "<div class='p-2'>";

foreach ($rows as $r) {

  $id         = (int)$r['id'];
  $arquivo    = (string)($r['arquivo'] ?? '');
  $nome       = trim((string)($r['nome'] ?? ''));
  $enviado_por       = trim((string)($r['enviado_por'] ?? ''));

  $anexo_user_id   = ($r['usuario_id'] !== null) ? (int)$r['usuario_id'] : 0;
  $anexo_cliente_id= ($r['cliente_id'] !== null) ? (int)$r['cliente_id'] : 0;

  if ($nome === '') $nome = $arquivo;

  $criado = fmtData($r['criado_em'] ?? '');

  // quem anexou (exibição)
  $autor = '';
  if (!empty($r['usuario_nome'])) {
    $autor = (string)$r['usuario_nome'];
  } elseif (!empty($r['cliente_nome'])) {
    $autor = (string)$r['cliente_nome'];
  } else {
    $autor = '—';
  }

  $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
  $fileUrl = $baseUrl . rawurlencode($arquivo);

  // tamanho
  $tamTxt = '';
  if ($uploadsAbs) {
    $abs = $uploadsAbs . DIRECTORY_SEPARATOR . $arquivo;
    if (is_file($abs)) {
      $tamTxt = fmtSize(filesize($abs));
    }
  }

  $isImg = in_array($ext, ['png','jpg','jpeg','gif','webp'], true);

  if ($isImg) {
    $thumbInner = "<img src='".esc($fileUrl)."' alt='' style='width:100%;height:100%;object-fit:cover'>";
  } else {
    $ico = iconUrl($ext);
    $thumbInner = "<img src='".esc($baseUrl . $ico)."' alt='' style='width:28px;height:28px;object-fit:contain'>";
  }
  
  // deixa clicável (abre em nova aba)
  $thumbHtml = "
  <a href='".esc($fileUrl)."'
     target='_blank'
     rel='noopener'
     title='Abrir arquivo'
     class='anexo-thumb-link'>
     {$thumbInner}
  </a>
  ";

  /**
   * ==========================================================
   * PERMISSÃO DE EXCLUIR (UNIFICADA)
   * - Admin: sempre
   * - Usuário do painel: se ele anexou (usuario_id)
   * - Cliente: se ele anexou (cliente_id)
   *
   * Observação:
   * - Mantemos podeFazer('excluir') para o painel.
   * - No painel do cliente, você pode não ter permissoes.php;
   *   então basta trocar a condição futuramente ou criar uma função equivalente.
   * ==========================================================
   */
  $podeExcluir = false;

  // painel (usuarios) respeita permissões do sistema
  $permExcluirPainel = podeFazer('excluir');

  if ($isAdmin && $permExcluirPainel) {
    $podeExcluir = true;
  } else {

    // usuário painel é dono do anexo
    if ($permExcluirPainel && $usuario_logado > 0 && $anexo_user_id > 0 && $usuario_logado === $anexo_user_id) {
      $podeExcluir = true;
    }

    // futuro painel do cliente: cliente dono do anexo
    // (aqui não exige podeFazer porque cliente não usa isso; mas por enquanto,
    //  como cliente_logado ainda não existe no seu painel, não vai liberar nada)
    if ($cliente_logado > 0 && $anexo_cliente_id > 0 && $cliente_logado === $anexo_cliente_id) {
      $podeExcluir = true;
    }
  }

  if($enviado_por != 'cliente'){
    $podeExcluir = false;
  }

  echo "
  <div class='anexo-item'>
    <div class='anexo-ico'>{$thumbHtml}</div>

    <div class='anexo-meta'>
      <div class='anexo-nome' title='".esc($nome)."'>".esc($nome)."</div>
      <div class='anexo-sub'>
        <span><i class='bi bi-person'></i> ".esc($autor)."</span>
        ".($criado ? "<span><i class='bi bi-clock'></i> ".esc($criado)."</span>" : "")."
        ".($tamTxt ? "<span><i class='bi bi-hdd'></i> ".esc($tamTxt)."</span>" : "")."
      </div>
    </div>

    <div class='anexo-actions'>
      <a class='btn btn-outline-primary btn-sm'
         href='".esc($fileUrl)."'
         target='_blank'
         rel='noopener'
         title='Abrir/baixar'>
        <i class='bi bi-box-arrow-up-right'></i>
      </a>

      ".($podeExcluir ? "
      <button type='button'
              class='btn btn-outline-danger btn-sm'
              onclick='excluirAnexo(".(int)$id.", ".(int)$chamado_id.")'
              title='Excluir'>
        <i class='bi bi-trash'></i>
      </button>
      " : "")."
    </div>
  </div>";
}

echo "</div>";
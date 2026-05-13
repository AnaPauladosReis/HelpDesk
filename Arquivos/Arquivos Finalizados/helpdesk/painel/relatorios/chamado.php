<?php
/**
 * Relatório PDF - Detalhamento do Chamado (DomPDF)
 * Caminho: painel/relatorios/chamado.php
 *
 * Parâmetros (GET):
 *  - token (string)  [obrigatório]
 *  - id (int)        [opcional - se vier, é apenas conferência]
 */

@session_start();

require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

// CORE PDF
require_once __DIR__ . '/_core/pdf_helpers.php';
require_once __DIR__ . '/_core/pdf_layout.php';
require_once __DIR__ . '/_core/pdf_bootstrap.php';

function outDie($msg) {
  // se quiser, pode renderizar um HTML simples também
  die($msg);
}

/**
 * Valida token e retorna array: ['id'=>int, 'scope'=>string, 'ts'=>int]
 * Token gerado como: base64("id|scope|ts|hmac")
 */
function validarTokenRel($token, $secret) {
  $decoded = base64_decode((string)$token, true);
  if ($decoded === false || $decoded === '') return null;

  $parts = explode('|', $decoded);
  if (count($parts) !== 4) return null;

  [$id, $scope, $ts, $sig] = $parts;

  $id = (int)$id;
  $scope = (string)$scope;
  $ts = (int)$ts;
  $sig = (string)$sig;

  if ($id <= 0 || $scope === '' || $ts <= 0 || $sig === '') return null;

  $payload = $id . '|' . $scope . '|' . $ts;
  $calc = hash_hmac('sha256', $payload, $secret);

  // compara assinatura de forma segura
  if (!hash_equals($calc, $sig)) return null;

  // (opcional) expiração: 10 minutos
  // if (time() - $ts > 600) return null;

  return ['id' => $id, 'scope' => $scope, 'ts' => $ts];
}

// =========================
// TOKEN (GET)
// =========================
$token = trim((string)($_GET['token'] ?? ''));
if ($token === '') outDie('Token não informado.');

// segredo: ideal você definir em algum config (ex: conexao.php) como $token_secret
$secret = $GLOBALS['token_secret'] ?? ($token_secret ?? 'hugocursos_token_2026');

// valida
$dadosTok = validarTokenRel($token, $secret);
if (!$dadosTok) outDie('Token inválido.');

// scope esperado (você escolheu "chamado" no JS)
if (($dadosTok['scope'] ?? '') !== 'chamado') outDie('Token com escopo inválido.');

$chamadoId = (int)$dadosTok['id'];

// Se ainda vier id na URL, pode conferir
$idUrl = (int)($_GET['id'] ?? 0);
if ($idUrl > 0 && $idUrl !== $chamadoId) {
  outDie('ID não confere com o token.');
}

// =========================
// CONTEXTO (empresa / usuário logado)
// =========================
$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario_logado = (int)($_SESSION['id'] ?? 0);

// =========================
// ASSINATURA (usuário logado) - mantém igual seu modelo
// =========================
$assinaturaHtml = '';

if ($usuario_logado > 0) {
  // se você NÃO tem empresa ainda, remova o "AND empresa = :empresa"
  $stAss = $pdo->prepare("SELECT nome, assinatura FROM usuarios WHERE id = :id LIMIT 1");
  $stAss->execute([':id' => $usuario_logado]);
  $assRow = $stAss->fetch(PDO::FETCH_ASSOC) ?: [];

  $assArquivo = trim((string)($assRow['assinatura'] ?? ''));
  $assNome    = trim((string)($assRow['nome'] ?? ''));

  if ($assArquivo !== '') {
    $assPath = __DIR__ . '/../../uploads/assinaturas/' . $assArquivo;

    if (is_file($assPath)) {
      $assDataUri = imgToDataUri($assPath);

      if ($assDataUri) {
        $assinaturaHtml = '
  <div style="margin-top:24px;">
    <div style="border-top:1px solid #e5e7eb; padding-top:14px;"></div>

    <div style="text-align:center;">
      <div style="font-size:12px; color:#6b7280; margin-bottom:8px;">Assinatura</div>

      <img src="'.esc($assDataUri).'" alt="Assinatura"
           style="max-width:280px; height:auto; display:block; margin:0 auto 10px auto;">

      <div style="width:320px; margin:0 auto; border-top:1px solid #111827;"></div>

      <div style="margin-top:6px; font-size:12px; color:#111827;">
        '.esc($assNome ?: 'Usuário').'
      </div>
    </div>
  </div>
';
      }
    }
  }
}

// =========================
// BUSCA DADOS DO CHAMADO
// =========================
$st = $pdo->prepare("
  SELECT
    c.id,
    c.protocolo,
    c.assunto,
    c.descricao,
    c.prioridade,
    c.criado_em,
    c.atualizado_em,
    c.fechado_em,

    -- ✅ setor
    c.setor_id,
    se.nome AS setor_nome,

    cli.nome AS cliente_nome,
    cli.email AS cliente_email,
    cli.telefone AS cliente_telefone,

    ua.nome AS abertura_nome,
    ua.email AS abertura_email,

    ur.nome AS resp_nome,
    ur.email AS resp_email,
    ur.telefone AS resp_telefone,

    st.nome AS status_nome,
    st.cor AS status_cor,
    st.fechado AS status_fechado

  FROM chamados c
  LEFT JOIN setores se ON se.id = c.setor_id
  LEFT JOIN clientes cli ON cli.id = c.cliente_id
  INNER JOIN usuarios ua ON ua.id = c.usuario_abertura_id
  LEFT JOIN usuarios ur  ON ur.id = c.usuario_responsavel_id
  INNER JOIN chamados_status st ON st.id = c.status_id
  WHERE c.id = :id
  LIMIT 1
");
$st->execute([':id' => $chamadoId]);
$ch = $st->fetch(PDO::FETCH_ASSOC);
if (!$ch) outDie('Chamado não encontrado.');

// =========================
// MOVIMENTOS / HISTÓRICO
// =========================
$stMov = $pdo->prepare("
  SELECT
    m.id,
    m.tipo,
    m.status_de,
    m.status_para,
    m.mensagem,
    m.criado_em,
    u.nome AS usuario_nome,
    u.email AS usuario_email,
    sde.nome AS status_de_nome,
    spa.nome AS status_para_nome
  FROM chamados_movimentos m
  LEFT JOIN usuarios u ON u.id = m.usuario_id
  LEFT JOIN chamados_status sde ON sde.id = m.status_de
  LEFT JOIN chamados_status spa ON spa.id = m.status_para
  WHERE m.chamado_id = :id
  ORDER BY m.id ASC
");
$stMov->execute([':id' => $chamadoId]);
$movs = $stMov->fetchAll(PDO::FETCH_ASSOC);

// =========================
// DADOS DO SISTEMA (logo / nome / contato) - mantém seu padrão
// =========================
$logoArquivo = trim((string)($logo ?? ''));
$logoPath = __DIR__ . '/../../uploads/' . $logoArquivo;

$fallback1 = __DIR__ . '/../../uploads/sem_foto.png';
$fallback2 = __DIR__ . '/../../uploads/sem_foto.webp';

$imgPathFinal = '';
if ($logoArquivo !== '' && is_file($logoPath)) $imgPathFinal = $logoPath;
elseif (is_file($fallback1)) $imgPathFinal = $fallback1;
elseif (is_file($fallback2)) $imgPathFinal = $fallback2;

$logoDataUri = imgToDataUri($imgPathFinal);
$logoHtml = $logoDataUri ? '<img class="brand-logo" src="'.esc($logoDataUri).'" alt="Logo">' : '';

$empresaNome = trim((string)($nome_sistema ?? 'Sistema'));
$empresaContato = trim((string)($telefone_sistema ?? ''));
$empresaEmail   = trim((string)($email_sistema ?? ''));
$empresaEndereco = trim((string)($endereco ?? ''));

$sep = ($empresaContato !== '' && $empresaEmail !== '') ? ' | ' : '';
$linhaContato = trim($empresaContato . $sep . $empresaEmail);

// =========================
// HELPERS VISUAIS
// =========================
function fmt($dt) {
  $dt = (string)$dt;
  if ($dt === '' || $dt === '0000-00-00 00:00:00') return '—';
  $ts = strtotime($dt);
  if (!$ts) return '—';
  return date('d/m/Y H:i', $ts);
}

function badgePrioridadePdf($p) {
  $p = (string)$p;
  $map = ['Baixa'=>'b-secondary','Media'=>'b-primary','Alta'=>'b-warning','Urgente'=>'b-danger'];
  $badge = $map[$p] ?? 'b-primary';
  $lbl = ($p === 'Media') ? 'Média' : ($p ?: 'Média');
  return '<span class="badge '.$badge.'">'.esc($lbl).'</span>';
}

function badgeStatusPdf($nome, $corHex, $fechado) {
  $nome = (string)$nome;
  $corHex = trim((string)$corHex) ?: '#6c757d';
  $isFechado = (strtolower((string)$fechado) === 'sim');
  $extra = $isFechado ? '' : '';
  return '<span class="badge" style="background:'.esc($corHex).';color:#fff;border:1px solid rgba(0,0,0,.08);padding:.28rem .55rem;border-radius:999px;">'
    .esc($nome ?: '—').'</span>'.$extra;
}

// =========================
// FILTROS HTML (BLOCO PADRÃO DO HEADER)
// =========================
$protLabel = esc($ch['protocolo'] ?? ('#'.$chamadoId));
$statusLabel = esc($ch['status_nome'] ?? '—');
$nome_setor = esc($ch['setor_nome'] ?? '—');
$prioLabel = esc(($ch['prioridade'] ?? 'Media') === 'Media' ? 'Média' : ($ch['prioridade'] ?? '—'));

$filtrosHtml = <<<HTML
<table class="filters" cellspacing="0" cellpadding="0">
  <tr>
    <td><b>Protocolo:</b> {$protLabel}</td>
    <td><b>Setor:</b> {$nome_setor}</td>
    <td><b>Status:</b> {$statusLabel}</td>
    <td><b>Prioridade:</b> {$prioLabel}</td>
  </tr>
</table>
HTML;

// =========================
// CONTEÚDO HTML (DETALHES + HISTÓRICO)
// =========================
$clienteLinha = trim((string)($ch['cliente_nome'] ?? ''));
$clienteEmail = trim((string)($ch['cliente_email'] ?? ''));
$clienteTel   = trim((string)($ch['cliente_telefone'] ?? ''));

$responsavel = trim((string)($ch['resp_nome'] ?? ''));
$respEmail   = trim((string)($ch['resp_email'] ?? ''));
$respTel     = trim((string)($ch['resp_telefone'] ?? ''));

$abertura = trim((string)($ch['abertura_nome'] ?? ''));
$aberturaEmail = trim((string)($ch['abertura_email'] ?? ''));

$statusBadge = badgeStatusPdf($ch['status_nome'] ?? '', $ch['status_cor'] ?? '#6c757d', $ch['status_fechado'] ?? 'Não');
$prioBadge   = badgePrioridadePdf($ch['prioridade'] ?? 'Media');

$descricao = trim((string)($ch['descricao'] ?? ''));
$assunto = trim((string)($ch['assunto'] ?? ''));

$movRows = '';
if (!$movs) {
  $movRows = '<tr><td colspan="5" class="muted">Nenhum movimento registrado.</td></tr>';
} else {
  foreach ($movs as $m) {
    $tipo = esc($m['tipo'] ?? '—');
    $data = esc(fmt($m['criado_em'] ?? ''));
    $user = trim((string)($m['usuario_nome'] ?? ''));
    $userEmail = trim((string)($m['usuario_email'] ?? ''));

    if ($user === '') $user = 'Sistema';
    $userCell = '<div>'.esc($user).'</div><div class="muted small">'.($userEmail ? esc($userEmail) : '—').'</div>';

    $stDe = trim((string)($m['status_de_nome'] ?? ''));
    $stPara = trim((string)($m['status_para_nome'] ?? ''));

    $statusMud = '—';
    if ($stDe !== '' || $stPara !== '') {
      $statusMud = esc(($stDe ?: '—') . ' → ' . ($stPara ?: '—'));
    }

    $msg = trim((string)($m['mensagem'] ?? ''));
    $msg = $msg !== '' ? esc($msg) : '—';

    $movRows .= '<tr>';
    $movRows .= '<td class="nowrap" style="width:120px;">'.$data.'</td>';
    $movRows .= '<td style="width:90px;">'.$tipo.'</td>';
    $movRows .= '<td style="width:160px;">'.$statusMud.'</td>';
    $movRows .= '<td style="width:190px;">'.$userCell.'</td>';
    $movRows .= '<td>'.$msg.'</td>';
    $movRows .= '</tr>';
  }
}

$conteudoHtml = '
  <div style="margin-bottom:10px;">
    <table class="kv" cellspacing="0" cellpadding="0" style="width:100%;border:1px solid #e5e7eb;border-radius:10px;">
      <tr>
        <td style="padding:10px;">
          <div class="muted small">Assunto</div>
          <div style="font-weight:700;">'.($assunto ? esc($assunto) : '—').'</div>
        </td>
        <td style="padding:10px; width:220px; text-align:right;">
          <div>'.$statusBadge.'</div>
          <div style="margin-top:6px;">'.$prioBadge.'</div>
        </td>
      </tr>
      <tr>
        <td colspan="2" style="padding:10px; border-top:1px solid #e5e7eb;">
          <div class="muted small">Descrição</div>
          <div style="white-space:pre-wrap;">'.($descricao ? esc($descricao) : '—').'</div>
        </td>
      </tr>
    </table>
  </div>

  <div style="margin:12px 0;">
    <table class="kv" cellspacing="0" cellpadding="0" style="width:100%;border:1px solid #e5e7eb;border-radius:10px;">
      <tr>
        <td style="padding:10px; width:50%;">
          <div class="muted small">Cliente</div>
          <div style="font-weight:700;">'.($clienteLinha ? esc($clienteLinha) : '—').'</div>
          <div class="muted small">'.($clienteEmail ? esc($clienteEmail) : '—').($clienteTel ? ' • '.esc($clienteTel) : '').'</div>
        </td>
        <td style="padding:10px; width:50%;">
          <div class="muted small">Responsável</div>
          <div style="font-weight:700;">'.($responsavel ? esc($responsavel) : '(Opcional)').'</div>
          <div class="muted small">'.($respEmail ? esc($respEmail) : '—').($respTel ? ' • '.esc($respTel) : '').'</div>
        </td>
      </tr>
      <tr>
        <td style="padding:10px; border-top:1px solid #e5e7eb;">
          <div class="muted small">Aberto por</div>
          <div style="font-weight:700;">'.($abertura ? esc($abertura) : '—').'</div>
          <div class="muted small">'.($aberturaEmail ? esc($aberturaEmail) : '—').'</div>
        </td>
        <td style="padding:10px; border-top:1px solid #e5e7eb;">
          <div class="muted small">Datas</div>
          <div class="small"><b>Criado:</b> '.esc(fmt($ch["criado_em"] ?? "")).'</div>
          <div class="small"><b>Atualizado:</b> '.esc(fmt($ch["atualizado_em"] ?? "")).'</div>
          <div class="small"><b>Encerrado:</b> '.esc(fmt($ch["fechado_em"] ?? "")).'</div>
        </td>
      </tr>
    </table>
  </div>

  <h3 style="margin:14px 0 8px 0;">Histórico / Movimentos</h3>

  <table>
    <thead>
      <tr>
        <th style="width:120px;">Data</th>
        <th style="width:90px;">Tipo</th>
        <th style="width:160px;">Status</th>
        <th style="width:190px;">Usuário</th>
        <th>Mensagem</th>
      </tr>
    </thead>
    <tbody>
      '.$movRows.'
    </tbody>
  </table>

  '.$assinaturaHtml.'
';

// =========================
// MONTA HTML FINAL + GERA PDF
// =========================
$tituloRelatorio = 'Detalhamento do Chamado';

$infoHeader = [
  'logoHtml' => $logoHtml,
  'empresaNome' => esc($empresaNome),
  'linhaContato' => esc($linhaContato),
  'empresaEndereco' => esc($empresaEndereco),
  'geradoEm' => date('d/m/Y H:i')
];

$htmlFinal = pdfPaginaHtml($tituloRelatorio, $infoHeader, $filtrosHtml, $conteudoHtml);

// nome do arquivo
$nomeArq = 'chamado_' . preg_replace('/[^a-zA-Z0-9\-_]/', '_', (string)($ch['protocolo'] ?? $chamadoId)) . '.pdf';

// detalhe normalmente é melhor em portrait
gerarPdf($htmlFinal, $nomeArq, 'portrait');

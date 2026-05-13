<?php
/**
 * Relatório PDF - Abertura de Chamados (DomPDF)
 * Caminho: painel/relatorios/abertura.php
 *
 * Parâmetros (GET):
 *  - data_ini (YYYY-mm-dd)
 *  - data_fim (YYYY-mm-dd)
 *  - status_id (int)
 *  - prioridade (Baixa|Media|Alta|Urgente)
 *  - cliente_id (int)
 *  - responsavel_id (int)
 *  - termo (string)
 */

@session_start();

require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

// CORE PDF
require_once __DIR__ . '/_core/pdf_helpers.php';
require_once __DIR__ . '/_core/pdf_layout.php';
require_once __DIR__ . '/_core/pdf_bootstrap.php';

// =========================
// FILTROS (GET)
// =========================
$data_ini       = normalizaData($_GET['data_ini'] ?? '');
$data_fim       = normalizaData($_GET['data_fim'] ?? '');
$status_id      = (int)($_GET['status_id'] ?? 0);
$cliente_id     = (int)($_GET['cliente_id'] ?? 0);
$responsavel_id = (int)($_GET['responsavel_id'] ?? 0);
$setor_id = (int)($_GET['setor_id'] ?? 0);
$prioridade     = trim((string)($_GET['prioridade'] ?? ''));
$termo          = trim((string)($_GET['termo'] ?? ''));

// por enquanto não é SaaS, mas seu banco tem campo empresa (null/0)
// então só filtra se existir sessão empresa > 0
$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario_logado = (int)($_SESSION['id'] ?? 0);

// normaliza prioridade
$mapPrio = ['Média'=>'Media','Media'=>'Media','Baixa'=>'Baixa','Alta'=>'Alta','Urgente'=>'Urgente'];
$prioridade = $mapPrio[$prioridade] ?? $prioridade;
$prioridadesValidas = ['Baixa','Media','Alta','Urgente'];
if ($prioridade !== '' && !in_array($prioridade, $prioridadesValidas, true)) {
  $prioridade = '';
}

// labels período
$dtIniLabel = $data_ini ? date('d/m/Y', strtotime($data_ini)) : '—';
$dtFimLabel = $data_fim ? date('d/m/Y', strtotime($data_fim)) : '—';

// =========================
// ASSINATURA (usuário logado) - mesmo padrão do logs
// =========================
$assinaturaHtml = '';

if ($usuario_logado > 0) {
  $sqlAss = "SELECT nome, assinatura FROM usuarios WHERE id = :id";
  $paramsAss = [':id' => $usuario_logado];

  if ($empresa > 0) {
    $sqlAss .= " AND empresa = :empresa";
    $paramsAss[':empresa'] = $empresa;
  }

  $sqlAss .= " LIMIT 1";

  $stAss = $pdo->prepare($sqlAss);
  $stAss->execute($paramsAss);
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
// Labels filtros (Status / Cliente / Responsável)
// =========================
$statusLabel = 'Todos';
if ($status_id > 0) {
  $st = $pdo->prepare("SELECT nome FROM chamados_status WHERE id = :id LIMIT 1");
  $st->execute([':id' => $status_id]);
  $x = $st->fetch(PDO::FETCH_ASSOC);
  $statusLabel = $x ? trim((string)$x['nome']) : "Status #{$status_id}";
}

$clienteLabel = 'Todos';
if ($cliente_id > 0) {
  $st = $pdo->prepare("SELECT nome, email FROM clientes WHERE id = :id LIMIT 1");
  $st->execute([':id' => $cliente_id]);
  $x = $st->fetch(PDO::FETCH_ASSOC);
  if ($x) {
    $n = trim((string)($x['nome'] ?? ''));
    $e = trim((string)($x['email'] ?? ''));
    $clienteLabel = $n . ($e ? " ({$e})" : '');
  } else {
    $clienteLabel = "Cliente #{$cliente_id}";
  }
}

$responsavelLabel = 'Todos';
if ($responsavel_id > 0) {
  $sql = "SELECT nome, email FROM usuarios WHERE id = :id";
  $pp = [':id' => $responsavel_id];
  if ($empresa > 0) {
    $sql .= " AND empresa = :empresa";
    $pp[':empresa'] = $empresa;
  }
  $sql .= " LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute($pp);
  $x = $st->fetch(PDO::FETCH_ASSOC);
  if ($x) {
    $n = trim((string)($x['nome'] ?? ''));
    $e = trim((string)($x['email'] ?? ''));
    $responsavelLabel = $n . ($e ? " ({$e})" : '');
  } else {
    $responsavelLabel = "Usuário #{$responsavel_id}";
  }
}

$prioLabel = ($prioridade !== '') ? (($prioridade === 'Media') ? 'Média' : $prioridade) : 'Todas';
$termoLabel = ($termo !== '') ? $termo : '—';

// =========================
// SQL (respeitando filtros)
// =========================
$where = [];
$params = [];

if ($empresa > 0) {
  // chamados.empresa pode ser NULL no seu cenário atual
  $where[] = "(c.empresa = :empresa)";
  $params[':empresa'] = $empresa;
}

if ($data_ini !== '' && $data_fim !== '') {
  $where[] = "DATE(c.criado_em) BETWEEN :ini AND :fim";
  $params[':ini'] = $data_ini;
  $params[':fim'] = $data_fim;
} elseif ($data_ini !== '') {
  $where[] = "DATE(c.criado_em) >= :ini";
  $params[':ini'] = $data_ini;
} elseif ($data_fim !== '') {
  $where[] = "DATE(c.criado_em) <= :fim";
  $params[':fim'] = $data_fim;
}

if ($status_id > 0) {
  $where[] = "c.status_id = :status_id";
  $params[':status_id'] = $status_id;
}

if ($cliente_id > 0) {
  $where[] = "c.cliente_id = :cliente_id";
  $params[':cliente_id'] = $cliente_id;
}

if ($responsavel_id > 0) {
  $where[] = "c.usuario_responsavel_id = :resp_id";
  $params[':resp_id'] = $responsavel_id;
}

if ($setor_id > 0) {
  $where[] = "c.setor_id = :setor_id";
  $params[':setor_id'] = $setor_id;
}


if ($prioridade !== '') {
  $where[] = "c.prioridade = :prioridade";
  $params[':prioridade'] = $prioridade;
}

if ($termo !== '') {
  $where[] = "(
    c.protocolo LIKE :q
    OR c.assunto LIKE :q
    OR cli.nome LIKE :q
    OR cli.email LIKE :q
  )";
  $params[':q'] = '%' . $termo . '%';
}




$usuario_logado = (int)($_SESSION['id'] ?? 0);

// Busca setores permitidos do usuário
$setoresPermitidos = [];

$stSet = $pdo->prepare("
  SELECT setor_id 
  FROM usuarios_setores 
  WHERE usuario_id = :uid
");
$stSet->execute([':uid' => $usuario_logado]);
$setoresPermitidos = $stSet->fetchAll(PDO::FETCH_COLUMN);


$nivel = $_SESSION['nivel'] ?? '';

if (strtolower($nivel) !== 'administrador') {

    // monta lista dinâmica de setores
    $placeholders = [];
    foreach ($setoresPermitidos as $k => $sid) {
        $ph = ":set_perm_$k";
        $placeholders[] = $ph;
        $params[$ph] = (int)$sid;
    }

    $params[':usuario_logado'] = $usuario_logado;

    if (!empty($placeholders)) {

        $where[] = "(
            c.usuario_responsavel_id = :usuario_logado
            OR (
                c.usuario_responsavel_id IS NULL
                AND c.setor_id IN (" . implode(',', $placeholders) . ")
            )
        )";

    } else {

        // se não tiver setores permitidos
        // só vê os que ele é responsável
        $where[] = "c.usuario_responsavel_id = :usuario_logado";
    }
}

$sql = "
  SELECT
    c.id,
    c.protocolo,
    c.assunto,
    c.prioridade,
    c.criado_em,
    c.atualizado_em,
    c.fechado_em,

    -- ✅ setor
    c.setor_id,
    se.nome AS setor_nome,

    cli.nome  AS cliente_nome,
    cli.email AS cliente_email,

    ua.nome   AS abertura_nome,
    ua.email  AS abertura_email,

    ur.nome   AS resp_nome,
    ur.email  AS resp_email,

    st.nome   AS status_nome,
    st.cor    AS status_cor,
    st.fechado AS status_fechado

  FROM chamados c
  LEFT JOIN setores se   ON se.id = c.setor_id
  LEFT JOIN clientes cli ON cli.id = c.cliente_id
  INNER JOIN usuarios ua ON ua.id = c.usuario_abertura_id
  LEFT JOIN usuarios ur  ON ur.id = c.usuario_responsavel_id
  INNER JOIN chamados_status st ON st.id = c.status_id
";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// DADOS DO SISTEMA (logo / nome / contato) - igual logs
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
// FILTROS HTML (header)
// =========================
$filtrosHtml = <<<HTML
<table class="filters" cellspacing="0" cellpadding="0">
  <tr>
    <td><b>Período:</b> {$dtIniLabel} até {$dtFimLabel}</td>
    <td><b>Status:</b> {$statusLabel}</td>
    <td><b>Prioridade:</b> {$prioLabel}</td>
  </tr>
  <tr>
    <td><b>Cliente:</b> {$clienteLabel}</td>
    <td><b>Responsável:</b> {$responsavelLabel}</td>
    <td><b>Busca:</b> {$termoLabel}</td>
  </tr>
</table>
HTML;

// =========================
// Resumo (contadores)
// =========================
$total = count($rows);
$abertos = 0;
$fechados = 0;
$mapPrioCount = ['Baixa'=>0,'Media'=>0,'Alta'=>0,'Urgente'=>0];

foreach ($rows as $r) {
  $isFechado = (strtolower((string)($r['status_fechado'] ?? 'nao')) === 'sim');
  if ($isFechado) $fechados++; else $abertos++;

  $p = (string)($r['prioridade'] ?? '');
  if (isset($mapPrioCount[$p])) $mapPrioCount[$p]++;
}

$resumoHtml = <<<HTML
<div style="margin: 0 0 10px 0;">
  <table class="filters" cellspacing="0" cellpadding="0">
    <tr>
      <td><b>Total:</b> {$total}</td>
      <td><b>Abertos:</b> {$abertos}</td>
      <td><b>Fechados:</b> {$fechados}</td>
    </tr>
    <tr>
      <td><b>Baixa:</b> {$mapPrioCount['Baixa']}</td>
      <td><b>Média:</b> {$mapPrioCount['Media']}</td>
      <td><b>Alta:</b> {$mapPrioCount['Alta']}</td>
      <td><b>Urgente:</b> {$mapPrioCount['Urgente']}</td>
    </tr>
  </table>
</div>
HTML;

// =========================
// TABELA HTML (conteúdo) - RETRATO (compacto)
// =========================
$rowsHtml = '';
if (!$rows) {
  $rowsHtml = '<tr><td colspan="6" class="muted">Nenhum chamado encontrado com os filtros selecionados.</td></tr>';
} else {
  foreach ($rows as $r) {
    $proto   = esc($r['protocolo'] ?? '');
    $assunto = esc($r['assunto'] ?? '');
    $nome_setor = esc($r['setor_nome'] ?? '');

    // Cliente (somente nome, sem email pra economizar)
    $cliNome = trim((string)($r['cliente_nome'] ?? ''));
    $cliTxt  = $cliNome !== '' ? esc($cliNome) : '—';

    // Status (badge)
    $staNome = trim((string)($r['status_nome'] ?? ''));
    $staCor  = trim((string)($r['status_cor'] ?? '#6c757d'));
    if ($staCor === '') $staCor = '#6c757d';
    $isFechado = (strtolower((string)($r['status_fechado'] ?? 'nao')) === 'sim');
    $staExtra = $isFechado ? ' ✓' : '';
    $staBadge = '<span class="badge" style="background:'.esc($staCor).';color:#fff;border:1px solid rgba(0,0,0,.08);">'.esc($staNome ?: '—').$staExtra.'</span>';

    // Prioridade (mini)
    $prio = (string)($r['prioridade'] ?? 'Media');
    $prioLabelRow = ($prio === 'Media') ? 'Média' : $prio;
    $prioBadge = '<span class="badge b-primary">'.esc($prioLabelRow).'</span>';
    if ($prio === 'Baixa')   $prioBadge = '<span class="badge b-secondary">Baixa</span>';
    if ($prio === 'Alta')    $prioBadge = '<span class="badge b-warning">Alta</span>';
    if ($prio === 'Urgente') $prioBadge = '<span class="badge b-danger">Urgente</span>';

    // Datas (somente criado)
    $criado = esc(fmtDataHora($r['criado_em'] ?? ''));

    $rowsHtml .= '<tr>';
    $rowsHtml .= '<td class="nowrap">'.$proto.'</td>';
    $rowsHtml .= '<td>'.$assunto.'</td>';
    $rowsHtml .= '<td>'.$cliTxt.'</td>';
    $rowsHtml .= '<td>'.$staBadge.'</td>';
    $rowsHtml .= '<td>'.$prioBadge.'</td>';
    $rowsHtml .= '<td class="nowrap">'.$nome_setor.'</td>';
    $rowsHtml .= '</tr>';
  }
}

$conteudoHtml = <<<HTML
{$resumoHtml}

<table class="compact">
  <thead>
    <tr>
      <th style="width:105px;">Protocolo</th>
      <th>Assunto</th>
      <th style="width:150px;">Cliente</th>
      <th style="width:95px;">Status</th>
      <th style="width:80px;">Prio</th>
      <th style="width:95px;">Setor</th>
    </tr>
  </thead>
  <tbody>
    {$rowsHtml}
  </tbody>
</table>

{$assinaturaHtml}
HTML;



// =========================
// MONTA HTML FINAL + GERA PDF
// =========================
$tituloRelatorio = 'Relatório - Abertura de Chamados';

$infoHeader = [
  'logoHtml' => $logoHtml,
  'empresaNome' => esc($empresaNome),
  'linhaContato' => esc($linhaContato),
  'empresaEndereco' => esc($empresaEndereco),
  'geradoEm' => date('d/m/Y H:i')
];

$htmlFinal = pdfPaginaHtml($tituloRelatorio, $infoHeader, $filtrosHtml, $conteudoHtml);

// muitas colunas -> landscape
gerarPdf($htmlFinal, 'relatorio_abertura_chamados.pdf', 'portrait');

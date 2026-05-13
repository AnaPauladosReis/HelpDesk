<?php
/**
 * Relatório PDF - Logs do Sistema (DomPDF)
 * Caminho: painel/relatorios/logs.php
 *
 * Parâmetros (GET):
 *  - data_ini (YYYY-mm-dd)
 *  - data_fim (YYYY-mm-dd)
 *  - usuario_id (int)
 *  - acao (login|logout|inserir|editar|excluir)
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
$data_ini   = normalizaData($_GET['data_ini'] ?? '');
$data_fim   = normalizaData($_GET['data_fim'] ?? '');
$usuario_id = (int)($_GET['usuario_id'] ?? 0);
$acao       = strtolower(trim((string)($_GET['acao'] ?? '')));

$empresa = (int)($_SESSION['empresa'] ?? 0);
$usuario_logado = (int)($_SESSION['id'] ?? 0);


// =========================
// ASSINATURA (usuário logado)
// =========================
$assinaturaHtml = '';

if ($usuario_logado > 0) {
  $stAss = $pdo->prepare("SELECT nome, assinatura FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
  $stAss->execute([':id' => $usuario_logado, ':empresa' => $empresa]);
  $assRow = $stAss->fetch(PDO::FETCH_ASSOC) ?: [];

  $assArquivo = trim((string)($assRow['assinatura'] ?? ''));
  $assNome    = trim((string)($assRow['nome'] ?? ''));

  if ($assArquivo !== '') {
    // caminho físico: raiz/uploads/assinaturas
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


// valida ação
$acoesPermitidas = ['login','logout','inserir','editar','excluir'];
if ($acao !== '' && !in_array($acao, $acoesPermitidas, true)) {
  $acao = '';
}

// labels
$dtIniLabel = $data_ini ? date('d/m/Y', strtotime($data_ini)) : '—';
$dtFimLabel = $data_fim ? date('d/m/Y', strtotime($data_fim)) : '—';

$acaoLabelMap = [
  'login'   => 'Login',
  'logout'  => 'Logout',
  'inserir' => 'Inserir',
  'editar'  => 'Editar',
  'excluir' => 'Excluir',
];
$acaoLabel = $acao ? ($acaoLabelMap[$acao] ?? ucfirst($acao)) : 'Todas';

// usuário label (se filtrado)
$usuarioLabel = 'Todos';
if ($usuario_id > 0) {
  $stU = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = :id AND empresa = :empresa LIMIT 1");
  $stU->execute([':id' => $usuario_id, ':empresa' => $empresa]);
  $u = $stU->fetch(PDO::FETCH_ASSOC);
  if ($u) {
    $usuarioLabel = trim(($u['nome'] ?? '')) . (trim(($u['email'] ?? '')) ? ' (' . trim($u['email']) . ')' : '');
  } else {
    $usuarioLabel = "Usuário #{$usuario_id}";
  }
}

// =========================
// SQL
// =========================
$where = [];
$params = [];

$where[] = "l.empresa = :empresa";
$params[':empresa'] = $empresa;

if ($usuario_id > 0) {
  $where[] = "l.usuario_id = :usuario_id";
  $params[':usuario_id'] = $usuario_id;
}

if ($acao !== '') {
  $where[] = "l.acao = :acao";
  $params[':acao'] = $acao;
}

if ($data_ini !== '' && $data_fim !== '') {
  $where[] = "DATE(l.criado_em) BETWEEN :ini AND :fim";
  $params[':ini'] = $data_ini;
  $params[':fim'] = $data_fim;
} elseif ($data_ini !== '') {
  $where[] = "DATE(l.criado_em) >= :ini";
  $params[':ini'] = $data_ini;
} elseif ($data_fim !== '') {
  $where[] = "DATE(l.criado_em) <= :fim";
  $params[':fim'] = $data_fim;
}

$sql = "
  SELECT
    l.*,
    u.nome  AS usuario_nome,
    u.email AS usuario_email
  FROM logs l
  LEFT JOIN usuarios u
    ON u.id = l.usuario_id
   AND u.empresa = l.empresa
";

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY l.criado_em DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// DADOS DO SISTEMA (logo / nome / contato)
// =========================
// Sua logo vem do config via conexao.php (variável $logo)
$logoArquivo = trim((string)($logo ?? ''));

// pasta correta: /helpdesk/uploads (raiz do projeto)
$logoPath = __DIR__ . '/../../uploads/' . $logoArquivo;

// fallbacks
$fallback1 = __DIR__ . '/../../uploads/sem_foto.png';
$fallback2 = __DIR__ . '/../../uploads/sem_foto.webp';

$imgPathFinal = '';
if ($logoArquivo !== '' && is_file($logoPath)) $imgPathFinal = $logoPath;
elseif (is_file($fallback1)) $imgPathFinal = $fallback1;
elseif (is_file($fallback2)) $imgPathFinal = $fallback2;

$logoDataUri = imgToDataUri($imgPathFinal);
$logoHtml = $logoDataUri ? '<img class="brand-logo" src="'.esc($logoDataUri).'" alt="Logo">' : '';

// empresa/sistema (da sua config)
$empresaNome = trim((string)($nome_sistema ?? 'Sistema'));
$empresaContato = trim((string)($telefone_sistema ?? ''));
$empresaEmail   = trim((string)($email_sistema ?? ''));
$empresaEndereco = trim((string)($endereco ?? ''));

$sep = ($empresaContato !== '' && $empresaEmail !== '') ? ' | ' : '';
$linhaContato = trim($empresaContato . $sep . $empresaEmail);

// =========================
// FILTROS HTML (bloco padrão dentro do header)
// =========================
$filtrosHtml = <<<HTML
<table class="filters" cellspacing="0" cellpadding="0">
  <tr>
    <td><b>Período:</b> {$dtIniLabel} até {$dtFimLabel}</td>
    <td><b>Usuário:</b> {$usuarioLabel}</td>
    <td><b>Ação:</b> {$acaoLabel}</td>
    
  </tr>
</table>
HTML;

// =========================
// TABELA HTML (conteúdo do relatório)
// =========================
$rowsHtml = '';
if (!$rows) {
  $rowsHtml = '<tr><td colspan="7" class="muted">Nenhum log encontrado com os filtros selecionados.</td></tr>';
} else {
  foreach ($rows as $r) {
    $acaoRow = strtolower((string)($r['acao'] ?? ''));
    $badge = 'b-secondary';
    if ($acaoRow === 'login')   $badge = 'b-success';
    if ($acaoRow === 'logout')  $badge = 'b-warning';
    if ($acaoRow === 'inserir') $badge = 'b-primary';
    if ($acaoRow === 'editar')  $badge = 'b-info';
    if ($acaoRow === 'excluir') $badge = 'b-danger';

    $acaoTxt = $acaoLabelMap[$acaoRow] ?? ($acaoRow ? ucfirst($acaoRow) : '—');

    $usuarioNome  = trim((string)($r['usuario_nome'] ?? ''));
    $usuarioEmail = trim((string)($r['usuario_email'] ?? ''));

    if ($usuarioNome === '' && (int)($r['usuario_id'] ?? 0) > 0) $usuarioNome = 'Usuário #' . (int)$r['usuario_id'];
    if ($usuarioNome === '') $usuarioNome = 'Sistema';

    $entidade = esc($r['entidade'] ?? '');
    $registro = (int)($r['registro_id'] ?? 0);
    $descricao = esc($r['descricao'] ?? '');
    $ip = esc($r['ip'] ?? '');

    $rowsHtml .= '<tr>';
    $rowsHtml .= '<td class="nowrap">'.esc(fmtDataHora($r['criado_em'] ?? '')).'</td>';
    $rowsHtml .= '<td><span class="badge '.$badge.'">'.esc($acaoTxt).'</span></td>';
    $rowsHtml .= '<td>'.($entidade ?: '-').'</td>';
    $rowsHtml .= '<td class="nowrap">'.($registro > 0 ? $registro : '-').'</td>';
    $rowsHtml .= '<td>'.($descricao ?: '-').'</td>';
    $rowsHtml .= '<td>';
    $rowsHtml .= '<div>'.esc($usuarioNome).'</div>';
    $rowsHtml .= '<div class="muted small">'.($usuarioEmail ? esc($usuarioEmail) : '—').'</div>';
    $rowsHtml .= '</td>';
    $rowsHtml .= '<td class="nowrap">'.($ip ?: '-').'</td>';
    $rowsHtml .= '</tr>';
  }
}

$conteudoHtml = <<<HTML
<table>
  <thead>
    <tr>
      <th style="width:135px;">Data</th>
      <th style="width:90px;">Ação</th>
      <th style="width:110px;">Entidade</th>
      <th style="width:60px;">ID</th>
      <th>Descrição</th>
      <th style="width:185px;">Usuário</th>
      <th style="width:95px;">IP</th>
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
$tituloRelatorio = 'Relatório de Logs';

$infoHeader = [
  'logoHtml' => $logoHtml,
  'empresaNome' => esc($empresaNome),
  'linhaContato' => esc($linhaContato),
  'empresaEndereco' => esc($empresaEndereco),
  'geradoEm' => date('d/m/Y H:i')
];

$htmlFinal = pdfPaginaHtml($tituloRelatorio, $infoHeader, $filtrosHtml, $conteudoHtml);

// logs melhor em landscape
gerarPdf($htmlFinal, 'relatorio_logs.pdf', 'landscape');

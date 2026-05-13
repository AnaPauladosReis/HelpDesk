<?php
/**
 * Relatório PDF - Clientes (DomPDF)
 * Caminho: painel/relatorios/clientes.php
 *
 * Parâmetros (GET):
 *  - ativo (Sim|Não|Nao)  // opcional (filtra a lista)
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
$ativo = trim((string)($_GET['ativo'] ?? ''));

$ativoLower = mb_strtolower($ativo);
if ($ativoLower === 'nao' || $ativoLower === 'não') $ativo = 'Não';
if ($ativoLower === 'sim') $ativo = 'Sim';

$ativosPermitidos = ['Sim', 'Não'];
if ($ativo !== '' && !in_array($ativo, $ativosPermitidos, true)) $ativo = '';

$ativoLabel = $ativo !== '' ? $ativo : 'Todos';

// =========================
// CONTADORES (Ativos / Inativos / Total) - SEM FILTRO
// =========================
$sqlCount = "
  SELECT
    COUNT(*) AS total,
    SUM(CASE
          WHEN LOWER(TRIM(REPLACE(ativo,'Não','Nao'))) = 'sim' THEN 1
          ELSE 0
        END) AS ativos,
    SUM(CASE
          WHEN LOWER(TRIM(REPLACE(ativo,'Não','Nao'))) = 'nao' THEN 1
          ELSE 0
        END) AS inativos
  FROM clientes
";
$stCount = $pdo->query($sqlCount);
$cnt = $stCount->fetch(PDO::FETCH_ASSOC) ?: [];

$totalGeral   = (int)($cnt['total'] ?? 0);
$totalAtivos  = (int)($cnt['ativos'] ?? 0);
$totalInativos= (int)($cnt['inativos'] ?? 0);

// =========================
// SQL (LISTA) - COM FILTRO OPCIONAL
// =========================
$where = [];
$params = [];

if ($ativo !== '') {
  $where[] = "c.ativo = :ativo";
  $params[':ativo'] = $ativo;
}

$sql = "SELECT c.* FROM clientes c";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY c.nome ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// total exibido (respeita filtro)
$totalFiltrado = is_array($rows) ? count($rows) : 0;

// =========================
// DADOS DO SISTEMA (logo / nome / contato)
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

$empresaNome     = trim((string)($nome_sistema ?? 'Sistema'));
$empresaContato  = trim((string)($telefone_sistema ?? ''));
$empresaEmail    = trim((string)($email_sistema ?? ''));
$empresaEndereco = trim((string)($endereco ?? ''));

$sep = ($empresaContato !== '' && $empresaEmail !== '') ? ' | ' : '';
$linhaContato = trim($empresaContato . $sep . $empresaEmail);


// =========================
// FOTO (cliente) -> data uri
// =========================
function clienteFotoDataUri($foto) {
  $foto = trim((string)$foto);
  if ($foto === '') $foto = 'sem_foto.webp';

  $path = __DIR__ . '/../../uploads/clientes/' . $foto;

  $fb1 = __DIR__ . '/../../uploads/sem_foto.png';
  $fb2 = __DIR__ . '/../../uploads/sem_foto.webp';

  $final = '';
  if (is_file($path)) $final = $path;
  elseif (is_file($fb1)) $final = $fb1;
  elseif (is_file($fb2)) $final = $fb2;

  return imgToDataUri($final);
}

// =========================
// =========================
// FILTROS HTML (TOPO) - Ativos / Inativos / Total (com cores)
// =========================
$totalFiltrado = is_array($rows) ? count($rows) : 0;

$ativosHtml   = '<span class="badge b-success">Ativos: '.$totalAtivos.'</span>';
$inativosHtml = '<span class="badge b-danger">Inativos: '.$totalInativos.'</span>';
$totalHtml    = '<span class="badge b-secondary">Total: '.$totalGeral.'</span>';

$filtrosHtml = <<<HTML
<table class="filters" cellspacing="0" cellpadding="0">
  <tr>
    <td><b>Filtro:</b> {$ativoLabel}</td>
    <td>{$ativosHtml}</td>
    <td>{$inativosHtml}</td>
    <td style="text-align:right;">
      {$totalHtml}
     
    </td>
  </tr>
</table>
HTML;


// =========================
// TABELA HTML
// =========================
$rowsHtml = '';

if (!$rows) {
  $rowsHtml = '<tr><td colspan="7" class="muted">Nenhum cliente encontrado com os filtros selecionados.</td></tr>';
} else {
  foreach ($rows as $r) {
    $nome     = esc($r['nome'] ?? '');
    $email    = trim((string)($r['email'] ?? ''));
    $telefone = trim((string)($r['telefone'] ?? ''));
    $cidade   = trim((string)($r['cidade'] ?? ''));
    $ativoRaw = trim((string)($r['ativo'] ?? 'Sim'));

    $emailHtml  = $email !== '' ? esc($email) : '<span class="muted">—</span>';
    $telHtml    = $telefone !== '' ? esc($telefone) : '<span class="muted">—</span>';
    $cidadeHtml = $cidade !== '' ? esc($cidade) : '<span class="muted">—</span>';

    // normaliza ativo
    $a = mb_strtolower($ativoRaw);
    if ($a === 'nao') $a = 'não';
    $ehNao = ($a === 'não');

    // coluna visual (badge)
    $badgeAtivo = $ehNao
      ? '<span class="badge b-danger">Inativo</span>'
      : '<span class="badge b-success">Ativo</span>';

    // coluna texto puro do campo
    $ativoCampo = $ehNao
  ? '<span style="color:#991b1b; font-weight:600;">Não</span>'
  : '<span style="color:#166534; font-weight:600;">Sim</span>';

    // foto pequena
    $fotoUri = clienteFotoDataUri($r['foto'] ?? '');
    $fotoHtml = $fotoUri
      ? '<img src="'.esc($fotoUri).'" alt="Foto" style="width:22px;height:22px;border-radius:6px;display:block;">'
      : '<span class="muted">—</span>';

    $rowsHtml .= '<tr>';
    $rowsHtml .= '<td style="text-align:center;">'.$fotoHtml.'</td>';
    $rowsHtml .= '<td><b>'.$nome.'</b></td>';
    $rowsHtml .= '<td>'.$emailHtml.'</td>';
    $rowsHtml .= '<td class="nowrap">'.$telHtml.'</td>';
    $rowsHtml .= '<td>'.$cidadeHtml.'</td>';
    $rowsHtml .= '<td class="nowrap">'.$ativoCampo.'</td>';   // NOVA COLUNA
    $rowsHtml .= '<td class="nowrap">'.$badgeAtivo.'</td>';  // STATUS VISUAL
    $rowsHtml .= '</tr>';
  }
}

$conteudoHtml = <<<HTML
<table>
  <thead>
    <tr>
      <th style="width:34px; text-align:center;">Foto</th>
      <th style="width:170px;">Nome</th>
      <th style="width:210px;">E-mail</th>
      <th style="width:110px;">Telefone</th>
      <th style="width:130px;">Cidade</th>
      <th style="width:60px;">Ativo</th>
      <th style="width:80px;">Status</th>
    </tr>
  </thead>
  <tbody>
    {$rowsHtml}
  </tbody>
</table>
HTML;



// =========================
// MONTA HTML FINAL + PDF
// =========================
$tituloRelatorio = 'Relatório de Clientes';

$infoHeader = [
  'logoHtml' => $logoHtml,
  'empresaNome' => esc($empresaNome),
  'linhaContato' => esc($linhaContato),
  'empresaEndereco' => esc($empresaEndereco),
  'geradoEm' => date('d/m/Y H:i')
];

$htmlFinal = pdfPaginaHtml($tituloRelatorio, $infoHeader, $filtrosHtml, $conteudoHtml);

gerarPdf($htmlFinal, 'relatorio_clientes.pdf', 'portrait');

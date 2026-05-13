<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: text/html; charset=utf-8');

function esc($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$usuario_id = (int)($_GET['id'] ?? 0);
if ($usuario_id <= 0) {
  echo '<div class="alert alert-danger rounded-4 mb-0">Usuário inválido.</div>';
  exit;
}

// Busca usuário e bloqueia admin (não aplica permissões)
$stmtU = $pdo->prepare("SELECT id, nome, nivel FROM usuarios WHERE id = :id LIMIT 1");
$stmtU->execute([':id' => $usuario_id]);
$user = $stmtU->fetch();

if (!$user) {
  echo '<div class="alert alert-danger rounded-4 mb-0">Usuário não encontrado.</div>';
  exit;
}

if (trim((string)$user['nivel']) === 'Administrador') {
  echo '<div class="alert alert-warning rounded-4 mb-0">
          Este usuário é <b>Administrador</b> e possui acesso total. Permissões não são aplicadas aqui.
        </div>';
  exit;
}

// Carrega catálogo de menus
$cfgPath = __DIR__ . '/../../config/menu.php';
$cfg = file_exists($cfgPath) ? require $cfgPath : null;

$grupos = $cfg['grupos'] ?? [];
$menus  = $cfg['menus']  ?? [];
$order  = $cfg['sidebar_order'] ?? [];

// Indexa menus
$menuById = [];
$menusPorGrupo = [];
$menusSemGrupo = [];

foreach ($menus as $m) {
  $id = (string)($m['id'] ?? '');
  if ($id === '') continue;

  $menuById[$id] = $m;

  $g = $m['grupo'] ?? null;
  if ($g) $menusPorGrupo[$g][] = $m;
  else $menusSemGrupo[] = $m;
}

// Ordena itens dentro de cada grupo
foreach ($menusPorGrupo as $gid => &$itens) {
  usort($itens, fn($a,$b) => (int)($a['ordem']??0) <=> (int)($b['ordem']??0));
}
unset($itens);

// Permissões (MENUS) já salvas
$stmtP = $pdo->prepare("SELECT menu_id FROM usuarios_permissoes WHERE usuario_id = :id");
$stmtP->execute([':id' => $usuario_id]);
$permitidos = $stmtP->fetchAll(PDO::FETCH_COLUMN);
$permitidos = array_flip($permitidos ?: []);

// Permissões (AÇÕES) já salvas
$acoesMarcadas = [];
try {
  $stmtA = $pdo->prepare("SELECT acao FROM usuarios_acoes WHERE usuario_id = :id");
  $stmtA->execute([':id' => $usuario_id]);
  $acoes = $stmtA->fetchAll(PDO::FETCH_COLUMN);
  $acoesMarcadas = array_flip($acoes ?: []);
} catch (Throwable $e) {
  // se a tabela ainda não existir por algum motivo, só não marca nada
  $acoesMarcadas = [];
}


// =========================
// PERMISSÕES (SETORES) já salvas
// =========================
$setoresMarcados = [];
try {
  $stS = $pdo->prepare("SELECT setor_id FROM usuarios_setores WHERE usuario_id = :id");
  $stS->execute([':id' => $usuario_id]);
  $setores = $stS->fetchAll(PDO::FETCH_COLUMN);
  $setoresMarcados = array_flip($setores ?: []);
} catch (Throwable $e) {
  $setoresMarcados = [];
}

// Catálogo de setores (para exibir na tela)
$rowsSetores = [];
try {
  $qSet = $pdo->query("SELECT id, nome FROM setores ORDER BY nome ASC");
  $rowsSetores = $qSet->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $rowsSetores = [];
}

$renderItem = function(array $m) use ($permitidos){
  $id = (string)$m['id'];
  $titulo = (string)($m['titulo'] ?? $id);
  $icone = (string)($m['icone'] ?? 'bi-dot');
  $checked = isset($permitidos[$id]) ? 'checked' : '';

  $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');

  return '
  <label class="perm-item d-flex align-items-center justify-content-between gap-2">
    <span class="d-flex align-items-center gap-2 text-truncate">
      <i class="bi '.$icone.' perm-ico"></i>
      <span class="perm-txt text-truncate">'.$titulo.'</span>
    </span>

    <span class="form-check form-switch m-0">
      <input class="form-check-input perm-check" type="checkbox" name="menus[]" value="'.$idEsc.'" '.$checked.'>
    </span>
  </label>';
};

$html = '';

/**
 * Estilos compactos e scripts (escopo local)
 */
$html .= '
<style>
  .perm-toolbar{
    display:flex; gap:8px; flex-wrap:wrap;
    margin-bottom:10px;
  }
  .perm-card{
    border:1px solid var(--line);
    border-radius:14px;
    background:#fff;
    padding:10px;
    margin-bottom:10px;
  }
  .perm-title{
    font-weight:800;
    font-size:13px;
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:8px;
    color:#0f172a;
  }
  .perm-item{
    padding:8px 10px;
    border-radius:12px;
    border:1px solid #eef2f7;
    margin-bottom:6px;
    background:#fff;
  }
  .perm-item:last-child{ margin-bottom:0; }
  .perm-item:hover{ background: rgba(2,132,199,0.04); }
  .perm-ico{ width:18px; text-align:center; font-size:14px; opacity:.95; }
  .perm-txt{ font-size:13px; font-weight:700; }
  .perm-card .form-check-input{ cursor:pointer; }
  .perm-mini-note{ font-size:12px; color:#64748b; }


  .perm-actions{
  display:grid;
  grid-template-columns: repeat(3, 1fr);
  gap:10px;
}

@media (max-width: 768px){
  .perm-actions{
    grid-template-columns: 1fr;
  }
}


.perm-setores-grid{
  display:grid;
  grid-template-columns: repeat(2, 1fr);
  gap:10px;
}

@media (max-width: 768px){
  .perm-setores-grid{
    grid-template-columns: 1fr;
  }
}
</style>
';

$html .= '
<form id="formPermissoes">
  <div class="perm-toolbar">
    <button type="button"
            class="btn btn-sm btn-outline-primary rounded-3"
            data-perm-action="all">
      <i class="bi bi-check2-square me-1"></i>Marcar todas
    </button>

    <button type="button"
            class="btn btn-sm btn-outline-secondary rounded-3"
            data-perm-action="none">
      <i class="bi bi-square me-1"></i>Desmarcar
    </button>
  </div>
';

// =========================
// CARD: AÇÕES DO SISTEMA
// =========================
$chkCriar  = isset($acoesMarcadas['criar'])  ? 'checked' : '';
$chkEditar = isset($acoesMarcadas['editar']) ? 'checked' : '';
$chkExcluir= isset($acoesMarcadas['excluir'])? 'checked' : '';

$html .= '
<div class="perm-card">
  <div class="perm-title">
    <i class="bi bi-lightning-charge"></i>
    <span>Ações do Sistema</span>
  </div>

  <div class="perm-actions">

    <label class="perm-item d-flex align-items-center justify-content-between gap-2">
      <span class="d-flex align-items-center gap-2 text-truncate">
        <i class="bi bi-plus-circle perm-ico"></i>
        <span class="perm-txt text-truncate">Criar / Salvar</span>
      </span>
      <span class="form-check form-switch m-0">
        <input class="form-check-input perm-check-acao" type="checkbox" name="acoes[]" value="criar" '.$chkCriar.'>
      </span>
    </label>

    <label class="perm-item d-flex align-items-center justify-content-between gap-2">
      <span class="d-flex align-items-center gap-2 text-truncate">
        <i class="bi bi-pencil perm-ico"></i>
        <span class="perm-txt text-truncate">Editar</span>
      </span>
      <span class="form-check form-switch m-0">
        <input class="form-check-input perm-check-acao" type="checkbox" name="acoes[]" value="editar" '.$chkEditar.'>
      </span>
    </label>

    <label class="perm-item d-flex align-items-center justify-content-between gap-2">
      <span class="d-flex align-items-center gap-2 text-truncate">
        <i class="bi bi-trash perm-ico"></i>
        <span class="perm-txt text-truncate">Excluir</span>
      </span>
      <span class="form-check form-switch m-0">
        <input class="form-check-input perm-check-acao" type="checkbox" name="acoes[]" value="excluir" '.$chkExcluir.'>
      </span>
    </label>

  </div>
</div>
';




// =========================
// CARD: SETORES PERMITIDOS
// =========================
$html .= '
<div class="perm-card">
  <div class="perm-title">
    <i class="bi bi-diagram-3"></i>
    <span>Setores Permitidos</span>
  </div>

  <div class="perm-mini-note mb-2">
    Marque os setores que este usuário poderá visualizar nos chamados.
  </div>
';

if (empty($rowsSetores)) {

  $html .= '<div class="text-muted small">Nenhum setor cadastrado.</div>';

} else {

  $html .= '<div class="perm-setores-grid">';

  foreach ($rowsSetores as $s) {

    $sid = (int)($s['id'] ?? 0);
    $snome = esc($s['nome'] ?? '');
    $checked = isset($setoresMarcados[$sid]) ? 'checked' : '';

    $html .= '
    <label class="perm-item d-flex align-items-center justify-content-between gap-2">
      <span class="d-flex align-items-center gap-2 text-truncate">
        <i class="bi bi-diagram-3 perm-ico"></i>
        <span class="perm-txt text-truncate">'.$snome.'</span>
      </span>

      <span class="form-check form-switch m-0">
        <input class="form-check-input perm-check perm-check-setor" 
               type="checkbox" 
               name="setores[]" 
               value="'.$sid.'" '.$checked.'>
      </span>
    </label>';
  }

  $html .= '</div>';
}

$html .= '</div>';


$renderedMenus = [];

/**
 * Render seguindo sidebar_order:
 * - item menu sem grupo (card único)
 * - item grupo (card com título do grupo)
 */


 $html .= '
<div class="perm-card">
  <div class="perm-title">
    <i class="bi bi-shield-check"></i>
    <span>Páginas do Sistema</span>
  </div>

  <div class="perm-mini-note mb-2">
    Defina quais páginas este usuário poderá acessar no menu lateral.
  </div>
</div>
';


foreach ($order as $key) {

  // MENU (somente se não estiver em grupo)
  if (isset($menuById[$key])) {
    $m = $menuById[$key];
    if (!empty($m['grupo'])) continue;

    $html .= '<div class="perm-card">';
    $html .= $renderItem($m);
    $html .= '</div>';

    $renderedMenus[$m['id']] = true;
    continue;
  }

  // GRUPO
  if (isset($grupos[$key])) {
    $gtitulo = $grupos[$key]['titulo'] ?? $key;
    $gicone  = $grupos[$key]['icone']  ?? 'bi-folder';

    // se o grupo não tiver nenhum menu, nem renderiza
    $itensGrupo = $menusPorGrupo[$key] ?? [];
    if (empty($itensGrupo)) continue;

    $html .= '<div class="perm-card">';
    $html .= '<div class="perm-title"><i class="bi '.esc($gicone).'"></i><span>'.esc($gtitulo).'</span></div>';

    foreach ($itensGrupo as $m) {
      $html .= $renderItem($m);
      $renderedMenus[$m['id']] = true;
    }

    $html .= '</div>';
    continue;
  }
}

/**
 * Fallback: menus sem grupo que não entraram na ordem
 */
$extras = array_filter($menusSemGrupo, function($m) use ($renderedMenus){
  $id = (string)($m['id'] ?? '');
  return $id !== '' && empty($renderedMenus[$id]);
});

if (!empty($extras)) {
  usort($extras, fn($a,$b) => (int)($a['ordem']??0) <=> (int)($b['ordem']??0));

  $html .= '<div class="perm-card">';
  $html .= '<div class="perm-title"><i class="bi bi-grid"></i><span>Outros</span></div>';
  foreach ($extras as $m) $html .= $renderItem($m);
  $html .= '</div>';
}

$html .= '</form>';

echo $html;

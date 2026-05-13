<?php
/**
 * Sidebar do painel (dinâmica via /painel/config/menu.php)
 */

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');

$segmentos = explode('/', $path);
$atual = end($segmentos);

if ($atual === 'painel' || $atual === '') $atual = 'dashboard';
$atual = preg_replace('/[^a-zA-Z0-9_-]/', '', $atual);

function menuAtivo($pagina, $atual) {
  return $pagina === $atual ? 'active' : '';
}

function esc($v){
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function renderSubmenu($id, $titulo, $iconeBootstrap, $linksHtml) {

  // ✅ se não tiver nenhum link dentro, não mostra o grupo
  if (trim(strip_tags($linksHtml)) === '') {
    return;
  }
  
  $isActive = (bool) preg_match('/sidebar-sublink\s+active/', $linksHtml);

  $openClass = $isActive ? 'open active' : '';
  $aria      = $isActive ? 'true' : 'false';
  $btnActive = $isActive ? 'active' : '';

  echo <<<HTML
  <div class="sidebar-group {$openClass}">
      <button type="button"
          class="sidebar-link sidebar-link-btn {$btnActive}"
          data-submenu="{$id}"
          aria-expanded="{$aria}">
          <i class="bi {$iconeBootstrap}"></i><span>{$titulo}</span>
          <i class="bi bi-chevron-down chevron"></i>
      </button>

      <div class="sidebar-submenu" data-submenu-content="{$id}">
          {$linksHtml}
      </div>
  </div>
  HTML;
}

require_once __DIR__ . '/permissoes.php';
$cfg = require __DIR__ . '/../config/menu.php';
$grupos = $cfg['grupos'] ?? [];
$menus  = $cfg['menus']  ?? [];

$perms = permissoesUsuario($pdo); // null = admin, array = comum

// Ordena menus por grupo e ordem
usort($menus, function($a, $b){
  $ga = $a['grupo'] ?? '';
  $gb = $b['grupo'] ?? '';
  if ($ga === $gb) return (int)($a['ordem'] ?? 0) <=> (int)($b['ordem'] ?? 0);
  return strcmp((string)$ga, (string)$gb);
});

// Separa menus por grupo
$menusSemGrupo = [];
$menusPorGrupo = [];

foreach ($menus as $m) {
  $grupo = $m['grupo'] ?? null;
  if (!$grupo) $menusSemGrupo[] = $m;
  else $menusPorGrupo[$grupo][] = $m;
}

// Ordena grupos por ordem
uksort($menusPorGrupo, function($a, $b) use ($grupos){
  $oa = (int)($grupos[$a]['ordem'] ?? 0);
  $ob = (int)($grupos[$b]['ordem'] ?? 0);
  if ($oa === $ob) return strcmp($a, $b);
  return $oa <=> $ob;
});
?>
<aside class="sidebar">
  <div class="sidebar-header">
    <div class="brand">
      <span class="brand-icon"><i class="bi bi-box-seam"></i></span>
      <span class="brand-text"><?= esc($nome_sistema ?? 'Sistema') ?></span>
    </div>

    <button class="btn btn-sm btn-light sidebar-toggle" id="btnToggleSidebar" type="button" title="Menu">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <nav class="sidebar-nav">

  <?php
$sidebarOrder = $cfg['sidebar_order'] ?? [];


$menusFiltrados = [];
foreach ($menus as $m) {
  $id = (string)($m['id'] ?? '');
  if ($id === '') continue;

  // se não tem permissão, não mostra
  if (!podeAcessar($perms, $id)) continue;

  $menusFiltrados[] = $m;
}
$menus = $menusFiltrados;

// indexa menus por id, e menus por grupo
$menuById = [];
$menusPorGrupo = [];

foreach ($menus as $m) {
  $menuById[$m['id']] = $m;
  $g = $m['grupo'] ?? null;
  if ($g) $menusPorGrupo[$g][] = $m;
}

// ordena itens dentro de cada grupo
foreach ($menusPorGrupo as $gid => &$itens) {
  usort($itens, fn($a,$b) => (int)($a['ordem']??0) <=> (int)($b['ordem']??0));
}
unset($itens);

// renderiza seguindo a ordem global
foreach ($sidebarOrder as $key) {

  // 1) Se for um MENU normal
  if (isset($menuById[$key])) {
    $m = $menuById[$key];
    echo '<a class="sidebar-link '.menuAtivo($m['id'], $atual).'" href="'.esc($m['url']).'">';
    echo '<i class="bi '.esc($m['icone']).'"></i><span>'.esc($m['titulo']).'</span>';
    echo '</a>';
    continue;
  }

  // 2) Se for um GRUPO (submenu)
  if (isset($grupos[$key])) {
    $gtitulo = $grupos[$key]['titulo'] ?? $key;
    $gicone  = $grupos[$key]['icone']  ?? 'bi-folder';

    $links = '';
    foreach (($menusPorGrupo[$key] ?? []) as $m) {
      $links .= '<a class="sidebar-sublink '.menuAtivo($m['id'], $atual).'" href="'.esc($m['url']).'">';
      $links .= '<i class="bi '.esc($m['icone']).'"></i><span>'.esc($m['titulo']).'</span>';
      $links .= '</a>';
    }

    renderSubmenu($key, esc($gtitulo), esc($gicone), $links);
    continue;
  }

  // Se não achou nada, ignora (evita quebrar)
}
?>


  </nav>

  <div class="sidebar-footer">
    <a class="sidebar-link danger" href="logout.php">
      <i class="bi bi-box-arrow-right"></i><span>Sair</span>
    </a>
  </div>
</aside>

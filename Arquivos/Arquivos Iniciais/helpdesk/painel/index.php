<?php
/**
 * Painel do sistema - área logada
 * Layout padrão (sidebar + topbar + páginas)
 */

require_once 'verificar.php';
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/includes/permissoes.php';
$perms = permissoesUsuario($pdo); // null = admin


// rota
$p = $_GET['p'] ?? 'dashboard';

// se não for admin, bloqueia página não permitida
if ($perms !== null && !podeAcessar($perms, $p)) {

    // tenta achar a primeira página permitida pela ordem do menu
    $cfg = require __DIR__ . '/config/menu.php';
    $menus = $cfg['menus'] ?? [];
    $sidebarOrder = $cfg['sidebar_order'] ?? [];
  
    $primeira = null;
  
    // prioridade: sidebar_order
    foreach ($sidebarOrder as $idMenu) {
      if (podeAcessar($perms, (string)$idMenu)) {
        $primeira = (string)$idMenu;
        break;
      }
    }
  
    // fallback: qualquer menu permitido
    if (!$primeira) {
      foreach ($menus as $m) {
        $idMenu = (string)($m['id'] ?? '');
        if ($idMenu && podeAcessar($perms, $idMenu)) {
          $primeira = $idMenu;
          break;
        }
      }
    }
  
    // se não tiver nenhuma permissão
    $p = $primeira ?: 'sem_permissao';
  }


$permitidas = [
'dashboard',
'chamados',
'clientes',
'usuarios',
'relatorios',
'cargos',
'sem_permissao',
'logs',
'status_abertura',
'abertura',
'setores',
];


if (!in_array($p, $permitidas)) {
    $p = 'dashboard';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include(__DIR__ . '/includes/head.php'); ?>
</head>

<body>
<div class="app">

    <?php include(__DIR__ . '/includes/sidebar.php'); ?>

    <div class="main">
        <?php include(__DIR__ . '/includes/topbar.php'); ?>

        <main class="content">
            <?php include(__DIR__ . "/paginas/{$p}.php"); ?>
        </main>

        <?php include(__DIR__ . '/includes/footer.php'); ?>
    </div>

</div>


<!-- Bootstrap JS (necessário para modais) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart.js (somente no dashboard) -->
<?php if ($p === 'dashboard') { ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<?php } ?>

<!-- ========================= -->
<!-- jQuery (obrigatório para DataTables) -->
<!-- ========================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<!-- DataTables core -->
<script src="assets/datatable/js/jquery.dataTables.min.js"></script>
<script src="assets/datatable/js/dataTables.bootstrap5.js"></script>

<!-- DataTables Responsive -->
<script src="assets/datatable/dataTables.responsive.min.js"></script>
<script src="assets/datatable/responsive.bootstrap5.min.js"></script>

<!-- DataTables Buttons (opcional, mas você já tem) -->
<script src="assets/datatable/js/dataTables.buttons.min.js"></script>
<script src="assets/datatable/js/buttons.bootstrap5.min.js"></script>
<script src="assets/datatable/js/buttons.html5.min.js"></script>
<script src="assets/datatable/js/buttons.print.min.js"></script>
<script src="assets/datatable/js/buttons.colVis.min.js"></script>
<script src="assets/datatable/js/jszip.min.js"></script>

<!-- ========================= -->
<!-- Scripts do sistema -->
<!-- ========================= -->
<script src="assets/js/painel.js"></script>
<script src="assets/js/masks.js"></script>
<script src="assets/js/funcoes.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mensagens (IMPORTANTE: exposto no window) -->
<script src="../js/mensagens.js"></script>

<!-- CRUD genérico -->
<script src="assets/js/crud.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


</body>
</html>

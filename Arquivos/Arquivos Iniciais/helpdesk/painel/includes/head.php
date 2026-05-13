<?php
// Ícone do sistema (config ou fallback)
$icone_sistema = $config['icone'] ?? '';
if ($icone_sistema === '' || !file_exists(__DIR__ . '/../../uploads/' . $icone_sistema)) {
    $icone_sistema = 'sem_foto.webp';
}

$icone_url = '../uploads/' . $icone_sistema;
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Painel - <?= htmlspecialchars($nome_sistema) ?></title>

<!-- Ícone do sistema -->
<link rel="icon" type="image/webp" href="<?= htmlspecialchars($icone_url) ?>">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($icone_url) ?>">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- ========================= -->
<!-- DataTables (Bootstrap 5 - Local) -->
<link rel="stylesheet" href="assets/datatable/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="assets/datatable/css/responsive.bootstrap5.min.css">


<style>
  :root{
    --cor-primaria: <?= htmlspecialchars($cor_primaria) ?>;
    --cor-secundaria: <?= htmlspecialchars($cor_secundaria) ?>;
  }
</style>

<link rel="stylesheet" href="assets/css/painel.css">

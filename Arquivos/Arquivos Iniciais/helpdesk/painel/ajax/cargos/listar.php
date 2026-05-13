<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

header('Content-Type: text/html; charset=utf-8');

$tabela = 'cargos';

// Como você ainda não usa SaaS:
$stmt = $pdo->query("SELECT * FROM {$tabela} ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-3 p-md-4">

    <div class="table-responsive">
      <table class="table table-sm align-middle w-100" id="tabela">
        <thead class="table-light">
          <tr>
            
            <th>Nome do Cargo</th>
            <th style="width:170px;" class="text-end">Ações</th>
          </tr>
        </thead>

        <tbody>
        <?php if (count($rows) === 0) { ?>
          <tr>
            <td colspan="3" class="text-muted small py-3">
              Nenhum cargo cadastrado.
            </td>
          </tr>
        <?php } else { ?>

          <?php foreach ($rows as $r) {
            $id   = (int)($r['id'] ?? 0);
            $nome = esc($r['nome'] ?? '');
          ?>
            <tr>
              
              <td class="fw-semibold"><?= $nome ?></td>

              <td class="text-end">
                <div class="btn-group btn-group-sm" role="group" aria-label="Ações">

                <?php if (podeFazer('editar')) { ?>
                  <button type="button"
                          class="btn btn-outline-primary"
                          onclick="editar(<?= $id ?>)"
                          title="Editar">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <?php } ?>

                  <?php if (podeFazer('excluir')) { ?>
                  <button type="button"
                          class="btn btn-outline-danger"
                          onclick="excluir(<?= $id ?>)"
                          title="Excluir">
                    <i class="bi bi-trash"></i>
                  </button>
                  <?php } ?>


                </div>
              </td>
            </tr>
          <?php } ?>

        <?php } ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
/* =========================
   DATATABLE
========================= */
(function () {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

  if (jQuery.fn.DataTable.isDataTable('#tabela')) {
    jQuery('#tabela').DataTable().destroy();
  }

  jQuery('#tabela').DataTable({
    responsive: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[1, 'asc']],
    columnDefs: [
      { orderable: false, targets: [2] }
    ],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json"
    }
  });
})();
</script>

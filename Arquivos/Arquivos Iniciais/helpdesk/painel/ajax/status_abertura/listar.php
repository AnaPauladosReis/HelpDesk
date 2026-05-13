<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

header('Content-Type: text/html; charset=utf-8');

$tabela = 'chamados_status';

// Não SaaS (empresa = 0), então lista geral
// Ordena por ordem ASC, depois nome ASC (bem melhor pra status)
$stmt = $pdo->query("SELECT id, nome, cor, ordem, ativo, padrao, fechado FROM {$tabela} ORDER BY ordem ASC, nome ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function corHexOk($c) {
  $c = trim((string)$c);
  if ($c === '') return false;
  if ($c[0] !== '#') $c = '#'.$c;
  return (bool)preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $c);
}
?>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-3 p-md-4">

    <div class="table-responsive">
      <table class="table table-sm align-middle w-100" id="tabela">
        <thead class="table-light">
          <tr>
            <th style="width:90px;">Cor</th>
            <th>Status</th>
            <th style="width:110px;">Ordem</th>
            <th style="width:120px;">Padrão</th>
            <th style="width:120px;">Ativo</th>
            <th style="width:120px;">Fecha</th>
            <th style="width:170px;" class="text-end">Ações</th>
          </tr>
        </thead>

        <tbody>
        <?php if (count($rows) === 0) { ?>
          <tr>
            <td colspan="7" class="text-muted small py-3">
              Nenhum status cadastrado.
            </td>
          </tr>
        <?php } else { ?>

          <?php foreach ($rows as $r) {
            $id      = (int)($r['id'] ?? 0);
            $nome    = esc($r['nome'] ?? '');
            $ordem   = (int)($r['ordem'] ?? 0);

            $ativo   = esc($r['ativo'] ?? 'Sim');
            $padrao  = esc($r['padrao'] ?? 'Não');
            $fechado = esc($r['fechado'] ?? 'Não');

            $corRaw  = (string)($r['cor'] ?? '#6c757d');
            $cor     = trim($corRaw);
            if ($cor !== '' && $cor[0] !== '#') $cor = '#'.$cor;
            if (!corHexOk($cor)) $cor = '#6c757d';

            $badgeTxt = ($padrao === 'Sim') ? 'Padrão' : $cor;
          ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="rounded-circle d-inline-block border"
                        style="width:18px;height:18px;background:<?= esc($cor) ?>;"></span>
                  <span class="badge rounded-pill"
                        style="background:<?= esc($cor) ?>;">
                    <?= esc($badgeTxt) ?>
                  </span>
                </div>
              </td>

              <td class="fw-semibold"><?= $nome ?></td>

              <td><?= $ordem ?></td>

              <td>
                <?php if ($padrao === 'Sim') { ?>
                  <span class="badge text-bg-primary">Sim</span>
                <?php } else { ?>
                  <span class="badge text-bg-secondary">Não</span>
                <?php } ?>
              </td>

              <td>
                <?php if ($ativo === 'Sim') { ?>
                  <span class="badge text-bg-success">Sim</span>
                <?php } else { ?>
                  <span class="badge text-bg-danger">Não</span>
                <?php } ?>
              </td>

              <td>
                <?php if ($fechado === 'Sim') { ?>
                  <span class="badge text-bg-dark">Sim</span>
                <?php } else { ?>
                  <span class="badge text-bg-light border">Não</span>
                <?php } ?>
              </td>

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
    // ✅ ordena por "Ordem" e depois "Status"
    order: [[2, 'asc'], [1, 'asc']],
    columnDefs: [
      { orderable: false, targets: [0, 6] } // cor e ações
    ],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json"
    }
  });
})();
</script>

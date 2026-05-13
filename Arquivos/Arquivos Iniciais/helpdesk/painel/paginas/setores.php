<?php
/**
 * Página: Setores (CRUD)
 * - Mantém padrão de modais (igual Cargos)
 * - Usa Crud (assets/js/crud.js) + Mensagens (js/mensagens.js)
 */

@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

$id_empresa = (int)($usuario_empresa ?? ($_SESSION['empresa'] ?? 0));
$pag = 'setores';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <?php if (podeFazer('criar')) { ?>
    <button type="button" class="btn btn-salvar-sistema" onclick="novo()">
      <i class="bi bi-plus-lg me-2"></i>Novo Setor
    </button>
  <?php } ?>
</div>

<div id="listagem"></div>

<!-- MODAL (GENÉRICO) -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalFormLabel">
          <i class="bi bi-diagram-3 me-2"></i>
          <span id="modalFormTitulo">Novo Registro</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <!-- FORM PADRÃO -->
      <form id="form" method="post" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">

          <input type="hidden" name="id" id="id" value="0">
          <input type="hidden" name="empresa" value="<?= $id_empresa ?>">

          <div class="row g-3">

            <div class="col-12">
              <div class="form-floating">
                <input type="text" class="form-control" id="nome" name="nome"
                       placeholder="Nome" maxlength="100" required>
                <label for="nome">Nome do Setor</label>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-cancelar-sistema" data-bs-dismiss="modal">
            Cancelar
          </button>

          <?php if (podeFazer('criar') || podeFazer('editar')) { ?>
            <button type="submit" class="btn btn-salvar-sistema">
              <i class="bi bi-save me-2"></i>Salvar
            </button>
          <?php } ?>
        </div>
      </form>

    </div>
  </div>
</div>

<script> window.pag = "<?= $pag ?>"; </script>

<script>
/**
 * BLOCO GLOBAL (reutilizável em outras páginas)
 */

/* =========================
   CONFIG / HELPERS
========================= */

// evita crash se Mensagens não estiver em window
function getMsg() {
  try {
    if (typeof window !== "undefined" && window.Mensagens) return window.Mensagens;
    if (typeof Mensagens !== "undefined") return Mensagens;
  } catch (e) {}
  return null;
}

// helper: garante que o DataTable não fica “duplo”
function reiniciarDataTable() {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

  if (jQuery.fn.DataTable.isDataTable("#tabela")) {
    jQuery("#tabela").DataTable().destroy();
  }

  jQuery("#tabela").DataTable({
    responsive: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[1, "asc"]],
  });
}

/* =========================
   FUNÇÕES PRINCIPAIS (GLOBAIS)
========================= */
window.novo = function () {
  Crud.resetForm("#form");
  document.getElementById("id").value = 0;
  Crud.setModalTitle("modalFormTitulo", "Novo Setor");
  Crud.showModal("modalForm");
};

window.carregarListagem = async function () {
  const Msg = getMsg();

  try {
    const container = document.getElementById("listagem");

    // destrói DT antes de trocar HTML
    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable("#tabela")) {
      jQuery("#tabela").DataTable().destroy();
    }

    const html = await fetch(`ajax/${pag}/listar.php`).then(r => r.text());
    container.innerHTML = html;

    // reinicia DT depois que entrou no DOM
    reiniciarDataTable();

  } catch (e) {
    //if (Msg && Msg.erro) Msg.erro("Erro", "Não foi possível carregar a listagem.");
    //console.error(e);
  }
};

/* =========================
   INIT GLOBAL
========================= */
(function initPagina() {
  document.addEventListener("DOMContentLoaded", function () {

    // submit ajax
    Crud.bindSubmit({
      formSelector: "#form",
      url: `ajax/${pag}/salvar.php`,
      modalId: "modalForm",
      reloadOnSuccess: false,
      onSuccess: () => window.carregarListagem()
    });

    // carrega listagem ao abrir a página
    window.carregarListagem();
  });
})();
</script>

<script>
/* =========================
   FUNÇÕES CRUD (GLOBAIS)
========================= */

// onclick="editar(1)"
window.editar = function (id) {
  if (!id) return;

  const Msg = getMsg();
  if (Msg && Msg.carregando) {
    Msg.carregando("Carregando...", "Buscando dados");
  }

  fetch(`ajax/${pag}/buscar.php?id=` + encodeURIComponent(id))
    .then(resp => resp.json())
    .then(json => {
      if (!json.ok) throw new Error(json.msg || "Erro ao carregar registro");

      const c = json.data || {};

      // abre modal
      Crud.showModal("modalForm");
      Crud.setModalTitle("modalFormTitulo", "Editar Setor");

      const form = document.querySelector("#form");
      if (!form) throw new Error("Form #form não encontrado.");

      const elId = form.querySelector('[name="id"]');
      if (elId) elId.value = c.id || 0;

      const elNome = form.querySelector('[name="nome"]');
      if (elNome) elNome.value = c.nome || "";

      if (Msg && Msg.fechar) Msg.fechar();
    })
    .catch(err => {
      if (Msg && Msg.fechar) Msg.fechar();
      if (Msg && Msg.erro) Msg.erro("Erro", err.message);
      console.error(err);
    });
};

window.excluir = async function (id) {
  if (!id) return;

  const Msg = getMsg();

  try {
    const res = await Msg.confirmar(
      "Excluir registro?",
      "Essa ação não pode ser desfeita.",
      "Sim, excluir",
      "Cancelar"
    );

    if (!res || !res.isConfirmed) return;

    Msg.carregando("Excluindo...", "Aguarde");

    const resp = await fetch(`ajax/${pag}/excluir.php`, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: "id=" + encodeURIComponent(id)
    });

    const ct = resp.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      const txt = await resp.text();
      throw new Error("Resposta inesperada do servidor.\n" + txt.slice(0, 300));
    }

    const json = await resp.json();
    if (!json.ok) throw new Error(json.msg || "Não foi possível excluir.");

    Msg.fechar();
    Msg.salvoSemReload(json.msg || "Setor excluído!");

    await window.carregarListagem();

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro ao excluir", err.message || "Falha ao excluir.");
    console.error(err);
  }
};
</script>
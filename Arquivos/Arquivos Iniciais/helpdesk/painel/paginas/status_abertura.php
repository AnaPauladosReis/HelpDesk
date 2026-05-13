<?php
/**
 * Página: Status Abertura (CRUD)
 * - Mantém padrão de modais
 * - Usa Crud (assets/js/crud.js) + Mensagens (../js/mensagens.js)
 */

@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

$pag = 'status_abertura';
?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <?php if (podeFazer('criar')) { ?>
    <button type="button" class="btn btn-salvar-sistema" onclick="novo()">
      <i class="bi bi-plus-lg me-2"></i>Novo Status
    </button>
  <?php } ?>
</div>

<div id="listagem"></div>

<!-- MODAL (STATUS ABERTURA) -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalFormLabel">
          <i class="bi bi-palette me-2"></i>
          <span id="modalFormTitulo">Novo Status</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="form" method="post" autocomplete="off">
        <div class="modal-body">

          <input type="hidden" name="id" id="id" value="0">
          <input type="hidden" name="empresa" value="<?= (int)$id_empresa ?>">

          <div class="row g-3">

            <!-- Nome -->
            <div class="col-12">
              <div class="form-floating">
                <input type="text" class="form-control" id="nome" name="nome"
                       placeholder="Nome do Status" maxlength="60" required>
                <label for="nome">Nome do Status</label>
              </div>
            </div>

            <!-- Cor -->
            <div class="col-12 col-md-6">              
              <div class="d-flex gap-2 align-items-center">
                <input type="color" class="form-control form-control-color" id="cor_picker" value="#6c757d" title="Escolher cor" style="width: 60px;">
                <div class="form-floating flex-grow-1">
                  <input type="text" class="form-control" id="cor" name="cor"
                         placeholder="#6c757d" maxlength="20" value="#6c757d" required>
                  <label for="cor">Cor do Status (HEX)</label>
                </div>
              </div>
              <small class="text-muted">Ex: #0d6efd, #198754, #dc3545</small>
            </div>

            <!-- Ordem -->
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="number" class="form-control" id="ordem" name="ordem"
                       placeholder="Ordem" value="0" min="0" step="1">
                <label for="ordem">Ordem</label>
              </div>
              <small class="text-muted">Define a ordem no select/listas.</small>
            </div>

            <!-- Ativo -->
            <div class="col-12 col-md-4">
              <div class="form-floating">
                <select class="form-select" id="ativo" name="ativo">
                  <option value="Sim" selected>Sim</option>
                  <option value="Não">Não</option>
                </select>
                <label for="ativo">Ativo</label>
              </div>
            </div>

            <!-- Padrão -->
            <div class="col-12 col-md-4">
              <div class="form-floating">
                <select class="form-select" id="padrao" name="padrao">
                  <option value="Não" selected>Não</option>
                  <option value="Sim">Sim</option>
                </select>
                <label for="padrao">Status Padrão</label>
              </div>
              <small class="text-muted">O status inicial ao abrir chamado.</small>
            </div>

            <!-- Fechado -->
            <div class="col-12 col-md-4">
              <div class="form-floating">
                <select class="form-select" id="fechado" name="fechado">
                  <option value="Não" selected>Não</option>
                  <option value="Sim">Sim</option>
                </select>
                <label for="fechado">Encerra Chamado</label>
              </div>
              <small class="text-muted">Se “Sim”, marca chamado como encerrado.</small>
            </div>

            <!-- Preview -->
            <div class="col-12">
              <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <span id="badgePreview" class="badge rounded-pill" style="background:#6c757d;">Prévia</span>
                  <span class="text-muted">Visualização rápida do status</span>
                </div>
                <span class="text-muted small" id="previewText">#6c757d</span>
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

<script>window.pag = "<?= $pag ?>";</script>

<script>
/* =========================
   HELPERS
========================= */
function getMsg() {
  try {
    if (typeof window !== "undefined" && window.Mensagens) return window.Mensagens;
    if (typeof Mensagens !== "undefined") return Mensagens;
  } catch (e) {}
  return null;
}

function reiniciarDataTable() {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

  if (jQuery.fn.DataTable.isDataTable("#tabela")) {
    jQuery("#tabela").DataTable().destroy();
  }

  jQuery("#tabela").DataTable({
    responsive: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    // deixe sem order fixo (cada listagem pode ter colunas diferentes)
  });
}

/* =========================
   PREVIEW COR
========================= */
function normalizaHex(v) {
  v = (v || "").trim();
  if (!v) return "#6c757d";
  if (v[0] !== "#") v = "#" + v;
  // aceita #RGB ou #RRGGBB
  if (!/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)) return "#6c757d";
  return v;
}
function atualizarPreviewCor() {
  const corInput = document.getElementById("cor");
  const picker   = document.getElementById("cor_picker");
  const badge    = document.getElementById("badgePreview");
  const txt      = document.getElementById("previewText");

  if (!corInput || !picker || !badge || !txt) return;

  const hex = normalizaHex(corInput.value);
  corInput.value = hex;
  picker.value = hex;
  badge.style.background = hex;
  txt.textContent = hex;
}

/* =========================
   FUNÇÕES PRINCIPAIS
========================= */
window.novo = function () {
  Crud.resetForm("#form");
  document.getElementById("id").value = 0;

  // defaults
  document.getElementById("cor").value = "#6c757d";
  document.getElementById("ordem").value = 0;
  document.getElementById("ativo").value = "Sim";
  document.getElementById("padrao").value = "Não";
  document.getElementById("fechado").value = "Não";

  Crud.setModalTitle("modalFormTitulo", "Novo Status");
  Crud.showModal("modalForm");
  atualizarPreviewCor();
};

window.carregarListagem = async function () {
  const Msg = getMsg();

  try {
    const container = document.getElementById("listagem");

    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable("#tabela")) {
      jQuery("#tabela").DataTable().destroy();
    }

    const html = await fetch(`ajax/${pag}/listar.php`).then(r => r.text());
    container.innerHTML = html;

    reiniciarDataTable();
  } catch (e) {
    //if (Msg && Msg.erro) Msg.erro("Erro", "Não foi possível carregar a listagem.");
    console.error(e);
  }
};

/* =========================
   INIT
========================= */
(function initPagina() {
  document.addEventListener("DOMContentLoaded", function () {

    // eventos cor
    const corInput = document.getElementById("cor");
    const picker   = document.getElementById("cor_picker");
    if (corInput) corInput.addEventListener("input", atualizarPreviewCor);
    if (picker) picker.addEventListener("input", () => {
      document.getElementById("cor").value = picker.value;
      atualizarPreviewCor();
    });
    atualizarPreviewCor();

    // submit ajax
    Crud.bindSubmit({
      formSelector: "#form",
      url: `ajax/${pag}/salvar.php`,
      modalId: "modalForm",
      reloadOnSuccess: false,
      onSuccess: () => window.carregarListagem()
    });

    // carrega listagem
    window.carregarListagem();
  });
})();
</script>

<script>
/* =========================
   EDITAR / EXCLUIR
========================= */
window.editar = function (id) {
  if (!id) return;

  const Msg = getMsg();
  if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando dados");

  fetch(`ajax/${pag}/buscar.php?id=` + encodeURIComponent(id))
    .then(resp => resp.json())
    .then(json => {
      if (!json.ok) throw new Error(json.msg || "Erro ao carregar registro");

      const s = json.data || {};

      Crud.showModal("modalForm");
      Crud.setModalTitle("modalFormTitulo", "Editar Status");

      const form = document.querySelector("#form");
      if (!form) throw new Error("Form #form não encontrado.");

      // campos
      form.querySelector('[name="id"]').value = s.id || 0;
      form.querySelector('[name="nome"]').value = s.nome || "";
      form.querySelector('[name="cor"]').value = s.cor || "#6c757d";
      form.querySelector('[name="ordem"]').value = (s.ordem ?? 0);
      form.querySelector('[name="ativo"]').value = s.ativo || "Sim";
      form.querySelector('[name="padrao"]').value = s.padrao || "Não";
      form.querySelector('[name="fechado"]').value = s.fechado || "Não";

      atualizarPreviewCor();

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
    if (!Msg || !Msg.confirmar) {
      const ok = confirm("Excluir registro? Essa ação não pode ser desfeita.");
      if (!ok) return;
    } else {
      const res = await Msg.confirmar("Excluir registro?", "Essa ação não pode ser desfeita.", "Sim, excluir", "Cancelar");
      if (!res || !res.isConfirmed) return;
      Msg.carregando("Excluindo...", "Aguarde");
    }

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

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(json.msg || "Registro excluído!");

    await window.carregarListagem();

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro ao excluir", err.message || "Falha ao excluir.");
    console.error(err);
  }
};
</script>

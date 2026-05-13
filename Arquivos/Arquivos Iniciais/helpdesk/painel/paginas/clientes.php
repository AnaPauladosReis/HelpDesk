<?php
/**
 * Página: Clientes (CRUD)
 * - Mantém padrão de modais
 * - Usa Crud (assets/js/crud.js) + Mensagens (js/mensagens.js)
 */

@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

$pag = 'clientes';
$podeEditar = podeFazer('editar') ? 1 : 0;
?>
<div class="d-flex align-items-center justify-content-between mb-3">


  <!-- ESQUERDA -->
  <div>
    <?php if (podeFazer('criar')) { ?>
      <button type="button" class="btn btn-salvar-sistema" onclick="novo()">
        <i class="bi bi-plus-lg me-2"></i>Novo Cliente
      </button>
    <?php } ?>
  </div>

  <!-- DIREITA -->
  <div class="d-flex gap-2">

    <!-- IMPORTAR (XLS/XLSX) -->
    <?php if (podeFazer('criar')) { ?>
      <button type="button" class="btn btn-outline-primary" onclick="importarClientesXls()">
        <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar
      </button>
    <?php } ?>

    <!-- EXPORTAR (XLS/XLSX) -->
    <button type="button" class="btn btn-outline-success" onclick="exportarClientesXls()">
      <i class="bi bi-file-earmark-excel me-2"></i>Exportar
    </button>

    <!-- RELATÓRIO PDF -->
    <a href="relatorios/clientes.php" target="_blank" class="btn btn-outline-secondary">
      <i class="bi bi-file-earmark-pdf me-2"></i>Relatório
    </a>

  </div>

</div>

<!-- input invisível p/ upload -->
<input type="file"
       id="inputImportClientesXls"
       accept=".xls,.xlsx"
       style="display:none;">



<div id="listagem"></div>

<!-- MODAL (CLIENTES) -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-labelledby="modalFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalFormLabel">
          <i class="bi bi-person-plus me-2"></i>
          <span id="modalFormTitulo">Novo Registro</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="form" method="post" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">

          <input type="hidden" name="id" id="id" value="0">
          <input type="hidden" name="foto_atual" id="foto_atual" value="sem_foto.webp">

          <div class="row g-3">

            <!-- LINHA 1 -->
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="nome" name="nome"
                       placeholder="Nome" maxlength="120" required>
                <label for="nome">Nome</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="telefone" name="telefone"
                       placeholder="Telefone" maxlength="20" data-mask="tel">
                <label for="telefone">Telefone</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
              <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj"
       placeholder="CPF/CNPJ" maxlength="18" data-mask="cpfcnpj">

                <label for="cpf_cnpj">CPF/CNPJ</label>
              </div>
            </div>

            <!-- LINHA 2 -->
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="E-mail" maxlength="120">
                <label for="email">E-mail</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <select class="form-select" id="tipo" name="tipo">
                  <option value="">Selecione</option>
                  <option value="Pessoa Física">Pessoa Física</option>
                  <option value="Pessoa Jurídica">Pessoa Jurídica</option>
                </select>
                <label for="tipo">Tipo</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <select class="form-select" id="ativo" name="ativo" required>
                  <option value="Sim">Sim</option>
                  <option value="Não">Não</option>
                </select>
                <label for="ativo">Ativo</label>
              </div>
            </div>


<!-- NOTIFICAÇÃO (somente ao criar) -->
<div class="col-12 col-md-2 d-none" id="wrap_notificacao_cliente">
  <div class="form-floating">
    <select class="form-select" id="enviar_notificacao" name="enviar_notificacao">
      <option value="Sim" selected>Sim</option>
      <option value="Não">Não</option>
    </select>
    <label for="enviar_notificacao">Notificar cadastro?</label>
  </div>
</div>



            <!-- FOTO -->
            <div class="col-12">
              <label class="form-label small fw-semibold mb-1">Foto</label>

              <div class="d-flex align-items-center gap-2">
                <img
                  src="../uploads/clientes/sem_foto.webp"
                  alt="Foto"
                  id="foto_preview"
                  style="width:44px;height:44px;object-fit:cover;border-radius:12px;border:1px solid #cbd5e1;"
                >
                <input class="form-control" type="file" name="foto" id="foto" accept="image/*">
              </div>

              <div class="form-text">JPG, PNG ou WEBP (até 2MB).</div>
            </div>

            <!-- ENDEREÇO -->
            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="text" class="form-control" id="cep" name="cep"
                       placeholder="CEP" maxlength="10" data-mask="cep" data-cep>
                <label for="cep">CEP</label>
              </div>
            </div>

            <div class="col-12 col-md-8">
              <div class="form-floating">
                <input type="text" class="form-control" id="endereco" name="endereco"
                       placeholder="Endereço" maxlength="150" data-cep-endereco>
                <label for="endereco">Endereço</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="text" class="form-control" id="numero" name="numero"
                       placeholder="Nº" maxlength="20">
                <label for="numero">Nº</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="bairro" name="bairro"
                       placeholder="Bairro" maxlength="80" data-cep-bairro>
                <label for="bairro">Bairro</label>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="form-floating">
                <input type="text" class="form-control" id="cidade" name="cidade"
                       placeholder="Cidade" maxlength="80" data-cep-cidade>
                <label for="cidade">Cidade</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
              <select
    class="form-select js-select2"
    id="estado"
    name="estado"
    
    data-placeholder=""
    data-dropdown-parent="#modalForm"
    data-allow-clear="1"
    data-cep-estado    
    style="width:100%"
  >
                  <option value="">UF</option>
                  <option value="AC">AC</option><option value="AL">AL</option><option value="AP">AP</option><option value="AM">AM</option>
                  <option value="BA">BA</option><option value="CE">CE</option><option value="DF">DF</option><option value="ES">ES</option>
                  <option value="GO">GO</option><option value="MA">MA</option><option value="MT">MT</option><option value="MS">MS</option>
                  <option value="MG">MG</option><option value="PA">PA</option><option value="PB">PB</option><option value="PR">PR</option>
                  <option value="PE">PE</option><option value="PI">PI</option><option value="RJ">RJ</option><option value="RN">RN</option>
                  <option value="RS">RS</option><option value="RO">RO</option><option value="RR">RR</option><option value="SC">SC</option>
                  <option value="SP">SP</option><option value="SE">SE</option><option value="TO">TO</option>
                </select>
                <label for="estado">UF</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="complemento" name="complemento"
                       placeholder="Compl." maxlength="60" data-cep-complemento>
                <label for="complemento">Compl.</label>
              </div>
            </div>

            <!-- OBSERVAÇÕES -->
            <div class="col-12">
              <div class="form-floating">
                <textarea class="form-control" id="observacoes" name="observacoes"
                          placeholder="Observações" style="height: 90px"></textarea>
                <label for="observacoes">Observações</label>
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
  window.PERMS_ACOES = window.PERMS_ACOES || {};
  window.PERMS_ACOES.editar = <?= (int)$podeEditar ?>;
</script>

<script>
/* =========================
   CONFIG / HELPERS
========================= */
const FOTO_PADRAO = "../uploads/clientes/sem_foto.webp";


function toggleNotificacaoCliente() {
  const idEl  = document.getElementById("id");
  const wrap  = document.getElementById("wrap_notificacao_cliente");
  const sel   = document.getElementById("enviar_notificacao");

  if (!idEl || !wrap || !sel) return;

  const id = parseInt(idEl.value || "0", 10);

  if (id === 0) {
    wrap.classList.remove("d-none");
    sel.value = "Sim"; // padrão quando for novo
  } else {
    wrap.classList.add("d-none");
    sel.value = "Não"; // segurança na edição
  }
}

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
    order: [[0, "asc"]] // Nome
  });
}

/* =========================
   FUNÇÕES PRINCIPAIS (GLOBAIS)
========================= */
window.novo = function () {
  Crud.resetForm("#form");
  document.getElementById("foto_preview").src = FOTO_PADRAO;
  document.getElementById("id").value = 0;
  document.getElementById("foto_atual").value = "sem_foto.webp";

  toggleNotificacaoCliente();
  // ✅ LIMPA O SELECT2 DO ESTADO
  const estado = document.getElementById("estado");
  if (estado) {
    estado.value = ""; // limpa o <select>

    // se for select2, sincroniza UI
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
      jQuery(estado).val("").trigger("change.select2");
    } else {
      estado.dispatchEvent(new Event("change", { bubbles: true }));
    }
  }
  
  Crud.setModalTitle("modalTitulo", "Novo Registro");
  Crud.showModal("modalForm");
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
    if (Msg && Msg.erro) Msg.erro("Erro", "Não foi possível carregar a listagem.");
    console.error(e);
  }
};

(function initPagina() {
  document.addEventListener("DOMContentLoaded", function () {
    Crud.bindImagePreview({
      inputId: "foto",
      previewId: "foto_preview",
      fallbackSrc: FOTO_PADRAO,
      maxMB: 2
    });

    Crud.bindSubmit({
      formSelector: "#form",
      url: `ajax/${pag}/salvar.php`,
      modalId: "modalForm",
      reloadOnSuccess: false,
      onSuccess: () => window.carregarListagem()
    });

    window.carregarListagem();
  });
})();
</script>

<script>
/* =========================
   CRUD (EDITAR / MOSTRAR / EXCLUIR)
========================= */

// onclick="editar(1)"
window.editar = function (id) {
  if (!id) return;

  const Msg = getMsg();
  if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando dados");

  

  const setVal = (form, name, value) => {
    const el = form.querySelector(`[name="${name}"]`);
    if (!el) return; // <- evita o erro
    el.value = value ?? "";
  };

  

  fetch(`ajax/${pag}/buscar.php?id=` + encodeURIComponent(id), { cache: "no-store" })
    .then(r => r.json())
    .then(json => {
      if (!json.ok) throw new Error(json.msg || "Erro ao carregar registro");

      const u = json.data || {};

      Crud.showModal("modalForm");
      Crud.setModalTitle("modalTitulo", "Editar Registro");

      const form = document.querySelector("#form");
      if (!form) throw new Error("Form #form não encontrado.");

      // ✅ campos de CLIENTES (só seta se existir)
      setVal(form, "id", u.id || 0);
      setVal(form, "nome", u.nome);
      setVal(form, "email", u.email);
      setVal(form, "telefone", u.telefone);
      setVal(form, "cpf_cnpj", u.cpf_cnpj);

      setVal(form, "ativo", u.ativo || "Sim");
      setVal(form, "tipo", u.tipo);
      setVal(form, "cep", u.cep);
      setVal(form, "endereco", u.endereco);
      setVal(form, "numero", u.numero);
      setVal(form, "bairro", u.bairro);
      setVal(form, "cidade", u.cidade);
      setVal(form, "estado", u.estado);
      jQuery('[name="estado"]').trigger('change');
      setVal(form, "complemento", u.complemento);
      setVal(form, "observacoes", u.observacoes);

      toggleNotificacaoCliente();

      // foto atual + preview
      const fotoAtual = document.getElementById("foto_atual");
      if (fotoAtual) fotoAtual.value = u.foto || "sem_foto.webp";

      const preview = document.getElementById("foto_preview");
      if (preview) preview.src = u.foto_url || "../uploads/clientes/sem_foto.webp";

      // reaplica máscaras caso precise
      if (window.Masks && window.Masks.bind) window.Masks.bind(form);

      if (Msg && Msg.fechar) Msg.fechar();
    })
    .catch(err => {
      if (Msg && Msg.fechar) Msg.fechar();
      if (Msg && Msg.erro) Msg.erro("Erro", err.message);
      console.error(err);
    });
};



</script>



<script>
/* =========================
   MOSTRAR + EXCLUIR (CLIENTES)
========================= */

window.mostrar = function (id) {
  if (!id) return;

  const Msg = getMsg();
  if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando dados");

  const FOTO_PADRAO_CLIENTES = "../uploads/clientes/sem_foto.webp";

  // escape simples para não quebrar HTML do Swal
  const esc = (v) => String(v ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

  fetch(`ajax/${pag}/buscar.php?id=${encodeURIComponent(id)}`, { cache: "no-store" })
    .then(async (r) => {
      if (!r.ok) {
        const txt = await r.text().catch(() => "");
        throw new Error("Falha ao buscar dados.\n" + (txt ? txt.slice(0, 200) : ""));
      }

      const ct = r.headers.get("content-type") || "";
      if (!ct.includes("application/json")) {
        const txt = await r.text().catch(() => "");
        throw new Error("Resposta inesperada do servidor.\n" + (txt ? txt.slice(0, 200) : ""));
      }

      return r.json();
    })
    .then((json) => {
      if (!json || !json.ok) {
        throw new Error((json && json.msg) ? json.msg : "Erro ao buscar registro");
      }

      const u = json.data || {};
      const foto = u.foto_url || FOTO_PADRAO_CLIENTES;

      const ativo = String(u.ativo || "Sim").toLowerCase();
      const ativoEhNao = (ativo === "não" || ativo === "nao");
      const badgeAtivo = ativoEhNao
        ? 'text-bg-danger">Não'
        : 'text-bg-success">Sim';

      const enderecoFull =
        [
          u.endereco,
          u.numero ? "Nº " + u.numero : "",
          u.complemento,
          u.bairro,
          u.cidade,
          u.estado,
          u.cep ? "CEP " + u.cep : ""
        ].filter(Boolean).map(esc).join(" • ") || '<span class="text-muted">—</span>';

      const html = `
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="${esc(foto)}" alt="Foto"
               style="width:56px;height:56px;object-fit:cover;border-radius:14px;border:1px solid #e5e7eb;">
          <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size:1.05rem">${esc(u.nome) || "<span class='text-muted'>—</span>"}</div>
            <div class="text-muted small">${esc(u.email) || "<span class='text-muted'>—</span>"}</div>
          </div>
          <div class="text-end">
            <span class="badge rounded-pill ${badgeAtivo}</span>
          </div>
        </div>

        <div class="row g-2">
          <div class="col-12 col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Telefone</div>
              <div class="fw-semibold">${u.telefone ? esc(u.telefone) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">CPF/CNPJ</div>
              <div class="fw-semibold">${u.cpf_cnpj ? esc(u.cpf_cnpj) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Tipo</div>
              <div class="fw-semibold">${u.tipo ? esc(u.tipo) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

          <div class="col-12">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Endereço</div>
              <div class="fw-semibold">${enderecoFull}</div>
            </div>
          </div>

          <div class="col-12">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Observações</div>
              <div class="fw-semibold">${u.observacoes ? esc(u.observacoes) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>
        </div>
      `;

      if (Msg && Msg.fechar) Msg.fechar();

      if (window.Swal) {

const podeEditar = !!(window.PERMS_ACOES && window.PERMS_ACOES.editar);

Swal.fire({
  title: "Dados do Usuário",
  html,
  width: 780,
  showCancelButton: true,
  showConfirmButton: podeEditar, // 🔐 só mostra se tiver permissão
  confirmButtonText: "Editar",
  cancelButtonText: "Fechar",
  confirmButtonColor: "#667eea"
}).then((res) => {
  if (podeEditar && res.isConfirmed && typeof window.editar === "function") {
    window.editar(id);
  }
});

return;
}


      // fallback sem SweetAlert
      alert("Cliente: " + (u.nome || ""));
    })
    .catch((err) => {
      if (Msg && Msg.fechar) Msg.fechar();
      if (Msg && Msg.erro) Msg.erro("Erro", err.message || "Falha ao mostrar dados.");
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
    Msg.salvoSemReload(json.msg || "Registro excluído!");
    await window.carregarListagem();

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro ao excluir", err.message || "Falha ao excluir.");
    console.error(err);
  }
};
</script>





<script>
  // EXPORTAR: abre o arquivo de exportação (gera e baixa o Excel)
  function exportarClientesXls() {
    // se você tiver filtro (ex: ativo), dá pra incluir aqui depois via querystring
    const url = "ajax/clientes/exportar_xls.php";
    window.open(url, "_blank");
  }

  // IMPORTAR: abre seletor de arquivo e envia via AJAX (fetch + FormData)
  function importarClientesXls() {
    const inp = document.getElementById("inputImportClientesXls");
    if (!inp) return;

    inp.value = ""; // garante pegar o mesmo arquivo de novo se precisar
    inp.click();

    inp.onchange = async () => {
      const file = inp.files && inp.files[0] ? inp.files[0] : null;
      if (!file) return;

      const ext = (file.name.split('.').pop() || '').toLowerCase();
      if (!['xls', 'xlsx'].includes(ext)) {
        Swal.fire({ icon: 'error', title: 'Arquivo inválido', text: 'Envie um arquivo .xls ou .xlsx' });
        return;
      }

      const confirmar = await Swal.fire({
        icon: 'question',
        title: 'Importar clientes?',
        html: `Arquivo: <b>${file.name}</b><br>Os registros serão lidos e inseridos/atualizados conforme a regra do importador.`,
        showCancelButton: true,
        confirmButtonText: 'Importar',
        cancelButtonText: 'Cancelar'
      });

      if (!confirmar.isConfirmed) return;

      const fd = new FormData();
      fd.append("arquivo", file);

      // se quiser enviar flags:
      // fd.append("modo", "mesclar"); // exemplo

      Swal.fire({
        title: 'Importando...',
        html: 'Aguarde, processando a planilha.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });

      try {
        const resp = await fetch("ajax/clientes/importar_xls.php", {
          method: "POST",
          body: fd
        });

        // seu padrão é JSON
        const data = await resp.json().catch(() => null);

        if (!resp.ok || !data) {
          throw new Error("Resposta inválida do servidor.");
        }

        if (!data.ok) {
          Swal.fire({ icon: 'error', title: 'Falha ao importar', text: data.msg || 'Erro ao importar.' });
          return;
        }

        Swal.fire({ icon: 'success', title: 'Importação concluída', text: data.msg || 'Clientes importados com sucesso!' });

        // atualiza listagem sem reload, se existir a função listar()
        if (typeof listar === 'function') listar();
        else location.reload();

      } catch (e) {
        Swal.fire({ icon: 'error', title: 'Erro', text: e.message || 'Falha ao importar.' });
      }
    };
  }
</script>

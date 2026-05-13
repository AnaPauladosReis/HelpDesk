<?php
/**
 * Página: Usuários (CRUD)
 * - Mantém padrão de modais (igual Perfil/Config)
 * - Usa Crud (assets/js/crud.js) + Mensagens (js/mensagens.js)
 */

@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

$id_empresa = (int)($usuario_empresa ?? ($_SESSION['empresa'] ?? 0));
$pag = 'usuarios';
$podeEditar = podeFazer('editar') ? 1 : 0;
?>

<div class="d-flex align-items-center gap-2 mb-3">

  <?php if (podeFazer('criar')) { ?>
  <button type="button" class="btn btn-salvar-sistema" onclick="novo()">
    <i class="bi bi-plus-lg me-2"></i>Novo Usuário
  </button>
  <?php } ?>

  <?php if (podeFazer('excluir')) { ?>
  <button type="button"
          class="btn btn-danger d-none"
          id="btnExcluirSelecionados"
          onclick="excluirSelecionados()">
    <i class="bi bi-trash me-2"></i>
    Excluir Selecionados (<span id="qtdSelecionados">0</span>)
  </button>
  <?php } ?>

</div>


<div id="listagem"></div>

<!-- MODAL (GENÉRICO) -->
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

      <!-- FORM PADRÃO -->
      <form id="form" method="post" enctype="multipart/form-data" autocomplete="off">
      <div class="modal-body">

<input type="hidden" name="id" id="id" value="0">
<input type="hidden" name="empresa" value="<?= $id_empresa ?>">
<input type="hidden" name="foto_atual" id="foto_atual" value="sem_foto.webp">

<div class="row g-3">

  <!-- LINHA 1 -->
  <div class="col-12 col-md-6">
    <div class="form-floating">
      <input type="text" class="form-control" id="nome" name="nome"
             placeholder="Nome" maxlength="100" required>
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
      <input type="text" class="form-control" id="cpf" name="cpf"
             placeholder="CPF" maxlength="14" data-mask="cpf">
      <label for="cpf">CPF</label>
    </div>
  </div>

  <!-- LINHA 2 -->
  <div class="col-12 col-md-4">
    <div class="form-floating">
      <input type="email" class="form-control" id="email" name="email"
             placeholder="E-mail" maxlength="100" required>
      <label for="email">E-mail</label>
    </div>
  </div>

  <div class="col-12 col-md-2">
    <div class="form-floating">
      <input type="password" class="form-control" id="senha" name="senha"
             placeholder="Senha" maxlength="255">
      <label for="senha">Senha</label>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <label class="form-label small fw-semibold mb-1">Foto</label>

    <div class="d-flex align-items-center gap-2">
      <img
        src="../uploads/perfil/sem_foto.webp"
        alt="Foto"
        id="foto_preview"
        style="width:44px;height:44px;object-fit:cover;border-radius:12px;border:1px solid #cbd5e1;"
      >
      <input class="form-control" type="file" name="foto" id="foto" accept="image/*">
    </div>

    <div class="form-text">JPG, PNG ou WEBP (até 2MB).</div>
  </div>


  <?php
// busca cargos (não SaaS: empresa = 0)
$stmtCargos = $pdo->query("SELECT id, nome FROM cargos ORDER BY nome ASC");
$cargos = $stmtCargos->fetchAll(PDO::FETCH_ASSOC);
?>


  <!-- LINHA 3: CARGO / ATIVO -->
<div class="col-12 col-md-3">
  
  <div class="form-floating">
  <select
    class="form-select js-select2"
    id="nivel"
    name="nivel"
    required
    data-placeholder=""
    data-dropdown-parent="#modalForm"
    data-allow-clear="1"

    style="width:100%"
  >
      
      <?php foreach ($cargos as $c) { ?>
        <option value="<?= $c['nome'] ?>">
          <?= $c['nome'] ?>
        </option>
      <?php } ?>

    </select>
    <label for="nivel">Nível</label>
  </div>
</div>


  <div class="col-12 col-md-3">
    <div class="form-floating">
      <select class="form-select" id="ativo" name="ativo" required>
        <option value="Sim">Sim</option>
        <option value="Não">Não</option>
      </select>
      <label for="ativo">Ativo</label>
    </div>
  </div>

  <div class="col-12 col-md-6">
    <div class="alert alert-light border rounded-4 mb-0">
      <div class="small">
        <b>Dica:</b> ao editar, deixe a senha em branco para não alterar.
      </div>
    </div>
  </div>

  <!-- ENDEREÇO -->
  <div class="col-12 col-md-2">
    <div class="form-floating">
      <input type="text" class="form-control" id="cep" name="cep"
             placeholder="CEP" maxlength="10"
             data-mask="cep" data-cep>
      <label for="cep">CEP</label>
    </div>
  </div>

  <div class="col-12 col-md-8">
    <div class="form-floating">
      <input type="text" class="form-control" id="endereco" name="endereco"
             placeholder="Endereço" maxlength="200"
             data-cep-endereco>
      <label for="endereco">Endereço</label>
    </div>
  </div>

  <div class="col-12 col-md-2">
    <div class="form-floating">
      <input type="text" class="form-control" id="numero" name="numero"
             placeholder="Nº" maxlength="10">
      <label for="numero">Nº</label>
    </div>
  </div>

  <div class="col-12 col-md-3">
    <div class="form-floating">
      <input type="text" class="form-control" id="bairro" name="bairro"
             placeholder="Bairro" maxlength="100"
             data-cep-bairro>
      <label for="bairro">Bairro</label>
    </div>
  </div>

  <div class="col-12 col-md-4">
    <div class="form-floating">
      <input type="text" class="form-control" id="cidade" name="cidade"
             placeholder="Cidade" maxlength="100"
             data-cep-cidade>
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
             placeholder="Compl." maxlength="100"
             data-cep-complemento>
      <label for="complemento">Compl.</label>
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








<!-- MODAL PERMISSÕES -->
<div class="modal fade" id="modalPermissoes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-shield-lock me-2"></i>
          Permissões do Usuário
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="perm_usuario_id" value="0">
        <div id="perm_conteudo"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-cancelar-sistema" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-salvar-sistema" onclick="salvarPermissoes()">
          <i class="bi bi-save me-2"></i>Salvar Permissões
        </button>
      </div>

    </div>
  </div>
</div>







<script> window.pag = "<?= $pag ?>"; </script>

<script>
  window.PERMS_ACOES = window.PERMS_ACOES || {};
  window.PERMS_ACOES.editar = <?= (int)$podeEditar ?>;
</script>

<script>
/**
 * BLOCO GLOBAL (reutilizável em outras páginas)
 * Requisitos:
 * - Deve existir a variável global `pag` (ex: window.pag = "usuarios";) antes deste script
 * - IDs/Elementos esperados na página (GENÉRICOS):
 *   #listagem, #tabela, #modalCadastro, #modalTitulo, #form
 *   #foto, #foto_preview, #id, #foto_atual
 */

/* =========================
   CONFIG / HELPERS
========================= */
const FOTO_PADRAO = "../uploads/perfil/sem_foto.webp";

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
    //columnDefs: [{ orderable: false, targets: [0, 5] }]
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

    // destrói DT antes de trocar HTML
    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable("#tabela")) {
      jQuery("#tabela").DataTable().destroy();
    }

    const html = await fetch(`ajax/${pag}/listar.php`).then(r => r.text());
    container.innerHTML = html;

    // reinicia DT depois que entrou no DOM
    reiniciarDataTable();

  } catch (e) {
    if (Msg && Msg.erro) Msg.erro("Erro", "Não foi possível carregar a listagem.");
    console.error(e);
  }
};

/* =========================
   INIT GLOBAL
========================= */
(function initPagina() {
  document.addEventListener("DOMContentLoaded", function () {

   

    // preview da foto
    Crud.bindImagePreview({
      inputId: "foto",
      previewId: "foto_preview",
      fallbackSrc: FOTO_PADRAO,
      maxMB: 2
    });

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

      const u = json.data || {};

      // abre modal genérico
      Crud.showModal("modalForm");
      Crud.setModalTitle("modalTitulo", "Editar Registro");

      const form = document.querySelector("#form");
      if (!form) throw new Error("Form #form não encontrado.");

      // campos padrão
      form.querySelector('[name="id"]').value = u.id || 0;
      form.querySelector('[name="nome"]').value = u.nome || "";
      form.querySelector('[name="email"]').value = u.email || "";
      form.querySelector('[name="cpf"]').value = u.cpf || "";
      form.querySelector('[name="telefone"]').value = u.telefone || "";

      form.querySelector('[name="nivel"]').value = u.nivel ?? '';
      jQuery('[name="nivel"]').trigger('change');

      form.querySelector('[name="ativo"]').value = u.ativo || "Sim";

      form.querySelector('[name="cep"]').value = u.cep || "";
      form.querySelector('[name="endereco"]').value = u.endereco || "";
      form.querySelector('[name="numero"]').value = u.numero || "";
      form.querySelector('[name="bairro"]').value = u.bairro || "";
      form.querySelector('[name="cidade"]').value = u.cidade || "";
      
      form.querySelector('[name="estado"]').value = u.estado ?? '';
      jQuery('[name="estado"]').trigger('change');

      form.querySelector('[name="complemento"]').value = u.complemento || "";

      // senha sempre em branco
      form.querySelector('[name="senha"]').value = "";

      // foto atual + preview (genérico)
      const fotoAtual = document.getElementById("foto_atual");
      if (fotoAtual) fotoAtual.value = u.foto || "sem_foto.webp";

      const preview = document.getElementById("foto_preview");
      if (preview) preview.src = u.foto_url || FOTO_PADRAO;

      if (Msg && Msg.fechar) Msg.fechar();
    })
    .catch(err => {
      if (Msg && Msg.fechar) Msg.fechar();
      if (Msg && Msg.erro) Msg.erro("Erro", err.message);
      console.error(err);
    });
};

window.mostrar = function (id) {
  if (!id) return;

  const Msg = getMsg();
  if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando dados");

  // escape simples para não quebrar HTML do Swal
  const esc = (v) => String(v ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

  fetch(`ajax/${pag}/buscar.php?id=${encodeURIComponent(id)}`, { cache: "no-store" })
    .then(async (r) => {
      // erro http
      if (!r.ok) {
        const txt = await r.text().catch(() => "");
        throw new Error("Falha ao buscar dados.\n" + (txt ? txt.slice(0, 200) : ""));
      }

      // garante JSON
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
      const foto = u.foto_url || FOTO_PADRAO;

      const nivel = String(u.nivel || "comum").toLowerCase();
      const ativo = String(u.ativo || "Sim").toLowerCase();
      const ativoEhNao = (ativo === "não" || ativo === "nao");

      const html = `
        <div class="d-flex align-items-center gap-3 mb-3">
          <img src="${esc(foto)}" alt="Foto"
               style="width:56px;height:56px;object-fit:cover;border-radius:14px;border:1px solid #e5e7eb;">
          <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size:1.05rem">${esc(u.nome) || "<span class='text-muted'>—</span>"}</div>
            <div class="text-muted small">${esc(u.email) || "<span class='text-muted'>—</span>"}</div>
          </div>
          <div class="text-end">
            <span class="badge rounded-pill ${nivel === "administrador" ? "text-bg-primary" : "text-bg-secondary"}">
              ${nivel === "administrador" ? "Administrador" : "Comum"}
            </span>
            <span class="badge rounded-pill ${ativoEhNao ? "text-bg-danger" : "text-bg-success"}">
              ${ativoEhNao ? "Não" : "Sim"}
            </span>
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
              <div class="text-muted small mb-1">CPF</div>
              <div class="fw-semibold">${u.cpf ? esc(u.cpf) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

          <div class="col-12">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Endereço</div>
              <div class="fw-semibold">
                ${
                  [
                    u.endereco,
                    u.numero ? "Nº " + u.numero : "",
                    u.complemento,
                    u.bairro,
                    u.cidade,
                    u.estado,
                    u.cep ? "CEP " + u.cep : ""
                  ].filter(Boolean).map(esc).join(" • ") || '<span class="text-muted">—</span>'
                }
              </div>
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
      if (Msg && Msg.aviso) return Msg.aviso("Dados do Registro", "Verifique os dados no modal/página.");
      alert("Registro: " + (u.nome || ""));
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

    // ✅ sempre disponível agora (global)
    await window.carregarListagem();

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro ao excluir", err.message || "Falha ao excluir.");
    console.error(err);
  }
};
</script>





<script>
  window.permissoes = async function (id) {
  if (!id) return;

  const Msg = getMsg();
  try {
    if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando permissões");

    document.getElementById("perm_usuario_id").value = id;

    const html = await fetch(`ajax/${pag}/permissoes_form.php?id=${encodeURIComponent(id)}`, { cache: "no-store" })
      .then(r => r.text());

    document.getElementById("perm_conteudo").innerHTML = html;

    initPermissoesUI();

    if (Msg && Msg.fechar) Msg.fechar();
    Crud.showModal("modalPermissoes");

  } catch (e) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro", "Não foi possível carregar permissões.");
    console.error(e);
  }
};

window.salvarPermissoes = async function () {
  const Msg = getMsg();

  try {
    const id = document.getElementById("perm_usuario_id").value;
    if (!id) return;

    const form = document.getElementById("formPermissoes");
    if (!form) throw new Error("formPermissoes não encontrado.");

    if (Msg && Msg.carregando) Msg.carregando("Salvando...", "Aguarde");

    const fd = new FormData(form);
    fd.append("usuario_id", id);

    const resp = await fetch(`ajax/${pag}/permissoes_salvar.php`, {
      method: "POST",
      body: fd
    });

    const ct = resp.headers.get("content-type") || "";

    // ✅ se não for JSON, pega o texto e mostra (assim você vê o erro real)
    if (!ct.includes("application/json")) {
      const txt = await resp.text().catch(() => "");
      throw new Error("Resposta não é JSON. Conteúdo:\n" + (txt ? txt.slice(0, 600) : "(vazio)"));
    }

    const json = await resp.json();
    if (!json.ok) throw new Error(json.msg || "Falha ao salvar.");

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(json.msg || "Permissões salvas!");

    const modalEl = document.getElementById("modalPermissoes");
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

  } catch (e) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro", e.message || "Falha ao salvar permissões.");
    console.error(e);
  }
};


</script>



<script>
  function initPermissoesUI() {
  const form = document.getElementById("formPermissoes");
  if (!form) return;

  // evita duplicar listeners se abrir a modal várias vezes
  if (form.dataset.bound === "1") return;
  form.dataset.bound = "1";

  form.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-perm-action]");
    if (!btn) return;

    const action = btn.getAttribute("data-perm-action");
    const checks = form.querySelectorAll(".perm-check");

    if (action === "all") {
      checks.forEach(c => c.checked = true);
    } else if (action === "none") {
      checks.forEach(c => c.checked = false);
    }
  });
}

</script>


<script>
document.addEventListener("DOMContentLoaded", function () {

  // depois do seu bindSubmit e carregarListagem...
  Crud.initSelecaoEmMassa({
    rowSelector: ".checkRow",
    checkAllId: "checkAll",
    btnId: "btnExcluirSelecionados",
    qtdId: "qtdSelecionados"
  });

});

window.excluirSelecionados = function () {
  return Crud.excluirSelecionados({
    url: `ajax/${pag}/excluir_selecionados.php`,
    onSuccess: async () => window.carregarListagem()
  }).catch(() => {});
};
</script>



<script>
window.resetarSenha = async function (id) {
  if (!id) return;

  const Msg = getMsg();

  try {
    const res = await Msg.confirmar(
      "Resetar senha?",
      "A senha do usuário será redefinida para a senha padrão do sistema.",
      "Sim, resetar",
      "Cancelar"
    );

    if (!res || !res.isConfirmed) return;

    Msg.carregando("Resetando...", "Aguarde");

    const resp = await fetch(`ajax/${pag}/resetar_senha.php`, {
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
    if (!json.ok) throw new Error(json.msg || "Não foi possível resetar a senha.");

    Msg.fechar();
    Msg.salvoSemReload(json.msg || "Senha resetada com sucesso!");

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro", err.message || "Falha ao resetar senha.");
    console.error(err);
  }
};
</script>
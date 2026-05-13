<?php
/**
 * Página: Abertura de Chamados (CRUD + Filtros + Relatório)
 * - Listagem via ajax/abertura/listar.php com filtros (data, status, prioridade, cliente, responsável, termo)
 * - Modal para abrir/editar chamado (padrão do sistema)
 * - Botão Relatório reaproveita os filtros atuais
 */

@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

$id_empresa = 0; // por enquanto não é SaaS
$pag = 'abertura';

// =========================
// Combos (Status / Clientes / Usuários)
// =========================

// Status cadastrados (chamados_status)
$stmtStatus = $pdo->query("SELECT id, nome, cor, ativo FROM chamados_status WHERE ativo = 'Sim' ORDER BY ordem ASC, nome ASC");
$statuses = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

// Clientes (ajuste o SELECT conforme sua tabela)
$clientes = [];
try {
  $stmtClientes = $pdo->query("SELECT id, nome, email FROM clientes ORDER BY nome ASC");
  $clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  // se ainda não tiver tabela clientes, deixa vazio
  $clientes = [];
}

// Usuários (responsável / abertura)
$stmtUsers = $pdo->query("SELECT id, nome, email FROM usuarios ORDER BY nome ASC");
$usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Datas padrão (hoje)
$hoje = date('Y-m-d');

$h = function ($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};


// ====== identifica usuário logado (ajuste se no seu verificar.php o nome for outro) ======
$uid   = (int)($_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0);
$nivel = (string)($_SESSION['nivel'] ?? $usuario_nivel ?? $nivel_usuario ?? '');



?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="d-flex gap-2">
      <button type="button" class="btn btn-salvar-sistema" onclick="novo()">
        <i class="bi bi-plus-lg me-2"></i>Novo Chamado
      </button>
    
    
  </div>
</div>


<div id="listagem"></div>


<?php
// ===== Setores para a MODAL (respeita permissões do usuário) =====
$uid   = (int)($_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0);
$nivel = (string)($_SESSION['nivel'] ?? $usuario_nivel ?? $nivel_usuario ?? '');

$setoresModal = [];


  $st = $pdo->query("SELECT id, nome FROM setores ORDER BY nome ASC");
  $setoresModal = $st->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- MODAL (ABERTURA / EDITAR) -->
<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-ticket-perforated me-2"></i>
          <span id="modalTitulo">Novo Chamado</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="form" method="post" autocomplete="off">
        <div class="modal-body">

          <input type="hidden" name="id" id="id" value="0">
          <input type="hidden" name="empresa" value="<?= (int)$id_empresa ?>">

          <div class="row g-3">

            <div class="col-12 col-md-7">
              <div class="form-floating">
                <input type="text" class="form-control" id="assunto" name="assunto"
                       placeholder="Assunto" maxlength="120" required>
                <label for="assunto">Assunto</label>
              </div>
            </div>

            <input type="hidden" name="prioridade" value="Media">
            <input type="hidden" name="cliente_id" value="<?= $usuario_id ?>">
            <input type="hidden" name="responsavel"  value="">
            <input type="hidden" name="status_id"  value="">
            
            <div class="col-12 col-md-5">
              <div class="form-floating">
                <select class="form-select js-select2"
                        id="setor_id"
                        name="setor_id"
                        data-dropdown-parent="#modalForm"
                        data-allow-clear="1"
                        style="width:100%"
                        required>
                  <option value="0" selected>Selecione</option>
                  <?php foreach ($setoresModal as $st) { ?>
                    <option value="<?= (int)$st['id'] ?>"><?= esc($st['nome']) ?></option>
                  <?php } ?>
                </select>
                <label for="setor_id">Setor</label>
              </div>
            </div>           

            <div class="col-12">
              <div class="form-floating">
                <textarea class="form-control" id="descricao" name="descricao"
                          placeholder="Descreva o problema" style="height: 140px" required></textarea>
                <label for="descricao">Descrição do problema</label>
              </div>
            </div>

          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-cancelar-sistema" data-bs-dismiss="modal">Cancelar</button>
          
            <button type="submit" class="btn btn-salvar-sistema">
              <i class="bi bi-save me-2"></i>Salvar
            </button>
         
        </div>
      </form>

    </div>
  </div>
</div>




<!-- MODAL ANEXOS (REESTRUTURADA) -->
<div class="modal fade" id="modalAnexos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-paperclip me-2"></i>Anexos do Chamado
          </h5>
          <div class="text-muted small" id="anexosSubtitulo">Chamado #—</div>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body pt-3">

        <!-- FORM UPLOAD (UI pronto p/ preview) -->
        <form id="formAnexo" class="border rounded-4 p-3 mb-3" enctype="multipart/form-data" autocomplete="off">
          <input type="hidden" name="chamado_id" id="anexo_chamado_id" value="0">

          <div class="row g-3">

            <!-- Preview -->
            <div class="col-12">
              <div id="anexoPreview" class="d-none border rounded-4 p-3 bg-light">
                <div class="d-flex align-items-center gap-3">

                  <!-- Miniatura (imagem) -->
                  <img id="anexoPreviewImg"
                       src=""
                       alt="Pré-visualização"
                       class="rounded-3 border"
                       style="width:70px;height:70px;object-fit:cover;display:none;">

                  <!-- Ícone (quando não for imagem) -->
                  <div id="anexoPreviewIcon"
                       class="rounded-3 border bg-white d-flex align-items-center justify-content-center"
                       style="width:70px;height:70px;">
                    <i class="bi bi-file-earmark fs-3 text-muted"></i>
                  </div>

                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate" id="anexoPreviewNome">—</div>
                    <div class="text-muted small" id="anexoPreviewTamanho">—</div>
                    <div class="text-muted small">Pré-visualização do arquivo selecionado</div>
                  </div>

                  <button type="button" class="btn btn-outline-secondary btn-sm rounded-3" id="btnLimparArquivo">
                    <i class="bi bi-x-lg me-1"></i>Remover
                  </button>

                </div>
              </div>
            </div>

            <!-- Arquivo -->
            <div class="col-12 col-md-7">
              <label class="form-label small text-muted mb-1">Arquivo</label>
              <input type="file" class="form-control" name="arquivo" id="anexo_arquivo" required>
              <div class="form-text">
                Envie PDF, Word, Excel, imagens, etc.
              </div>
            </div>

            <!-- Nome opcional -->
            <div class="col-12 col-md-5">
            <div class="mb-3">
              <label class="form-label">Nome do Arquivo</label>
              <input type="text" 
                    name="nome" 
                    class="form-control" 
                    maxlength="255"
                    placeholder="Ex: Nota fiscal, Contrato assinado...">
            </div>
            </div>

            <!-- Ações -->
            <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
              <button type="submit" class="btn btn-primary rounded-3" id="btnEnviarAnexo">
                <i class="bi bi-upload me-2"></i>Enviar
              </button>

              <button type="button" class="btn btn-outline-secondary rounded-3" id="btnRecarregarAnexos">
                <i class="bi bi-arrow-clockwise me-2"></i>Atualizar
              </button>

              <div class="ms-auto small text-muted d-flex align-items-center" id="anexosInfo"></div>
            </div>

          </div>
        </form>

        <!-- LISTAGEM -->
        <div id="boxListaAnexos" class="border rounded-4 p-2">
          <div class="text-muted small p-3">Carregando anexos…</div>
        </div>

      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
          Fechar
        </button>
      </div>

    </div>
  </div>
</div>

<style>
  /* Preview box */
  #anexoPreview { transition: .15s ease; }

  /* seus cards de anexos (mantidos) */
  .anexo-item{
    display:flex;
    align-items:center;
    gap:12px;
    padding:10px 12px;
    border-radius:14px;
    border:1px solid rgba(0,0,0,.06);
    background:#fff;
  }
  .anexo-item + .anexo-item{ margin-top:10px; }
  .anexo-ico{
    width:42px;height:42px;
    display:flex;align-items:center;justify-content:center;
    border-radius:12px;
    background:rgba(0,0,0,.03);
    overflow:hidden;
    flex:0 0 auto;
  }
  .anexo-ico img{ width:28px;height:28px; object-fit:contain; }
  .anexo-meta{ min-width:0; flex:1 1 auto; }
  .anexo-nome{
    font-weight:700;
    font-size:.92rem;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  .anexo-sub{
    font-size:.78rem;
    color:#6b7280;
    display:flex; gap:10px; flex-wrap:wrap;
  }
  .anexo-actions{ display:flex; gap:6px; }
</style>




<script>
// Apenas UI: botão "Remover" limpa seleção (sem gerar preview ainda)
document.addEventListener("click", function(e){
  if (e.target.id !== "btnLimparArquivo" && !e.target.closest("#btnLimparArquivo")) return;

  const inpFile = document.getElementById("anexo_arquivo");
  if (inpFile) inpFile.value = "";

  const pv = document.getElementById("anexoPreview");
  if (pv) pv.classList.add("d-none");

  const pvImg = document.getElementById("anexoPreviewImg");
  if (pvImg) { pvImg.src = ""; pvImg.style.display = "none"; }

  const pvIco = document.getElementById("anexoPreviewIcon");
  if (pvIco) pvIco.innerHTML = `<i class="bi bi-file-earmark fs-3 text-muted"></i>`;

  const pvNome = document.getElementById("anexoPreviewNome");
  if (pvNome) pvNome.textContent = "";

  const pvTam = document.getElementById("anexoPreviewTamanho");
  if (pvTam) pvTam.textContent = "";
});
</script>








<!-- MODAL RESPOSTAS -->
<div class="modal fade" id="modalRespostas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header border-0 text-white" style="background:linear-gradient(90deg,#5b6fe0,#7b3dbb);border-top-left-radius:1rem;border-top-right-radius:1rem;">
        <div>
          <h5 class="modal-title fw-bold mb-0">
            <i class="bi bi-chat-dots me-2"></i>Respostas do Chamado
          </h5>
          <!-- ✅ aqui vai o protocolo -->
          <div class="small opacity-75" id="respostasSubtitulo">Protocolo: —</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body pt-3">

        <!-- MINI ANEXOS (compacto) -->
        <div class="border rounded-4 p-2 mb-3">
          <div class="small text-muted fw-semibold mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-paperclip"></i> Anexos
            <span class="ms-auto small text-muted" id="miniAnexosInfo"></span>
          </div>

          <div id="boxMiniAnexos" class="d-flex flex-wrap gap-2">
            <div class="text-muted small px-2 py-1">Carregando…</div>
          </div>
        </div>

        <!-- ✅ FORM NOVA RESPOSTA (AGORA EM CIMA) -->
        <form id="formResposta" class="border rounded-4 p-2 p-md-3 mb-3">
          <input type="hidden" name="chamado_id" id="resposta_chamado_id" value="0">
          <!-- ✅ opcional: guardar protocolo pra não ter que buscar de novo no JS -->
          <input type="hidden" id="resposta_protocolo" value="">

          <div class="form-floating">
            <textarea class="form-control rounded-4"
                      name="mensagem"
                      id="resposta_mensagem"
                      placeholder="Digite sua resposta"
                      style="height:110px; resize:vertical;"
                      required></textarea>
            <label for="resposta_mensagem">Escreva uma resposta</label>
          </div>

          <div class="d-flex gap-2 mt-2 align-items-center">
            <button class="btn btn-primary rounded-3" id="btnEnviarResposta">
              <i class="bi bi-send me-2"></i>Enviar
            </button>

           

            <div class="ms-auto small text-muted d-flex align-items-center" id="respostasInfo"></div>
          </div>
        </form>

        <!-- LISTA DE RESPOSTAS -->
        <div class="border rounded-4 p-2" id="boxRespostas" style="min-height:220px;">
          <div class="text-muted small p-3">Carregando respostas…</div>
        </div>

      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-secondary rounded-3" data-bs-dismiss="modal">
          Fechar
        </button>
      </div>

    </div>
  </div>
</div>

<style>
/* ===== MINI ANEXOS ===== */
.mini-anexo{
  width: 190px;
  border: 1px solid rgba(0,0,0,.06);
  border-radius: 14px;
  padding: 8px 10px;
  background: #fff;
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.mini-anexo .mini-ico{
  width: 30px;
  height: 30px;
  border-radius: 10px;
  background: rgba(0,0,0,.04);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex: 0 0 auto;
}
.mini-anexo .mini-ico img{
  width: 18px;
  height: 18px;
  object-fit: contain;
}
.mini-anexo .mini-meta{
  min-width: 0;
  line-height: 1.15;
}
.mini-anexo .mini-nome{
  font-size: .78rem;
  font-weight: 800;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 135px;
}
.mini-anexo .mini-data{
  font-size: .72rem;
  color: #6b7280;
  margin-top: 4px;
}
.mini-anexo a{
  color: inherit;
  text-decoration: none;
}
.mini-anexo a:hover .mini-nome{
  text-decoration: underline;
  color: #0d6efd;
}
</style>













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

function qs(obj) {
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k, v]) => {
    if (v !== null && v !== undefined && String(v).trim() !== "") p.append(k, v);
  });
  return p.toString();
}

function getFiltros() {
  return {
    data_ini: document.getElementById("data_ini")?.value || "",
    data_fim: document.getElementById("data_fim")?.value || "",
    status_id: document.getElementById("f_status")?.value || "",
    prioridade: document.getElementById("f_prioridade")?.value || "",
    cliente_id: document.getElementById("f_cliente")?.value || "",
    responsavel_id: document.getElementById("f_responsavel")?.value || "",
    setor_id: document.getElementById("f_setor")?.value || "",
    termo: document.getElementById("f_termo")?.value || "",
  };
}

let __tFiltro = null;
function aplicarAutoFiltro(delay = 250) {
  if (__tFiltro) clearTimeout(__tFiltro);
  __tFiltro = setTimeout(() => window.carregarListagem(), delay);
}

/* =========================
   LISTAGEM
========================= */
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
    order: [[0, "desc"]],
    //columnDefs: [{ orderable: false, targets: [0, 5] }]
  });
}


/* =========================
   MODAL / CRUD
========================= */
window.novo = function () {
  Crud.resetForm("#form");
  document.getElementById("id").value = 0;
  
  if (window.Crud && Crud.setModalTitle) Crud.setModalTitle("modalTitulo", "Novo Chamado");
  Crud.showModal("modalForm");
};

window.editar = function (id) {
  if (!id) return;
  const Msg = getMsg();
  if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando dados");

  fetch(`ajax/${pag}/buscar.php?id=` + encodeURIComponent(id), { cache: "no-store" })
    .then(r => r.json())
    .then(json => {
      if (!json.ok) throw new Error(json.msg || "Erro ao carregar registro");
      const c = json.data || {};

      Crud.showModal("modalForm");
      Crud.setModalTitle("modalTitulo", "Editar Chamado");

      const form = document.querySelector("#form");
      form.querySelector('[name="id"]').value = c.id || 0;
      form.querySelector('[name="assunto"]').value = c.assunto || "";
      form.querySelector('[name="descricao"]').value = c.descricao || "";
      //form.querySelector('[name="prioridade"]').value = c.prioridade || "Media";

      // select2
      if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
        
        jQuery("#setor_id").val(c.setor_id || "").trigger("change.select2");
      } else {
        
        document.getElementById("setor_id").value = c.setor_id || "";
      }

      if (Msg && Msg.fechar) Msg.fechar();
    })
    .catch(e => {
      if (Msg && Msg.fechar) Msg.fechar();
      if (Msg && Msg.erro) Msg.erro("Erro", e.message);
      console.error(e);
    });
};






window.mostrar = function (id) {
  if (!id) return;

  const Msg = getMsg();
  if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando dados do chamado");

  // escape simples para não quebrar HTML do Swal
  const esc = (v) => String(v ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");

  // badge de prioridade (igual padrão da sua tabela)
  const badgePrioridade = (p) => {
    const pr = String(p || "Media");
    const map = { Baixa:"secondary", Media:"primary", Alta:"warning", Urgente:"danger" };
    const cls = map[pr] || "primary";
    const label = (pr === "Media") ? "Média" : pr;
    return `<span class="badge rounded-pill text-bg-${cls}">${esc(label)}</span>`;
  };

  // badge de status usando corHex
  const badgeStatus = (nome, corHex, fechado) => {
    const n = String(nome || "—");
    const c = String(corHex || "#6c757d");
    const isFechado = String(fechado || "Não").toLowerCase() === "sim";
    const extra = isFechado ? ' <i class="bi bi-check2-circle ms-1"></i>' : '';
    return `<span class="badge rounded-pill" style="background:${esc(c)};color:#fff;border:1px solid rgba(0,0,0,.08)">
      ${esc(n)}${extra}
    </span>`;
  };


  // formata "2026-02-18 11:43:54" para "18/02/2026 11:43"
const formatarDataHora = (dataStr) => {
  if (!dataStr) return "";

  try {
    // troca espaço por T para garantir compatibilidade
    const iso = dataStr.replace(" ", "T");
    const data = new Date(iso);

    if (isNaN(data.getTime())) return dataStr; // fallback

    return data.toLocaleString("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit"
    });

  } catch (e) {
    return dataStr; // fallback se der erro
  }
};

  fetch(`ajax/${pag}/buscar.php?id=${encodeURIComponent(id)}`, { cache: "no-store" })
    .then(async (r) => {
      if (!r.ok) {
        const txt = await r.text().catch(() => "");
        throw new Error("Falha ao buscar dados.\n" + (txt ? txt.slice(0, 300) : ""));
      }

      const ct = r.headers.get("content-type") || "";
      if (!ct.includes("application/json")) {
        const txt = await r.text().catch(() => "");
        throw new Error("Resposta inesperada do servidor.\n" + (txt ? txt.slice(0, 300) : ""));
      }

      return r.json();
    })
    .then((json) => {
      if (!json || !json.ok) {
        throw new Error((json && json.msg) ? json.msg : "Erro ao buscar registro");
      }

      const c = json.data || {};

      const protocolo = c.protocolo || "";
      const assunto   = c.assunto || "";
      const descricao = c.descricao || "";
      const setor_nome = c.setor_nome || "";

      const prioridade = c.prioridade || "Media";
      const statusNome = c.status_nome || c.status || "";
      const statusCor  = c.status_cor || "#6c757d";
      const statusFechado = c.status_fechado || "Não";

      // datas (se backend já mandar formatado, melhor)
      const criado = formatarDataHora(c.criado_em_fmt || c.criado_em);
const atualizado = formatarDataHora(c.atualizado_em_fmt || c.atualizado_em);
const fechado = formatarDataHora(c.fechado_em_fmt || c.fechado_em);

      const clienteNome = c.cliente_nome || "";
      const clienteEmail = c.cliente_email || "";
      const clienteTel = c.cliente_telefone || "";

      const respNome = c.resp_nome || "";
      const respEmail = c.resp_email || "";
      const respTel = c.resp_telefone || "";

      const aberturaNome = c.abertura_nome || "";
      const aberturaEmail = c.abertura_email || "";

      const html = `
        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
          <div>
            <div class="fw-bold" style="font-size:1.05rem">${esc(protocolo || "Chamado")} (${setor_nome}) </div>
            <div class="text-muted small">Detalhes do chamado</div>
          </div>
          <div class="text-end d-flex flex-column gap-1">
            ${badgeStatus(statusNome, statusCor, statusFechado)}
            
          </div>
        </div>

        <div class="row g-2">

          <div class="col-12">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Assunto</div>
              <div class="fw-semibold">${assunto ? esc(assunto) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

          <div class="col-12">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Descrição</div>
              <div style="white-space:pre-wrap">${descricao ? esc(descricao) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

         

          <div class="col-12 col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Responsável</div>
              <div class="fw-semibold">${respNome ? esc(respNome) : '<span class="text-muted">(Opcional)</span>'}</div>
              <div class="text-muted small">
                ${respEmail ? esc(respEmail) : ""}
                ${respTel ? (respEmail ? " • " : "") + esc(respTel) : ""}
                ${(!respEmail && !respTel && !respNome) ? '<span class="text-muted">—</span>' : ""}
              </div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Aberto por</div>
              <div class="fw-semibold">${aberturaNome ? esc(aberturaNome) : '<span class="text-muted">—</span>'}</div>
              <div class="text-muted small">${aberturaEmail ? esc(aberturaEmail) : '<span class="text-muted">—</span>'}</div>
            </div>
          </div>

          <div class="col-12 col-md-6">
            <div class="border rounded-4 p-3">
              <div class="text-muted small mb-1">Datas</div>
              <div class="small">
                <div><b>Criado:</b> ${criado ? esc(criado) : '<span class="text-muted">—</span>'}</div>
                <div><b>Atualizado:</b> ${atualizado ? esc(atualizado) : '<span class="text-muted">—</span>'}</div>
                <div><b>Encerrado:</b> ${fechado ? esc(fechado) : '<span class="text-muted">—</span>'}</div>
              </div>
            </div>
          </div>

        </div>
      `;

      if (Msg && Msg.fechar) Msg.fechar();

      if (window.Swal) {
        const podeEditar = !!(window.PERMS_ACOES && window.PERMS_ACOES.editar);

        Swal.fire({
          title: "Dados do Chamado",
          html,
          width: 860,
          showCancelButton: true,
          showConfirmButton: podeEditar,
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
      if (Msg && Msg.aviso) return Msg.aviso("Dados do Chamado", "Verifique os dados no modal/página.");
      alert("Chamado: " + (protocolo || id));
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

/* =========================
   INIT
========================= */
(function initPagina() {
  document.addEventListener("DOMContentLoaded", function () {

    // select2 nos filtros (sem dropdown parent)
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
      ["#f_status","#f_prioridade","#f_cliente","#f_responsavel","#f_setor"].forEach(sel => {
        jQuery(sel).select2({ theme:"bootstrap-5", width:"100%", allowClear:true });
        jQuery(sel).on("change.select2 select2:select select2:clear", () => aplicarAutoFiltro());
      });

      // select2 no modal (com dropdown-parent já no HTML via data-attr)
      jQuery("#cliente_id, #responsavel_id, #status_id").select2({
        theme:"bootstrap-5",
        width:"100%",
        dropdownParent: jQuery("#modalForm")
      });
    }

    // filtros datas/termo
    document.getElementById("data_ini")?.addEventListener("change", () => aplicarAutoFiltro());
    document.getElementById("data_fim")?.addEventListener("change", () => aplicarAutoFiltro());

    // termo: debounce ao digitar
    let t = null;
    document.getElementById("f_termo")?.addEventListener("input", () => {
      if (t) clearTimeout(t);
      t = setTimeout(() => window.carregarListagem(), 350);
    });

    // submit do modal
    Crud.bindSubmit({
      formSelector: "#form",
      url: `ajax/${pag}/salvar.php`,
      modalId: "modalForm",
      reloadOnSuccess: false,
      onSuccess: () => window.carregarListagem()
    });

    // inicial
    window.carregarListagem();
  });
})();
</script>



<script>
/**
 * Popup de ações (funciona com listagem recarregada via AJAX)
 * - delegação no container #listagem
 */
(function () {
  const listagem = document.getElementById("listagem");
  if (!listagem) return;

  function fecharTodos() {
    listagem.querySelectorAll(".acoes-pop").forEach(el => el.style.display = "none");
  }

  // Clique dentro da área da listagem
  listagem.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-acoes-mais");
    const pop = e.target.closest(".acoes-pop");

    // clicou em algum botão "mais"
    if (btn) {
      e.preventDefault();
      e.stopPropagation();

      const id = btn.getAttribute("data-id");
      const el = document.getElementById("acoesPop" + id);
      if (!el) return;

      const aberto = (el.style.display === "block");
      fecharTodos();
      el.style.display = aberto ? "none" : "block";
      return;
    }

    // clicou dentro do popup -> não fecha
    if (pop) return;

    // clicou em qualquer lugar da listagem (fora do popup) -> fecha
    fecharTodos();
  });

  // Clique fora da listagem -> fecha tudo
  document.addEventListener("click", function (e) {
    if (!listagem.contains(e.target)) fecharTodos();
  });
})();
</script>





<script>
  // Abre a modal de anexos e prepara UI (SEM upload/listagem ainda)
window.anexos = function (chamadoId) {
  chamadoId = parseInt(chamadoId || "0", 10);
  if (!chamadoId) return;

  // seta o id no hidden
  const hid = document.getElementById("anexo_chamado_id");
  if (hid) hid.value = String(chamadoId);

  // subtítulo
  const sub = document.getElementById("anexosSubtitulo");
  if (sub) sub.textContent = `Chamado #${chamadoId}`;

  // limpa input file e nome
  const inpFile = document.getElementById("anexo_arquivo");
  if (inpFile) inpFile.value = "";

  const inpNome = document.getElementById("anexo_nome");
  if (inpNome) inpNome.value = "";

  // limpa área de preview (UI pronta)
  const pv = document.getElementById("anexoPreview");
  if (pv) pv.classList.add("d-none");

  const pvImg = document.getElementById("anexoPreviewImg");
  if (pvImg) pvImg.src = "";

  const pvIco = document.getElementById("anexoPreviewIcon");
  if (pvIco) pvIco.innerHTML = `<i class="bi bi-file-earmark fs-3 text-muted"></i>`;

  const pvNome = document.getElementById("anexoPreviewNome");
  if (pvNome) pvNome.textContent = "";

  const pvTam = document.getElementById("anexoPreviewTamanho");
  if (pvTam) pvTam.textContent = "";

  // zera info/placeholder de lista
  const info = document.getElementById("anexosInfo");
  if (info) info.textContent = "";

  const box = document.getElementById("boxListaAnexos");
  if (box) box.innerHTML = `<div class="text-muted small p-3">Carregando anexos…</div>`;

  // abre modal
  const modalEl = document.getElementById("modalAnexos");
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();

  // ⚠️ AQUI DEPOIS vamos chamar a função que lista anexos via AJAX
  // ex: carregarAnexos(chamadoId);
};
</script>




<script>
  (function () {

function formatarTamanho(bytes) {
  if (!bytes) return "0 KB";
  const kb = bytes / 1024;
  if (kb < 1024) return kb.toFixed(1) + " KB";
  return (kb / 1024).toFixed(2) + " MB";
}

function obterIconePorExtensao(ext) {
  const mapa = {
    pdf: "bi-file-earmark-pdf text-danger",
    doc: "bi-file-earmark-word text-primary",
    docx: "bi-file-earmark-word text-primary",
    xls: "bi-file-earmark-excel text-success",
    xlsx: "bi-file-earmark-excel text-success",
    csv: "bi-file-earmark-spreadsheet text-success",
    zip: "bi-file-earmark-zip text-warning",
    rar: "bi-file-earmark-zip text-warning",
    txt: "bi-file-earmark-text text-secondary",
    xml: "bi-file-earmark-code text-dark",
    json: "bi-file-earmark-code text-dark"
  };

  return mapa[ext] || "bi-file-earmark text-muted";
}

document.addEventListener("change", function (e) {

  if (e.target.id !== "anexo_arquivo") return;

  const input = e.target;
  const file = input.files && input.files[0];
  if (!file) return;

  const previewBox = document.getElementById("anexoPreview");
  const previewImg = document.getElementById("anexoPreviewImg");
  const previewIcon = document.getElementById("anexoPreviewIcon");
  const previewNome = document.getElementById("anexoPreviewNome");
  const previewTam = document.getElementById("anexoPreviewTamanho");

  // Mostra box
  previewBox.classList.remove("d-none");

  // Nome
  previewNome.textContent = file.name;

  // Tamanho
  previewTam.textContent = formatarTamanho(file.size);

  // Detecta extensão
  const ext = file.name.split('.').pop().toLowerCase();

  // Se for imagem → mostra miniatura real
  if (file.type.startsWith("image/")) {

    previewImg.style.display = "block";
    previewImg.src = URL.createObjectURL(file);

    previewIcon.innerHTML = "";
    previewIcon.style.display = "none";

  } else {

    // Não é imagem → mostra ícone
    previewImg.style.display = "none";
    previewImg.src = "";

    const iconeClasse = obterIconePorExtensao(ext);
    previewIcon.style.display = "flex";
    previewIcon.innerHTML = `<i class="bi ${iconeClasse} fs-3"></i>`;
  }

});

})();
</script>


<script>
  document.addEventListener("submit", async function (e) {
  if (e.target.id !== "formAnexo") return;

  e.preventDefault();

  const Msg = (typeof getMsg === "function") ? getMsg() : null;
  const form = e.target;

  const chamadoId = parseInt(document.getElementById("anexo_chamado_id")?.value || "0", 10);
  if (!chamadoId) {
    if (Msg && Msg.erro) Msg.erro("Erro", "Chamado inválido.");
    return;
  }

  const inp = document.getElementById("anexo_arquivo");
  if (!inp || !inp.files || !inp.files[0]) {
    if (Msg && Msg.aviso) Msg.aviso("Atenção", "Selecione um arquivo.");
    return;
  }

  const btn = document.getElementById("btnEnviarAnexo");
  const old = btn ? btn.innerHTML : "";

  try {
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Enviando...`;
    }
    if (Msg && Msg.carregando) Msg.carregando("Enviando...", "Salvando anexo");

    const fd = new FormData(form);

    const resp = await fetch(`ajax/${window.pag}/anexos_salvar.php`, {
      method: "POST",
      body: fd,
      cache: "no-store"
    });

    const json = await resp.json().catch(() => null);
    if (!resp.ok || !json) throw new Error("Resposta inválida do servidor.");
    if (!json.ok) throw new Error(json.msg || "Falha ao anexar.");

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(json.msg);

    // limpa file/nome e preview (mantém chamado_id)
    form.reset();
    document.getElementById("anexo_chamado_id").value = String(chamadoId);

    const pv = document.getElementById("anexoPreview");
    if (pv) pv.classList.add("d-none");

    await window.carregarAnexos(chamadoId);

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro ao anexar", err.message || "Falha no upload.");
    console.error(err);
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = old;
    }
  }
});
</script>


<script>
  // abre a modal e carrega anexos
window.anexos = function (chamadoId) {
  if (!chamadoId) return;

  document.getElementById("anexo_chamado_id").value = chamadoId;
  document.getElementById("anexosSubtitulo").textContent = "Chamado #" + chamadoId;

  // limpa file + preview
  const inp = document.getElementById("anexo_arquivo");
  if (inp) inp.value = "";
  const pv = document.getElementById("anexoPreview");
  if (pv) pv.classList.add("d-none");

  // abre modal
  bootstrap.Modal.getOrCreateInstance(document.getElementById("modalAnexos")).show();

  // carrega lista
  window.carregarAnexos(chamadoId);
};

window.carregarAnexos = async function (chamadoId) {
  const box = document.getElementById("boxListaAnexos");
  const info = document.getElementById("anexosInfo");
  const Msg = (typeof getMsg === "function") ? getMsg() : null;

  try {
    if (info) info.textContent = "Carregando...";
    box.innerHTML = `<div class="text-muted small p-3">Carregando anexos…</div>`;

    const url = `ajax/${window.pag}/anexos_listar.php?chamado_id=` + encodeURIComponent(chamadoId);
    const resp = await fetch(url, { cache: "no-store" });
    const html = await resp.text();

    if (!resp.ok) throw new Error(html.slice(0, 400));

    box.innerHTML = html;
    if (info) info.textContent = "";

  } catch (e) {
    if (info) info.textContent = "";
    box.innerHTML = `<div class="text-danger small p-3">Erro ao carregar anexos.</div>`;
    if (Msg && Msg.erro) Msg.erro("Erro", e.message || "Falha ao carregar anexos.");
    console.error(e);
  }
};

// botão "Atualizar" dentro da modal
document.addEventListener("click", function (e) {
  const btn = e.target.closest("#btnRecarregarAnexos");
  if (!btn) return;

  const chamadoId = parseInt(document.getElementById("anexo_chamado_id")?.value || "0", 10);
  if (!chamadoId) return;

  window.carregarAnexos(chamadoId);
});
</script>


<script>
  window.excluirAnexo = async function (anexoId, chamadoId) {
  if (!anexoId) return;

  const Msg = (typeof getMsg === "function") ? getMsg() : null;

  try {
    const r = await Swal.fire({
      title: "Excluir arquivo?",
      text: "Essa ação não pode ser desfeita.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sim, excluir",
      cancelButtonText: "Cancelar",
    });
    if (!r.isConfirmed) return;

    if (Msg && Msg.carregando) Msg.carregando("Excluindo...", "Aguarde");

    const resp = await fetch(`ajax/${window.pag}/anexos_excluir.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json; charset=utf-8" },
      body: JSON.stringify({ id: anexoId }),
      cache: "no-store"
    });

    const json = await resp.json().catch(() => null);
    if (!resp.ok || !json || !json.ok) throw new Error((json && json.msg) ? json.msg : "Falha ao excluir.");

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(json.msg || "Excluído!");

    // recarrega anexos
    const cid = chamadoId || parseInt(document.getElementById("anexo_chamado_id")?.value || "0", 10);
    if (cid && typeof window.carregarAnexos === "function") window.carregarAnexos(cid);

  } catch (e) {
    if (Msg && Msg.fechar) Msg.fechar();
    Swal.fire("Erro", e.message || "Falha ao excluir.", "error");
    console.error(e);
  }
};
</script>



<script>
window.respostas = function (chamadoId, protocolo) {
  chamadoId = parseInt(chamadoId || "0", 10);
  if (!chamadoId) return;

  // seta o id no hidden
  const hid = document.getElementById("resposta_chamado_id");
  if (hid) hid.value = chamadoId;

  // subtítulo da modal
  const sub = document.getElementById("respostasSubtitulo");
  if (sub) sub.textContent = "Chamado #" + protocolo;

  // limpa campo de nova resposta (se existir)
  const campo = document.getElementById("resposta_mensagem");
  if (campo) campo.value = "";

  // abre modal
  const modalEl = document.getElementById("modalRespostas");
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();

  // carrega respostas existentes
  if (typeof window.carregarRespostas === "function") {
    window.carregarRespostas(chamadoId);
  }

  if (typeof window.carregarMiniAnexos === "function") window.carregarMiniAnexos(chamadoId);

};
</script>



<script>
window.carregarMiniAnexos = async function (chamadoId) {
  const box  = document.getElementById("boxMiniAnexos");
  const info = document.getElementById("miniAnexosInfo");
  const Msg  = (typeof getMsg === "function") ? getMsg() : null;

  try {
    if (info) info.textContent = "Carregando...";
    if (box) box.innerHTML = `<div class="text-muted small px-2 py-1">Carregando…</div>`;

    const url = `ajax/${window.pag}/mini_anexos.php?chamado_id=` + encodeURIComponent(chamadoId);
    const resp = await fetch(url, { cache: "no-store" });
    const html = await resp.text();

    if (!resp.ok) throw new Error(html.slice(0, 400));

    if (box) box.innerHTML = html;
    if (info) info.textContent = "";

  } catch (e) {
    if (info) info.textContent = "";
    if (box) box.innerHTML = `<div class="text-danger small px-2 py-1">Erro ao carregar anexos.</div>`;
    if (Msg && Msg.erro) Msg.erro("Erro", e.message || "Falha ao carregar anexos.");
    console.error(e);
  }
};
</script>


<script>
document.addEventListener("click", async function (e) {
  const btn = e.target.closest("#btnEnviarResposta");
  if (!btn) return;

  e.preventDefault();

  const chamadoId = (document.getElementById("resposta_chamado_id")?.value || "0").trim();
  const mensagem  = (document.getElementById("resposta_mensagem")?.value || "").trim();

  if (!chamadoId || chamadoId === "0") return alert("Chamado inválido.");
  if (!mensagem) return alert("Digite uma resposta.");

  btn.disabled = true;
  const oldHtml = btn.innerHTML;
  btn.innerHTML = "Enviando...";

  const info = document.getElementById("respostasInfo");
  if (info) info.innerText = "";

  try {
    const url = "ajax/abertura/respostas_salvar.php"; // ajuste se necessário

    const resp = await fetch(url, {
      method: "POST",
      headers: {"Content-Type":"application/json; charset=utf-8"},
      cache: "no-store",
      body: JSON.stringify({ chamado_id: chamadoId, mensagem })
    });

    const text = await resp.text();
    const raw = (text || "").trim();

    // ✅ Se o PHP não retornou JSON (vazio, HTML, warning, etc):
    // não alerta, só tenta recarregar a lista e segue vida.
    if (!raw || raw[0] !== "{") {
      console.warn("Resposta inválida (não JSON). Suprimido.", raw.slice(0, 300));
      document.getElementById("resposta_mensagem").value = "";

      if (typeof window.carregarRespostas === "function") {
        await window.carregarRespostas(parseInt(chamadoId, 10));
      }
      if (info) info.innerText = "Enviado";
      return;
    }

    let json;
    try {
      json = JSON.parse(raw);
    } catch (err) {
      console.warn("JSON parse falhou. Suprimido.", raw.slice(0, 300));
      document.getElementById("resposta_mensagem").value = "";

      if (typeof window.carregarRespostas === "function") {
        await window.carregarRespostas(parseInt(chamadoId, 10));
      }
      if (info) info.innerText = "Enviado";
      return;
    }

    // ✅ Se veio JSON de erro, aí sim mostra (porque é retorno controlado seu)
    if (!resp.ok || !json.ok) {
      return alert(json.msg || "Erro ao salvar.");
    }

    // sucesso
    document.getElementById("resposta_mensagem").value = "";
    if (info) info.innerText = "Resposta enviada!";

    if (typeof window.carregarRespostas === "function") {
      await window.carregarRespostas(parseInt(chamadoId, 10));
    }

  } catch (err) {
    console.error(err);
    alert("Erro na requisição.");
  } finally {
    btn.disabled = false;
    btn.innerHTML = oldHtml;
  }
});
</script>


<script>
/**
 * Abre a modal de respostas e carrega a listagem (HTML)
 * Uso: respostas(id, 'CH20260223-ABC123')
 */
window.respostas = function (chamadoId, protocolo) {
  if (!chamadoId) return;

  // seta hidden
  const inpId = document.getElementById("resposta_chamado_id");
  if (inpId) inpId.value = chamadoId;

  const inpProt = document.getElementById("resposta_protocolo");
  if (inpProt) inpProt.value = protocolo || "";

  // subtítulo
  const subt = document.getElementById("respostasSubtitulo");
  if (subt) subt.textContent = "Protocolo: " + (protocolo || "—");

  // limpa textarea
  const txt = document.getElementById("resposta_mensagem");
  if (txt) txt.value = "";

  // abre modal
  bootstrap.Modal.getOrCreateInstance(document.getElementById("modalRespostas")).show();

  // carrega anexos mini (se você já tiver)
  if (typeof window.carregarMiniAnexos === "function") {
    window.carregarMiniAnexos(chamadoId);
  }

  // carrega respostas
  window.carregarRespostas(chamadoId);
};


/**
 * Carrega o HTML da listagem de respostas dentro do boxRespostas
 * Endpoint esperado: ajax/{pag}/respostas_listar.php?chamado_id=ID
 */
window.carregarRespostas = async function (chamadoId) {
  const box  = document.getElementById("boxRespostas");
  const info = document.getElementById("respostasInfo");
  const Msg  = (typeof getMsg === "function") ? getMsg() : null;

  try {
    if (info) info.textContent = "Carregando...";
    if (box) box.innerHTML = `<div class="text-muted small p-3">Carregando respostas…</div>`;

    const url = `ajax/${window.pag}/respostas_listar.php?chamado_id=` + encodeURIComponent(chamadoId);
    const resp = await fetch(url, { cache: "no-store" });
    const html = await resp.text();

    if (!resp.ok) throw new Error(html.slice(0, 500));

    if (box) box.innerHTML = html;
    if (info) info.textContent = "";

  } catch (e) {
    //if (info) info.textContent = "";
    //if (box) box.innerHTML = `<div class="text-danger small p-3">Erro ao carregar respostas.</div>`;
    //if (Msg && Msg.erro) Msg.erro("Erro", e.message || "Falha ao carregar respostas.");
    console.error(e);
  }
};


/**
 * (Opcional) botão "Atualizar" se você colocar na modal
 * <button id="btnRecarregarRespostas">Atualizar</button>
 */
document.addEventListener("click", function (e) {
  const btn = e.target.closest("#btnRecarregarRespostas");
  if (!btn) return;

  const chamadoId = parseInt(document.getElementById("resposta_chamado_id")?.value || "0", 10);
  if (!chamadoId) return;

  window.carregarRespostas(chamadoId);
});
</script>



<script>
  window.excluirResposta = async function (id, chamadoId) {
  if (!id) return;

  const Msg = (typeof getMsg === "function") ? getMsg() : null;

  try {
    const res = await Msg.confirmar(
      "Excluir mensagem?",
      "Essa ação não pode ser desfeita.",
      "Sim, excluir",
      "Cancelar"
    );

    if (!res || !res.isConfirmed) return;

    Msg.carregando("Excluindo...", "Aguarde");

    const resp = await fetch(`ajax/abertura/respostas_excluir.php`, {
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
    Msg.salvoSemReload(json.msg || "Mensagem excluída!");

    // Recarrega as respostas (use sua função que abre/carrega a modal)
    // Se você já tem uma função tipo respostas(chamadoId, protocolo), pode chamar ela.
    // Aqui vou deixar genérico:
    if (typeof carregarRespostas === "function") {
      await carregarRespostas(chamadoId);
    } else if (typeof respostas === "function") {
      // se a sua função respostas já sabe pegar o protocolo por fora, só passa o id:
      await respostas(chamadoId);
    } else {
      // fallback
      location.reload();
    }

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro ao excluir", err.message || "Falha ao excluir.");
    console.error(err);
  }
};
</script>
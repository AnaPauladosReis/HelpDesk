<?php
/**
 * Página: Logs (visualização + filtros)
 * - Sem modal / sem CRUD
 * - Listagem via ajax/logs/listar.php com filtros (data, usuário, ação)
 * - Sem botão "Filtrar": qualquer alteração já recarrega
 * - Botão Relatório reaproveita os filtros atuais
 */

@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';

$id_empresa = (int)($usuario_empresa ?? ($_SESSION['empresa'] ?? 0)); // por enquanto 0, mas já deixamos
$pag = 'logs';

// usuários (para filtro)
$stmtUsers = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE empresa = :empresa ORDER BY nome ASC");
$stmtUsers->execute([':empresa' => $id_empresa]);
$usuarios = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// ações possíveis (ENUM do logs)
$acoes = [
  ''        => 'Todas',
  'login'   => 'Login',
  'logout'  => 'Logout',
  'inserir' => 'Inserir',
  'editar'  => 'Editar',
  'excluir' => 'Excluir',
];

// datas padrão (hoje)
$hoje = date('Y-m-d');
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">

  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-clock-history fs-5 text-muted"></i>
    <h5 class="mb-0 fw-bold">Logs do Sistema</h5>
  </div>

  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary" onclick="limparFiltros()">
      <i class="bi bi-x-circle me-2"></i>Limpar
    </button>

    <button type="button" class="btn btn-salvar-sistema" onclick="abrirRelatorio()">
      <i class="bi bi-file-earmark-pdf me-2"></i>Relatório
    </button>
  </div>
</div>

<!-- FILTROS -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
  <div class="card-body">
    <div class="row g-3 align-items-end">

      <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">Data inicial</label>
        <input type="date" class="form-control" id="data_ini" value="<?= $hoje ?>">
      </div>

      <div class="col-6 col-md-2">
        <label class="form-label small fw-semibold mb-1">Data final</label>
        <input type="date" class="form-control" id="data_fim" value="<?= $hoje ?>">
      </div>

      <div class="col-12 col-md-4">
        <label class="form-label small fw-semibold mb-1">Atalhos</label>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="atalhoHoje()">Hoje</button>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="atalhoOntem()">Ontem</button>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="atalhoMes()">Este mês</button>
          <button type="button" class="btn btn-outline-primary btn-sm" onclick="atalhoUltimos7()">Últimos 7 dias</button>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="form-floating">
          <select
            class="form-select js-select2"
            id="usuario_id"
            name="usuario_id"
            data-placeholder=""
            data-allow-clear="1"
            style="width:100%"
          >
            <option value="">Todos</option>
            <?php foreach ($usuarios as $u) { ?>
              <option value="<?= (int)$u['id'] ?>">
                <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?>)
              </option>
            <?php } ?>
          </select>
          <label for="usuario_id">Selecionar Usuário</label>
        </div>
      </div>

      <div class="col-12 col-md-1">
        <div class="form-floating">
          <select
            class="form-select js-select2"
            id="acao"
            name="acao"
            data-placeholder=""
            data-allow-clear="1"
            style="width:100%"
          >
            <?php foreach ($acoes as $val => $label) { ?>
              <option value="<?= esc($val = (string)$val) ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php } ?>
          </select>
          <label for="acao">Ação</label>
        </div>
      </div>

    </div>
  </div>
</div>

<div id="listagem"></div>

<script>
  window.pag = "<?= $pag ?>";
</script>

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
    order: [[0, "desc"]]
  });
}

function qs(obj) {
  const p = new URLSearchParams();
  Object.entries(obj).forEach(([k, v]) => {
    if (v !== null && v !== undefined && String(v).trim() !== "") p.append(k, v);
  });
  return p.toString();
}

function getFiltros() {
  const ini = document.getElementById("data_ini")?.value || "";
  const fim = document.getElementById("data_fim")?.value || "";

  const usuario_id = document.getElementById("usuario_id")?.value || "";
  const acao = document.getElementById("acao")?.value || "";

  return { ini, fim, usuario_id, acao };
}

// debounce simples para não disparar 10x em sequência
let __tFiltro = null;
function aplicarAutoFiltro(delay = 250) {
  if (__tFiltro) clearTimeout(__tFiltro);
  __tFiltro = setTimeout(() => window.carregarListagem(), delay);
}

/* =========================
   LISTAGEM (AJAX) - com debug
========================= */
window.carregarListagem = async function () {
  const Msg = getMsg();
  const container = document.getElementById("listagem");

  try {
    const { ini, fim, usuario_id, acao } = getFiltros();

    // destrói DT antes de trocar HTML
    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable("#tabela")) {
      jQuery("#tabela").DataTable().destroy();
    }

    if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Buscando logs");

    const url = `ajax/${pag}/listar.php?` + qs({
      data_ini: ini,
      data_fim: fim,
      usuario_id: usuario_id,
      acao: acao
    });

    const resp = await fetch(url, { cache: "no-store" });
    const txt = await resp.text();

    // ✅ se listar.php explodiu (500/erro php), mostra o texto pra você ver o motivo
    if (!resp.ok) {
      throw new Error(txt.slice(0, 900));
    }

    container.innerHTML = txt;
    reiniciarDataTable();

    if (Msg && Msg.fechar) Msg.fechar();

  } catch (e) {
    if (Msg && Msg.fechar) Msg.fechar();

    const msg = (e && e.message) ? e.message : "Erro desconhecido";
    if (Msg && Msg.erro) Msg.erro("Erro ao carregar logs", msg.slice(0, 700));

    console.error(e);
  }
};

/* =========================
   FILTROS (UI) - SEM BOTÃO FILTRAR
========================= */
window.limparFiltros = function () {
  const hoje = new Date();
  const yyyy = hoje.getFullYear();
  const mm = String(hoje.getMonth() + 1).padStart(2, "0");
  const dd = String(hoje.getDate()).padStart(2, "0");
  const v = `${yyyy}-${mm}-${dd}`;

  document.getElementById("data_ini").value = v;
  document.getElementById("data_fim").value = v;

  // limpa selects (select2)
  if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
    jQuery("#usuario_id").val("").trigger("change.select2");
    jQuery("#acao").val("").trigger("change.select2");
  } else {
    const u = document.getElementById("usuario_id");
    const a = document.getElementById("acao");
    if (u) u.value = "";
    if (a) a.value = "";
  }

  window.carregarListagem();
};

window.atalhoHoje = function () {
  const d = new Date();
  const v = d.toISOString().slice(0, 10);
  document.getElementById("data_ini").value = v;
  document.getElementById("data_fim").value = v;
  window.carregarListagem();
};

window.atalhoOntem = function () {
  const d = new Date();
  d.setDate(d.getDate() - 1);
  const v = d.toISOString().slice(0, 10);
  document.getElementById("data_ini").value = v;
  document.getElementById("data_fim").value = v;
  window.carregarListagem();
};

window.atalhoMes = function () {
  const d = new Date();
  const ini = new Date(d.getFullYear(), d.getMonth(), 1);
  const fim = new Date(d.getFullYear(), d.getMonth() + 1, 0);

  document.getElementById("data_ini").value = ini.toISOString().slice(0, 10);
  document.getElementById("data_fim").value = fim.toISOString().slice(0, 10);

  window.carregarListagem();
};

window.atalhoUltimos7 = function () {
  const fim = new Date();
  const ini = new Date();
  ini.setDate(fim.getDate() - 6);

  document.getElementById("data_ini").value = ini.toISOString().slice(0, 10);
  document.getElementById("data_fim").value = fim.toISOString().slice(0, 10);

  window.carregarListagem();
};

/* =========================
   RELATÓRIO (placeholder)
========================= */
window.abrirRelatorio = function () {
  const { ini, fim, usuario_id, acao } = getFiltros();
  const url = `relatorios/logs.php?` + qs({ data_ini: ini, data_fim: fim, usuario_id, acao });
  window.open(url, "_blank");
};

/* =========================
   INIT
========================= */
(function initPagina() {
  document.addEventListener("DOMContentLoaded", function () {

    // init select2 (página, sem dropdown-parent)
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {

      const $user = jQuery("#usuario_id");
      const $acao = jQuery("#acao");

      $user.select2({
        theme: "bootstrap-5",
        width: "100%",
        allowClear: true,
        placeholder: "Todos"
      });

      $acao.select2({
        theme: "bootstrap-5",
        width: "100%",
        allowClear: true,
        placeholder: "Todas"
      });

      // ✅ eventos corretos do Select2
      $user.on("change.select2 select2:select select2:clear", () => aplicarAutoFiltro());
      $acao.on("change.select2 select2:select select2:clear", () => aplicarAutoFiltro());
    }

    // auto filtro ao mudar datas
    document.getElementById("data_ini")?.addEventListener("change", () => aplicarAutoFiltro());
    document.getElementById("data_fim")?.addEventListener("change", () => aplicarAutoFiltro());

    // inicial
    window.carregarListagem();
  });
})();

</script>

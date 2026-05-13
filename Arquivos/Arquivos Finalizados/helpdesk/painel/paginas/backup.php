<?php
@session_start();
require_once __DIR__ . '/../verificar.php';
require_once __DIR__ . '/../../conexao.php';
require_once __DIR__ . '/../includes/permissoes.php';

$pag = 'backup';

if (!podeFazer('editar')) {
  echo '<div class="alert alert-danger">Sem permissão para acessar o módulo de backup.</div>';
  return;
}
?>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-3 p-md-4">

    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
      <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-database-down me-2"></i>Backup do Banco</h5>
        <div class="text-muted small">Gera um arquivo .sql completo do banco <b><?= htmlspecialchars($banco) ?></b>.</div>

        <br>
        <button type="button" class="btn btn-salvar-sistema" onclick="gerarBackup()">
        <i class="bi bi-download me-2"></i>Gerar Backup
      </button>
    
    </div>

      
    </div>

    <hr class="my-3">

    <div id="backupInfo" class="text-muted small">
      Dica: se você gerar muitos backups, podemos criar uma lista/histórico e limpeza automática.
    </div>

  </div>
</div>

<script> window.pag = "<?= $pag ?>"; </script>

<script>
function getMsg() {
  try {
    if (typeof window !== "undefined" && window.Mensagens) return window.Mensagens;
    if (typeof Mensagens !== "undefined") return Mensagens;
  } catch (e) {}
  return null;
}

window.gerarBackup = async function () {
  const Msg = getMsg();

  try {
    if (Msg && Msg.carregando) Msg.carregando("Gerando backup...", "Aguarde");

    const resp = await fetch(`ajax/${pag}/gerar.php`, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: "ok=1"
    });

    const ct = resp.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      const txt = await resp.text();
      throw new Error("Resposta inesperada do servidor.\n" + txt.slice(0, 300));
    }

    const json = await resp.json();
    if (!json.ok) throw new Error(json.msg || "Não foi possível gerar o backup.");

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload("Backup gerado!", "Baixando o arquivo...");

    // baixa via endpoint seguro (não expõe pasta)
    window.location.href = `ajax/${pag}/download.php?f=` + encodeURIComponent(json.file);

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro", err.message || "Falha ao gerar backup.");
    console.error(err);
  }
};
</script>
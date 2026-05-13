<?php
session_start();
require_once 'conexao.php';

$icone_sistema = $config['icone'] ?? '';
$icone_path = __DIR__ . '/uploads/' . $icone_sistema;
if ($icone_sistema === '' || !file_exists($icone_path)) $icone_sistema = 'sem_foto.webp';
$icone_url = 'uploads/' . rawurlencode($icone_sistema);

$logo_sistema = $config['logo'] ?? '';
$logo_path = __DIR__ . '/uploads/' . $logo_sistema;
if ($logo_sistema === '' || !file_exists($logo_path)) $logo_sistema = 'sem_foto.webp';
$logo_url = 'uploads/' . rawurlencode($logo_sistema);

$cor_primaria   = $config['cor_primaria']   ?? ($cor_primaria ?? '#667eea');
$cor_secundaria = $config['cor_secundaria'] ?? ($cor_secundaria ?? '#764ba2');

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// setores
$setores = [];
try {
  $stS = $pdo->query("SELECT id, nome FROM setores ORDER BY nome ASC");
  $setores = $stS->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $setores = [];
}

// status "Aberto" (pega ID automaticamente)
$status_aberto_id = 0;
try{
  $stA = $pdo->prepare("SELECT id FROM chamados_status WHERE ativo='Sim' AND nome = 'Aberto' LIMIT 1");
  $stA->execute();
  $rowA = $stA->fetch(PDO::FETCH_ASSOC);
  $status_aberto_id = (int)($rowA['id'] ?? 0);
}catch(Throwable $e){
  $status_aberto_id = 0;
}

// defaults
$id_empresa = 0;
$prioridade_padrao = 'Media';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Abertura de Chamado - <?= esc($nome_sistema ?? 'HelpDesk') ?></title>

  <link rel="icon" type="image/webp" href="<?= esc($icone_url) ?>">
  <link rel="apple-touch-icon" href="<?= esc($icone_url) ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <style>
    :root{ --cor-primaria: <?= esc($cor_primaria) ?>; --cor-secundaria: <?= esc($cor_secundaria) ?>; }
    body{ min-height:100vh; background: linear-gradient(135deg, rgba(102,126,234,.10), rgba(118,75,162,.10)); }
    .page-wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
    .card-shell{ width:100%; max-width:980px; border:0; border-radius:22px; overflow:hidden; box-shadow:0 18px 50px rgba(0,0,0,.10); background:#fff; }
    .left-panel{ background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); color:#fff; padding:28px; height:100%; }
    .left-panel .logo{ max-height:56px; max-width:220px; background: rgba(255,255,255,.10); border-radius:14px; padding:10px 12px; }
    .left-panel h2{ font-size:1.25rem; margin-top:18px; font-weight:800; }
    .left-panel p{ opacity:.9; }
    .feature{ display:flex; gap:10px; align-items:flex-start; padding:10px 0; border-top:1px solid rgba(255,255,255,.18); }
    .feature:first-of-type{ border-top:0; }
    .feature i{ font-size:1.2rem; }
    .right-panel{ padding:26px; }
    .title-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; }
    .title-row .t{ font-weight:900; font-size:1.05rem; letter-spacing:.2px; }
    .btn-salvar{ background: var(--cor-primaria); border:0; color:#fff; font-weight:700; border-radius:14px; padding:.65rem 1rem; }
    .btn-salvar:hover{ filter: brightness(.95); }

    .select2-container .select2-selection--single{
      height: calc(3.5rem + 2px);
      padding: 1.625rem .75rem .625rem .75rem;
      border: 1px solid #dee2e6;
      border-radius: .375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{ line-height:1.2rem; padding-left:0; }
    .select2-container--default .select2-selection--single .select2-selection__arrow{ height: calc(3.5rem + 2px); right:8px; }
  </style>
</head>

<body>
<div class="page-wrap">
  <div class="card card-shell">
    <div class="row g-0">
      <div class="col-12 col-lg-4">
        <div class="left-panel h-100">
          <div class="text-center">
            <img src="<?= esc($logo_url) ?>" alt="Logo" class="logo">
          </div>

          <h2>Abertura de Chamado</h2>
          <p class="mb-3">Informe seus dados e descreva o problema. Em seguida, envie sua solicitação.</p>

          <div class="feature">
            <i class="bi bi-ticket-perforated"></i>
            <div><b>Protocolo automático</b><div class="small opacity-75">Você recebe um número para acompanhar.</div></div>
          </div>
          <div class="feature">
            <i class="bi bi-bell"></i>
            <div><b>Atualizações</b><div class="small opacity-75">Resposta e status do chamado.</div></div>
          </div>
          <div class="feature">
            <i class="bi bi-shield-check"></i>
            <div><b>Organização</b><div class="small opacity-75">Setor e registro seguro.</div></div>
          </div>
        </div>
      </div>

      <div class="col-12 col-lg-8">
        <div class="right-panel">
          <div class="title-row">
            <div class="t"><i class="bi bi-plus-circle me-2"></i>Novo Chamado</div>
            <a href="./" class="btn btn-outline-secondary btn-sm rounded-3">
              <i class="bi bi-box-arrow-left me-1"></i>Voltar
            </a>
          </div>

          <?php if ($status_aberto_id <= 0) { ?>
            <div class="alert alert-warning rounded-4">
              Não encontrei um status chamado <b>"Aberto"</b> em <code>chamados_status</code>.
              Crie esse status ou me diga qual nome você usa (ex: "Em Aberto").
            </div>
          <?php } ?>

          <form id="formAbertura" autocomplete="off">
            <input type="hidden" name="id" value="0">
            <input type="hidden" name="empresa" value="<?= (int)$id_empresa ?>">
            <input type="hidden" name="prioridade" value="<?= esc($prioridade_padrao) ?>">
            <input type="hidden" name="status_id" value="<?= (int)$status_aberto_id ?>">
            <input type="hidden" name="cliente_id" id="cliente_id_hidden" value="">

            <div class="row g-3">

                            <!-- CPF/CNPJ PRIMEIRO -->
                <div class="col-12 col-md-4">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj"
                            placeholder="CPF/CNPJ" maxlength="18" required>
                    <label for="cpf_cnpj">CPF / CNPJ</label>
                    </div>
                    
                </div>

                <div class="col-12 col-md-8">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="nome" name="nome"
                            placeholder="Nome" maxlength="100" required>
                    <label for="nome">Nome</label>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email"
                            placeholder="E-mail" maxlength="100">
                    <label for="email">E-mail</label>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="telefone" name="telefone"
                            placeholder="Telefone" maxlength="20">
                    <label for="telefone">Telefone</label>
                    </div>
                </div>

              <div class="col-12">
                <div class="form-floating">
                  <input type="text" class="form-control" id="assunto" name="assunto"
                         placeholder="Assunto" maxlength="120" required>
                  <label for="assunto">Assunto</label>
                </div>
              </div>

              <div class="col-12 col-md-7">
                <div class="form-floating">
                  <select class="form-select js-select2" id="setor_id" name="setor_id" style="width:100%" required>
                    <option value="0" selected>Selecione</option>
                    <?php foreach ($setores as $s) { ?>
                      <option value="<?= (int)$s['id'] ?>"><?= esc($s['nome'] ?? '') ?></option>
                    <?php } ?>
                  </select>
                  <label for="setor_id">Setor</label>
                </div>
              </div>

              <div class="col-12 col-md-5">
                <div class="alert alert-light border rounded-4 mb-0 h-100 d-flex align-items-center">
                  <div class="small">
                    <b>Status:</b> Aberto (automático)
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="form-floating">
                  <textarea class="form-control" id="descricao" name="descricao"
                            placeholder="Descreva o problema" style="height: 160px" required></textarea>
                  <label for="descricao">Descrição do problema</label>
                </div>
              </div>

              <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <button type="reset" class="btn btn-outline-secondary rounded-3">
                  <i class="bi bi-eraser me-2"></i>Limpar
                </button>

                <button type="submit" class="btn btn-salvar" <?= ($status_aberto_id<=0 ? 'disabled' : '') ?>>
                  <i class="bi bi-send me-2"></i>Enviar Chamado
                </button>
              </div>

            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="js/mensagens.js"></script>

<script>
(function(){
  // select2
  try{
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
      jQuery('.js-select2').select2({ width:'100%', placeholder:'Selecione', allowClear:true });
    }
  }catch(e){}

  function getMsg(){
    try{
      if (typeof window !== "undefined" && window.Mensagens) return window.Mensagens;
      if (typeof Mensagens !== "undefined") return Mensagens;
    }catch(e){}
    return null;
  }

  function soNumeros(v){ return String(v || "").replace(/\D+/g, ""); }

  function mascaraCpfCnpj(input){
    let v = soNumeros(input.value);
    if (v.length <= 11) {
      v = v.replace(/(\d{3})(\d)/, "$1.$2")
           .replace(/(\d{3})(\d)/, "$1.$2")
           .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    } else {
      v = v.replace(/^(\d{2})(\d)/, "$1.$2")
           .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
           .replace(/\.(\d{3})(\d)/, ".$1/$2")
           .replace(/(\d{4})(\d{1,2})$/, "$1-$2");
    }
    input.value = v;
  }

  const cpfInp = document.getElementById('cpf_cnpj');
  if (cpfInp){
    cpfInp.addEventListener('input', ()=> mascaraCpfCnpj(cpfInp));
    cpfInp.addEventListener('blur', ()=> mascaraCpfCnpj(cpfInp));
  }

  const form = document.getElementById('formAbertura');
  if(!form) return;

  form.addEventListener('submit', async function(e){
    e.preventDefault();

    const Msg = getMsg();

    try{
      if (Msg && Msg.carregando) Msg.carregando("Enviando...", "Aguarde");
      else Swal.fire({ title:"Enviando...", html:"Aguarde", allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

      const nome = (document.getElementById('nome')?.value || "").trim();
      const doc  = soNumeros(document.getElementById('cpf_cnpj')?.value || "");

        

      if (nome.length < 3) throw new Error("Informe o nome (mínimo 3 caracteres).");
      if (![11,14].includes(doc.length)) throw new Error("Informe um CPF (11) ou CNPJ (14) válido.");

      // 1) resolve/cria cliente e pega cliente_id
      const fdCli = new FormData();
      fdCli.append("nome", nome);
      fdCli.append("cpf_cnpj", doc);

      const respCli = await fetch("ajax/cliente_resolver.php", { method:"POST", body: fdCli, cache:"no-store" });
      const ctCli = respCli.headers.get("content-type") || "";
      if (!ctCli.includes("application/json")) {
        const t = await respCli.text().catch(()=> "");
        throw new Error("Resposta inesperada ao resolver cliente:\n" + (t ? t.slice(0, 400) : ""));
      }

      const jsonCli = await respCli.json();
      if (!jsonCli.ok) throw new Error(jsonCli.msg || "Não foi possível validar o cliente.");
      const clienteId = parseInt(jsonCli.cliente_id || "0", 10);
      if (!clienteId) throw new Error("Cliente inválido.");

      document.getElementById("cliente_id_hidden").value = String(clienteId);

      // 2) salva chamado no mesmo endpoint do painel
      const fd = new FormData(form);

      const resp = await fetch("painel/ajax/abertura/salvar.php", {
        method: "POST",
        body: fd,
        cache: "no-store"
      });

      const ct = resp.headers.get("content-type") || "";
      if (!ct.includes("application/json")) {
        const txt = await resp.text().catch(()=> "");
        throw new Error("Resposta inesperada do servidor:\n" + (txt ? txt.slice(0, 400) : ""));
      }

      const json = await resp.json();
      if (!json.ok) throw new Error(json.msg || "Não foi possível abrir o chamado.");

      if (Msg && Msg.fechar) Msg.fechar();

      // exibe protocolo se seu salvar retornar
      const proto = json.protocolo ? `<div class="mt-2"><b>Protocolo:</b> ${json.protocolo}</div>` : "";
      Swal.fire({
        icon: "success",
        title: "Chamado aberto!",
        html: `<div style="text-align:left">${escHtml(json.msg || "Solicitação registrada com sucesso.")}${proto}</div>`,
        confirmButtonText: "Ok"
      });

      form.reset();
      try { jQuery('.js-select2').val(null).trigger('change'); } catch(e){}

    }catch(err){
      if (Msg && Msg.fechar) Msg.fechar();
      Swal.fire({ icon:"error", title:"Erro", text: err.message || "Falha ao enviar." });
      console.error(err);
    }
  });

  // evita XSS no Swal html
  function escHtml(str){
    return String(str||"").replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }





  async function buscarClientePorDoc(docSomenteNumeros){
  const resp = await fetch(`ajax/cliente_buscar.php?cpf_cnpj=${encodeURIComponent(docSomenteNumeros)}`, {
    cache: "no-store"
  });

  const ct = resp.headers.get("content-type") || "";
  if (!ct.includes("application/json")) {
    const t = await resp.text().catch(()=> "");
    throw new Error("Resposta inesperada ao buscar cliente:\n" + (t ? t.slice(0, 300) : ""));
  }
  return resp.json();
}

function setReadonlyCamposCliente(on){
  ["nome","email","telefone"].forEach(id=>{
    const el = document.getElementById(id);
    if (!el) return;
    el.readOnly = !!on;
  });
}

function limparClienteAuto(){
  document.getElementById("cliente_id_hidden").value = "";
  // não apaga CPF/CNPJ
  // deixa campos para digitação
  setReadonlyCamposCliente(false);
}

async function preencherSeExiste(){
  const cpfInp = document.getElementById("cpf_cnpj");
  const doc = (cpfInp?.value || "").trim();

  // só busca quando tiver tamanho válido
  if (![11,14].includes(doc.length)) {
    limparClienteAuto();
    return;
  }

  try{
    // opcional: mostrar um loading leve
    // Mensagens.carregando("Consultando...", "Verificando cadastro");
    console.log("Buscando cliente por doc:", doc);
    const json = await buscarClientePorDoc(doc);

    if (!json.ok) {
      // se der erro, só libera pra digitar
      limparClienteAuto();
      return;
    }

    if (json.exists && json.data) {
      document.getElementById("cliente_id_hidden").value = String(json.data.id || "");
      document.getElementById("nome").value = json.data.nome || "";
      document.getElementById("email").value = json.data.email || "";
      document.getElementById("telefone").value = json.data.telefone || "";

      // trava para evitar divergência
      setReadonlyCamposCliente(true);
    } else {
      // não existe → libera pra preencher
      limparClienteAuto();
      // mantém o foco no nome (boa UX)
      setTimeout(()=> document.getElementById("nome")?.focus(), 50);
    }

    // Mensagens.fechar();

  } catch(e){
    limparClienteAuto();
    console.error(e);
    // Mensagens.fechar();
  }
}

// dispara no blur e também quando completar tamanho
document.getElementById("cpf_cnpj")?.addEventListener("blur", preencherSeExiste);
document.getElementById("cpf_cnpj")?.addEventListener("keyup", function(){
  const doc = soNumeros(this.value || "");
  if (doc.length === 11 || doc.length === 14) preencherSeExiste();
});




})();
</script>
</body>
</html>


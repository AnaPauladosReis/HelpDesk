<?php
/**
 * TOPBAR + DROPDOWN + MODAIS (Config e Perfil)
 * - Recupera dados do usuário e config para edição
 * - Puxa foto em uploads/perfil
 */

// =========================
// FOTO DO USUÁRIO (dropdown)
// =========================
$foto_usuario = '';
$stmt = $pdo->prepare("SELECT foto FROM clientes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $usuario_id]);
$rowFoto = $stmt->fetch();
if ($rowFoto) {
    $foto_usuario = $rowFoto['foto'] ?? '';
}

$foto_path = "../uploads/clientes/" . $foto_usuario;
$foto_url  = "../uploads/clientes/" . rawurlencode($foto_usuario);

// =========================
// DADOS PARA EDITAR CONFIG
// =========================
$empresa_cfg = 0; // igual seu conexao.php (empresa = 0). Se virar multi, troque para $usuario_empresa.
$stmt = $pdo->prepare("SELECT * FROM config WHERE empresa = :empresa LIMIT 1");
$stmt->execute([':empresa' => $empresa_cfg]);
$cfg = $stmt->fetch() ?: [];

$cfg_cor_primaria   = $cfg['cor_primaria']   ?? ($cor_primaria ?? '#667eea');
$cfg_cor_secundaria = $cfg['cor_secundaria'] ?? ($cor_secundaria ?? '#764ba2');

// =========================
// DADOS PARA EDITAR PERFIL
// =========================
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $usuario_id]);
$perfil = $stmt->fetch() ?: [];
?>

<header class="topbar">
  <div class="topbar-left">
    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" id="btnToggleSidebarMobile">
      <i class="bi bi-list"></i>
    </button>
  </div>

  <div class="topbar-right">
   
  <div class="position-relative d-inline-block">
  <button class="icon-btn position-relative"
          type="button"
          id="btnNotifPendentes"
          title="Notificações">
    <i class="bi bi-bell"></i>

    <span id="notifPendentesBadge"
          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
          style="display:none;font-size:.70rem;">
      0
    </span>
  </button>

  <div id="popupPendentes"
       class="shadow-sm border rounded-4 bg-white"
       style="display:none; position:absolute; right:0; top:45px; width:360px; z-index:1050;">

    <div class="p-3 border-bottom fw-semibold">
      Respostas pendentes
    </div>

    <div id="popupPendentesLista"
         class="p-2"
         style="max-height:320px; overflow:auto;">
    </div>

    <div class="p-3 border-top">
      <a href="abertura"
         class="btn btn-sm btn-outline-primary w-100">
        Ir para Abertura
      </a>
    </div>
  </div>
</div>

    <div class="dropdown">
      <button class="user-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">

        <?php if (!empty($foto_usuario) && file_exists($foto_path)) { ?>
          <img src="<?= $foto_url ?>" class="user-photo" alt="Foto do usuário">
        <?php } else { ?>
          <span class="avatar"><?= mb_strtoupper(mb_substr($usuario_nome, 0, 1)) ?></span>
        <?php } ?>

        <span class="d-none d-md-inline"><?= $usuario_nome ?></span>
      </button>

      <ul class="dropdown-menu dropdown-menu-end">
        
      

        <li>
          <a class="dropdown-item dropdown-item-soft" href="#"
             data-bs-toggle="modal" data-bs-target="#modalPerfil">
            <i class="bi bi-person-circle me-2"></i>Editar Perfil
          </a>
        </li>

        <li><hr class="dropdown-divider"></li>

        <li>
          <a class="dropdown-item dropdown-item-danger" href="logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>Sair
          </a>
        </li>
      </ul>
    </div>
  </div>
</header>




<!-- MODAL PERFIL (CLIENTE) -->
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-labelledby="modalPerfilClienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalPerfilClienteLabel">
          <i class="bi bi-person-circle me-2"></i>Editar Perfil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="formPerfil" method="post" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= (int)($usuario_id ?? 0) ?>">
          <input type="hidden" name="empresa" value="<?= (int)($cliente_empresa ?? 0) ?>">

          <?php
            // Foto: se você tiver foto do cliente no banco futuramente, troca aqui.
            // Como na sua tabela clientes não tem "foto", deixei opcional.
            $fotoAtual = trim((string)($perfil['foto'] ?? 'sem_foto.webp'));
            if ($fotoAtual === '') $fotoAtual = 'sem_foto.webp';

            // se você for usar outra pasta, ajuste:
            $fotoPreviewUrl = "../uploads/clientes/" . rawurlencode($fotoAtual);
          ?>

          <div class="row g-3">

            <!-- LINHA 1 -->
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_nome" name="nome"
                       placeholder="Nome" maxlength="120"
                       value="<?= htmlspecialchars($perfil['nome'] ?? '') ?>" required>
                <label for="cli_nome">Nome</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_telefone" name="telefone"
                       placeholder="Telefone" maxlength="20"
                       data-mask="tel"
                       value="<?= htmlspecialchars($perfil['telefone'] ?? '') ?>">
                <label for="cli_telefone">Telefone</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_cpf" name="cpf_cnpj"
                       placeholder="CPF/CNPJ" maxlength="20"
                       data-mask="cpf"
                       value="<?= htmlspecialchars($perfil['cpf_cnpj'] ?? '') ?>">
                <label for="cli_cpf">CPF/CNPJ</label>
              </div>
            </div>

            <!-- LINHA 2 -->
            <div class="col-12 col-md-7">
              <div class="form-floating">
                <input type="email" class="form-control" id="cli_email" name="email"
                       placeholder="E-mail" maxlength="120"
                       value="<?= htmlspecialchars($perfil['email'] ?? '') ?>">
                <label for="cli_email">E-mail</label>
              </div>
            </div>

            <div class="col-12 col-md-5">
              <div class="form-floating">
                <input type="password" class="form-control" id="cli_senha" name="senha"
                       placeholder="Nova senha" maxlength="255" value=""
                       autocomplete="new-password">
                <label for="cli_senha">Nova Senha</label>
              </div>
              <div class="form-text">Deixe em branco para manter.</div>
            </div>

            
            <!-- (OPCIONAL) FOTO - só use se você realmente tiver upload de foto do cliente -->
            <div class="col-12">
              <label class="form-label small fw-semibold mb-1">Foto do Perfil (opcional)</label>
              <div class="d-flex align-items-center gap-2">
                <img
                  src="<?= $fotoPreviewUrl ?>"
                  alt="Foto atual"
                  id="perfil_foto_preview"
                  style="width:44px;height:44px;object-fit:cover;border-radius:12px;border:1px solid #cbd5e1;"
                >
                <input class="form-control" type="file" name="foto" id="perfil_foto" accept="image/*">
              </div>
              <div class="form-text">JPG, PNG ou WEBP (até 2MB).</div>
              <input type="hidden" name="foto_atual" value="<?= htmlspecialchars($fotoAtual) ?>">
            </div>

            <!-- ENDEREÇO -->
            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_cep" name="cep"
                       placeholder="CEP" maxlength="10"
                       data-mask="cep"
                       data-cep
                       value="<?= htmlspecialchars($perfil['cep'] ?? '') ?>">
                <label for="cli_cep">CEP</label>
              </div>
            </div>

            <div class="col-12 col-md-8">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_endereco" name="endereco"
                       placeholder="Endereço" maxlength="150"
                       data-cep-endereco
                       value="<?= htmlspecialchars($perfil['endereco'] ?? '') ?>">
                <label for="cli_endereco">Endereço</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_numero" name="numero"
                       placeholder="Nº" maxlength="20"
                       value="<?= htmlspecialchars($perfil['numero'] ?? '') ?>">
                <label for="cli_numero">Nº</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_bairro" name="bairro"
                       placeholder="Bairro" maxlength="80"
                       data-cep-bairro
                       value="<?= htmlspecialchars($perfil['bairro'] ?? '') ?>">
                <label for="cli_bairro">Bairro</label>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_cidade" name="cidade"
                       placeholder="Cidade" maxlength="80"
                       data-cep-cidade
                       value="<?= htmlspecialchars($perfil['cidade'] ?? '') ?>">
                <label for="cli_cidade">Cidade</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <?php $uf_sel = strtoupper($perfil['estado'] ?? ''); ?>
                <select class="form-select" id="cli_estado" name="estado" data-cep-estado>
                  <option value="">UF</option>
                  <option value="AC" <?= ($uf_sel==='AC'?'selected':'') ?>>AC</option>
                  <option value="AL" <?= ($uf_sel==='AL'?'selected':'') ?>>AL</option>
                  <option value="AP" <?= ($uf_sel==='AP'?'selected':'') ?>>AP</option>
                  <option value="AM" <?= ($uf_sel==='AM'?'selected':'') ?>>AM</option>
                  <option value="BA" <?= ($uf_sel==='BA'?'selected':'') ?>>BA</option>
                  <option value="CE" <?= ($uf_sel==='CE'?'selected':'') ?>>CE</option>
                  <option value="DF" <?= ($uf_sel==='DF'?'selected':'') ?>>DF</option>
                  <option value="ES" <?= ($uf_sel==='ES'?'selected':'') ?>>ES</option>
                  <option value="GO" <?= ($uf_sel==='GO'?'selected':'') ?>>GO</option>
                  <option value="MA" <?= ($uf_sel==='MA'?'selected':'') ?>>MA</option>
                  <option value="MT" <?= ($uf_sel==='MT'?'selected':'') ?>>MT</option>
                  <option value="MS" <?= ($uf_sel==='MS'?'selected':'') ?>>MS</option>
                  <option value="MG" <?= ($uf_sel==='MG'?'selected':'') ?>>MG</option>
                  <option value="PA" <?= ($uf_sel==='PA'?'selected':'') ?>>PA</option>
                  <option value="PB" <?= ($uf_sel==='PB'?'selected':'') ?>>PB</option>
                  <option value="PR" <?= ($uf_sel==='PR'?'selected':'') ?>>PR</option>
                  <option value="PE" <?= ($uf_sel==='PE'?'selected':'') ?>>PE</option>
                  <option value="PI" <?= ($uf_sel==='PI'?'selected':'') ?>>PI</option>
                  <option value="RJ" <?= ($uf_sel==='RJ'?'selected':'') ?>>RJ</option>
                  <option value="RN" <?= ($uf_sel==='RN'?'selected':'') ?>>RN</option>
                  <option value="RS" <?= ($uf_sel==='RS'?'selected':'') ?>>RS</option>
                  <option value="RO" <?= ($uf_sel==='RO'?'selected':'') ?>>RO</option>
                  <option value="RR" <?= ($uf_sel==='RR'?'selected':'') ?>>RR</option>
                  <option value="SC" <?= ($uf_sel==='SC'?'selected':'') ?>>SC</option>
                  <option value="SP" <?= ($uf_sel==='SP'?'selected':'') ?>>SP</option>
                  <option value="SE" <?= ($uf_sel==='SE'?'selected':'') ?>>SE</option>
                  <option value="TO" <?= ($uf_sel==='TO'?'selected':'') ?>>TO</option>
                </select>
                <label for="cli_estado">UF</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="cli_complemento" name="complemento"
                       placeholder="Compl." maxlength="60"
                       data-cep-complemento
                       value="<?= htmlspecialchars($perfil['complemento'] ?? '') ?>">
                <label for="cli_complemento">Compl.</label>
              </div>
            </div>

            <div class="col-12">
              <div class="form-floating">
                <textarea class="form-control" id="cli_observacoes" name="observacoes"
                          placeholder="Observações" style="height:90px;"><?= htmlspecialchars($perfil['observacoes'] ?? '') ?></textarea>
                <label for="cli_observacoes">Observações</label>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-cancelar-sistema" data-bs-dismiss="modal">
            Cancelar
          </button>

          <button type="submit" class="btn btn-salvar-sistema">
            <i class="bi bi-save me-2"></i>Salvar
          </button>
        </div>
      </form>

    </div>
  </div>
</div>





<script>
/**
 * Preview da foto no input file
 * - Mostra a foto atual ao abrir modal
 * - Ao escolher arquivo, atualiza miniatura
 */
(function(){
  const input = document.getElementById('perfil_foto');
  const preview = document.getElementById('perfil_foto_preview');
  if(!input || !preview) return;

  input.addEventListener('change', function(){
    const file = this.files && this.files[0];
    if(!file) return;

    // só imagem
    if(!file.type.startsWith('image/')) return;

    const url = URL.createObjectURL(file);
    preview.src = url;
  });
})();
</script>



<script>
(function () {

  async function postForm(url, formEl) {
    const fd = new FormData(formEl);

    const resp = await fetch(url, {
      method: "POST",
      body: fd,
    });

    const ct = resp.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      const text = await resp.text();
      throw new Error("Resposta inesperada do servidor.\n" + text.slice(0, 300));
    }

    const data = await resp.json();
    if (!data.ok) throw new Error(data.msg || "Falha ao salvar.");
    return data;
  }

  function hideModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    bootstrap.Modal.getInstance(el)?.hide();
  }

  document.addEventListener("DOMContentLoaded", () => {

  

    // ==========================
    // PERFIL
    // ==========================
    const formPerfil = document.getElementById("formPerfil");
    if (formPerfil) {
      formPerfil.addEventListener("submit", async (e) => {
        e.preventDefault();

        Mensagens.carregando("Salvando...", "Aguarde");

        try {
          const data = await postForm("scripts/salvar_perfil.php", formPerfil);

          Mensagens.fechar();
          Mensagens.salvoSemReload(data.msg || "Perfil atualizado!");

          hideModal("modalPerfil");

          setTimeout(() => location.reload(), 1000);

        } catch (err) {
          Mensagens.fechar();

          if ((err.message || "").toLowerCase().includes("sessão")) {
            return Mensagens.sessaoExpirada("../index.php");
          }

          Mensagens.erro("Erro ao salvar", err.message);
        }
      });
    }

  });
})();
</script>






<script>
// ✅ helper padrão: evita quebrar se Mensagens não existir
function getMsg() {
  try {
    if (typeof window !== "undefined" && window.Mensagens) return window.Mensagens;
    if (typeof Mensagens !== "undefined") return Mensagens;
  } catch (e) {}
  return null;
}
</script>






<script>
(function(){

  const URL = "ajax/notificacoes/chamados_pendentes_cliente.php";

  const btn = document.getElementById("btnNotifPendentes");
  const badge = document.getElementById("notifPendentesBadge");
  const popup = document.getElementById("popupPendentes");
  const lista = document.getElementById("popupPendentesLista");

  function show(el){ if(el) el.style.display = ""; }
  function hide(el){ if(el) el.style.display = "none"; }

  function carregarPendentes(){
    fetch(URL)
      .then(r=>r.json())
      .then(json=>{
        if(!json.ok) return;

        const count = parseInt(json.count || 0);
        const items = json.items || [];

        if(count > 0){
          badge.textContent = count;
          show(badge);
        }else{
          hide(badge);
        }

        if(!count){
          lista.innerHTML = `
            <div class="text-muted small p-3 text-center">
              Nenhuma resposta pendente.
            </div>`;
          return;
        }

        let html = "";
        items.forEach(item=>{
          html += `
            <div class="border-bottom p-2 d-flex justify-content-between align-items-start">
              <div>
                <div class="fw-semibold small">
                  ${item.protocolo}
                </div>
                <div class="text-muted small">
                  ${item.usuario_nome || 'Cliente'}
                </div>
              </div>
              <a href="../chamado/${item.protocolo}" target="_blank"
                 class="btn btn-sm btn-outline-primary">
                 Abrir
              </a>
            </div>
          `;
        });

        lista.innerHTML = html;
      });
  }

  btn.addEventListener("click", function(){
    if(popup.style.display === "none" || popup.style.display === ""){
      carregarPendentes();
      show(popup);
    }else{
      hide(popup);
    }
  });

  document.addEventListener("click", function(e){
    if(!btn.contains(e.target) && !popup.contains(e.target)){
      hide(popup);
    }
  });

  // carrega automaticamente ao abrir página
  document.addEventListener("DOMContentLoaded", carregarPendentes);

})();
</script>
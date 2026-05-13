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
$stmt = $pdo->prepare("SELECT foto FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $usuario_id]);
$rowFoto = $stmt->fetch();
if ($rowFoto) {
    $foto_usuario = $rowFoto['foto'] ?? '';
}

$foto_path = "../uploads/perfil/" . $foto_usuario;
$foto_url  = "../uploads/perfil/" . rawurlencode($foto_usuario);

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
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
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


  <!-- 🔎 Busca por Protocolo -->
  <div class="me-2 d-none d-md-flex align-items-center">
      <div class="input-group input-group-sm" style="width: 260px;">
        <span class="input-group-text bg-white">
          <i class="bi bi-search"></i>
        </span>
        <input type="text"
               class="form-control"
               id="topbarBuscaProtocolo"
               placeholder="Buscar protocolo..."
               maxlength="60"
               autocomplete="off"
               inputmode="numeric"
               aria-label="Buscar chamado por protocolo">
      </div>
    </div>
   
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
        <li class="px-3 py-2">
          <div class="small"><b>Nível:</b> <?= $usuario_nivel ?></div>
        </li>

        <li><hr class="dropdown-divider"></li>

        <li>
          <a class="dropdown-item dropdown-item-soft" href="#"
             data-bs-toggle="modal" data-bs-target="#modalConfig">
            <i class="bi bi-gear me-2"></i>Editar Configurações
          </a>
        </li>

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


<!-- MODAL CONFIGURAÇÕES -->
<div class="modal fade" id="modalConfig" tabindex="-1" aria-labelledby="modalConfigLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalConfigLabel">
          <i class="bi bi-gear me-2"></i>Configurações do Sistema
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="formConfig" method="post" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">

          <div class="mb-2">
            <div class="small text-muted fw-semibold mb-2">Dados do Sistema</div>

            <input type="hidden" name="id" value="<?= (int)($cfg['id'] ?? 0) ?>">
            <input type="hidden" name="empresa" value="<?= (int)($cfg['empresa'] ?? $empresa_cfg) ?>">

            <div class="row g-3 align-items-end">
              <!-- Nome / Telefone / Email -->
              <div class="col-12 col-md-5">
                <div class="form-floating">
                  <input type="text" class="form-control" id="nome_sistema" name="nome_sistema"
                         placeholder="Nome do Sistema" maxlength="100"
                         value="<?= htmlspecialchars($cfg['nome_sistema'] ?? '') ?>">
                  <label for="nome_sistema">Nome do Sistema</label>
                </div>
              </div>

              <div class="col-12 col-md-3">
                <div class="form-floating">
                  <input type="text" class="form-control" id="telefone_sistema" name="telefone_sistema"
                         placeholder="Telefone" maxlength="20"
                         data-mask="tel"
                         value="<?= htmlspecialchars($cfg['telefone_sistema'] ?? '') ?>">
                  <label for="telefone_sistema">Telefone</label>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="form-floating">
                  <input type="email" class="form-control" id="email_sistema" name="email_sistema"
                         placeholder="E-mail" maxlength="75"
                         value="<?= htmlspecialchars($cfg['email_sistema'] ?? '') ?>">
                  <label for="email_sistema">E-mail do Sistema</label>
                </div>
              </div>

              <!-- Endereço (antes das cores) -->
              <div class="col-12 col-md-4">
                <div class="form-floating">
                  <input type="text" class="form-control" id="endereco" name="endereco"
                         placeholder="Endereço" maxlength="255"
                         value="<?= htmlspecialchars($cfg['endereco'] ?? '') ?>">
                  <label for="endereco">Endereço</label>
                </div>
              </div>


             

              <!-- Cor Primária (3 colunas) -->
              <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Cor Primária</label>
                <div class="d-flex gap-2 align-items-center">
                  <input type="color" class="form-control form-control-color"
                         id="cor_primaria" name="cor_primaria"
                         value="<?= htmlspecialchars($cfg_cor_primaria) ?>" title="Cor Primária">
                  <input type="text" class="form-control" id="cor_primaria_txt"
                         placeholder="#667eea" maxlength="25"
                         value="<?= htmlspecialchars($cfg_cor_primaria) ?>"
                         oninput="document.getElementById('cor_primaria').value=this.value">
                </div>
              </div>

              <!-- Cor Secundária (3 colunas) -->
              <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Cor Secundária</label>
                <div class="d-flex gap-2 align-items-center">
                  <input type="color" class="form-control form-control-color"
                         id="cor_secundaria" name="cor_secundaria"
                         value="<?= htmlspecialchars($cfg_cor_secundaria) ?>" title="Cor Secundária">
                  <input type="text" class="form-control" id="cor_secundaria_txt"
                         placeholder="#764ba2" maxlength="25"
                         value="<?= htmlspecialchars($cfg_cor_secundaria) ?>"
                         oninput="document.getElementById('cor_secundaria').value=this.value">
                </div>
              </div>



              <div class="col-12 col-md-2">
                <div class="form-floating">
                  <input type="number" class="form-control" id="dias_excluir_logs" name="dias_excluir_logs"
                         placeholder="" 
                         value="<?= htmlspecialchars($cfg['dias_excluir_logs'] ?? '') ?>">
                  <label for="dias_excluir_logs">Dias Excluir Logs</label>
                </div>
              </div>



            </div>
          </div>

          <hr class="my-4">

          <div class="mb-0">
            <div class="small text-muted fw-semibold mb-2">Configuração SMTP</div>

            <div class="row g-3">
              <div class="col-12 col-md-5">
                <div class="form-floating">
                  <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                         placeholder="smtp.seudominio.com" maxlength="120"
                         value="<?= htmlspecialchars($cfg['smtp_host'] ?? '') ?>">
                  <label for="smtp_host">Servidor SMTP (Host)</label>
                </div>
              </div>

              <div class="col-12 col-md-2">
                <div class="form-floating">
                  <input type="number" class="form-control" id="smtp_porta" name="smtp_porta"
                         placeholder="587" min="1" max="65535"
                         value="<?= htmlspecialchars($cfg['smtp_porta'] ?? '') ?>">
                  <label for="smtp_porta">Porta SMTP</label>
                </div>
              </div>

              <div class="col-12 col-md-3">
                <div class="form-floating">
                  <input type="password" class="form-control" id="smtp_senha" name="smtp_senha"
                         placeholder="Senha SMTP" maxlength="255" value="" autocomplete="new-password">
                  <label for="smtp_senha">Senha SMTP</label>
                </div>
              </div>

              <div class="col-12 col-md-2">
                <div class="form-floating">
                  <?php $seg = $cfg['smtp_seguranca'] ?? ''; ?>
                  <select class="form-select" id="smtp_seguranca" name="smtp_seguranca">
                    <option value=""    <?= ($seg === '' ? 'selected' : '') ?>>Nenhuma</option>
                    <option value="tls" <?= ($seg === 'tls' ? 'selected' : '') ?>>TLS</option>
                    <option value="ssl" <?= ($seg === 'ssl' ? 'selected' : '') ?>>SSL</option>
                  </select>
                  <label for="smtp_seguranca">Segurança</label>
                </div>
              </div>
            </div>

            
          </div>




<!-- Apis -->
          <hr class="my-4">

<div class="mb-0">
 
<div class="d-flex align-items-center justify-content-between mb-2">
  <div class="small text-muted fw-semibold">Apis do Sistema</div>

  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-success btn-sm" id="btnTesteWhatsapp">
      <i class="bi bi-whatsapp me-1"></i> Testar WhatsApp
    </button>

    <button type="button" class="btn btn-outline-primary btn-sm" id="btnTesteIA">
      <i class="bi bi-stars me-1"></i> Testar IA
    </button>
  </div>
</div>


  <div class="row g-3">

  <div class="col-12 col-md-2">
  <div class="form-floating">
    <select class="form-select" id="api_whatsapp" name="api_whatsapp">
      <option value="Nenhuma"  <?= ($api_whatsapp === 'Nenhuma' ? 'selected' : '') ?>>Nenhuma</option>
      <option value="evolution" <?= ($api_whatsapp === 'evolution' ? 'selected' : '') ?>>Evolution</option>
      <option value="meta"     <?= ($api_whatsapp === 'meta' ? 'selected' : '') ?>>Meta (Api Oficial)</option>
      <option value="menuia"   <?= ($api_whatsapp === 'menuia' ? 'selected' : '') ?>>Menuia</option>
    </select>
    <label for="api_whatsapp">Api Whatsapp</label>
  </div>
</div>


              <div class="col-12 col-md-5">
                <div class="form-floating">
                  <input type="text" class="form-control" id="instancia_whatsapp" name="instancia_whatsapp"
                         placeholder="Instancia / Authckey / Id Number Phone" 
                         value="<?= htmlspecialchars($cfg['instancia_whatsapp'] ?? '') ?>">
                  <label for="smtp_host">Authkey / Instancia / Id Number Phone</label>
                </div>
              </div>


              <div class="col-12 col-md-5">
                <div class="form-floating">
                  <input type="text" class="form-control" id="token_whatsapp" name="token_whatsapp"
                         placeholder="Token / Appkey / Token Api Meta" maxlength="255"
                         value="<?= htmlspecialchars($cfg['token_whatsapp'] ?? '') ?>">
                  <label for="smtp_host">Appkey / Token / Token Meta</label>
                </div>
              </div>




              <!-- Linha extra (só aparece na Evolution) -->
<div class="col-12 mt-2" id="row_url_evolution" style="<?= ($api_whatsapp === 'evolution' ? '' : 'display:none;') ?>">
  <div class="form-floating">
    <input type="url"
           class="form-control"
           id="url_evolution"
           name="url_api"
           placeholder="https://evolution.seudominio.com"
           value="<?= htmlspecialchars($url_api ?? '', ENT_QUOTES) ?>">
    <label for="url_evolution">URL da Evolution Ex: https://evolution.seudominio.com (sem / no final)</label>
   
  </div>
</div>

           
            
 </div>


 <div class="row g-3 mt-1">

 <div class="col-12 col-md-2">
  <div class="form-floating">
    <select class="form-select" id="api_ia" name="api_ia">
      <option value="Nenhuma"  <?= ($api_ia === 'Nenhuma' ? 'selected' : '') ?>>Nenhuma</option>
      <option value="chatgpt" <?= ($api_ia === 'chatgpt' ? 'selected' : '') ?>>ChatGPT</option>
      <option value="gemini"     <?= ($api_ia === 'gemini' ? 'selected' : '') ?>>Gemini</option>
     
    </select>
    <label for="api_whatsapp">Api IA</label>
  </div>
</div>


<div class="col-12 col-md-8">
                <div class="form-floating">
                  <input type="text" class="form-control" id="token_ia" name="token_ia"
                         placeholder="Token Chatgpt ou Gemini" maxlength="255"
                         value="<?= htmlspecialchars($cfg['token_ia'] ?? '') ?>">
                  <label for="smtp_host">Token ChatGPT ou Gemini</label>
                </div>
              </div>


</div>



   </div>


         

          <!-- Imagens no final -->
<hr class="my-4">

<div class="mb-0">
  <div class="small text-muted fw-semibold mb-2">Imagens do Sistema</div>

  <?php
    $fallback_img = "../uploads/sem_foto.png";

    $logo_db  = trim($cfg['logo']  ?? '');
    $icone_db = trim($cfg['icone'] ?? '');

    $logo_path  = "../uploads/" . $logo_db;
    $icone_path = "../uploads/" . $icone_db;

    $logo_url  = (!empty($logo_db)  && file_exists($logo_path))  ? "../uploads/" . rawurlencode($logo_db)  : $fallback_img;
    $icone_url = (!empty($icone_db) && file_exists($icone_path)) ? "../uploads/" . rawurlencode($icone_db) : $fallback_img;
  ?>

  <div class="row g-3">
    <!-- LOGO -->
    <div class="col-12 col-md-6">
      <label class="form-label small fw-semibold">Logo</label>

      <div class="d-flex align-items-center gap-3">
        <div class="border rounded-3 p-2 bg-white" style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;">
          <img id="preview_logo"
               src="<?= $logo_url ?>"
               alt="Logo"
               style="max-width:100%;max-height:100%;object-fit:contain;">
        </div>

        <div class="flex-grow-1">
          <input class="form-control" type="file" name="logo" id="cfg_logo" accept="image/*">
          <input type="hidden" name="logo_atual" value="<?= htmlspecialchars($logo_db) ?>">
        </div>
      </div>
    </div>

    <!-- ÍCONE -->
    <div class="col-12 col-md-6">
      <label class="form-label small fw-semibold">Ícone</label>

      <div class="d-flex align-items-center gap-3">
        <div class="border rounded-3 p-2 bg-white" style="width:72px;height:72px;display:flex;align-items:center;justify-content:center;">
          <img id="preview_icone"
               src="<?= $icone_url ?>"
               alt="Ícone"
               style="max-width:100%;max-height:100%;object-fit:contain;">
        </div>

        <div class="flex-grow-1">
          <input class="form-control" type="file" name="icone" id="cfg_icone" accept="image/*">
          <input type="hidden" name="icone_atual" value="<?= htmlspecialchars($icone_db) ?>">
        </div>
      </div>
    </div>
  </div>
</div>


<input type="hidden" name="logo_atual" value="<?= htmlspecialchars($cfg['logo'] ?? '') ?>">
<input type="hidden" name="icone_atual" value="<?= htmlspecialchars($cfg['icone'] ?? '') ?>">




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







<!-- MODAL PERFIL -->
<div class="modal fade" id="modalPerfil" tabindex="-1" aria-labelledby="modalPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalPerfilLabel">
          <i class="bi bi-person-circle me-2"></i>Editar Perfil
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <form id="formPerfil" method="post" enctype="multipart/form-data" autocomplete="off">
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= (int)$usuario_id ?>">
          <input type="hidden" name="empresa" value="<?= (int)$usuario_empresa ?>">
          <input type="hidden" name="nivel" value="<?= htmlspecialchars($usuario_nivel) ?>">

          <?php
            // preview da foto atual (usa sem_foto.webp como padrão)
            $fotoAtual = $perfil['foto'] ?? 'sem_foto.webp';
            if (!$fotoAtual) $fotoAtual = 'sem_foto.webp';
            $fotoPreviewUrl = "../uploads/perfil/" . rawurlencode($fotoAtual);
          ?>

          <div class="row g-3">

            <!-- LINHA 1 -->
            <div class="col-12 col-md-6">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_nome" name="nome"
                       placeholder="Nome" maxlength="100"
                       value="<?= htmlspecialchars($perfil['nome'] ?? '') ?>">
                <label for="perfil_nome">Nome</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_telefone" name="telefone"
                       placeholder="Telefone" maxlength="20"
                       data-mask="tel"
                       value="<?= htmlspecialchars($perfil['telefone'] ?? '') ?>">
                <label for="perfil_telefone">Telefone</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_cpf" name="cpf"
                       placeholder="CPF" maxlength="14"
                       data-mask="cpf"
                       value="<?= htmlspecialchars($perfil['cpf'] ?? '') ?>">
                <label for="perfil_cpf">CPF</label>
              </div>
            </div>

            <!-- LINHA 2 -->
            <div class="col-12 col-md-4">
              <div class="form-floating">
                <input type="email" class="form-control" id="perfil_email" name="email"
                       placeholder="E-mail" maxlength="100"
                       value="<?= htmlspecialchars($perfil['email'] ?? '') ?>">
                <label for="perfil_email">E-mail</label>
              </div>
            </div>

            <!-- senha + foto lado a lado -->
            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="password" class="form-control" id="perfil_senha" name="senha"
                       placeholder="Nova senha" maxlength="255" value="">
                <label for="perfil_senha">Nova Senha</label>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold mb-1">Foto do Perfil</label>

              <div class="d-flex align-items-center gap-2">
                <!-- miniatura -->
                <img
                  src="<?= $fotoPreviewUrl ?>"
                  alt="Foto atual"
                  id="perfil_foto_preview"
                  style="width:44px;height:44px;object-fit:cover;border-radius:12px;border:1px solid #cbd5e1;"
                >

                <input class="form-control" type="file" name="foto" id="perfil_foto" accept="image/*">
              </div>

              <div class="form-text">JPG, PNG ou WEBP (até 2MB).</div>
            </div>

            <!-- ENDEREÇO (mais compacto / mais campos por linha) -->

            <!-- LINHA 3 -->
            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_cep" name="cep"
                       placeholder="CEP" maxlength="10"
                       data-mask="cep"
                       data-cep
                       value="<?= htmlspecialchars($perfil['cep'] ?? '') ?>">
                <label for="perfil_cep">CEP</label>
              </div>
            </div>

            <div class="col-12 col-md-8">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_endereco" name="endereco"
                       placeholder="Endereço" maxlength="200"
                       data-cep-endereco
                       value="<?= htmlspecialchars($perfil['endereco'] ?? '') ?>">
                <label for="perfil_endereco">Endereço</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_numero" name="numero"
                       placeholder="Nº" maxlength="10"
                       value="<?= htmlspecialchars($perfil['numero'] ?? '') ?>">
                <label for="perfil_numero">Nº</label>
              </div>
            </div>

            <!-- LINHA 4 -->
            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_bairro" name="bairro"
                       placeholder="Bairro" maxlength="100"
                       data-cep-bairro
                       value="<?= htmlspecialchars($perfil['bairro'] ?? '') ?>">
                <label for="perfil_bairro">Bairro</label>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_cidade" name="cidade"
                       placeholder="Cidade" maxlength="100"
                       data-cep-cidade
                       value="<?= htmlspecialchars($perfil['cidade'] ?? '') ?>">
                <label for="perfil_cidade">Cidade</label>
              </div>
            </div>

            <div class="col-12 col-md-2">
              <div class="form-floating">
                <?php $uf_sel = strtoupper($perfil['estado'] ?? ''); ?>
                <select class="form-select" id="perfil_estado" name="estado" data-cep-estado>
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
                <label for="perfil_estado">UF</label>
              </div>
            </div>

            <div class="col-12 col-md-3">
              <div class="form-floating">
                <input type="text" class="form-control" id="perfil_complemento" name="complemento"
                       placeholder="Compl." maxlength="100"
                       data-cep-complemento
                       value="<?= htmlspecialchars($perfil['complemento'] ?? '') ?>">
                <label for="perfil_complemento">Compl.</label>
              </div>
            </div>




            <?php
  $assinaturaAtual = trim((string)($perfil['assinatura'] ?? ''));
  $assinaturaUrlPerfil = $assinaturaAtual ? "../uploads/assinaturas/" . rawurlencode($assinaturaAtual) : "";
?>
<div class="col-12">
  <div class="border rounded-4 p-3 d-flex align-items-center justify-content-between">
    <div>
      <div class="fw-semibold">Assinatura</div>
      <div class="small text-muted">
        <?= $assinaturaAtual ? "Assinatura cadastrada" : "Nenhuma assinatura cadastrada" ?>
      </div>

      <?php if ($assinaturaUrlPerfil) { ?>
        <div class="mt-2">
          <img src="<?= $assinaturaUrlPerfil ?>"
               alt="Assinatura"
               style="max-width:220px;height:auto;border-radius:12px;border:1px solid #e5e7eb;background:#fff;">
        </div>
      <?php } ?>
    </div>

    <button type="button"
            class="btn <?= $assinaturaAtual ? 'btn-outline-warning' : 'btn-outline-success' ?>"
            onclick="abrirAssinaturaPerfil()">
      <i class="bi bi-pen me-2"></i><?= $assinaturaAtual ? 'Editar assinatura' : 'Fazer assinatura' ?>
    </button>
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
// sincroniza os inputs hex com os color pickers e mantém o texto atualizado
(function(){
  const c1 = document.getElementById('cor_primaria');
  const t1 = document.getElementById('cor_primaria_txt');
  const c2 = document.getElementById('cor_secundaria');
  const t2 = document.getElementById('cor_secundaria_txt');

  if(c1 && t1){
    t1.value = c1.value;
    c1.addEventListener('input', ()=> t1.value = c1.value);
  }
  if(c2 && t2){
    t2.value = c2.value;
    c2.addEventListener('input', ()=> t2.value = c2.value);
  }
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
    // CONFIGURAÇÕES
    // ==========================
    const formConfig = document.getElementById("formConfig");
    if (formConfig) {
      formConfig.addEventListener("submit", async (e) => {
        e.preventDefault();

        Mensagens.carregando("Salvando...", "Aguarde");

        try {
          const data = await postForm("scripts/salvar_config.php", formConfig);

          Mensagens.fechar();
          Mensagens.salvoSemReload(data.msg || "Configurações salvas!");

          hideModal("modalConfig");

          // pequeno delay só para UX
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
(function(){
  function previewFile(input, imgId, fallbackSrc){
    const img = document.getElementById(imgId);
    if(!input || !img) return;

    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      if(!file){
        img.src = fallbackSrc;
        return;
      }
      const url = URL.createObjectURL(file);
      img.src = url;
      img.onload = () => URL.revokeObjectURL(url);
    });
  }

  document.addEventListener('DOMContentLoaded', () => {
    const fallback = "../uploads/sem_foto.png";
    previewFile(document.getElementById('cfg_logo'),  'preview_logo',  fallback);
    previewFile(document.getElementById('cfg_icone'), 'preview_icone', fallback);
  });
})();
</script>



<script>
  function toggleEvolutionUrl(){
    const api = document.getElementById('api_whatsapp').value;
    const row = document.getElementById('row_url_evolution');
    const inp = document.getElementById('url_evolution');

    const show = (api === 'evolution');
    row.style.display = show ? '' : 'none';

    // opcional: obrigar URL só quando for evolution
    if (inp) inp.required = show;
  }

  document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('api_whatsapp');
    sel.addEventListener('change', toggleEvolutionUrl);
    toggleEvolutionUrl(); // aplica no carregamento
  });
</script>




<script>
(function () {
  const btnW = document.getElementById("btnTesteWhatsapp");
  const btnI = document.getElementById("btnTesteIA");

  // ✅ Ajuste aqui para os seus arquivos reais:
  const URL_TESTE_WHATS = "apis/teste_whatsapp.php";
  const URL_TESTE_IA    = "apis/teste_ia.php";

  // opcional: container dentro da modal (se você criou)
  const box = document.getElementById("resultadoTesteApis");

  async function chamarTesteComForm(url, titulo, formSelector) {
  Swal.fire({
    title: titulo,
    html: "Executando teste, aguarde...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  try {
    const form = document.querySelector(formSelector);
    const fd = new FormData(form);

    // opcional: flag pra o PHP saber que é teste via modal
    fd.set("modo_teste_modal", "1");

    const resp = await fetch(url, {
      method: "POST",
      body: fd,
      cache: "no-store"
    });

    const html = await resp.text();

    Swal.fire({
      title: titulo,
      html: `<div style="text-align:left">${html}</div>`,
      width: 900,
      confirmButtonText: "Fechar"
    });

  } catch (e) {
    Swal.fire({ icon: "error", title: "Erro ao testar", text: e.message || "Falha." });
  }
}

// Exemplo de uso:
document.getElementById("btnTesteWhatsapp")?.addEventListener("click", () => {
  chamarTesteComForm("apis/teste_whatsapp.php", "Teste WhatsApp", "#formConfig");
});
document.getElementById("btnTesteIA")?.addEventListener("click", () => {
  chamarTesteComForm("apis/teste_ia.php", "Teste IA", "#formConfig");
});


  if (btnW) {
    btnW.addEventListener("click", () => {
      chamarTeste(URL_TESTE_WHATS, "Teste WhatsApp");
    });
  }

  if (btnI) {
    btnI.addEventListener("click", () => {
      chamarTeste(URL_TESTE_IA, "Teste IA");
    });
  }
})();
</script>



<!-- MODAL ASSINATURA (PERFIL) -->
<div class="modal fade" id="modalAssinatura" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-pen me-2"></i>Minha Assinatura
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="ass_usuario_id" value="0">
        <div id="ass_conteudo" class="text-center text-muted small py-4">Carregando...</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-cancelar-sistema" data-bs-dismiss="modal">Cancelar</button>

        <button type="button" class="btn btn-outline-secondary" id="btnLimparAssinatura" disabled>
          <i class="bi bi-eraser me-2"></i>Limpar
        </button>

        <button type="button" class="btn btn-salvar-sistema" id="btnSalvarAssinatura" disabled>
          <i class="bi bi-save me-2"></i>Salvar Assinatura
        </button>
      </div>

    </div>
  </div>
</div>



<script>
window.USUARIO_ID_LOGADO = <?= (int)$usuario_id ?>;

window.abrirAssinaturaPerfil = async function () {
  const id = window.USUARIO_ID_LOGADO;
  if (!id) return;

  // fecha modal perfil para não ficar 2 modais abertas (opcional mas recomendado)
  const modalPerfilEl = document.getElementById("modalPerfil");
  bootstrap.Modal.getInstance(modalPerfilEl)?.hide();

  // abre assinatura
  if (typeof window.assinar === "function") {
    // reaproveita a função que você já tem (assinar(id))
    window.assinar(id);
    return;
  }

  // fallback se você ainda não colou a função assinar aqui
  alert("Função assinar(id) não encontrada. Cole o JS da assinatura no topbar.");
};
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
const URL_ASS_FORM   = "ajax/usuarios/assinatura_form.php";
const URL_ASS_SALVAR = "ajax/usuarios/assinatura_salvar.php";

let _assPad = null;

window.assinar = async function (id) {
  if (!id) return;

  const Msg = getMsg();
  try {
    if (Msg && Msg.carregando) Msg.carregando("Carregando...", "Abrindo assinatura");

    document.getElementById("ass_usuario_id").value = id;
    document.getElementById("ass_conteudo").innerHTML =
      `<div class="text-center text-muted small py-4">Carregando...</div>`;

    const html = await fetch(`${URL_ASS_FORM}?id=${encodeURIComponent(id)}`, { cache: "no-store" })
      .then(r => r.text());

    document.getElementById("ass_conteudo").innerHTML = html;

    // abre modal (bootstrap puro, sem Crud)
    const modalEl = document.getElementById("modalAssinatura");
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // inicializa canvas quando a modal estiver visível
    const onShown = () => {
      modalEl.removeEventListener("shown.bs.modal", onShown);
      initAssinaturaPad();
    };
    modalEl.addEventListener("shown.bs.modal", onShown);

    // habilita botões
    document.getElementById("btnSalvarAssinatura").disabled = false;
    document.getElementById("btnLimparAssinatura").disabled = false;

    if (Msg && Msg.fechar) Msg.fechar();

  } catch (e) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro", e.message || "Não foi possível abrir assinatura.");
    console.error(e);
  }
};

function initAssinaturaPad() {
  const canvas = document.getElementById("canvasAssinatura");
  if (!canvas) return;

  // evita scroll no touch e permite desenhar
  canvas.style.touchAction = "none";

  if (_assPad && _assPad._cleanup) _assPad._cleanup();

  const rect = canvas.getBoundingClientRect();
  const dpr = window.devicePixelRatio || 1;

  const w = Math.max(1, Math.round(rect.width));
  const h = Math.max(1, Math.round(rect.height));

  canvas.width  = Math.round(w * dpr);
  canvas.height = Math.round(h * dpr);

  const ctx = canvas.getContext("2d");

  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.clearRect(0, 0, w, h);

  ctx.lineWidth = 2.6;
  ctx.strokeStyle = "#111";
  ctx.lineCap = "round";
  ctx.lineJoin = "round";

  let drawing = false;
  let lastX = 0, lastY = 0;

  const pos = (ev) => {
    const r = canvas.getBoundingClientRect();
    return { x: ev.clientX - r.left, y: ev.clientY - r.top };
  };

  const onDown = (ev) => {
    drawing = true;
    const p = pos(ev);
    lastX = p.x; lastY = p.y;
    canvas.setPointerCapture(ev.pointerId);
    _assPad.hasInk = false;
  };

  const onMove = (ev) => {
    if (!drawing) return;
    const p = pos(ev);

    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();

    lastX = p.x; lastY = p.y;
    _assPad.hasInk = true;
  };

  const onUp = () => { drawing = false; };

  canvas.addEventListener("pointerdown", onDown);
  canvas.addEventListener("pointermove", onMove);
  canvas.addEventListener("pointerup", onUp);
  canvas.addEventListener("pointercancel", onUp);

  _assPad = {
    canvas,
    ctx,
    hasInk: false,
    _cleanup: () => {
      canvas.removeEventListener("pointerdown", onDown);
      canvas.removeEventListener("pointermove", onMove);
      canvas.removeEventListener("pointerup", onUp);
      canvas.removeEventListener("pointercancel", onUp);
    }
  };
}

// LIMPAR
document.addEventListener("click", function (e) {
  const btn = e.target.closest("#btnLimparAssinatura");
  if (!btn) return;
  if (!_assPad || !_assPad.canvas) return;

  const canvas = _assPad.canvas;
  const rect = canvas.getBoundingClientRect();
  _assPad.ctx.clearRect(0, 0, rect.width, rect.height);
  _assPad.hasInk = false;
});

// SALVAR
document.addEventListener("click", async function (e) {
  const btn = e.target.closest("#btnSalvarAssinatura");
  if (!btn) return;

  const Msg = getMsg();
  try {
    const id = parseInt(document.getElementById("ass_usuario_id").value || "0", 10);
    if (!id) return;

    if (!_assPad || !_assPad.canvas) throw new Error("Canvas não encontrado.");
    if (!_assPad.hasInk) throw new Error("Faça a assinatura antes de salvar.");

    const dataUrl = _assPad.canvas.toDataURL("image/png");

    if (Msg && Msg.carregando) Msg.carregando("Salvando...", "Aguarde");

    const fd = new FormData();
    fd.append("id", String(id));
    fd.append("assinatura", dataUrl);

    const resp = await fetch(URL_ASS_SALVAR, { method: "POST", body: fd });
    const ct = resp.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      const txt = await resp.text().catch(() => "");
      throw new Error("Resposta inesperada do servidor:\n" + (txt ? txt.slice(0, 400) : ""));
    }

    const json = await resp.json();
    if (!json.ok) throw new Error(json.msg || "Falha ao salvar.");

    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.salvoSemReload) Msg.salvoSemReload(json.msg || "Assinatura salva!");

    // fecha modal
    const modalEl = document.getElementById("modalAssinatura");
    bootstrap.Modal.getInstance(modalEl)?.hide();

    // atualiza a página (como é topbar, é mais simples)
    setTimeout(() => location.reload(), 800);

  } catch (err) {
    if (Msg && Msg.fechar) Msg.fechar();
    if (Msg && Msg.erro) Msg.erro("Erro", err.message || "Falha ao salvar assinatura.");
    console.error(err);
  }
});
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
                  ${item.cliente_nome || 'Cliente'}
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

<script>
  const BASE_PUBLIC = "<?= $url_sistema ?>";
</script>

<script>
(function () {
  const inp = document.getElementById('topbarBuscaProtocolo');
  if (!inp) return;

  

  function normalizaProtocolo(v) {
    // mantém letras/números/_/-
    return (v || '')
      .trim()
      .replace(/\s+/g, '')              // remove espaços
      .replace(/[^a-zA-Z0-9_-]/g, '');  // remove caracteres estranhos
  }

  function irParaChamado() {
  const prot = normalizaProtocolo(inp.value);
  if (!prot) return;

  const url = BASE_PUBLIC + "/chamado/" + encodeURIComponent(prot);

  window.open(url, '_blank', 'noopener,noreferrer');
}

  // Enter para buscar
  inp.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      irParaChamado();
    }
  });

  // opcional: clicar no ícone de pesquisa também buscar
  const icon = inp.closest('.input-group')?.querySelector('.input-group-text');
  if (icon) icon.style.cursor = 'pointer';
  if (icon) icon.addEventListener('click', irParaChamado);
})();
</script>
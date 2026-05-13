<?php
session_start();
require_once 'conexao.php';

// Token CSRF para o formulário de login
if (empty($_SESSION['csrf_token_login_cliente'])) {
  $_SESSION['csrf_token_login_cliente'] = bin2hex(random_bytes(32));
}

// Ícone do sistema (config ou fallback)
$icone_sistema = $config['icone'] ?? '';
$icone_path = __DIR__ . '/uploads/' . $icone_sistema;

if ($icone_sistema === '' || !file_exists($icone_path)) {
  $icone_sistema = 'sem_foto.webp';
}
$icone_url = 'uploads/' . rawurlencode($icone_sistema);

// Logo do sistema (config ou fallback)
$logo_sistema = $config['logo'] ?? '';
$logo_path = __DIR__ . '/uploads/' . $logo_sistema;

if ($logo_sistema === '' || !file_exists($logo_path)) {
  $logo_sistema = 'sem_foto.webp';
}
$logo_url = 'uploads/' . rawurlencode($logo_sistema);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acesso do Cliente - <?= htmlspecialchars($nome_sistema) ?></title>

  <link rel="icon" type="image/webp" href="<?= htmlspecialchars($icone_url) ?>">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($icone_url) ?>">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Cores do sistema -->
  <style>
    :root { --cor-primaria: <?= $cor_primaria ?>; --cor-secundaria: <?= $cor_secundaria ?>; }
    body{
      min-height:100vh;
      background: linear-gradient(135deg, rgba(102,126,234,.12), rgba(118,75,162,.10));
    }
    .card-login{
      border:0;
      border-radius: 1.25rem;
      box-shadow: 0 10px 30px rgba(0,0,0,.08);
      overflow:hidden;
    }
    .login-side{
      background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
      color:#fff;
    }
    .btn-login{
      background: var(--cor-primaria);
      border:0;
      color:#fff;
      padding:.85rem 1rem;
      border-radius: .9rem;
      font-weight: 600;
    }
    .btn-login:hover{ filter: brightness(.95); }
    .hint{
      font-size:.85rem;
      color:#6c757d;
    }
  </style>
</head>

<body>

<div class="container py-5">
  <div class="row justify-content-center align-items-center g-4">
    <div class="col-12 col-lg-9 col-xl-8">
      <div class="card card-login">
        <div class="row g-0">

          <!-- Lado info -->
          <div class="col-lg-5 d-none d-lg-block login-side p-4">
            <div class="d-flex flex-column h-100">
              <div class="mb-4">
                <img src="<?= htmlspecialchars($logo_url) ?>"
                     alt="Logo do sistema"
                     style="max-height:55px; max-width:210px;">
              </div>

              <h4 class="fw-bold mb-2">Área do Cliente</h4>
              <p class="mb-4 opacity-90">
                Acompanhe seus chamados, envie mensagens e anexe arquivos em um só lugar.
              </p>

              <ul class="list-unstyled small mb-0">
                <li class="mb-2"><i class="bi bi-ticket-perforated me-2"></i>Abrir chamados</li>
                <li class="mb-2"><i class="bi bi-clock-history me-2"></i>Ver andamento</li>
                <li class="mb-2"><i class="bi bi-chat-dots me-2"></i>Conversar com suporte</li>
                <li class="mb-2"><i class="bi bi-paperclip me-2"></i>Anexar arquivos</li>
              </ul>

              <div class="mt-auto pt-4 small opacity-75">
                <?= htmlspecialchars($nome_sistema) ?>
              </div>
            </div>
          </div>

          <!-- Form -->
          <div class="col-12 col-lg-7 p-4 p-md-5">
            <div class="text-center d-lg-none mb-3">
              <img src="<?= htmlspecialchars($logo_url) ?>"
                   alt="Logo do sistema"
                   style="max-height:55px; max-width:210px;">
            </div>

            <div class="mb-4">
              <h4 class="fw-bold mb-1">Entrar</h4>
              <div class="text-muted">Use seu <b>CPF</b> ou <b>Telefone</b> para acessar.</div>
            </div>

            <form class="login-form" action="<?= $url_sistema ?>autenticar_cliente.php" method="POST" autocomplete="off">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token_login_cliente']) ?>">

              <div class="mb-3">
                <label for="usuario" class="form-label fw-semibold">CPF ou Telefone</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="usuario"
                    name="usuario"
                    placeholder="Digite seu CPF ou telefone"
                    required
                    inputmode="numeric"
                    autocomplete="username"
                  >
                </div>
                <div class="hint mt-2">
                  CPF: 11 dígitos • Telefone: DDD + número
                </div>
              </div>

              <div class="mb-3">
                <label for="senha" class="form-label fw-semibold">Senha</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                  <input
                    type="password"
                    class="form-control"
                    id="senha"
                    name="senha"
                    placeholder="Digite sua senha"
                    required
                    autocomplete="current-password"
                  >
                </div>
              </div>

              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="lembrar" id="lembrar">
                  <label class="form-check-label" for="lembrar">Lembrar-me</label>
                </div>

                <a href="#" class="text-decoration-none small" data-bs-toggle="modal" data-bs-target="#modalRecuperarSenhaCliente">
                  <i class="bi bi-question-circle"></i> Esqueci minha senha
                </a>
              </div>

              <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
              </button>

              <div class="text-center mt-3 small text-muted">
                Se você não tem acesso, solicite ao suporte.
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>



</body>
</html>






<!-- Modal recuperar senha (cliente) - versão melhorada -->
<div class="modal fade" id="modalRecuperarSenhaCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

      <!-- HEADER COM FUNDO -->
      <div class="modal-header border-0 text-white"
           style="background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));">

        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25"
               style="width:48px;height:48px;">
            <i class="bi bi-shield-lock fs-4"></i>
          </div>

          <div>
            <h5 class="modal-title fw-bold mb-0">Recuperar acesso</h5>
            <small class="opacity-75">
              Vamos localizar sua conta e enviar as instruções
            </small>
          </div>
        </div>

        <button type="button" class="btn-close btn-close-white"
                data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <!-- BODY -->
      <div class="modal-body p-4">

        <form id="formRecuperarSenhaCliente" method="post" action="#" autocomplete="off">

          <!-- Campo -->
          <div class="mb-4">
            <label for="cpf_ou_telefone_recuperar"
                   class="form-label fw-semibold text-secondary">
              CPF ou Telefone
            </label>

            <div class="input-group input-group-lg">
              <span class="input-group-text bg-light border-0">
                <i class="bi bi-person-vcard text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-0 shadow-sm"
                id="cpf_ou_telefone_recuperar"
                name="cpf_ou_telefone"
                placeholder="Digite seu CPF ou telefone"
                required
                inputmode="numeric"
                autocomplete="off"
              >
            </div>

            <div class="form-text mt-2">
              CPF: 11 dígitos • Telefone: DDD + número
            </div>
          </div>

          <!-- Bloco informativo (mais elegante) -->
          <div class="rounded-3 p-3 mb-4 d-none"
               id="recuperarInfo"
               style="background: rgba(102,126,234,.08);">

            <div class="d-flex gap-3">
              <i class="bi bi-info-circle fs-5 text-primary mt-1"></i>
              <div>
                <div class="fw-semibold">Enviaremos para:</div>
                <div class="small text-muted">
                  <span id="recEmail">E-mail cadastrado</span><br>
                  <span id="recFone">Telefone cadastrado</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Botão principal -->
          <button type="submit"
                  class="btn w-100 py-2 fw-semibold text-white"
                  style="background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria)); border:0;">

            <i class="bi bi-search me-2"></i>
            Buscar cadastro e enviar instruções
          </button>

        </form>

      </div>

      <!-- FOOTER -->
      <div class="modal-footer border-0 bg-light">
        <button type="button"
                class="btn btn-outline-secondary rounded-pill px-4"
                data-bs-dismiss="modal">
          Fechar
        </button>
      </div>

    </div>
  </div>
</div>




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Mensagens (carregar 1x só!) -->
<script src="js/mensagens.js"></script>

<!-- Flash do PHP -> JS (1x só!) -->
<script>
  window.LOGIN_FLASH = <?php
    echo json_encode($_SESSION['flash'] ?? null);
    unset($_SESSION['flash']);
  ?>;
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // dispara mensagens do login
  if (typeof Mensagens !== "undefined" && Mensagens.exibirRetornoLogin) {
    Mensagens.exibirRetornoLogin();
  }

  const chk = document.getElementById('lembrar');
  const inputUser = document.getElementById('usuario');
  const inputSenha = document.getElementById('senha');

  // Carrega dados salvos
  const saved = localStorage.getItem('login_cliente_dados');
  if (saved) {
    try {
      const obj = JSON.parse(saved);
      if (obj.usuario) inputUser.value = obj.usuario;
      if (obj.senha) inputSenha.value = obj.senha;
      chk.checked = true;
    } catch (e) {}
  }

  // Salva ou remove ao enviar
  const form = document.querySelector('form.login-form');
  if (form) {
    form.addEventListener('submit', function () {
      if (chk && chk.checked) {
        localStorage.setItem('login_cliente_dados', JSON.stringify({
          usuario: (inputUser?.value || '').trim(),
          senha: inputSenha?.value || ''
        }));
      } else {
        localStorage.removeItem('login_cliente_dados');
      }
    });
  }

  // Máscara simples
  function onlyDigits(v){ return (v || '').replace(/\D+/g, ''); }

  function maskCPF(v){
    v = onlyDigits(v).slice(0, 11);
    v = v.replace(/^(\d{3})(\d)/, '$1.$2');
    v = v.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1-$2');
    return v;
  }

  function maskFone(v){
    v = onlyDigits(v).slice(0, 11);
    if (v.length <= 10) {
      v = v.replace(/^(\d{2})(\d)/, '($1) $2');
      v = v.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
      v = v.replace(/^(\d{2})(\d)/, '($1) $2');
      v = v.replace(/(\d{5})(\d)/, '$1-$2');
    }
    return v;
  }

  if (inputUser) {
    inputUser.addEventListener('input', function () {
      const digits = onlyDigits(this.value);
      if (digits.length === 11) this.value = maskCPF(digits);
      else this.value = maskFone(digits);
    });
  }
});
</script>







<script>
(function () {
  function onlyDigits(v){ return (v || '').replace(/\D+/g, ''); }

  function maskCPF(v){
    v = onlyDigits(v).slice(0, 11);
    v = v.replace(/^(\d{3})(\d)/, '$1.$2');
    v = v.replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1-$2');
    return v;
  }

  function maskFone(v){
    v = onlyDigits(v).slice(0, 11);
    if (v.length <= 10) {
      v = v.replace(/^(\d{2})(\d)/, '($1) $2');
      v = v.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
      v = v.replace(/^(\d{2})(\d)/, '($1) $2');
      v = v.replace(/(\d{5})(\d)/, '$1-$2');
    }
    return v;
  }

  function applyMaskCPFOrPhone(inputEl){
    if (!inputEl) return;
    inputEl.addEventListener('input', function () {
      const d = onlyDigits(this.value);
      // 11 dígitos: CPF por padrão; abaixo: telefone (fica mais natural no começo)
      this.value = (d.length === 11) ? maskCPF(d) : maskFone(d);
    });
  }

  function showMsg(tipo, titulo, texto){
    // tenta usar seu Mensagens.js
    try {
      if (window.Mensagens) {
        if (tipo === 'carregando' && Mensagens.carregando) return Mensagens.carregando(titulo, texto);
        if (tipo === 'sucesso' && Mensagens.sucesso) return Mensagens.sucesso(titulo, texto);
        if (tipo === 'erro' && Mensagens.erro) return Mensagens.erro(titulo, texto);
        if (tipo === 'aviso' && Mensagens.aviso) return Mensagens.aviso(titulo, texto);
        if (tipo === 'fechar' && Mensagens.fechar) return Mensagens.fechar();
      }
    } catch (e) {}

    // fallback
    if (tipo === 'fechar') return;
    alert((titulo ? titulo + "\n" : "") + (texto || ""));
  }

  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalRecuperarSenhaCliente');
    const inputModal = document.getElementById('cpf_ou_telefone_recuperar');
    const form = document.getElementById('formRecuperarSenhaCliente');

    // máscara no input da modal
    applyMaskCPFOrPhone(inputModal);

    // ao abrir a modal, preenche com o valor do login (se existir)
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', function () {
        const loginUser = document.getElementById('usuario')?.value || '';
        if (inputModal) {
          // se já tiver algo digitado, não sobrescreve
          if (!inputModal.value.trim() && loginUser.trim()) {
            inputModal.value = loginUser.trim();
            // dispara máscara uma vez
            const d = onlyDigits(inputModal.value);
            inputModal.value = (d.length === 11) ? maskCPF(d) : maskFone(d);
          }
          inputModal.focus();
          inputModal.select();
        }
      });
    }

    if (form) {
  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const valor = (inputModal?.value || '').trim();
    const dig = onlyDigits(valor);

    if (!dig || dig.length < 10) {
      return Mensagens.aviso(
        'Atenção',
        'Informe um CPF ou telefone válido.'
      );
    }

    Mensagens.carregando('Enviando...', 'Aguarde');

    try {

      const resp = await fetch('scripts/recuperar_senha_cliente_enviar.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          cpf_telefone: valor
        })
      });

      const data = await resp.json();

      Mensagens.fechar();

      if (!data.ok) {
        return Mensagens.erro('Erro', data.msg || 'Falha ao enviar.');
      }

      Mensagens.sucesso(
        'Pronto!',
        data.msg
      );

      // fecha modal
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();

    } catch (err) {
      Mensagens.fechar();
      Mensagens.erro('Erro', 'Falha na requisição.');
      console.error(err);
    }

  });
}



  });
})();
</script>
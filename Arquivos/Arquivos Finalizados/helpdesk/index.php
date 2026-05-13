<?php
session_start();
require_once 'conexao.php';

// Token CSRF para o formulário de login (proteção contra cross-site request forgery)
if (empty($_SESSION['csrf_token_login'])) {
    $_SESSION['csrf_token_login'] = bin2hex(random_bytes(32));
}

// Usuário padrão administrador (se não existir nenhum com nivel Administrador)
$stmt = $pdo->query("SELECT id FROM usuarios WHERE nivel = 'Administrador' LIMIT 1");
if (!$stmt->fetch()) {
    $senha_hash = password_hash($senha_padrao, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nome, email, senha, nivel, ativo, empresa, foto)
        VALUES (:nome, :email, :senha, 'Administrador', :ativo, :empresa, 'sem_foto.webp')
    ");
    $stmt->execute([
        ':nome' => 'Administrador',
        ':email' => $email_sistema,
        ':senha' => $senha_hash,
        ':ativo' => 'Sim',
        ':empresa' => 0
    ]);
}


// se nao existir o cargo Administrador, ele vai criar um
$pdo->exec("
    INSERT INTO cargos (nome, empresa)
    SELECT 'Administrador', 0
    FROM DUAL
    WHERE NOT EXISTS (
        SELECT 1 FROM cargos WHERE nome = 'Administrador'
    )
");



// Ícone do sistema (config ou fallback)
$icone_sistema = $config['icone'] ?? '';
$icone_path = __DIR__ . '/uploads/' . $icone_sistema;

if ($icone_sistema === '' || !file_exists($icone_path)) {
    $icone_sistema = 'sem_foto.webp';
}

$icone_url = 'uploads/' . rawurlencode($icone_sistema);



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
    <title>Login - <?php echo htmlspecialchars($nome_sistema); ?></title>

    <link rel="icon" type="image/webp" href="<?= htmlspecialchars($icone_url) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($icone_url) ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Flatpickr (datas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Cores do sistema (variáveis PHP) -->
    <style>:root { --cor-primaria: <?php echo $cor_primaria; ?>; --cor-secundaria: <?php echo $cor_secundaria; ?>; }</style>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-box">
                
            <div class="login-logo text-center mb-3">
                <img src="<?= htmlspecialchars($logo_url) ?>"
                    alt="Logo do sistema"
                    style="max-height:60px; max-width:200px;">
            </div>

            
                
                <form class="login-form" action="autenticar.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token_login']); ?>">
                    <div class="form-group">
                        <label for="usuario">E-mail</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input 
                                type="email" 
                                class="form-control" 
                                id="usuario" 
                                name="usuario" 
                                placeholder="Digite seu e-mail"
                                required
                                autocomplete="email"
                                value="<?php echo ($modo_teste === 'Sim') ? htmlspecialchars($teste_email) : ''; ?>"
                            >

                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input 
                                type="password" 
                                class="form-control" 
                                id="senha" 
                                name="senha" 
                                placeholder="Digite sua senha"
                                required
                                autocomplete="current-password"
                                value="<?php echo ($modo_teste === 'Sim') ? htmlspecialchars($teste_senha) : ''; ?>"
                            >

                        </div>
                    </div>
                    
                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="lembrar" id="lembrar">
                            <label class="form-check-label" for="lembrar">
                                Lembrar-me
                            </label>
                        </div>
                        <a href="#" class="forgot-password" data-bs-toggle="modal" data-bs-target="#modalRecuperarSenha">
                            <i class="bi bi-question-circle"></i> Esqueci minha senha
                        </a>

                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </button>
                </form>
            </div>
            
            <div class="login-info">
                <h2>Bem-vindo ao <?php echo htmlspecialchars($nome_sistema); ?></h2>
                <p>Gerencie seus chamados e solicitações de suporte de forma eficiente</p>
                <ul class="features-list">
                    <li>
                        <i class="bi bi-ticket-perforated"></i>
                        Abertura de chamados
                    </li>
                    <li>
                        <i class="bi bi-clock-history"></i>
                        Acompanhamento em tempo real
                    </li>
                    <li>
                        <i class="bi bi-file-earmark-text"></i>
                        Histórico completo
                    </li>
                    <li>
                        <i class="bi bi-person-check"></i>
                        Suporte técnico especializado
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Flatpickr (datas) + locale pt -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    <!-- Mensagens e config do sistema -->
    <script src="js/mensagens.js"></script>
    <script src="js/flatpickr-config.js"></script>
    <script src="js/scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Mensagens.exibirRetornoLogin();
        });
    </script>


<script>
    window.LOGIN_FLASH = <?php
        echo json_encode($_SESSION['flash'] ?? null);
        unset($_SESSION['flash']); // IMPORTANTE: limpa após uso
    ?>;
</script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    Mensagens.exibirRetornoLogin();

    const chk = document.getElementById('lembrar');
    const inputEmail = document.getElementById('usuario');
    const inputSenha = document.getElementById('senha');

    // Ao abrir: carrega dados salvos
    const saved = localStorage.getItem('login_dados');
    if (saved) {
        try {
            const obj = JSON.parse(saved);
            if (obj.email) inputEmail.value = obj.email;
            if (obj.senha) inputSenha.value = obj.senha;
            chk.checked = true;
        } catch (e) {}
    }

    // Se estiver em modo teste, sobrescreve valores do teste (opcional)
    const modoTeste = <?php echo json_encode($modo_teste); ?>;
    if (modoTeste === 'Sim') {
        chk.checked = true;
    }

    // Ao enviar: salva ou remove
    const form = document.querySelector('form.login-form');
    form.addEventListener('submit', function() {
        if (chk.checked) {
            localStorage.setItem('login_dados', JSON.stringify({
                email: inputEmail.value.trim(),
                senha: inputSenha.value
            }));
        } else {
            localStorage.removeItem('login_dados');
        }
    });
});
</script>




<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('modalRecuperarSenha');
  modal.addEventListener('shown.bs.modal', function () {
    const loginEmail = document.getElementById('usuario')?.value || '';
    const input = document.getElementById('email_recuperar');
    if (input) {
      input.value = loginEmail;
      input.focus();
      input.select();
    }
  });
});
</script>



<div class="modal fade modal-recuperar" id="modalRecuperarSenha" tabindex="-1" aria-labelledby="modalRecuperarSenhaLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <div class="modal-icon">
            <i class="bi bi-envelope"></i>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="modalRecuperarSenhaLabel">Recuperar senha</h5>
            <small class="modal-subtitle">Vamos te enviar as instruções por e-mail</small>
          </div>
        </div>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <p class="mb-3 text-muted">
          Informe seu e-mail para receber as instruções de recuperação.
        </p>

        <form id="formRecuperarSenha" method="post" action="#">
          <div class="mb-3">
            <label for="email_recuperar" class="form-label fw-semibold">E-mail</label>

            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
              <input type="email" class="form-control" id="email_recuperar" name="email_recuperar"
                     placeholder="Digite seu e-mail" required autocomplete="email">
            </div>

            <small class="form-text text-muted d-block mt-2">
              Dica: verifique também a caixa de spam.
            </small>
          </div>

          <button type="submit" class="btn btn-modal-primary w-100">
            <i class="bi bi-send"></i> Enviar instruções
          </button>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-modal-outline" data-bs-dismiss="modal">
          Fechar
        </button>
      </div>

    </div>
  </div>
</div>

</div>



</body>
</html>




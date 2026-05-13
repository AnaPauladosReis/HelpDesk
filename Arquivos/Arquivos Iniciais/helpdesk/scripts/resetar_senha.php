<?php
/**
 * scripts/resetar_senha.php
 * Tela + processamento de redefinição de senha
 */

session_start();
require_once __DIR__ . '/../conexao.php';

$token = trim((string)($_GET['token'] ?? ''));

$erro = '';
$sucesso = '';
$mostrarFormulario = false;

$tokenRow = null;  // registro da tabela recuperacao_senha
$usuario = null;   // registro do usuário

// 1) Carrega token e valida
if ($token === '' || strlen($token) > 200) {
    $erro = 'Token inválido.';
} else {

    // Busca tokens ainda válidos (não usados e não expirados)
    $stmt = $pdo->prepare("
        SELECT id, usuario_id, token_hash, expira_em, usado_em
        FROM recuperacao_senha
        WHERE usado_em IS NULL
          AND expira_em >= NOW()
        ORDER BY id DESC
        LIMIT 50
    ");
    $stmt->execute();
    $tokens = $stmt->fetchAll();

    foreach ($tokens as $t) {
        if (!empty($t['token_hash']) && password_verify($token, $t['token_hash'])) {
            $tokenRow = $t;
            break;
        }
    }

    if (!$tokenRow) {
        $erro = 'Este link é inválido, expirou ou já foi utilizado.';
    } else {
        // Busca usuário
        $stmtU = $pdo->prepare("SELECT id, nome, email, ativo FROM usuarios WHERE id = :id LIMIT 1");
        $stmtU->execute([':id' => (int)$tokenRow['usuario_id']]);
        $usuario = $stmtU->fetch();

        if (!$usuario) {
            $erro = 'Usuário não encontrado.';
        } elseif (strtoupper(trim((string)$usuario['ativo'])) !== 'SIM') {
            $erro = 'Usuário inativo. Contate o administrador.';
        } else {
            $mostrarFormulario = true;
        }
    }
}

// 2) Processa POST
if ($mostrarFormulario && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $senha  = (string)($_POST['senha'] ?? '');
    $senha2 = (string)($_POST['senha2'] ?? '');

    // Regras mínimas
    if (strlen($senha) < 6 || strlen($senha) > 256) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } elseif ($senha !== $senha2) {
        $erro = 'As senhas não conferem.';
    } else {

        try {
            $pdo->beginTransaction();

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Atualiza senha
            $pdo->prepare("
                UPDATE usuarios
                SET senha = :senha
                WHERE id = :id
            ")->execute([
                ':senha' => $senhaHash,
                ':id'    => (int)$usuario['id']
            ]);

            // Marca token atual como usado
            $pdo->prepare("
                UPDATE recuperacao_senha
                SET usado_em = NOW()
                WHERE id = :id
                LIMIT 1
            ")->execute([':id' => (int)$tokenRow['id']]);

            // Invalida qualquer outro token pendente do usuário (boa prática)
            $pdo->prepare("
                UPDATE recuperacao_senha
                SET usado_em = NOW()
                WHERE usuario_id = :uid
                  AND usado_em IS NULL
            ")->execute([':uid' => (int)$usuario['id']]);

            $pdo->commit();

            $sucesso = 'Senha redefinida com sucesso! Você já pode fazer login.';
            $mostrarFormulario = false;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = 'Erro ao redefinir senha. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir senha - <?= htmlspecialchars($nome_sistema) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Cores do sistema -->
    <style>
        :root { --cor-primaria: <?= $cor_primaria ?>; --cor-secundaria: <?= $cor_secundaria ?>; }
        body {
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
            min-height: 100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding: 20px;
        }
        .card {
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(0,0,0,.18);
            border: 0;
        }
        .btn-primary {
            border:0;
            background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
        }
        .btn-primary:hover { opacity: .95; }
    </style>
</head>
<body>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card">
        <div class="card-body p-4">

          <h4 class="text-center mb-3">🔐 Redefinir senha</h4>

          <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
          <?php endif; ?>

          <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
            <div class="text-center">
              <a href="../index.php" class="btn btn-primary mt-2">Ir para o login</a>
            </div>
          <?php endif; ?>

          <?php if ($mostrarFormulario): ?>
            <p class="text-muted mb-3">
              Olá <strong><?= htmlspecialchars($usuario['nome'] ?: 'usuário') ?></strong>, escolha sua nova senha.
            </p>

            <form method="post" autocomplete="off">
              <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="senha" class="form-control" required minlength="6">
              </div>

              <div class="mb-3">
                <label class="form-label">Confirmar senha</label>
                <input type="password" name="senha2" class="form-control" required minlength="6">
              </div>

              <button type="submit" class="btn btn-primary w-100">
                Salvar nova senha
              </button>
            </form>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>

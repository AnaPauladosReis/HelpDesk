<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

$id = (int)($_GET['id'] ?? 0);

$st = $pdo->prepare("SELECT id, nome, assinatura FROM usuarios WHERE id = :id LIMIT 1");
$st->execute([':id' => $id]);
$u = $st->fetch(PDO::FETCH_ASSOC);

if (!$u) {
  echo '<div class="alert alert-danger">Usuário não encontrado.</div>';
  exit;
}

$nome = htmlspecialchars($u['nome'] ?? '', ENT_QUOTES, 'UTF-8');
$assinatura = trim((string)($u['assinatura'] ?? ''));

// URL pública (painel fica 1 nível abaixo da raiz)
$assinaturaUrl = $assinatura ? "../uploads/assinaturas/" . rawurlencode($assinatura) : "";
?>

<div class="d-flex align-items-center justify-content-between mb-2">
  <div>
    <div class="fw-semibold"><?= $nome ?></div>
    <div class="small text-muted">Desenhe no quadro abaixo e clique em salvar.</div>
  </div>
</div>



<div class="border rounded-4 p-2">
<canvas id="canvasAssinatura"
        style="width:100%;height:120px;display:block;border-radius:14px;background:#fff;touch-action:none;"></canvas>

</div>

<div class="form-text mt-2">
  Dica: use o mouse ou o dedo (celular).
</div>


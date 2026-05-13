<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: text/html; charset=utf-8');

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmt($dt){
  $dt = (string)$dt;
  if ($dt === '' || $dt === '0000-00-00 00:00:00') return '';
  $ts = strtotime($dt);
  return $ts ? date('d/m/Y H:i', $ts) : '';
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { echo "<div class='text-danger'>ID inválido.</div>"; exit; }

$st = $pdo->prepare("
  SELECT
    m.id,
    m.tipo,
    m.mensagem,
    m.status_de,
    m.status_para,
    m.criado_em,
    u.nome AS usuario_nome
  FROM chamados_movimentos m
  LEFT JOIN usuarios u ON u.id = m.usuario_id
  WHERE m.chamado_id = :id
  ORDER BY m.id DESC
");
$st->execute([':id' => $id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!count($rows)) { ?>
  <div class="text-muted small">Nenhum movimento registrado.</div>
<?php } else { ?>

  <div class="timeline-historico">
    <?php foreach ($rows as $r) {
      $tipo = (string)($r['tipo'] ?? 'sistema');
      $dotClass = ($tipo === 'status') ? 'dot-status' : 'dot-sistema';
      $user = $r['usuario_nome'] ? esc($r['usuario_nome']) : 'Sistema';
      $data = fmt($r['criado_em'] ?? '');
      $msg  = esc($r['mensagem'] ?? '');
    ?>
      <div class="timeline-item">
        <div class="timeline-dot <?= $dotClass ?>"></div>
        <div class="timeline-content">
          <div class="fw-semibold"><?= $msg ?: '<span class="text-muted">—</span>' ?></div>
          <div class="text-muted small"><?= $user ?> • <?= esc($data) ?></div>
        </div>
      </div>
    <?php } ?>
  </div>

<?php } ?>

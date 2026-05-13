<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

header('Content-Type: text/html; charset=utf-8');

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function fmtData($dt){
  $dt = (string)$dt;
  if ($dt === '' || $dt === '0000-00-00 00:00:00') return '';
  $ts = strtotime($dt);
  if (!$ts) return '';
  return date('d/m/Y H:i', $ts);
}

function baseProjetoUrl(){
  $sn = $_SERVER['SCRIPT_NAME'] ?? '';
  $pos = strpos($sn, '/painel/');
  if ($pos !== false) return substr($sn, 0, $pos);

  $p = explode('/', trim($sn, '/'));
  if (count($p) >= 3) {
    array_pop($p);
    array_pop($p);
    array_pop($p);
    return '/' . implode('/', $p);
  }
  return '';
}

function uploadsFsPath(){
  $root = realpath(__DIR__ . '/../../../'); // => .../helpdesk
  return $root ? ($root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR) : null;
}

function fotoUrl($tipo, $fotoArquivo){
  $baseUrl = rtrim(baseProjetoUrl(), '/') . '/uploads/';
  $fsBase  = uploadsFsPath();

  $fotoArquivo = trim((string)$fotoArquivo);

  // ✅ fallbacks (ajuste aqui se o nome do arquivo for diferente)
  $fallbackCliente = $baseUrl . 'sem_foto.webp';
  $fallbackUsuario = $baseUrl . 'sem_foto_perfil.webp';

  if (!$fsBase) return ($tipo === 'usuario') ? $fallbackUsuario : $fallbackCliente;

  if ($tipo === 'usuario') {
    if ($fotoArquivo !== '' && file_exists($fsBase . 'perfil' . DIRECTORY_SEPARATOR . $fotoArquivo)) {
      return $baseUrl . 'perfil/' . rawurlencode($fotoArquivo);
    }
    return $fallbackUsuario;
  }

  if ($fotoArquivo !== '' && file_exists($fsBase . 'clientes' . DIRECTORY_SEPARATOR . $fotoArquivo)) {
    return $baseUrl . 'clientes/' . rawurlencode($fotoArquivo);
  }

  return $fallbackCliente;
}

$chamado_id = (int)($_GET['chamado_id'] ?? 0);
if ($chamado_id <= 0) {
  echo '<div class="text-muted small p-3">Chamado inválido.</div>';
  exit;
}

/**
 * ✅ SEM r.excluida_em / r.excluida_por (não existem na tabela)
 */
$st = $pdo->prepare("
  SELECT
    r.id,
    r.tipo_autor,
    r.mensagem,
    r.criado_em,

    r.usuario_id,
    u.nome AS usuario_nome,
    u.foto AS usuario_foto,

    r.cliente_id,
    cli.nome AS cliente_nome,
    cli.foto AS cliente_foto

  FROM chamados_respostas r
  LEFT JOIN usuarios u ON u.id = r.usuario_id
  LEFT JOIN clientes cli ON cli.id = r.cliente_id
  WHERE r.chamado_id = :cid
  ORDER BY r.id DESC
");
$st->execute([':cid' => $chamado_id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

if (!count($rows)) {
  echo '<div class="text-muted small p-3">Nenhuma resposta ainda.</div>';
  exit;
}

$baseUrl = rtrim(baseProjetoUrl(), '/') . '/uploads/';
$iconeSistema = $baseUrl . 'icone_20260203_194422_8aa51489.png';

$uidSess   = (int)($_SESSION['id'] ?? 0);
$nivelSess = (string)($_SESSION['nivel'] ?? '');
$isAdmin   = (trim($nivelSess) === 'Administrador');
?>

<div class="chat-wrap" data-chamado-id="<?= (int)$chamado_id ?>">
  <?php foreach ($rows as $r):

    $respId = (int)($r['id'] ?? 0);

    $tipo = (string)($r['tipo_autor'] ?? 'usuario');
    if (!in_array($tipo, ['usuario','cliente','sistema'], true)) $tipo = 'usuario';

    $autorUsuarioId = (int)($r['usuario_id'] ?? 0);

    // ✅ regra do botão (sem mexer na tabela)
    $podeExcluirEsta = $isAdmin || ($tipo === 'usuario' && $autorUsuarioId === $uidSess);

    $msg  = trim((string)($r['mensagem'] ?? ''));
    $data = fmtData($r['criado_em'] ?? '');

    if ($tipo === 'cliente') {
      $nome = (string)($r['cliente_nome'] ?? 'Cliente');
      $foto = fotoUrl('cliente', $r['cliente_foto'] ?? '');
      $lado = 'left';
    } elseif ($tipo === 'sistema') {
      $nome = 'Sistema';
      $foto = $iconeSistema;
      $lado = 'center';
    } else {
      $nome = (string)($r['usuario_nome'] ?? 'Usuário');
      $foto = fotoUrl('usuario', $r['usuario_foto'] ?? '');
      $lado = 'right';
    }

    $msgHtml = nl2br(esc($msg));
  ?>

    <?php if ($lado === 'center') { ?>
      <div class="chat-system">
        <div class="system-pill">
          <i class="bi bi-info-circle me-1"></i>
          <?= $msgHtml ?>
          <?php if ($data) { ?>
            <span class="system-time ms-2"><?= esc($data) ?></span>
          <?php } ?>
        </div>
      </div>
    <?php } else { ?>

      <div class="chat-row <?= $lado === 'right' ? 'is-me' : 'is-other' ?>">
        <img class="chat-avatar" src="<?= esc($foto) ?>" alt="">

        <div class="chat-bubble">
          <div class="chat-top">
            <span class="chat-name"><?= esc($nome) ?></span>

            <div class="d-flex align-items-center gap-2">
              <?php if ($data) { ?>
                <span class="chat-time"><?= esc($data) ?></span>
              <?php } ?>

              <?php if ($podeExcluirEsta && podeFazer('excluir')) { ?>
                <button type="button"
                        class="btn btn-sm btn-light border py-0 px-2"
                        onclick="excluirResposta(<?= (int)$respId ?>, <?= (int)$chamado_id ?>)"
                        title="Excluir mensagem">
                  <i class="bi bi-trash"></i>
                </button>
              <?php } ?>
            </div>
          </div>

          <div class="chat-msg"><?= $msgHtml ?></div>
        </div>
      </div>

    <?php } ?>

  <?php endforeach; ?>
</div>

<style>
.chat-wrap{display:flex;flex-direction:column;gap:.65rem;}
.chat-row{display:flex;align-items:flex-end;gap:.55rem;}
.chat-row.is-me{justify-content:flex-end;}
.chat-row.is-me .chat-avatar{order:2;}
.chat-row.is-me .chat-bubble{order:1;}

.chat-avatar{
  width:34px;height:34px;border-radius:999px;object-fit:cover;
  border:1px solid rgba(0,0,0,.08);background:#fff;
}

.chat-bubble{
  max-width:78%;
  border-radius:16px;
  padding:.55rem .70rem;
  border:1px solid rgba(0,0,0,.08);
  background:#fff;
  box-shadow:0 1px 0 rgba(0,0,0,.04);
}
.chat-row.is-me .chat-bubble{
  background: rgba(13,110,253,.08);
  border-color: rgba(13,110,253,.20);
}

.chat-top{
  display:flex;
  gap:.5rem;
  align-items:baseline;
  justify-content:space-between;
  margin-bottom:.20rem;
}
.chat-name{font-weight:700;font-size:.80rem;color:#374151;}
.chat-time{font-size:.72rem;color:#6b7280;white-space:nowrap;}

.chat-msg{font-size:.90rem;line-height:1.25rem;color:#111827;word-break:break-word;}

.chat-system{display:flex;justify-content:center;}
.system-pill{
  font-size:.78rem;color:#6b7280;background:#f8fafc;
  border:1px dashed rgba(0,0,0,.12);border-radius:999px;
  padding:.35rem .70rem;
}
.system-time{font-size:.72rem;opacity:.8;}
</style>
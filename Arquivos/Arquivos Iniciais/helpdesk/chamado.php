<?php
@session_start();
require_once __DIR__ . '/conexao.php';

$BASE_URL = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // ex: /helpdesk
if ($BASE_URL === '') $BASE_URL = '';

function esc($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function fmtData($dt){
  $dt = (string)$dt;
  if ($dt === '' || $dt === '0000-00-00 00:00:00') return '';
  $ts = strtotime($dt);
  return $ts ? date('d/m/Y H:i', $ts) : '';
}

function extArquivo($arq){
    $arq = (string)$arq;
    $p = strrpos($arq, '.');
    return $p !== false ? strtolower(substr($arq, $p+1)) : '';
  }
  
  function isImagem($ext){
    return in_array($ext, ['jpg','jpeg','png','webp','gif'], true);
  }
  
  function iconeArquivoImg($ext){
    $map = [
      'pdf'  => 'pdf.png',
      'rar'  => 'rar.png',
      'zip'  => 'rar.png',
      'doc'  => 'word.png',
      'docx' => 'word.png',
      'xls'  => 'excel.png',
      'xlsx' => 'excel.png',
      'xml'  => 'xml.png',
    ];
  
    return $map[$ext] ?? 'sem-foto.png';
  }


  function baseProjetoUrl(){
    $sn = $_SERVER['SCRIPT_NAME'] ?? '';
    // se tiver /chamado.php dentro de /helpdesk/chamado.php
    // base = /helpdesk
    $p = explode('/', trim($sn, '/'));
    if (count($p) >= 2) {
      array_pop($p); // remove chamado.php
      return '/' . implode('/', $p);
    }
    return '';
  }
  
  function uploadsFsPath(){
    // raiz do projeto (helpdesk)
    $root = realpath(__DIR__); // chamado.php na raiz => .../helpdesk
    return $root ? ($root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR) : null;
  }
  
  function fotoUrl($tipo, $fotoArquivo){
    $baseUrl = rtrim(baseProjetoUrl(), '/') . '/uploads/';
    $fsBase  = uploadsFsPath();
    $fotoArquivo = trim((string)$fotoArquivo);
  
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

$protocolo = trim((string)($_GET['protocolo'] ?? ''));

// valida protocolo básico (ajuste se quiser permitir outros formatos)
if ($protocolo === '' || mb_strlen($protocolo) > 60) {
  http_response_code(400);
  die('Protocolo inválido.');
}

// Ícone / Logo (igual seu padrão)
$icone_sistema = $config['icone'] ?? '';
$icone_path = __DIR__ . '/uploads/' . $icone_sistema;
if ($icone_sistema === '' || !file_exists($icone_path)) $icone_sistema = 'sem_foto.webp';
$icone_url = $BASE_URL . '/uploads/' . rawurlencode($icone_sistema);


$logo_sistema = $config['logo'] ?? '';
$logo_path = __DIR__ . '/uploads/' . $logo_sistema;
if ($logo_sistema === '' || !file_exists($logo_path)) $logo_sistema = 'sem_foto.webp';
$logo_url  = $BASE_URL . '/uploads/' . rawurlencode($logo_sistema);

$cor_primaria   = $config['cor_primaria']   ?? '#667eea';
$cor_secundaria = $config['cor_secundaria'] ?? '#764ba2';

$chamado = null;
$respostas = [];
$erro = '';

try {
  // 1) Busca o chamado por protocolo
  $st = $pdo->prepare("
    SELECT
      c.id,
      c.protocolo,
      c.assunto,
      c.descricao,
      c.prioridade,
      c.criado_em,
      c.atualizado_em,
      c.fechado_em,
      c.setor_id,
      se.nome AS setor_nome,

      c.status_id,
      stt.nome AS status_nome,
      stt.cor  AS status_cor,
      stt.fechado AS status_fechado,

      c.cliente_id,
      cli.nome AS cliente_nome,
      cli.email AS cliente_email,
      cli.telefone AS cliente_telefone,

      c.usuario_abertura_id,
      ua.nome AS abertura_nome,

      c.usuario_responsavel_id,
      ur.nome AS resp_nome,
      ur.email AS resp_email,
      ur.telefone AS resp_telefone

    FROM chamados c
    LEFT JOIN setores se ON se.id = c.setor_id
    LEFT JOIN chamados_status stt ON stt.id = c.status_id
    LEFT JOIN clientes cli ON cli.id = c.cliente_id
    LEFT JOIN usuarios ua ON ua.id = c.usuario_abertura_id
    LEFT JOIN usuarios ur ON ur.id = c.usuario_responsavel_id
    WHERE c.protocolo = :p
    LIMIT 1
  ");
  $st->execute([':p' => $protocolo]);
  $chamado = $st->fetch(PDO::FETCH_ASSOC);

  $anexos = [];

try{
  $stAx = $pdo->prepare("
    SELECT id, arquivo, nome, criado_em
    FROM chamados_anexos
    WHERE chamado_id = :cid
    ORDER BY id DESC
  ");
  $stAx->execute([':cid' => (int)($chamado['id'] ?? 0)]);
  $anexos = $stAx->fetchAll(PDO::FETCH_ASSOC);
}catch(Throwable $e){
  $anexos = [];
}

  if (!$chamado) {
    $erro = 'Chamado não encontrado para este protocolo.';
  } else {
    // 2) Tenta buscar respostas (se existir a tabela)
    // Observação: não sei seus campos exatos; deixei o mais compatível possível.
    // Se sua tabela tiver outro nome/campos, me mande que eu ajusto.
    try {
        $stR = $pdo->prepare("
        SELECT
          r.id,
          r.mensagem,
          r.criado_em,
          r.tipo_autor,
      
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
      $stR->execute([':cid' => (int)$chamado['id']]);
      $respostas = $stR->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      // se não existir tabela/campos, apenas ignora
      $respostas = [];
    }
  }

} catch (Throwable $e) {
  $erro = 'Erro ao consultar o chamado: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chamado <?= esc($protocolo) ?> - <?= esc($nome_sistema ?? 'HelpDesk') ?></title>

  <link rel="icon" type="image/webp" href="<?= esc($icone_url) ?>">
  <link rel="apple-touch-icon" href="<?= esc($icone_url) ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    :root{ --cor-primaria: <?= esc($cor_primaria) ?>; --cor-secundaria: <?= esc($cor_secundaria) ?>; }
    body{ min-height:100vh; background: linear-gradient(135deg, rgba(102,126,234,.10), rgba(118,75,162,.10)); }
    .wrap{ padding:24px; }
    .topbar{
      background: linear-gradient(135deg, var(--cor-primaria), var(--cor-secundaria));
      color:#fff; border-radius:18px; padding:18px 18px;
      box-shadow: 0 18px 50px rgba(0,0,0,.10);
    }
    .brand img{ max-height:46px; max-width:200px; background: rgba(255,255,255,.10); padding:8px 10px; border-radius:14px; }
    .badge-status{ color:#fff; border-radius:999px; padding:.35rem .6rem; font-weight:800; font-size:.85rem; }
    .cardx{ border:0; border-radius:18px; box-shadow: 0 18px 50px rgba(0,0,0,.08); }
    .meta{ font-size:.92rem; color:#6b7280; }
    .msg-box{ background:#fff; border:1px solid rgba(0,0,0,.08); border-radius:16px; padding:14px; }
    .timeline .item{ border-left:3px solid rgba(0,0,0,.08); padding-left:14px; margin-left:6px; }
    .timeline .dot{
      width:10px; height:10px; border-radius:999px; background: var(--cor-primaria);
      position: relative; left:-21px; top:16px; display:inline-block;
    }

    .ticket-top{
  display:flex;
  justify-content:space-between;
  align-items:flex-start;
  gap:20px;
  padding:22px;
  border-radius:20px;
  background: linear-gradient(135deg, rgba(102,126,234,.10), rgba(118,75,162,.08));
  border:1px solid rgba(0,0,0,.05);
}

.tt-left{flex:1}

.tt-title{
  font-size:1.2rem;
  font-weight:900;
  color:#111827;
  line-height:1.2;
  margin-bottom:8px;
}

.tt-sub{
  display:flex;
  gap:16px;
  font-size:.95rem;
  font-weight:600;
  color:#374151;
  margin-bottom:8px;
}

.tt-setor,
.tt-prio{
  display:inline-flex;
  align-items:center;
  padding:6px 12px;
  border-radius:999px;
  background:#fff;
  border:1px solid rgba(0,0,0,.06);
}

.tt-prio{
  background:rgba(255,193,7,.15);
  border-color:rgba(255,193,7,.35);
}

.tt-dates{
  display:flex;
  gap:18px;
  font-size:.85rem;
  color:#6b7280;
}

.tt-status{
  --st:#6c757d;
  background:var(--st);
  color:#fff;
  font-weight:800;
  padding:8px 16px;
  border-radius:999px;
  font-size:.9rem;
  box-shadow:0 8px 18px rgba(0,0,0,.12);
}

@media (max-width:768px){
  .ticket-top{
    flex-direction:column;
  }
  .tt-right{
    margin-top:10px;
  }
}
  </style>
</head>

<body>
<div class="wrap">
  <div class="container" style="max-width: 980px;">

    <div class="topbar mb-4">
      <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div class="brand d-flex align-items-center gap-3">
          <img src="<?= esc($logo_url) ?>" alt="Logo">
          <div>
            <div class="fw-bold" style="font-size:1.05rem;">Detalhamento do Chamado</div>
            <div class="small opacity-75">Protocolo: <b><?= esc($protocolo) ?></b></div>
          </div>
        </div>

        <div class="d-flex gap-2">
          <a href="../abertura" class="btn btn-light rounded-3">
            <i class="bi bi-plus-circle me-1"></i>Abrir novo
          </a>
          <a href="./../acesso" class="btn btn-outline-light rounded-3">
            <i class="bi bi-box-arrow-left me-1"></i>Acesso Cliente
          </a>
        </div>
      </div>
    </div>

    <?php if ($erro !== '') { ?>
      <div class="alert alert-danger rounded-4">
        <b>Erro:</b> <?= esc($erro) ?>
      </div>
    <?php } else { ?>

      <?php
        $statusNome = (string)($chamado['status_nome'] ?? '—');
        $statusCor  = (string)($chamado['status_cor'] ?? '#6c757d');
        $setorNome  = (string)($chamado['setor_nome'] ?? '—');
        $prio       = (string)($chamado['prioridade'] ?? 'Media');

        $criado     = fmtData($chamado['criado_em'] ?? '');
        $atualizado = fmtData($chamado['atualizado_em'] ?? '');
        $fechado    = fmtData($chamado['fechado_em'] ?? '');

        $clienteNome = (string)($chamado['cliente_nome'] ?? '');
        $clienteEmail = (string)($chamado['cliente_email'] ?? '');
        $clienteTel = (string)($chamado['cliente_telefone'] ?? '');

        $respNome = (string)($chamado['resp_nome'] ?? '');
      ?>



<?php
  // ajuste este caminho conforme onde você salva os arquivos
  // exemplo comum: /painel/img/chamados/ ou /uploads/chamados/
  $baseAnexosUrl = $BASE_URL . '/uploads/arquivos/';
  $baseIconesUrl = $BASE_URL . '/uploads/arquivos/';
?>

<div class="msg-box mb-3">
  <div class="fw-bold mb-3">
    <i class="bi bi-paperclip me-1"></i>Anexos
  </div>

  <?php if (!count($anexos)) { ?>
    <div class="text-muted">Nenhum anexo enviado.</div>
  <?php } else { ?>

    <div class="d-flex flex-wrap gap-3">

      <?php foreach ($anexos as $ax):

        $arq = (string)($ax['arquivo'] ?? '');
        $nm  = trim((string)($ax['nome'] ?? ''));
        $dt  = fmtData($ax['criado_em'] ?? '');
        $ext = extArquivo($arq);

        $urlArquivo = $baseAnexosUrl . rawurlencode($arq);
        
        if (isImagem($ext)) {
            $thumb = $urlArquivo; // ✅ miniatura real
          } else {
            $thumb = $baseIconesUrl . iconeArquivoImg($ext); // ✅ ícone
          }

        $titulo = $nm !== '' ? $nm : $arq;
      ?>

        <a href="<?= esc($urlArquivo) ?>" target="_blank"
           class="text-decoration-none text-dark">

          <div style="
            width:120px;
            background:#fff;
            border-radius:14px;
            padding:10px;
            text-align:center;
            border:1px solid #eee;
          ">

<img src="<?= esc($thumb) ?>"
style="width:48px;height:48px;object-fit:cover;border-radius:10px;">

            <div style="font-size:13px; font-weight:600; margin-top:6px;">
              <?= esc(mb_strimwidth($titulo, 0, 18, '...')) ?>
            </div>

            <div style="font-size:11px; color:#777;">
              <?= esc($dt) ?>
            </div>

          </div>

        </a>

      <?php endforeach; ?>

    </div>

  <?php } ?>
</div>

      <div class="card cardx mb-3">
        <div class="card-body p-3 p-md-4">

          <div class="ticket-top mb-3">

  <div class="tt-left">
    <div class="tt-title">
      <?= esc($chamado['assunto'] ?? '') ?>
    </div>

    <div class="tt-sub">
      <span class="tt-setor">
        <i class="bi bi-building me-1"></i> Setor: 
        <?= esc($setorNome) ?>
      </span>

      <span class="tt-prio">
        <i class="bi bi-lightning-charge me-1"></i> Prioridade: 
        <?= esc($prio === 'Media' ? 'Média' : $prio) ?>
      </span>
    </div>

    <div class="tt-dates">
      <?php if ($criado) { ?>
        <span><i class="bi bi-calendar-check me-1"></i><?= esc($criado) ?></span>
      <?php } ?>
      <?php if ($atualizado) { ?>
        <span><i class="bi bi-arrow-repeat me-1"></i><?= esc($atualizado) ?></span>
      <?php } ?>
      <?php if ($fechado) { ?>
        <span><i class="bi bi-check2-circle me-1"></i><?= esc($fechado) ?></span>
      <?php } ?>
    </div>
  </div>

  <div class="tt-right">
    <span class="tt-status" style="--st: <?= esc($statusCor ?: '#6c757d') ?>;">
      <?= esc($statusNome) ?>
    </span>
  </div>

</div>

          <hr class="my-3">

          <div class="msg-box">
            <div class="fw-bold mb-2"><i class="bi bi-card-text me-1"></i>Descrição</div>
            <div style="white-space:pre-wrap;"><?= esc($chamado['descricao'] ?? '') ?></div>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-12 col-md-6">
              <div class="msg-box">
                <div class="fw-bold mb-2"><i class="bi bi-person me-1"></i>Cliente</div>
                <div><b><?= esc($clienteNome ?: '—') ?></b></div>
                <div class="meta">
                  <?php if ($clienteEmail) { ?><div><i class="bi bi-envelope me-1"></i><?= esc($clienteEmail) ?></div><?php } ?>
                  <?php if ($clienteTel) { ?><div><i class="bi bi-telephone me-1"></i><?= esc($clienteTel) ?></div><?php } ?>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6">
              <div class="msg-box">
                <div class="fw-bold mb-2"><i class="bi bi-person-check me-1"></i>Responsável</div>
                <div><b><?= esc($respNome ?: '(a definir)') ?></b></div>
                <div class="meta">
                  <?php if (!empty($chamado['resp_email'])) { ?><div><i class="bi bi-envelope me-1"></i><?= esc($chamado['resp_email']) ?></div><?php } ?>
                  <?php if (!empty($chamado['resp_telefone'])) { ?><div><i class="bi bi-telephone me-1"></i><?= esc($chamado['resp_telefone']) ?></div><?php } ?>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>



      <div class="card cardx">
        <div class="card-body p-3 p-md-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div class="fw-bold"><i class="bi bi-chat-dots me-1"></i>Respostas</div>
            <div class="meta">Acompanhe por aqui as atualizações do chamado.</div>
          </div>

          <?php if (!count($respostas)) { ?>
            <div class="text-muted">Nenhuma resposta registrada até o momento.</div>
          <?php } else { ?>
            <div class="timeline mt-2">
            

                                <?php
                    $baseUploads = rtrim(baseProjetoUrl(), '/') . '/uploads/';
                    $iconeSistema = $baseUploads . ($config['icone'] ?? 'sem_foto.webp'); // ou seu ícone fixo
                    ?>



<div class="resp-list mt-2">

  <?php foreach ($respostas as $r):

    $tipo = strtolower((string)($r['tipo_autor'] ?? 'usuario'));
    if (!in_array($tipo, ['usuario','cliente','sistema'], true)) $tipo = 'usuario';

    $dt  = fmtData($r['criado_em'] ?? '');
    $msg = (string)($r['mensagem'] ?? '');
    $msgHtml = nl2br(esc($msg));

    if ($tipo === 'cliente') {
      $nome = (string)($r['cliente_nome'] ?? 'Cliente');
      $foto = fotoUrl('cliente', $r['cliente_foto'] ?? '');
      $classe = 'is-cliente';
      $badge  = 'Cliente';
    } elseif ($tipo === 'sistema') {
      $nome = 'Sistema';
      $foto = $iconeSistema ?? ''; // se quiser exibir um ícone aqui
      $classe = 'is-sistema';
      $badge  = 'Sistema';
    } else {
      $nome = (string)($r['usuario_nome'] ?? 'Usuário');
      $foto = fotoUrl('usuario', $r['usuario_foto'] ?? '');
      $classe = 'is-usuario';
      $badge  = 'Atendente';
    }

  ?>

    <div class="resp-item <?= $classe ?>">
      <div class="resp-top">
        <?php if ($tipo !== 'sistema') { ?>
          <img class="resp-avatar" src="<?= esc($foto) ?>" alt="">
        <?php } else { ?>
          <div class="resp-avatar resp-avatar-sys">
            <i class="bi bi-info-circle"></i>
          </div>
        <?php } ?>

        <div class="resp-head">
          <div class="resp-line1">
            <div class="resp-nome"><?= esc($nome) ?></div>
            <span class="resp-badge"><?= esc($badge) ?></span>
          </div>

          <?php if ($dt) { ?>
            <div class="resp-data"><?= esc($dt) ?></div>
          <?php } ?>
        </div>
      </div>

      <div class="resp-msg"><?= $msgHtml ?></div>
    </div>

  <?php endforeach; ?>

</div>

<style>
.resp-list{display:flex;flex-direction:column;gap:.85rem;}

.resp-item{
  border-radius:16px;
  border:1px solid rgba(0,0,0,.08);
  padding:.85rem .95rem;
  background:#fff;
  box-shadow:0 1px 0 rgba(0,0,0,.04);
}

.resp-item.is-usuario{
  background: rgba(13,110,253,.07);
  border-color: rgba(13,110,253,.18);
}
.resp-item.is-cliente{
  background: rgba(25,135,84,.08);
  border-color: rgba(25,135,84,.18);
}
.resp-item.is-sistema{
  background: #f8fafc;
  border-style:dashed;
}

.resp-top{display:flex;gap:.65rem;align-items:center;margin-bottom:.55rem;}
.resp-avatar{
  width:38px;height:38px;border-radius:999px;object-fit:cover;
  border:1px solid rgba(0,0,0,.10);background:#fff;
  flex:0 0 auto;
}
.resp-avatar-sys{
  display:flex;align-items:center;justify-content:center;
  font-size:1.05rem;color:#6b7280;
}

.resp-head{min-width:0;flex:1;}
.resp-line1{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;}
.resp-nome{font-weight:800;font-size:.92rem;color:#111827;}
.resp-badge{
  font-size:.70rem;font-weight:800;
  padding:.18rem .45rem;border-radius:999px;
  background: rgba(255,255,255,.65);
  border:1px solid rgba(0,0,0,.08);
  color:#374151;
}
.resp-data{font-size:.78rem;color:#6b7280;margin-top:.05rem;}

.resp-msg{
  font-size:.95rem;
  line-height:1.35rem;
  color:#111827;
  word-break:break-word;
  white-space:normal;
}
</style>



          <?php } ?>

        </div>
      </div>

    <?php } ?>

    <div class="text-center text-muted small mt-4">
      © <?= date('Y') ?> <?= esc($nome_sistema ?? 'HelpDesk') ?>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
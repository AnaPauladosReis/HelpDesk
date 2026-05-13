<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

// Status ativos (para dropdown rápido)
$stAll = $pdo->query("SELECT id, nome, cor, fechado FROM chamados_status WHERE ativo='Sim' ORDER BY ordem ASC, nome ASC");
$allStatus = $stAll->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/html; charset=utf-8');

function esc($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fmtData($dt) {
  $dt = (string)$dt;
  if ($dt === '' || $dt === '0000-00-00 00:00:00') return '';
  $ts = strtotime($dt);
  if (!$ts) return '';
  return date('d/m/Y H:i', $ts);
}

function badgePrioridade($p) {
  $p = trim((string)$p);
  $map = [
    'Baixa'   => 'text-bg-secondary',
    'Media'   => 'text-bg-primary',
    'Alta'    => 'text-bg-warning',
    'Urgente' => 'text-bg-danger',
  ];
  $cls = $map[$p] ?? 'text-bg-primary';
  $label = ($p === 'Media') ? 'Média' : ($p ?: 'Média');
  return '<span class="badge rounded-pill '.$cls.'">'.esc($label).'</span>';
}

function badgeStatus($nome, $corHex, $fechado = 'Não') {
  $nome = trim((string)$nome);
  $corHex = trim((string)$corHex);
  if ($corHex === '') $corHex = '#6c757d';

  $isFechado = (strtolower((string)$fechado) === 'sim');

  $style = "background: {$corHex}; color:#fff; border:1px solid rgba(0,0,0,.08);";
  $extra = $isFechado ? ' <i class="bi bi-check2-circle ms-1"></i>' : '';
  return '<span class="badge rounded-pill" style="'.esc($style).'">'.esc($nome ?: '—').$extra.'</span>';
}

/* =========================
   FILTROS (GET) - mesmos IDs da página
========================= */
$data_ini = trim($_GET['data_ini'] ?? '');
$data_fim = trim($_GET['data_fim'] ?? '');
$status_id = (int)($_GET['status_id'] ?? 0);
$cliente_id = (int)($_GET['cliente_id'] ?? 0);
$responsavel_id = (int)($_GET['responsavel_id'] ?? 0);
$setor_id = (int)($_GET['setor_id'] ?? 0); // ✅ NOVO
$prioridade = trim($_GET['prioridade'] ?? '');
$termo = trim($_GET['termo'] ?? '');

/* datas: se vier vazio, não filtra (mas sua página manda hoje por padrão) */
$where = [];
$params = [];

/* intervalo por criado_em */
if ($data_ini !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_ini)) {
  $where[] = "c.criado_em >= :ini";
  $params[':ini'] = $data_ini . " 00:00:00";
}
if ($data_fim !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_fim)) {
  $where[] = "c.criado_em <= :fim";
  $params[':fim'] = $data_fim . " 23:59:59";
}

if ($status_id > 0) {
  $where[] = "c.status_id = :status_id";
  $params[':status_id'] = $status_id;
}

if ($cliente_id > 0) {
  $where[] = "c.cliente_id = :cliente_id";
  $params[':cliente_id'] = $cliente_id;
}

if ($responsavel_id > 0) {
  $where[] = "c.usuario_responsavel_id = :resp_id";
  $params[':resp_id'] = $responsavel_id;
}

// ✅ FILTRO POR SETOR
if ($setor_id > 0) {
  $where[] = "c.setor_id = :setor_id";
  $params[':setor_id'] = $setor_id;
}

if ($prioridade !== '') {
  // normaliza (Baixa/Media/Alta/Urgente)
  $map = ['Baixa'=>'Baixa','Média'=>'Media','Media'=>'Media','Alta'=>'Alta','Urgente'=>'Urgente'];
  $prioridade = $map[$prioridade] ?? $prioridade;
  if (in_array($prioridade, ['Baixa','Media','Alta','Urgente'], true)) {
    $where[] = "c.prioridade = :prioridade";
    $params[':prioridade'] = $prioridade;
  }
}

if ($termo !== '') {
  // busca por protocolo/assunto/cliente
  $where[] = "(
    c.protocolo LIKE :q
    OR c.assunto LIKE :q
    OR cli.nome LIKE :q
    OR cli.email LIKE :q
  )";
  $params[':q'] = '%' . $termo . '%';
}



$usuario_logado = (int)($_SESSION['id'] ?? 0);

// Busca setores permitidos do usuário
$setoresPermitidos = [];

$stSet = $pdo->prepare("
  SELECT setor_id 
  FROM usuarios_setores 
  WHERE usuario_id = :uid
");
$stSet->execute([':uid' => $usuario_logado]);
$setoresPermitidos = $stSet->fetchAll(PDO::FETCH_COLUMN);


$nivel = $_SESSION['nivel'] ?? '';

if (strtolower($nivel) !== 'administrador') {

    // monta lista dinâmica de setores
    $placeholders = [];
    foreach ($setoresPermitidos as $k => $sid) {
        $ph = ":set_perm_$k";
        $placeholders[] = $ph;
        $params[$ph] = (int)$sid;
    }

    $params[':usuario_logado'] = $usuario_logado;

    if (!empty($placeholders)) {

        $where[] = "(
            c.usuario_responsavel_id = :usuario_logado
            OR (
                c.usuario_responsavel_id IS NULL
                AND c.setor_id IN (" . implode(',', $placeholders) . ")
            )
        )";

    } else {

        // se não tiver setores permitidos
        // só vê os que ele é responsável
        $where[] = "c.usuario_responsavel_id = :usuario_logado";
    }
}

$sql = "
  SELECT
    c.id,
    c.protocolo,
    c.assunto,
    c.prioridade,
    c.criado_em,
    c.atualizado_em,
    c.fechado_em,

    c.setor_id,
    se.nome AS setor_nome,

    cli.id   AS cliente_id,
    cli.nome AS cliente_nome,
    cli.email AS cliente_email,
    cli.telefone AS cliente_telefone,

    ua.id    AS abertura_id,
    ua.nome  AS abertura_nome,

    ur.id    AS resp_id,
    ur.nome  AS resp_nome,
    ur.telefone AS resp_telefone,

    st.id    AS status_id,
    st.nome  AS status_nome,
    st.cor   AS status_cor,
    st.fechado AS status_fechado,

    -- ✅ NOVO (precisa estar no SELECT)
    ult.tipo_autor AS ult_tipo_autor,
    ult.criado_em  AS ult_resp_em

  FROM chamados c
  LEFT JOIN setores se ON se.id = c.setor_id
  LEFT JOIN clientes cli ON cli.id = c.cliente_id
  INNER JOIN usuarios ua ON ua.id = c.usuario_abertura_id
  LEFT JOIN usuarios ur ON ur.id = c.usuario_responsavel_id
  INNER JOIN chamados_status st ON st.id = c.status_id

  -- ✅ JOIN DA ÚLTIMA RESPOSTA (o seu está certo)
  LEFT JOIN chamados_respostas ult
    ON ult.id = (
      SELECT r2.id
      FROM chamados_respostas r2
      WHERE r2.chamado_id = c.id
      ORDER BY r2.id DESC
      LIMIT 1
    )
";

if (count($where)) {
  $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.id DESC";

$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card border-0 shadow-sm rounded-4" id="cardAberturaTabela">
  <div class="card-body p-3 p-md-4">

    <div class="table-responsive">
      <table class="table table-sm align-middle w-100" id="tabela">
        <thead class="table-light">
          <tr>
            <th style="width:130px;">Protocolo</th>
            <th style="width:220px;">Assunto</th>
            <th style="width:240px;">Cliente</th>
            <th style="width:200px;">Responsável</th>
            <th style="width:150px;">Status</th>
            <th style="width:120px;">Prioridade</th>
            <th style="width:170px;">Setor</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>

        <tbody>
        <?php if (!count($rows)) { ?>
          <tr>
            <td colspan="8" class="text-muted small py-3">
              Nenhum chamado encontrado para os filtros informados.
            </td>
          </tr>
        <?php } else { ?>

          <?php foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);

            $protocolo = esc($r['protocolo'] ?? '');
            $assunto   = esc($r['assunto'] ?? '');

            $clienteNome  = esc($r['cliente_nome'] ?? '');
            $clienteEmail = esc($r['cliente_email'] ?? '');
            $clienteTel   = (string)($r['cliente_telefone'] ?? '');

            $respNome = esc($r['resp_nome'] ?? '');
            $respTel  = (string)($r['resp_telefone'] ?? '');

            $statusNome = (string)($r['status_nome'] ?? '');
            $statusCor  = (string)($r['status_cor'] ?? '#6c757d');
            $statusFechado = (string)($r['status_fechado'] ?? 'Não');

            $prioridade = (string)($r['prioridade'] ?? 'Media');

            $criado = fmtData($r['criado_em'] ?? '');
            $fechado = fmtData($r['fechado_em'] ?? '');

            $nome_setor = (string)($r['setor_nome'] ?? '');
            

            $ultTipo = strtolower((string)($r['ult_tipo_autor'] ?? ''));
            $temNotifCliente = ($ultTipo === 'cliente');

            $notifHtml = $temNotifCliente
              ? "<span class='badge rounded-pill text-bg-danger ms-2' title='Última mensagem do cliente'><i class='bi bi-bell-fill me-1'></i>Novo</span>"
              : "";

            $clienteHtml = $clienteNome
              ? "<div class='fw-semibold d-flex align-items-center flex-wrap'>{$clienteNome}{$notifHtml}</div>"
              : "<span class='text-muted'>—</span>";

            $respHtml = $respNome
              ? "<span class='fw-semibold'>{$respNome}</span>"
              : "<span class='text-muted'>(Opcional)</span>";

            $statusBadge = badgeStatus($statusNome, $statusCor, $statusFechado);
            $prioBadge   = badgePrioridade($prioridade);

            $criadoHtml = $criado ? "<div class='fw-semibold'>{$criado}</div>" : "<span class='text-muted'>—</span>";
            if ($fechado) $criadoHtml .= "<div class='text-muted small'>Encerrado: {$fechado}</div>";

            $isFechado = (strtolower($statusFechado) === 'sim');

            // flags (sem quebrar se JS ainda não tiver funções)
            $telClienteOk = trim(preg_replace('/\D+/', '', $clienteTel)) !== '';
            $telRespOk    = trim(preg_replace('/\D+/', '', $respTel)) !== '';
          ?>
            <tr>
            <td class="col-protocolo">
              <div class="proto">
                <a href="javascript:void(0);" 
                  onclick="respostas(<?= (int)$id ?>, '<?= esc($r['protocolo'] ?? '') ?>')"
                  class="link-protocolo"
                  title="Ver respostas do chamado">
                  <?= esc($r['protocolo'] ?? '') ?>
                </a>
              </div>
            </td>

            <td class="col-assunto">
  <div class="d-flex align-items-center gap-2">

    <div class="fw-semibold">
      <?= $assunto ?: '<span class="text-muted">—</span>' ?>
    </div>

    <?php if (!empty($protocolo)) { ?>
      <a href="/helpdesk/chamado/<?= urlencode($protocolo) ?>"
         class="link-detalhe"
         title="Abrir detalhamento"
         target="_blank">
        <i class="bi bi-box-arrow-up-right"></i>
      </a>
    <?php } ?>

  </div>
</td>

              <td><?= $clienteHtml ?></td>

              <td><?= $respHtml ?></td>

              <td>
                <?php if (podeFazer('editar')) { ?>
                  <div class="dropdown d-inline-block">
                    <button class="btn p-0 border-0 bg-transparent"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Alterar status">
                      <?= $statusBadge ?>
                      <i class="bi bi-chevron-down small text-muted"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-4 p-1">
                      <?php foreach ($allStatus as $ss) { ?>
                        <li>
                          <button type="button"
                                  class="dropdown-item rounded-3 js-set-status"
                                  data-id="<?= (int)$id ?>"
                                  data-status-id="<?= (int)$ss['id'] ?>"
                                  data-fechado="<?= esc($ss['fechado'] ?? 'Não') ?>"
                                  style="display:flex;align-items:center;gap:.5rem;">
                            <span style="width:10px;height:10px;border-radius:999px;background:<?= esc($ss['cor'] ?: '#6c757d') ?>;display:inline-block;"></span>
                            <span><?= esc($ss['nome']) ?></span>
                          </button>
                        </li>
                      <?php } ?>
                    </ul>
                  </div>
                <?php } else { ?>
                  <?= $statusBadge ?>
                <?php } ?>
              </td>

              <td>
                <?php if (podeFazer('editar')) { ?>
                  <div class="dropdown d-inline-block">
                    <button class="btn p-0 border-0 bg-transparent"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            title="Alterar prioridade">
                      <?= $prioBadge ?>
                      <i class="bi bi-chevron-down small text-muted"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-4 p-1">
                      <?php
                        $prios = ['Baixa'=>'Baixa','Media'=>'Média','Alta'=>'Alta','Urgente'=>'Urgente'];
                        foreach ($prios as $val => $lbl) {
                      ?>
                        <li>
                          <button type="button"
                                  class="dropdown-item rounded-3 js-set-prio"
                                  data-id="<?= (int)$id ?>"
                                  data-prioridade="<?= esc($val) ?>">
                            <?= esc($lbl) ?>
                          </button>
                        </li>
                      <?php } ?>
                    </ul>
                  </div>
                <?php } else { ?>
                  <?= $prioBadge ?>
                <?php } ?>
              </td>

              <td><?= $nome_setor ?></td>

              <td class="text-end">
                <div class="acoes-wrap position-relative d-inline-block">
                  <button type="button"
                          class="btn btn-outline-secondary btn-sm btn-acoes-mais"
                          data-id="<?= (int)$id ?>"
                          title="Mais ações">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>

                  <div class="acoes-pop shadow-sm border rounded-4 p-2 bg-white"
                       id="acoesPop<?= (int)$id ?>"
                       style="display:none;">
                    <div class="d-flex flex-wrap gap-1">

                      <button type="button" class="btn btn-outline-dark btn-sm"
                              onclick="mostrar(<?= (int)$id ?>)" title="Ver">
                        <i class="bi bi-eye"></i>
                      </button>

                      <?php if (podeFazer('editar')) { ?>
                        <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="editar(<?= (int)$id ?>)" title="Editar">
                          <i class="bi bi-pencil"></i>
                        </button>
                      <?php } ?>

                      <button type="button" class="btn btn-outline-secondary btn-sm"
                              onclick="historico(<?= (int)$id ?>)" title="Histórico">
                        <i class="bi bi-clock-history"></i>
                      </button>

                      <button type="button" class="btn btn-outline-secondary btn-sm"
                              onclick="pdfChamado(<?= (int)$id ?>)" title="PDF">
                        <i class="bi bi-file-earmark-pdf"></i>
                      </button>

                      <?php if ($telClienteOk) { ?>
                        <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="whatsCliente(<?= (int)$id ?>)" title="WhatsApp Cliente">
                          <i class="bi bi-whatsapp"></i>
                        </button>
                      <?php } ?>

                      <?php if ($telRespOk) { ?>
                        <button type="button" class="btn btn-outline-success btn-sm"
                                onclick="whatsResp(<?= (int)$id ?>)" title="WhatsApp Responsável">
                          <i class="bi bi-person-check"></i>
                        </button>
                      <?php } ?>

                      <?php if (!$isFechado && podeFazer('editar')) { ?>
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="encerrar(<?= (int)$id ?>)" title="Encerrar">
                          <i class="bi bi-check2-circle"></i>
                        </button>
                      <?php } ?>

                      <?php if (podeFazer('excluir')) { ?>
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="excluir(<?= (int)$id ?>)" title="Excluir">
                          <i class="bi bi-trash"></i>
                        </button>
                      <?php } ?>


                      <button type="button" 
                          class="btn btn-outline-secondary btn-sm"
                          onclick="anexos(<?= (int)$id ?>)" 
                          title="Arquivos">
                      <i class="bi bi-paperclip"></i>
                  </button>


                      <button type="button" 
                          class="btn btn-outline-primary btn-sm"
                          onclick="respostas(<?= (int)$id ?>, '<?= esc($r['protocolo'] ?? '') ?>')" 
                          title="Respostas">
                      <i class="bi bi-chat-dots"></i>
                  </button>

                    </div>
                  </div>

                </div>
              </td>

            </tr>
          <?php } ?>

        <?php } ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
/* =========================
   DATATABLE
========================= */
(function () {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

  if (jQuery.fn.DataTable.isDataTable('#tabela')) {
    jQuery('#tabela').DataTable().destroy();
  }

  jQuery('#tabela').DataTable({
    responsive: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[6, 'desc']], // Criado
    columnDefs: [
      { orderable: false, targets: [7] }
    ],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json"
    }
  });
})();
</script>

<style>
/* ==========================================================
   TABELA ABERTURA (SÓ AQUI) - CSS ÚNICO (SEM DUPLICAÇÃO)
========================================================== */

/* Base */
#cardAberturaTabela .table{
  font-size: .875rem;
}

#cardAberturaTabela thead th{
  font-size: .80rem;
  letter-spacing: .2px;
  text-transform: uppercase;
  color: #6b7280;
  border-bottom: 1px solid rgba(0,0,0,.08);
  white-space: nowrap;
}

#cardAberturaTabela tbody td{
  vertical-align: middle;
  padding-top: .65rem;
  padding-bottom: .65rem;
}

#cardAberturaTabela tbody tr{
  border-color: rgba(0,0,0,.06);
}

#cardAberturaTabela tbody tr:hover{
  background: rgba(0,0,0,.02);
}

#cardAberturaTabela .badge{
  font-size: .75rem;
  padding: .38rem .55rem;
}

/* Protocolo compacto */
#cardAberturaTabela .col-protocolo .proto{
  font-size: .78rem;
  font-weight: 800;
  letter-spacing: .2px;
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 576px){
  #cardAberturaTabela .col-protocolo .proto{ max-width: 110px; }
}

/* Assunto com clamp (precisa ter class="col-assunto" no <td>) */
#cardAberturaTabela .col-assunto{
  max-width: 420px;
}
#cardAberturaTabela .col-assunto .fw-semibold{
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Cliente/responsável */
#cardAberturaTabela .text-muted.small{
  font-size: .78rem;
}

/* ==========================================================
   AÇÕES + POPUP
========================================================== */

/* CRÍTICO: permite o popup "sair" da table sem ser cortado */
#cardAberturaTabela .table-responsive{
  overflow: visible !important;
}

/* Wrapper das ações */
#cardAberturaTabela .acoes-wrap{
  position: relative;
  display: inline-block;
}

/* Botão ... (garante visível e clicável) */
#cardAberturaTabela .btn-acoes-mais{
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  padding: 0;
}
#cardAberturaTabela .btn-acoes-mais i{
  font-size: 1.05rem;
}

/* Popup */
#cardAberturaTabela .acoes-pop{
  position: absolute;
  top: calc(100% + 10px);
  right: 0;                 /* alinha à direita do botão */
  z-index: 99999;
  min-width: 260px;         /* cabe mais botões */
  background: #fff;
}

/* Setinha do popup */
#cardAberturaTabela .acoes-pop::before{
  content:"";
  position:absolute;
  top:-7px;
  right: 14px;              /* alinha a seta ao botão */
  width: 12px;
  height: 12px;
  background: #fff;
  border-left: 1px solid rgba(0,0,0,.12);
  border-top: 1px solid rgba(0,0,0,.12);
  transform: rotate(45deg);
}

/* Botões dentro do popup */
#cardAberturaTabela .acoes-pop .btn.btn-sm{
  padding: .28rem .50rem;
  border-radius: .6rem;
}
#cardAberturaTabela .acoes-pop .btn.btn-sm i{
  font-size: .95rem;
}

/* (Opcional) popup mais “leve” */
#cardAberturaTabela .acoes-pop{
  box-shadow: 0 10px 30px rgba(0,0,0,.12);
}

/* ==========================================================
   DATATABLES - pequenos ajustes sem afetar outras tabelas
========================================================== */

/* evita quebra feia quando o DT aplica classes */
#cardAberturaTabela table.dataTable tbody td{
  border-top-color: rgba(0,0,0,.06);
}

/* se o DT injetar wrapper, mantém o visual */
#cardAberturaTabela .dataTables_wrapper .dataTables_filter,
#cardAberturaTabela .dataTables_wrapper .dataTables_length{
  font-size: .85rem;
}


/* Faz cada linha parecer um card */
#cardAberturaTabela tbody tr{
  background: #fff;
  border: 0;
}
#cardAberturaTabela tbody td{
  border-top: 0 !important;
  padding-top: .55rem;
  padding-bottom: .55rem;
}

/* Espaçamento entre linhas (trque: usa border-spacing) */
#cardAberturaTabela table{
  border-collapse: separate !important;
  border-spacing: 0 .55rem !important;
}

/* “Card” arredondado */
#cardAberturaTabela tbody tr td:first-child{
  border-top-left-radius: 14px;
  border-bottom-left-radius: 14px;
}
#cardAberturaTabela tbody tr td:last-child{
  border-top-right-radius: 14px;
  border-bottom-right-radius: 14px;
}

/* Sombra leve no hover */
#cardAberturaTabela tbody tr{
  box-shadow: 0 1px 0 rgba(0,0,0,.06);
}
#cardAberturaTabela tbody tr:hover{
  box-shadow: 0 10px 25px rgba(0,0,0,.08);
  /*transform: translateY(-1px); */
  transition: .15s ease;
}


.link-protocolo {
  text-decoration: none;
  color: inherit;
  font-weight: 800;
  cursor: pointer;
  transition: .15s ease;
}

.link-protocolo:hover {
  color: #0d6efd;
  text-decoration: underline;
}


.link-detalhe{
  font-size:.9rem;
  color:#6b7280;
  text-decoration:none;
  transition:.2s ease;
}

.link-detalhe:hover{
  color:var(--cor-primaria, #667eea);
  transform:translateY(-1px);
}

</style>





<?php
@session_start();
require_once __DIR__ . '/../../verificar.php'; // se você tiver verificar_cliente.php, troque aqui
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: text/html; charset=utf-8');

function esc($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
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

$cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
if ($cliente_id <= 0) {
  echo "<div class='text-muted small p-3'>Sessão expirada. Faça login novamente.</div>";
  exit;
}

$sql = "
  SELECT
    c.id,
    c.protocolo,
    c.assunto,
    c.prioridade,
    c.setor_id,
    se.nome AS setor_nome,
    

    c.usuario_responsavel_id,
    ur.nome AS resp_nome,

    st.nome AS status_nome,
    st.cor  AS status_cor,
    st.fechado AS status_fechado,

    ult.tipo_autor AS ult_tipo_autor,
    ult.usuario_id AS ult_usuario_id,
    ult.cliente_id AS ult_cliente_id

  FROM chamados c
  LEFT JOIN setores se ON se.id = c.setor_id
  LEFT JOIN usuarios ur ON ur.id = c.usuario_responsavel_id
  INNER JOIN chamados_status st ON st.id = c.status_id

  LEFT JOIN chamados_respostas ult
    ON ult.id = (
      SELECT r2.id
      FROM chamados_respostas r2
      WHERE r2.chamado_id = c.id
      ORDER BY r2.id DESC
      LIMIT 1
    )

  WHERE c.cliente_id = :cid
  ORDER BY c.id DESC
";
$st = $pdo->prepare($sql);
$st->execute([':cid' => $cliente_id]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card border-0 shadow-sm rounded-4" id="cardAberturaTabela">
  <div class="card-body p-3 p-md-4">

    <div class="table-responsive">
      <table class="table table-sm align-middle w-100" id="tabela" style="font-size:13px">
        <thead class="table-light">
          <tr>
          <th class="d-none">ID</th>
            <th style="width:160px;">Protocolo</th>
            <th>Assunto</th>
            <th style="width:210px;">Responsável</th>
            <th style="width:100px;">Status</th>
           
            <th style="width:120px;">Setor</th>
            <th class="text-end" style="width:180px;">Ações</th>
          </tr>
        </thead>

        <tbody>
        <?php if (!count($rows)) { ?>
          <tr>
            <td colspan="7" class="text-muted small py-3">
              Nenhum chamado encontrado.
            </td>
          </tr>
        <?php } else { ?>

          <?php foreach ($rows as $r) {
            $id = (int)($r['id'] ?? 0);
            $protocolo = esc($r['protocolo'] ?? '');
            $assunto = esc($r['assunto'] ?? '');
            $respNome = esc($r['resp_nome'] ?? '');
            $setorNome = esc($r['setor_nome'] ?? '');

            $statusBadge = badgeStatus($r['status_nome'] ?? '', $r['status_cor'] ?? '#6c757d', $r['status_fechado'] ?? 'Não');
            $prioBadge   = badgePrioridade($r['prioridade'] ?? 'Media');

            // ✅ Novo: notifica somente se a última resposta foi do usuário/suporte
            $ultTipo = strtolower(trim((string)($r['ult_tipo_autor'] ?? '')));

          // só mostra se a ÚLTIMA mensagem foi do usuário
          $temNotif = ($ultTipo === 'usuario');

          $notifHtml = $temNotif
            ? "<span class='badge rounded-pill text-bg-danger ms-2' title='Nova mensagem do suporte'><i class='bi bi-bell-fill'></i></span>"
            : "";

            $respHtml = $respNome !== ''
              ? "<span class='fw-semibold'>{$respNome}</span>"
              : "<span class='text-muted'>(Opcional)</span>";
          ?>
            <tr>
            <td class="d-none"><?= (int)$id ?></td>
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
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <div class="fw-semibold">
                    <?= $assunto ?: '<span class="text-muted">—</span>' ?>
                  </div>

                  <a href="javascript:void(0);"
                     onclick="respostas(<?= (int)$id ?>, '<?= esc($r['protocolo'] ?? '') ?>')"
                     class="link-protocolo"
                     title="Ver respostas do chamado">
                  <?= $notifHtml ?>
                </a>

                  <?php if ($protocolo !== '') { ?>
                    <a href="<?= $url_sistema ?>chamado/<?= urlencode($protocolo) ?>"
                       class="link-detalhe"
                       title="Abrir detalhamento"
                       target="_blank">
                      <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                  <?php } ?>
                </div>
              </td>

              <td><?= $respHtml ?></td>
              <td><?= $statusBadge ?></td>
              
              <td><?= $setorNome ?: '<span class="text-muted">—</span>' ?></td>

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

                    <button type="button" class="btn btn-outline-primary btn-sm"
                                onclick="editar(<?= (int)$id ?>)" title="Editar">
                          <i class="bi bi-pencil"></i>
                        </button>



                      <button type="button" class="btn btn-outline-dark btn-sm"
                              onclick="mostrar(<?= (int)$id ?>)" title="Ver">
                        <i class="bi bi-eye"></i>
                      </button>

                      <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="excluir(<?= (int)$id ?>)" title="Excluir">
                          <i class="bi bi-trash"></i>
                        </button>

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


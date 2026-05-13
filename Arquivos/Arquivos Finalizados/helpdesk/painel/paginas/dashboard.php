<?php
$usuario_logado = (int)($_SESSION['id'] ?? 0);
$nivel = strtolower(trim((string)($_SESSION['nivel'] ?? '')));

// multiempresa (se existir)
$id_empresa = (int)($_SESSION['id_empresa'] ?? 0);

// ============
// 1) Setores permitidos do usuário
// ============
$setoresPermitidos = [];
if ($nivel !== 'administrador' && $usuario_logado > 0) {
  $stSet = $pdo->prepare("SELECT setor_id FROM usuarios_setores WHERE usuario_id = :uid");
  $stSet->execute([':uid' => $usuario_logado]);
  $setoresPermitidos = $stSet->fetchAll(PDO::FETCH_COLUMN);
}

// ============
// 2) Monta WHERE base (empresa + permissões)
// ============
// =========================
// 2) Monta WHERE base (seguro)
// =========================
$params = [];
$where = [];

$where[] = "1=1"; // evita erro de AND

if ($id_empresa > 0) {
  $where[] = "c.empresa = :empresa";
  $params[':empresa'] = $id_empresa;
}

if ($nivel !== 'administrador') {

  $params[':uid'] = $usuario_logado;

  $placeholders = [];
  foreach ($setoresPermitidos as $k => $sid) {
    $ph = ":set_perm_$k";
    $placeholders[] = $ph;
    $params[$ph] = (int)$sid;
  }

  if (!empty($placeholders)) {
    $where[] = "(
      c.usuario_responsavel_id = :uid
      OR (
        (c.usuario_responsavel_id IS NULL OR c.usuario_responsavel_id = 0)
        AND c.setor_id IN (" . implode(',', $placeholders) . ")
      )
    )";
  } else {
    $where[] = "c.usuario_responsavel_id = :uid";
  }
}

$wherePerm = " WHERE " . implode(" AND ", $where);

// helper count
function getCount(PDO $pdo, string $sql, array $params): int {
  $st = $pdo->prepare($sql);
  $st->execute($params);
  return (int)($st->fetchColumn() ?? 0);
}

// ============
// 3) Cards
// ============

// 1) Chamados Abertos (status não fechado)
$chamados_abertos = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  AND LOWER(st.fechado) = 'não'
", $params);

// 2) Concluídos (status fechado)
$concluidos = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  AND LOWER(st.fechado) = 'sim'
", $params);

// 3) Aguardando Cliente (status nome)
$aguardando_cliente = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  AND LOWER(st.fechado) = 'não'
  AND LOWER(TRIM(st.nome)) = 'aguardando cliente'
", $params);

// 4) Urgentes Ativos
$urgentes_ativos = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  AND LOWER(st.fechado) = 'não'
  AND c.prioridade = 'Urgente'
", $params);

// 5) Pendentes Suporte
// regra segura: (não fechado) e (visualizado_suporte='Não' OU última resposta do cliente)
$pendentes_suporte = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  AND LOWER(st.fechado) = 'não'
  AND (
    LOWER(c.visualizado_suporte) = 'não'
    OR (
      SELECT r3.tipo_autor
      FROM chamados_respostas r3
      WHERE r3.id = (
        SELECT MAX(r4.id)
        FROM chamados_respostas r4
        WHERE r4.chamado_id = c.id
      )
    ) = 'cliente'
  )
", $params);

// 6) Sem Responsável (dentro da permissão do usuário já filtra automaticamente)
$sem_responsavel = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  AND LOWER(st.fechado) = 'não'
  AND (c.usuario_responsavel_id IS NULL OR c.usuario_responsavel_id = 0)
", $params);

// 7) Clientes Ativos (clientes com chamados visíveis pela permissão)
$clientes_ativos = getCount($pdo, "
  SELECT COUNT(DISTINCT c.cliente_id)
  FROM chamados c
  $wherePerm
  AND c.cliente_id IS NOT NULL
  AND c.cliente_id > 0
", $params);

// 8) Chamados no Mês (mês atual) dentro da permissão
$chamados_mes = getCount($pdo, "
  SELECT COUNT(*)
  FROM chamados c
  $wherePerm
  AND YEAR(c.criado_em) = YEAR(CURDATE())
  AND MONTH(c.criado_em) = MONTH(CURDATE())
", $params);
?>

<!-- CARDS TOPO (8 CARDS) -->
<div class="row g-3 mb-3">

  <!-- 1 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-blue">
      <div class="label">
        <span class="icon"><i class="bi bi-ticket-perforated"></i></span>
        Chamados Abertos
      </div>
      <div class="value"><?= $chamados_abertos ?></div>
      <div class="watermark"><i class="bi bi-ticket-perforated"></i></div>
    </div>
  </div>

  <!-- 2 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-red">
      <div class="label">
        <span class="icon"><i class="bi bi-exclamation-triangle"></i></span>
        Urgentes Ativos
      </div>
      <div class="value"><?= $urgentes_ativos ?></div>
      <div class="watermark"><i class="bi bi-exclamation-triangle"></i></div>
    </div>
  </div>

  <!-- 3 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-orange">
      <div class="label">
        <span class="icon"><i class="bi bi-hourglass-split"></i></span>
        Aguardando Cliente
      </div>
      <div class="value"><?= $aguardando_cliente ?></div>
      <div class="watermark"><i class="bi bi-hourglass-split"></i></div>
    </div>
  </div>

  <!-- 4 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-purple">
      <div class="label">
        <span class="icon"><i class="bi bi-bell"></i></span>
        Pendentes Suporte
      </div>
      <div class="value"><?= $pendentes_suporte ?></div>
      <div class="watermark"><i class="bi bi-bell"></i></div>
    </div>
  </div>

  <!-- 5 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-green">
      <div class="label">
        <span class="icon"><i class="bi bi-check2-circle"></i></span>
        Concluídos
      </div>
      <div class="value"><?= $concluidos ?></div>
      <div class="watermark"><i class="bi bi-check2-circle"></i></div>
    </div>
  </div>

  <!-- 6 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-yellow">
      <div class="label">
        <span class="icon"><i class="bi bi-person-x"></i></span>
        Sem Responsável
      </div>
      <div class="value"><?= $sem_responsavel ?></div>
      <div class="watermark"><i class="bi bi-person-x"></i></div>
    </div>
  </div>

  <!-- 7 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-orange-dark">
      <div class="label">
        <span class="icon"><i class="bi bi-people"></i></span>
        Clientes Ativos
      </div>
      <div class="value"><?= $clientes_ativos ?></div>
      <div class="watermark"><i class="bi bi-people"></i></div>
    </div>
  </div>

  <!-- 8 -->
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-teal">
      <div class="label">
        <span class="icon"><i class="bi bi-calendar-event"></i></span>
        Chamados no Mês
      </div>
      <div class="value"><?= $chamados_mes ?></div>
      <div class="watermark"><i class="bi bi-calendar-event"></i></div>
    </div>
  </div>

</div>














<?php
// =========================
// GRÁFICO: Chamados por Status
// =========================
$stGraf = $pdo->prepare("
  SELECT
    st.id,
    st.nome,
    st.cor,
    st.ordem,
    st.fechado,
    COUNT(*) AS total
  FROM chamados c
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  GROUP BY st.id, st.nome, st.cor, st.ordem, st.fechado
  ORDER BY st.ordem ASC, st.nome ASC
");
$stGraf->execute($params);
$grafRows = $stGraf->fetchAll(PDO::FETCH_ASSOC);

$graf_labels = [];
$graf_values = [];
$graf_colors = [];

foreach ($grafRows as $g) {
  $nome = (string)($g['nome'] ?? '—');
  $cor  = trim((string)($g['cor'] ?? ''));

  if ($cor === '') $cor = '#6c757d'; // fallback
  // garante # no hex
  if ($cor[0] !== '#') $cor = '#'.$cor;

  $graf_labels[] = $nome;
  $graf_values[] = (int)($g['total'] ?? 0);
  $graf_colors[] = $cor;
}
?>

<!-- LINHA: GRÁFICO + ÚLTIMOS CHAMADOS -->
<div class="row g-3">

  <!-- VISÃO GERAL -->
  <div class="col-12 col-lg-6">
    <div class="card-soft p-3 card-section">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="section-title">
          <i class="bi bi-graph-up me-2"></i>Visão Geral
        </div>
        <div class="text-muted small">Chamados por status</div>
      </div>

      <div class="chart-wrap">
        <canvas id="chartChamados"></canvas>
      </div>
    </div>
  </div>


  <script>
  window.DASH_GRAF_STATUS = true; // sinaliza que este dashboard tem gráfico real
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  window.DASH_GRAF_STATUS = true;

  const grafLabels = <?= json_encode($graf_labels, JSON_UNESCAPED_UNICODE) ?>;
  const grafValues = <?= json_encode($graf_values, JSON_UNESCAPED_UNICODE) ?>;
  const grafColors = <?= json_encode($graf_colors, JSON_UNESCAPED_UNICODE) ?>;

  const canvas = document.getElementById('chartChamados');
  if (!canvas || typeof Chart === 'undefined') return;

  if (window.__chartChamados) window.__chartChamados.destroy();

  window.__chartChamados = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: grafLabels,
      datasets: [{ data: grafValues, backgroundColor: grafColors, borderWidth: 0 }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: { legend: { position: 'bottom' } }
    }
  });
});
</script>




<?php
// =========================
// ÚLTIMOS CHAMADOS (reais)
// =========================
$stUlt = $pdo->prepare("
  SELECT
    c.id,
    c.protocolo,
    c.assunto,
    cli.nome AS cliente_nome,
    st.nome  AS status_nome,
    st.cor   AS status_cor,
    st.fechado AS status_fechado
  FROM chamados c
  LEFT JOIN clientes cli       ON cli.id = c.cliente_id
  INNER JOIN chamados_status st ON st.id = c.status_id
  $wherePerm
  ORDER BY c.id DESC
  LIMIT 4
");
$stUlt->execute($params);
$ultimos = $stUlt->fetchAll(PDO::FETCH_ASSOC);

// função auxiliar p/ badge (padrão bootstrap)
function badgeClassFromHex($hex){
  $hex = strtolower(trim((string)$hex));
  if ($hex === '') return 'secondary';

  // mapeamento simples por cor (ajuste se quiser)
  if (strpos($hex,'#ef') === 0 || strpos($hex,'#dc') === 0) return 'danger';   // vermelhos
  if (strpos($hex,'#f5') === 0 || strpos($hex,'#d9') === 0) return 'warning';  // amarelos/laranjas
  if (strpos($hex,'#16') === 0 || strpos($hex,'#19') === 0) return 'success';  // verdes
  if (strpos($hex,'#25') === 0 || strpos($hex,'#1d') === 0) return 'primary';  // azuis
  if (strpos($hex,'#6f') === 0) return 'info';                                  // roxos/azuis claros
  return 'secondary';
}
?>


<?php
function contrastTextColor($hex){
  $hex = trim((string)$hex);
  if ($hex === '') return '#fff';
  if ($hex[0] === '#') $hex = substr($hex, 1);

  if (strlen($hex) === 3) {
    $r = hexdec(str_repeat($hex[0], 2));
    $g = hexdec(str_repeat($hex[1], 2));
    $b = hexdec(str_repeat($hex[2], 2));
  } else {
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
  }

  $luma = (0.2126*$r + 0.7152*$g + 0.0722*$b);
  return ($luma > 150) ? '#0f172a' : '#ffffff';
}
?>

  <!-- ÚLTIMOS CHAMADOS (VISUAL MAIS PRÓXIMO DO MODELO) -->
  <div class="col-12 col-lg-6">
    <div class="card-soft p-0 card-section">
      <div class="card-head">
        <div class="section-title mb-0">
          <i class="bi bi-clock-history me-2"></i>Últimos Chamados
        </div>

        <a href="abertura" class="btn btn-sm btn-outline-primary btn-soft">
          Ver todos
        </a>
      </div>

      <div class="table-responsive px-3 pb-3">
        <table class="table table-clean align-middle mb-0">
          <thead>
            <tr>
              <th class="col-id">#</th>
              <th>Assunto</th>
              <th>Cliente</th>
              <th class="text-end">Status</th>
            </tr>
          </thead>
          <tbody>
<?php if (!count($ultimos)) { ?>
  <tr>
    <td colspan="4" class="text-muted small py-3">Nenhum chamado encontrado.</td>
  </tr>
<?php } else { ?>
  <?php foreach ($ultimos as $i => $c) {

    $assunto = $c['assunto'] ?? '';
    $cliente = $c['cliente_nome'] ?? '—';
    $status  = $c['status_nome'] ?? '—';
    $corHex  = $c['status_cor'] ?? '';
    $badge   = badgeClassFromHex($corHex);

    // (opcional) link do chamado pelo protocolo
    $protocolo = $c['protocolo'] ?? '';
    $idChamado = (int)($c['id'] ?? 0);
  ?>
    <tr>
      <td class="col-id"><?= $i+1 ?></td>

      <td class="fw-semibold">
        <?= htmlspecialchars($assunto) ?>
        <?php if ($protocolo) { ?>
          <div class="text-muted small">#<?= htmlspecialchars($protocolo) ?></div>
        <?php } ?>
      </td>

      <td class="text-muted"><?= htmlspecialchars($cliente) ?></td>

      <td class="text-end">
      
      <?php
$corHex = trim((string)($c['status_cor'] ?? ''));
if ($corHex !== '' && $corHex[0] !== '#') $corHex = '#'.$corHex;
if ($corHex === '') $corHex = '#6c757d';
?>
<span class="badge badge-soft"
      style="background-color: <?= htmlspecialchars($corHex) ?> !important;
             color: #fff !important;">
  <?= htmlspecialchars($status) ?>
</span>

    </td>
    </tr>
  <?php } ?>
<?php } ?>
</tbody>
        </table>
      </div>
    </div>
  </div>

</div>





<div class="row g-3 mt-1">



<?php
// =========================
// GRÁFICO: Chamados por dia (últimos 14 dias)
// =========================
$stDias = $pdo->prepare("
  SELECT
    DATE(c.criado_em) AS dia,
    COUNT(*) AS total
  FROM chamados c
  $wherePerm
  AND c.criado_em >= (CURDATE() - INTERVAL 13 DAY)
  GROUP BY DATE(c.criado_em)
  ORDER BY dia ASC
");
$stDias->execute($params);
$rowsDias = $stDias->fetchAll(PDO::FETCH_ASSOC);

// monta lista completa dos últimos 14 dias (pra não “furar” no gráfico)
$dias_labels = [];
$dias_values = [];

$map = [];
foreach ($rowsDias as $r) {
  $map[$r['dia']] = (int)$r['total'];
}

for ($i = 13; $i >= 0; $i--) {
  $d = date('Y-m-d', strtotime("-{$i} day"));
  $dias_labels[] = date('d/m', strtotime($d));
  $dias_values[] = $map[$d] ?? 0;
}
?>



<div class="col-12 col-lg-6">
  <div class="card-soft p-3 card-section">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="section-title"><i class="bi bi-activity me-2"></i>Últimos 14 dias</div>
      <div class="text-muted small">Chamados abertos por dia</div>
    </div>
    <div class="chart-wrap" style="height:260px;">
      <canvas id="chartDias"></canvas>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('chartDias');
  if (!el || typeof Chart === 'undefined') return;

  const labels = <?= json_encode($dias_labels, JSON_UNESCAPED_UNICODE) ?>;
  const values = <?= json_encode($dias_values, JSON_UNESCAPED_UNICODE) ?>;

  if (window.__chartDias) window.__chartDias.destroy();

  window.__chartDias = new Chart(el, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Chamados',
        data: values,
        tension: 0.35,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
});
</script>







<?php
// =========================
// GRÁFICO: Chamados por Setor (Top 6)
// =========================
$stSetor = $pdo->prepare("
  SELECT
    se.nome AS setor,
    COUNT(*) AS total
  FROM chamados c
  LEFT JOIN setores se ON se.id = c.setor_id
  $wherePerm
  GROUP BY se.nome
  ORDER BY total DESC
  LIMIT 6
");
$stSetor->execute($params);
$rowsSetor = $stSetor->fetchAll(PDO::FETCH_ASSOC);

$setor_labels = [];
$setor_values = [];

foreach ($rowsSetor as $r) {
  $setor_labels[] = (string)($r['setor'] ?? 'Sem setor');
  $setor_values[] = (int)($r['total'] ?? 0);
}
?>


<div class="col-12 col-lg-6">
  <div class="card-soft p-3 card-section">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="section-title"><i class="bi bi-diagram-3 me-2"></i>Por Setor</div>
      <div class="text-muted small">Top 6 setores</div>
    </div>
    <div class="chart-wrap" style="height:260px;">
      <canvas id="chartSetor"></canvas>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('chartSetor');
  if (!el || typeof Chart === 'undefined') return;

  const labels = <?= json_encode($setor_labels, JSON_UNESCAPED_UNICODE) ?>;
  const values = <?= json_encode($setor_values, JSON_UNESCAPED_UNICODE) ?>;

  if (window.__chartSetor) window.__chartSetor.destroy();

  window.__chartSetor = new Chart(el, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Chamados',
        data: values,
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });
});
</script>



</div>
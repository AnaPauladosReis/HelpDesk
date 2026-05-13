<?php
// Depois você troca esses números por consultas reais no banco
$chamados_abertos = 12;
$concluidos = 85;
$clientes = 230;
$usuarios = 15;

// Mock dos últimos chamados
$ultimos = [
  ['assunto'=>'Problema no servidor',      'cliente'=>'João Souza',     'status'=>'Aberto',   'badge'=>'success'],
  ['assunto'=>'Atualização do Sistema',    'cliente'=>'Ana Lima',       'status'=>'Pendente', 'badge'=>'warning'],
  ['assunto'=>'Dúvida sobre contrato',     'cliente'=>'Marcelo Farias', 'status'=>'Pendente', 'badge'=>'warning'],
  ['assunto'=>'Erro de login',            'cliente'=>'Patrícia Melo',  'status'=>'Pendente', 'badge'=>'warning'],
];
?>


<!-- CARDS TOPO (MODELO) -->
<div class="row g-3 mb-3">
  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-blue">
      <div class="label">
        <span class="icon"><i class="bi bi-ticket-perforated"></i></span>
        Chamados Abertos
      </div>
      <div class="value"><?= (int)$chamados_abertos ?></div>
      <div class="watermark"><i class="bi bi-ticket-perforated"></i></div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-green">
      <div class="label">
        <span class="icon"><i class="bi bi-check2-circle"></i></span>
        Chamados Concluídos
      </div>
      <div class="value"><?= (int)$concluidos ?></div>
      <div class="watermark"><i class="bi bi-check2-circle"></i></div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-orange">
      <div class="label">
        <span class="icon"><i class="bi bi-people"></i></span>
        Clientes
      </div>
      <div class="value"><?= (int)$clientes ?></div>
      <div class="watermark"><i class="bi bi-people"></i></div>
    </div>
  </div>

  <div class="col-12 col-md-6 col-xl-3">
    <div class="stat-card bg-red">
      <div class="label">
        <span class="icon"><i class="bi bi-person-gear"></i></span>
        Usuários
      </div>
      <div class="value"><?= (int)$usuarios ?></div>
      <div class="watermark"><i class="bi bi-person-gear"></i></div>
    </div>
  </div>
</div>

<!-- LINHA: GRÁFICO + ÚLTIMOS CHAMADOS -->
<div class="row g-3">

  <!-- VISÃO GERAL -->
  <div class="col-12 col-lg-7">
    <div class="card-soft p-3 card-section">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="section-title">
          <i class="bi bi-graph-up me-2"></i>Visão Geral
        </div>
        <div class="text-muted small">Chamados por mês</div>
      </div>

      <div class="chart-wrap">
        <canvas id="chartChamados"></canvas>
      </div>
    </div>
  </div>

  <!-- ÚLTIMOS CHAMADOS (VISUAL MAIS PRÓXIMO DO MODELO) -->
  <div class="col-12 col-lg-5">
    <div class="card-soft p-0 card-section">
      <div class="card-head">
        <div class="section-title mb-0">
          <i class="bi bi-clock-history me-2"></i>Últimos Chamados
        </div>

        <a href="index.php?p=chamados" class="btn btn-sm btn-outline-primary btn-soft">
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
          <?php foreach ($ultimos as $i => $c) { ?>
            <tr>
              <td class="col-id"><?= $i+1 ?></td>
              <td class="fw-semibold"><?= htmlspecialchars($c['assunto']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($c['cliente']) ?></td>
              <td class="text-end">
                <span class="badge badge-soft text-bg-<?= $c['badge'] ?>">
                  <?= htmlspecialchars($c['status']) ?>
                </span>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

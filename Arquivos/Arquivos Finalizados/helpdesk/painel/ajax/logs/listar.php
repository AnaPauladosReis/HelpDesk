<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: text/html; charset=utf-8');

$tabela = 'logs';

function esc($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// =========================
// FILTROS
// =========================
$data_ini   = trim($_GET['data_ini'] ?? '');
$data_fim   = trim($_GET['data_fim'] ?? '');
$usuario_id = (int)($_GET['usuario_id'] ?? 0);
$acao       = trim($_GET['acao'] ?? '');

// como você ainda não usa SaaS:
$empresa = (int)($_SESSION['empresa'] ?? 0);

$where = [];
$params = [];

// ⚠️ prefixar com alias por causa do JOIN (empresa existe em usuarios também)
$where[] = "l.empresa = :empresa";
$params[':empresa'] = $empresa;

// filtro por usuário (opcional)
if ($usuario_id > 0) {
  $where[] = "l.usuario_id = :usuario_id";
  $params[':usuario_id'] = $usuario_id;
}

// filtro por ação (opcional) - garante só valores permitidos
$acoesPermitidas = ['login','logout','inserir','editar','excluir'];
if ($acao !== '') {
  $acao = strtolower($acao);
  if (in_array($acao, $acoesPermitidas, true)) {
    $where[] = "l.acao = :acao";
    $params[':acao'] = $acao;
  }
}

// filtro por data (opcional)
if ($data_ini !== '' && $data_fim !== '') {
  $where[] = "DATE(l.criado_em) BETWEEN :ini AND :fim";
  $params[':ini'] = $data_ini;
  $params[':fim'] = $data_fim;
} elseif ($data_ini !== '') {
  $where[] = "DATE(l.criado_em) >= :ini";
  $params[':ini'] = $data_ini;
} elseif ($data_fim !== '') {
  $where[] = "DATE(l.criado_em) <= :fim";
  $params[':fim'] = $data_fim;
}

// =========================
// SQL (JOIN usuários para nome)
// =========================
$sql = "
  SELECT
    l.*,
    u.nome  AS usuario_nome,
    u.email AS usuario_email
  FROM {$tabela} l
  LEFT JOIN usuarios u
    ON u.id = l.usuario_id
   AND u.empresa = l.empresa
";

if (!empty($where)) {
  $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY l.criado_em DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-3 p-md-4">

    <div class="table-responsive">
      <table class="table table-sm align-middle w-100" id="tabela">
        <thead class="table-light">
          <tr>
            <th style="width:160px;">Data</th>
            <th style="width:110px;">Ação</th>
            <th style="width:140px;">Entidade</th>
            <th style="width:110px;">ID</th>
            <th>Descrição</th>
            <th style="width:240px;">Usuário</th>
            <th style="width:140px;">IP</th>
          </tr>
        </thead>

        <tbody>
        <?php if (count($rows) === 0) { ?>
          </tbody>
        </table>

        <div class="text-muted small py-3">
          Nenhum log encontrado com os filtros selecionados.
        </div>

        <?php } else { ?>

          <?php foreach ($rows as $r) {

            $data = '';
            if (!empty($r['criado_em'])) {
              $ts = strtotime((string)$r['criado_em']);
              if ($ts) $data = date('d/m/Y H:i', $ts);
            }

            $acaoRow   = (string)($r['acao'] ?? '');
            $entidade  = esc($r['entidade'] ?? '');
            $regId     = (int)($r['registro_id'] ?? 0);
            $descricao = esc($r['descricao'] ?? '');
            $ip        = esc($r['ip'] ?? '');

            $uid    = (int)($r['usuario_id'] ?? 0);
            $unome  = esc($r['usuario_nome'] ?? '');
            $uemail = esc($r['usuario_email'] ?? '');

            // badge por tipo
            $badge = 'secondary';
            if ($acaoRow === 'login')   $badge = 'success';
            if ($acaoRow === 'logout')  $badge = 'warning';
            if ($acaoRow === 'inserir') $badge = 'primary';
            if ($acaoRow === 'editar')  $badge = 'info';
            if ($acaoRow === 'excluir') $badge = 'danger';

            $acaoLabel = $acaoRow !== '' ? ucfirst($acaoRow) : '—';
          ?>
            <tr>
              <td class="small"><?= esc($data) ?></td>

              <td>
                <span class="badge text-bg-<?= $badge ?>">
                  <?= esc($acaoLabel) ?>
                </span>
              </td>

              <td class="fw-semibold"><?= $entidade ?: '-' ?></td>

              <td><?= $regId > 0 ? $regId : '-' ?></td>

              <td class="small"><?= $descricao ?: '-' ?></td>

              <td class="small">
                <?php if ($uid > 0 && $unome !== '') { ?>
                  <div class="fw-semibold"><?= $unome ?></div>
                  <div class="text-muted"><?= $uemail !== '' ? $uemail : ("#".$uid) ?></div>
                <?php } elseif ($uid > 0) { ?>
                  <div class="fw-semibold">Usuário #<?= (int)$uid ?></div>
                  <div class="text-muted">—</div>
                <?php } else { ?>
                  <span class="text-muted">Sistema</span>
                <?php } ?>
              </td>

              <td class="small"><?= $ip ?: '-' ?></td>
            </tr>
          <?php } ?>

        <?php } ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
(function () {
  if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;

  if (jQuery.fn.DataTable.isDataTable('#tabela')) {
    jQuery('#tabela').DataTable().destroy();
  }

  jQuery('#tabela').DataTable({
    responsive: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    order: [[0, 'desc']],
    language: {
      url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json"
    }
  });
})();
</script>

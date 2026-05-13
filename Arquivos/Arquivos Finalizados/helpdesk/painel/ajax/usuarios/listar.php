<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';

header('Content-Type: text/html; charset=utf-8');

$tabela = 'usuarios';

// Como você ainda não usa SaaS:
$stmt = $pdo->query("SELECT * FROM {$tabela} ORDER BY id DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function esc($v) {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function fotoUrl($foto) {
  $foto = trim((string)$foto);
  if ($foto === '') $foto = 'sem_foto.webp';
  return "../uploads/perfil/" . $foto;
}
?>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-3 p-md-4">

    <div class="table-responsive">
      <table class="table table-sm align-middle w-100" id="tabela">
        <thead class="table-light">
          <tr>
          <th style="width:40px;" class="text-center">
            <input type="checkbox" id="checkAll">
          </th>
            <th style="width:48px;">Foto</th>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Nível</th>
            <th>Ativo</th>
            <th style="width:170px;" class="text-end">Ações</th>
          </tr>
        </thead>

        <tbody>
        <?php if (count($rows) === 0) { ?>
          <tr>
            <td colspan="6" class="text-muted small py-3">
              Nenhum usuário cadastrado.
            </td>
          </tr>
        <?php } else { ?>

          <?php foreach ($rows as $r) {
            $id    = (int)($r['id'] ?? 0);
            $nome  = esc($r['nome'] ?? '');
            $email = esc($r['email'] ?? '');
            $foto  = fotoUrl($r['foto'] ?? 'sem_foto.webp');
            $cpf = esc($r['cpf'] ?? '');
            $assinatura = esc($r['assinatura'] ?? '');

            $nivelRaw = trim((string)($r['nivel'] ?? 'Comum'));
            $ativoRaw = (string)($r['ativo'] ?? 'Sim');

            $badgeNivel = ($nivelRaw === 'Administrador')
              ? '<span class="badge rounded-pill text-bg-primary">Administrador</span>'
              : '<span class="badge rounded-pill text-bg-secondary">'.$r['nivel'].'</span>';

            $badgeAtivo = (strtolower($ativoRaw) === 'não' || strtolower($ativoRaw) === 'nao')
              ? '<span class="badge rounded-pill text-bg-danger">Não</span>'
              : '<span class="badge rounded-pill text-bg-success">Sim</span>';
          ?>
            <tr>
            <td class="text-center">
  <input type="checkbox" class="checkRow" value="<?= $id ?>">
</td>
              <td>
                <img src="<?= esc($foto) ?>"
                     alt="Foto"
                     style="width:36px;height:36px;object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;">
              </td>

              <td class="fw-semibold"><?= $nome ?> <span style="display:none"><?= $cpf ?></span></td>
              <td><?= $email ?></td>
              <td><?= $badgeNivel ?></td>
              <td><?= $badgeAtivo ?></td>

              <td class="text-end">
                
              <?php
$isAdmin = ($nivelRaw === 'Administrador');
?>

<div class="btn-group btn-group-sm" role="group" aria-label="Ações">

  <?php if (!$isAdmin) { ?>
    <button type="button"
            class="btn btn-outline-secondary"
            onclick="permissoes(<?= $id ?>)"
            title="Permissões">
      <i class="bi bi-shield-lock"></i>
    </button>
  <?php } ?>

  <?php if (podeFazer('editar')) { ?>
  <button class="btn btn-outline-primary" onclick="editar(<?= $id ?>)">
    <i class="bi bi-pencil"></i>
  </button>
<?php } ?>




  <button type="button" class="btn btn-outline-dark" onclick="mostrar(<?= $id ?>)" title="Mostrar dados">
    <i class="bi bi-eye"></i>
  </button>

  <?php if (podeFazer('excluir')) { ?>
  <button class="btn btn-outline-danger" onclick="excluir(<?= $id ?>)">
    <i class="bi bi-trash"></i>
  </button>
<?php } ?>


<?php if (podeFazer('editar')) { ?>
                  <button type="button"
        class="btn btn-outline-info"
        onclick="resetarSenha(<?= $id ?>)"
        title="Resetar senha">
  <i class="bi bi-key"></i>
</button>
<?php } ?>




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
    order: [[1, 'asc']], // <- vírgula aqui é obrigatória
    columnDefs: [
      { orderable: false, targets: [0, 5] }
    ],
    language: {
      // ✅ se você não quer CORS, depois trocamos por objeto local (eu te mando)
      url: "//cdn.datatables.net/plug-ins/1.13.8/i18n/pt-BR.json"
    }
  });
})();


</script>


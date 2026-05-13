<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../funcoes/email.php';

$tabela = 'chamados';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  if (!podeFazer('editar')) out(false, 'Sem permissão para editar.');

  // aceita POST normal e JSON
  $input = [];
  if (!empty($_POST)) {
    $input = $_POST;
  } else {
    $raw = file_get_contents('php://input');
    if ($raw) {
      $j = json_decode($raw, true);
      if (json_last_error() === JSON_ERROR_NONE && is_array($j)) $input = $j;
    }
  }

  $id = isset($input['id']) ? (int)$input['id'] : 0;
  if ($id <= 0) out(false, 'ID inválido.');

  // quem está alterando
  $usuario_id = (int)($_SESSION['id'] ?? 0);
  if ($usuario_id <= 0) out(false, 'Sessão inválida. Faça login novamente.');
  

  // Campos permitidos
  $temStatus = array_key_exists('status_id', $input);
  $temPrio   = array_key_exists('prioridade', $input);

  if (!$temStatus && !$temPrio) out(false, 'Nada para atualizar.');

  $novoStatusId = $temStatus ? (int)$input['status_id'] : 0;
  $novaPrio = $temPrio ? trim((string)$input['prioridade']) : '';

  // valida prioridade
  $prioridadesValidas = ['Baixa','Media','Alta','Urgente'];
  if ($temPrio) {
    // normaliza possíveis entradas
    $map = ['Média'=>'Media','Media'=>'Media','Baixa'=>'Baixa','Alta'=>'Alta','Urgente'=>'Urgente'];
    $novaPrio = $map[$novaPrio] ?? $novaPrio;
    if (!in_array($novaPrio, $prioridadesValidas, true)) out(false, 'Prioridade inválida.');
  }

  // pega dados atuais
  $stOld = $pdo->prepare("
    SELECT
      c.id, c.protocolo, c.status_id, c.prioridade, c.fechado_em,
      st.fechado AS status_fechado
    FROM chamados c
    INNER JOIN chamados_status st ON st.id = c.status_id
    WHERE c.id = :id
    LIMIT 1
  ");
  $stOld->execute([':id' => $id]);
  $old = $stOld->fetch(PDO::FETCH_ASSOC);
  if (!$old) out(false, 'Chamado não encontrado.');

  $protocolo = (string)($old['protocolo'] ?? '');
  $oldStatusId = (int)($old['status_id'] ?? 0);
  $oldPrio = (string)($old['prioridade'] ?? 'Media');
  $oldFechadoEm = $old['fechado_em'] ?? null;
  $oldIsFechado = (strtolower((string)($old['status_fechado'] ?? 'não')) === 'sim');

  $updates = [];
  $params  = [':id' => $id];

  // para movimentos
  $movimentos = [];

  // ===== STATUS =====
  $staNovo = null;
  $novoIsFechado = $oldIsFechado;

  if ($temStatus) {
    if ($novoStatusId <= 0) out(false, 'Status inválido.');

    $stSta = $pdo->prepare("SELECT id, nome, fechado FROM chamados_status WHERE id = :id AND ativo='Sim' LIMIT 1");
    $stSta->execute([':id' => $novoStatusId]);
    $staNovo = $stSta->fetch(PDO::FETCH_ASSOC);
    if (!$staNovo) out(false, 'Status inválido.');

    $novoIsFechado = (strtolower((string)$staNovo['fechado']) === 'sim');

    if ($novoStatusId !== $oldStatusId) {
      $updates[] = "status_id = :status_id";
      $params[':status_id'] = $novoStatusId;

      // fechado_em: preserva o primeiro fechamento
      if ($novoIsFechado) {
        // se estava aberto e agora fechou, e ainda não tem fechado_em -> seta agora
        if (!$oldIsFechado && (empty($oldFechadoEm) || $oldFechadoEm === '0000-00-00 00:00:00')) {
          $updates[] = "fechado_em = NOW()";
        }
        // se já estava fechado antes, não mexe no fechado_em (preserva)
      } else {
        // reabriu -> limpa fechado_em
        $updates[] = "fechado_em = NULL";
      }

      // movimento de status (sempre quando mudar)
      $movimentos[] = [
        'tipo' => 'status',
        'status_de' => ($oldStatusId ?: null),
        'status_para' => $novoStatusId,
        'mensagem' => "Status alterado para: " . (string)$staNovo['nome']
      ];
    }
  }

  // ===== PRIORIDADE =====
  if ($temPrio) {
    if ($novaPrio !== $oldPrio) {
      $updates[] = "prioridade = :prioridade";
      $params[':prioridade'] = $novaPrio;

      $movimentos[] = [
        'tipo' => 'sistema',
        'status_de' => null,
        'status_para' => null,
        'mensagem' => "Prioridade: {$oldPrio} → {$novaPrio}"
      ];
    }
  }

  if (!count($updates)) {
    out(true, 'Nenhuma alteração a aplicar.');
  }

  // sempre atualiza atualizado_em
  $updates[] = "atualizado_em = NOW()";

  $pdo->beginTransaction();

  // update
  $sqlUp = "UPDATE chamados SET " . implode(", ", $updates) . " WHERE id = :id LIMIT 1";
  $stUp = $pdo->prepare($sqlUp);
  $stUp->execute($params);

  // movimentos
  if (count($movimentos)) {
    $stMov = $pdo->prepare("
      INSERT INTO chamados_movimentos
        (chamado_id, usuario_id, tipo, status_de, status_para, mensagem, criado_em)
      VALUES
        (:chamado_id, :usuario_id, :tipo, :status_de, :status_para, :mensagem, NOW())
    ");

    foreach ($movimentos as $m) {
      $stMov->execute([
        ':chamado_id' => $id,
        ':usuario_id' => $usuario_id,
        ':tipo' => $m['tipo'],
        ':status_de' => $m['status_de'],
        ':status_para' => $m['status_para'],
        ':mensagem' => $m['mensagem'],
      ]);
    }
  }

  // log
  $resumo = [];
  if ($temStatus && $staNovo && $novoStatusId !== $oldStatusId) $resumo[] = "status -> {$staNovo['nome']}";
  if ($temPrio && $novaPrio !== $oldPrio) $resumo[] = "prioridade -> {$novaPrio}";
  $txt = count($resumo) ? implode(' | ', $resumo) : 'Atualização rápida';
  registrarLog($pdo, 'editar', $tabela, $id, "Chamado '{$protocolo}' atualizado (rápido): {$txt}");

  // marque que deve enviar e guarde o nome do novo status
  $emailDeveEnviar = true;
  $emailNovoStatusNome = (string)($staNovo['nome'] ?? '');


  $pdo->commit();

  // =========================
// DISPARO DE E-MAIL (somente se mudou status)
// =========================
if (!empty($emailDeveEnviar)) {
  try {
    // busca dados do chamado + cliente (email/nome) + status antigo/novo
    $stE = $pdo->prepare("
      SELECT
        c.protocolo,
        c.assunto,
        cli.nome  AS cliente_nome,
        cli.email AS cliente_email,
        stOld.nome AS status_old_nome
      FROM chamados c
      LEFT JOIN clientes cli ON cli.id = c.cliente_id
      INNER JOIN chamados_status stOld ON stOld.id = :old_status_id
      WHERE c.id = :id
      LIMIT 1
    ");
    $stE->execute([
      ':id' => $id,
      ':old_status_id' => $oldStatusId
    ]);
    $e = $stE->fetch(PDO::FETCH_ASSOC) ?: [];

    $clienteNome  = trim((string)($e['cliente_nome'] ?? 'Cliente'));
    $clienteEmail = trim((string)($e['cliente_email'] ?? ''));
    $protoc       = trim((string)($e['protocolo'] ?? $protocolo));
    $assuntoCham  = trim((string)($e['assunto'] ?? ''));
    $statusOldNm  = trim((string)($e['status_old_nome'] ?? ''));
    $statusNewNm  = trim((string)($emailNovoStatusNome ?? ''));

    if ($clienteEmail !== '' && filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {

      $nomeSistema = (string)($nome_sistema ?? 'HelpDesk');

      $assuntoEmail = "Atualização do chamado ({$protoc}) - {$nomeSistema}";
      $titulo = "Status do chamado atualizado";

      $mensagemHtml = "
        <p>Olá <strong>" . htmlspecialchars($clienteNome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>O status do seu chamado foi atualizado.</p>

        <ul style='margin:0; padding-left:18px;'>
          <li><b>Protocolo:</b> " . htmlspecialchars($protoc, ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Assunto:</b> " . htmlspecialchars($assuntoCham, ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Status anterior:</b> " . htmlspecialchars($statusOldNm ?: '—', ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Novo status:</b> " . htmlspecialchars($statusNewNm ?: '—', ENT_QUOTES, 'UTF-8') . "</li>
        </ul>

        <p style='margin-top:10px;'>Em caso de dúvidas, responda este e-mail para falar com nossa equipe.</p>
      ";

      $htmlEmail = emailTemplatePadrao($titulo, $mensagemHtml, '', '', '');

      $env = enviarEmailPadrao($clienteEmail, $clienteNome, $assuntoEmail, $htmlEmail);

      if (!(bool)($env['ok'] ?? false)) {
        error_log("Falha e-mail update status chamado {$protoc}: " . (string)($env['erro'] ?? ''));
      }
    }
  } catch (Throwable $e) {
    error_log("Exceção e-mail update status chamado #{$id}: " . $e->getMessage());
  }
}


  out(true, 'Atualizado com sucesso!', ['id' => $id]);

} catch (Throwable $e) {
  if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
  out(false, 'Erro ao atualizar: ' . $e->getMessage());
}

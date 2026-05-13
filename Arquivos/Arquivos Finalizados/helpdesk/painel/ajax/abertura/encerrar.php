<?php
@session_start();

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../funcoes/email.php';

header('Content-Type: application/json; charset=utf-8');

function jexit($ok, $msg = '') {
  echo json_encode(['ok' => (bool)$ok, 'msg' => (string)$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

if (!podeFazer('editar')) {
  jexit(false, 'Sem permissão.');
}

try {

  $raw = file_get_contents('php://input');
  $json = json_decode($raw, true);

  $id = (int)($json['id'] ?? 0);
  if ($id <= 0) jexit(false, 'ID inválido.');

  $usuarioLogado = (int)($_SESSION['id'] ?? 0);
  if ($usuarioLogado <= 0) jexit(false, 'Sessão inválida.');

  // ✅ garante que o usuario logado existe (evita FK no movimentos)
  $stChk = $pdo->prepare("SELECT 1 FROM usuarios WHERE id = :id LIMIT 1");
  $stChk->execute([':id' => $usuarioLogado]);
  $usuarioExiste = (bool)$stChk->fetchColumn();

  // Busca status "fechado" (o primeiro por ordem)
  $stStatus = $pdo->prepare("
    SELECT id, nome
    FROM chamados_status
    WHERE fechado = 'Sim' AND ativo = 'Sim'
    ORDER BY ordem ASC
    LIMIT 1
  ");
  $stStatus->execute();
  $statusFechado = $stStatus->fetch(PDO::FETCH_ASSOC);

  if (!$statusFechado) {
    jexit(false, 'Nenhum status configurado como fechado.');
  }

  $statusId   = (int)$statusFechado['id'];
  $statusNome = (string)($statusFechado['nome'] ?? 'Fechado');

  // Busca chamado + dados para disparo
  $stCham = $pdo->prepare("
    SELECT
      c.id,
      c.protocolo,
      c.assunto,
      c.status_id,
      c.cliente_id,
      c.usuario_responsavel_id,

      cli.nome     AS cliente_nome,
      cli.email    AS cliente_email,
      cli.telefone AS cliente_telefone,

      ur.nome      AS resp_nome,
      ur.email     AS resp_email,
      ur.telefone  AS resp_telefone

    FROM chamados c
    LEFT JOIN clientes cli ON cli.id = c.cliente_id
    LEFT JOIN usuarios ur  ON ur.id  = c.usuario_responsavel_id
    WHERE c.id = :id
    LIMIT 1
  ");
  $stCham->execute([':id' => $id]);
  $ch = $stCham->fetch(PDO::FETCH_ASSOC);

  if (!$ch) jexit(false, 'Chamado não encontrado.');

  if ((int)$ch['status_id'] === $statusId) {
    jexit(false, 'Chamado já está encerrado.');
  }

  $protocolo = (string)($ch['protocolo'] ?? '');
  $assunto   = (string)($ch['assunto'] ?? '');

  $pdo->beginTransaction();

  // Atualiza chamado
  $upd = $pdo->prepare("
    UPDATE chamados
       SET status_id = :status,
           fechado_em = NOW(),
           atualizado_em = NOW()
     WHERE id = :id
     LIMIT 1
  ");
  $upd->execute([
    ':status' => $statusId,
    ':id' => $id
  ]);

  // Registra movimento (só se o usuário existir pra não violar FK)
  if ($usuarioExiste) {
    $insMov = $pdo->prepare("
      INSERT INTO chamados_movimentos
        (chamado_id, usuario_id, tipo, status_de, status_para, mensagem, criado_em)
      VALUES
        (:chamado, :usuario, 'encerramento', :de, :para, :mensagem, NOW())
    ");

    $insMov->execute([
      ':chamado' => $id,
      ':usuario' => $usuarioLogado,
      ':de' => (int)$ch['status_id'],
      ':para' => $statusId,
      ':mensagem' => "Chamado encerrado (status: {$statusNome})"
    ]);
  }

  // Log do sistema
  registrarLog(
    $pdo,
    'editar',
    'chamados',
    $id,
    "Chamado encerrado: {$protocolo}"
  );

  $pdo->commit();

  // =========================================================
  // ✅ DISPAROS (fora da transaction, sem quebrar o encerramento)
  // =========================================================

  $clienteNome  = (string)($ch['cliente_nome'] ?? '');
  $clienteEmail = (string)($ch['cliente_email'] ?? '');
  $clienteTel   = (string)($ch['cliente_telefone'] ?? '');

  $respNome  = (string)($ch['resp_nome'] ?? '');
  $respEmail = (string)($ch['resp_email'] ?? '');
  $respTel   = (string)($ch['resp_telefone'] ?? '');

  // ---- WhatsApp Cliente
  try {
    if (($api_whatsapp ?? 'Nenhuma') !== 'Nenhuma' && trim($clienteTel) !== '') {
      $telefone_disparo = preg_replace('/\D+/', '', $clienteTel);

      $mensagem_whatsapp =
        "✅ Chamado encerrado\n\n" .
        "📌 Protocolo: {$protocolo}\n" .
        "📝 Assunto: {$assunto}\n" .
        "📍 Status: {$statusNome}\n\n" .
        "Se precisar, é só responder por aqui.";

      require __DIR__ . '/../../apis/texto_whatsapp.php';
    }
  } catch (Throwable $e) {
    error_log("Falha WhatsApp cliente encerrar {$protocolo}: " . $e->getMessage());
  }

  // ---- WhatsApp Responsável
  try {
    if (($api_whatsapp ?? 'Nenhuma') !== 'Nenhuma' && trim($respTel) !== '') {
      $telefone_disparo = preg_replace('/\D+/', '', $respTel);

      $mensagem_whatsapp =
        "✅ Chamado encerrado\n\n" .
        "📌 Protocolo: {$protocolo}\n" .
        "📝 Assunto: {$assunto}\n" .
        "📍 Status: {$statusNome}";

      require __DIR__ . '/../../apis/texto_whatsapp.php';
    }
  } catch (Throwable $e) {
    error_log("Falha WhatsApp responsável encerrar {$protocolo}: " . $e->getMessage());
  }

  // ---- Email Cliente
  try {
    if ($clienteEmail !== '' && filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {
      $assuntoEmail = "Chamado encerrado ({$protocolo}) - " . ($nome_sistema ?? 'HelpDesk');
      $titulo = "Seu chamado foi encerrado";

      $mensagemHtml = "
        <p>Olá <strong>" . htmlspecialchars($clienteNome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Seu chamado foi encerrado.</p>
        <ul style='margin:0; padding-left:18px;'>
          <li><b>Protocolo:</b> " . htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Assunto:</b> " . htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Status:</b> " . htmlspecialchars($statusNome, ENT_QUOTES, 'UTF-8') . "</li>
        </ul>
        <p style='margin-top:10px;'>Se precisar, responda este e-mail ou abra um novo chamado.</p>
      ";

      $htmlEmail = emailTemplatePadrao($titulo, $mensagemHtml, '', '', '');
      $env = enviarEmailPadrao($clienteEmail, $clienteNome, $assuntoEmail, $htmlEmail);

      if (!(bool)($env['ok'] ?? false)) {
        error_log("Falha e-mail cliente encerrar {$protocolo}: " . (string)($env['erro'] ?? ''));
      }
    }
  } catch (Throwable $e) {
    error_log("Exceção e-mail cliente encerrar {$protocolo}: " . $e->getMessage());
  }

  // ---- Email Responsável
  try {
    if ($respEmail !== '' && filter_var($respEmail, FILTER_VALIDATE_EMAIL)) {
      $assuntoEmail = "Chamado encerrado ({$protocolo}) - " . ($nome_sistema ?? 'HelpDesk');
      $titulo = "Chamado encerrado";

      $mensagemHtml = "
        <p>Olá <strong>" . htmlspecialchars($respNome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>O chamado abaixo foi encerrado.</p>
        <ul style='margin:0; padding-left:18px;'>
          <li><b>Protocolo:</b> " . htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Assunto:</b> " . htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8') . "</li>
          <li><b>Status:</b> " . htmlspecialchars($statusNome, ENT_QUOTES, 'UTF-8') . "</li>
        </ul>
      ";

      $htmlEmail = emailTemplatePadrao($titulo, $mensagemHtml, '', '', '');
      $env = enviarEmailPadrao($respEmail, $respNome, $assuntoEmail, $htmlEmail);

      if (!(bool)($env['ok'] ?? false)) {
        error_log("Falha e-mail responsável encerrar {$protocolo}: " . (string)($env['erro'] ?? ''));
      }
    }
  } catch (Throwable $e) {
    error_log("Exceção e-mail responsável encerrar {$protocolo}: " . $e->getMessage());
  }

  jexit(true, 'Chamado encerrado com sucesso!');

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  jexit(false, 'Erro ao encerrar: ' . $e->getMessage());
}

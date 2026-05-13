<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../funcoes/email.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  // Permissão: responder (use editar/criar por enquanto)
  if (!podeFazer('editar') && !podeFazer('criar')) {
    out(false, 'Sem permissão para responder.');
  }

  $uid = (int)($_SESSION['id'] ?? 0);
  if ($uid <= 0) out(false, 'Sessão inválida. Faça login novamente.');

  // aceita JSON
  $raw = file_get_contents('php://input');
  $json = $raw ? json_decode($raw, true) : null;
  if (!$json || json_last_error() !== JSON_ERROR_NONE) out(false, 'JSON inválido.');

  $chamado_id = (int)($json['chamado_id'] ?? 0);
  $mensagem   = trim((string)($json['mensagem'] ?? ''));

  if ($chamado_id <= 0) out(false, 'Chamado inválido.');
  if ($mensagem === '') out(false, 'Digite uma resposta.');
  if (mb_strlen($mensagem) > 5000) out(false, 'Mensagem muito grande (máx. 5000).');

  // confere chamado e pega protocolo + cliente_id + assunto
  $stC = $pdo->prepare("
    SELECT id, protocolo, cliente_id, assunto
    FROM chamados
    WHERE id = :id
    LIMIT 1
  ");
  $stC->execute([':id' => $chamado_id]);
  $ch = $stC->fetch(PDO::FETCH_ASSOC);
  if (!$ch) out(false, 'Chamado não encontrado.');

  $protocolo  = (string)($ch['protocolo'] ?? '');
  $cliente_id = (int)($ch['cliente_id'] ?? 0);
  $assuntoChamado = (string)($ch['assunto'] ?? '');

  /**
   * Autor:
   * - agora: usuário
   * - futuro: cliente (quando tiver painel do cliente)
   */
  $tipo_autor = 'usuario'; // usuario | cliente | sistema
  $usuario_id = $uid;

  // insere resposta
  $st = $pdo->prepare("
    INSERT INTO chamados_respostas
      (chamado_id, usuario_id, cliente_id, tipo_autor, mensagem, criado_em)
    VALUES
      (:chamado_id, :usuario_id, :cliente_id, :tipo_autor, :mensagem, NOW())
  ");
  $st->execute([
    ':chamado_id' => $chamado_id,
    ':usuario_id' => $usuario_id,
    ':cliente_id' => ($cliente_id > 0 ? $cliente_id : null), // cliente do chamado (não o autor)
    ':tipo_autor' => $tipo_autor,
    ':mensagem'   => $mensagem
  ]);

  $respId = (int)$pdo->lastInsertId();

  // log
  registrarLog($pdo, 'inserir', 'chamados_respostas', $respId, "Resposta no chamado {$protocolo}");

  // =========================
  // NOTIFICAÇÕES (cliente)
  // =========================
  $clienteNome = '';
  $clienteEmail = '';
  $clienteTelefone = '';

  if ($cliente_id > 0) {
    $stCli = $pdo->prepare("SELECT nome, email, telefone FROM clientes WHERE id = :id LIMIT 1");
    $stCli->execute([':id' => $cliente_id]);
    $cli = $stCli->fetch(PDO::FETCH_ASSOC);
    if ($cli) {
      $clienteNome = (string)($cli['nome'] ?? '');
      $clienteEmail = (string)($cli['email'] ?? '');
      $clienteTelefone = (string)($cli['telefone'] ?? '');
    }
  }

  // pega nome do usuário que respondeu (pra compor mensagem)
  $nomeAutor = '';
  $stU = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
  $stU->execute([':id' => $uid]);
  $nomeAutor = (string)($stU->fetchColumn() ?: '');

  // WhatsApp (cliente)
  try {

    $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://')
          . $_SERVER['HTTP_HOST']
          . '/helpdesk';

    $linkChamado = $baseUrl . '/chamado/' . urlencode($protocolo);

    if (($api_whatsapp ?? 'Nenhuma') !== 'Nenhuma' && $clienteTelefone !== '') {

      $telefone_disparo = preg_replace('/\D+/', '', $clienteTelefone);

      // mensagem curta (whats)
      $mensagem_whatsapp =
        "Olá, {$clienteNome}! ✅\n\n" .
        "Temos uma nova resposta no seu chamado.\n\n" .
        "📌 Protocolo: {$protocolo}\n" .
        ($assuntoChamado ? "📝 Assunto: {$assuntoChamado}\n" : "") .
        ($nomeAutor ? "👨‍💻 Atendente: {$nomeAutor}\n\n" : "\n") .
        "🔎 Acompanhe seu chamado pelo link abaixo:\n\n" .
          "{$linkChamado}\n\n" .
        "💬 Resposta:\n" . $mensagem;

      require __DIR__ . '/../../apis/texto_whatsapp.php';
    }
  } catch (Throwable $e) {
    error_log("Falha WhatsApp cliente resposta {$protocolo}: " . $e->getMessage());
  }

  // E-mail (cliente)
  try {
    if ($clienteEmail !== '' && filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {

      $assuntoEmail = "Nova resposta no chamado ({$protocolo}) - " . ($nome_sistema ?? 'HelpDesk');
      $titulo = "Nova resposta no seu chamado";

      $mensagemHtml = "
        <p>Olá <strong>" . htmlspecialchars($clienteNome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Você recebeu uma nova resposta no seu chamado.</p>

        <ul style='margin:0; padding-left:18px;'>
          <li><b>Protocolo:</b> " . htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') . "</li>
          " . ($assuntoChamado ? "<li><b>Assunto:</b> " . htmlspecialchars($assuntoChamado, ENT_QUOTES, 'UTF-8') . "</li>" : "") . "
          " . ($nomeAutor ? "<li><b>Atendente:</b> " . htmlspecialchars($nomeAutor, ENT_QUOTES, 'UTF-8') . "</li>" : "") . "
        </ul>

        <div style='margin-top:12px; padding:12px; border:1px solid #eee; border-radius:10px; background:#fafafa;'>
          <div style='font-size:13px; color:#666; margin-bottom:6px;'><b>Resposta:</b></div>
          <div style='white-space:pre-wrap;'>" . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . "</div>
        </div>


        <div style='margin-top:16px; padding:14px; border-radius:10px; background:#f3f4f6; text-align:center;'>
  <div style='font-size:14px; margin-bottom:8px; color:#333;'>
    Acompanhe o andamento do seu chamado:
  </div>

  <a href='" . htmlspecialchars($linkChamado, ENT_QUOTES, 'UTF-8') . "'
     style='display:inline-block;
            padding:10px 18px;
            background:#667eea;
            color:#fff;
            text-decoration:none;
            border-radius:6px;
            font-weight:bold;
            font-size:14px;'>
    🔎 Visualizar Chamado
  </a>

  <div style='font-size:12px; color:#777; margin-top:8px; word-break:break-all;'>
    " . htmlspecialchars($linkChamado, ENT_QUOTES, 'UTF-8') . "
  </div>
</div>

        <p style='margin-top:12px;'>Se precisar, responda por este canal ou entre em contato com nossa equipe.</p>
      ";

      $htmlEmail = emailTemplatePadrao($titulo, $mensagemHtml, '', '', '');
      $env = enviarEmailPadrao($clienteEmail, $clienteNome, $assuntoEmail, $htmlEmail);

      if (!(bool)($env['ok'] ?? false)) {
        error_log("Falha e-mail cliente resposta {$protocolo}: " . (string)($env['erro'] ?? ''));
      }
    }
  } catch (Throwable $e) {
    error_log("Exceção e-mail cliente resposta {$protocolo}: " . $e->getMessage());
  }

  out(true, 'Resposta enviada!', ['id' => $respId]);

} catch (Throwable $e) {
  out(false, 'Erro: ' . $e->getMessage());
}
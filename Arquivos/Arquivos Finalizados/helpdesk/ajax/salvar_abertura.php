<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados';
$id_empresa = 0;

require_once __DIR__ . '/../painel/verificar.php';
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../painel/includes/logs.php';
require_once __DIR__ . '/../painel/includes/permissoes.php';
require_once __DIR__ . '/../painel/funcoes/email.php';


function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $acao = $id > 0 ? 'editar' : 'criar';
  if (!podeFazer($acao)) out(false, "Sem permissão para $acao.");

  // inputs
  $cliente_id = isset($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : 0;
  $usuario_responsavel_id = isset($_POST['usuario_responsavel_id']) && $_POST['usuario_responsavel_id'] !== ''
    ? (int)$_POST['usuario_responsavel_id']
    : null;

  $assunto   = trim($_POST['assunto'] ?? '');
  $descricao = trim($_POST['descricao'] ?? '');
  $prioridade = trim($_POST['prioridade'] ?? 'Media');
  $status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;
  $setor_id = isset($_POST['setor_id']) ? (int)$_POST['setor_id'] : 0;


if ($setor_id <= 0) out(false, "Selecione um setor.");

// valida setor existe
$stSet = $pdo->prepare("SELECT id FROM setores WHERE id = :id LIMIT 1");
$stSet->execute([':id' => $setor_id]);
if (!$stSet->fetchColumn()) out(false, "Setor inválido.");

  // quem abriu (ajuste aqui conforme seu sistema guarda)
  $usuario_abertura_id = (int)($_SESSION['id'] ?? 0);
  if ($usuario_abertura_id <= 0) out(false, "Sessão inválida. Faça login novamente.");

  if ($assunto === '') out(false, "Informe o assunto.");
  if (mb_strlen($assunto) > 120) out(false, "Assunto máximo 120 caracteres.");
  if ($descricao === '') out(false, "Informe a descrição.");

  $prioridadesValidas = ['Baixa','Media','Alta','Urgente'];
  if (!in_array($prioridade, $prioridadesValidas, true)) $prioridade = 'Media';

  // valida status
  $stSta = $pdo->prepare("SELECT id, nome, fechado FROM chamados_status WHERE id = :id AND ativo = 'Sim' LIMIT 1");
  $stSta->execute([':id' => $status_id]);
  $sta = $stSta->fetch(PDO::FETCH_ASSOC);
  if (!$sta) out(false, "Status inválido.");

  // opcional: valida responsável
  if ($usuario_responsavel_id) {
    $stU = $pdo->prepare("SELECT id, nome, email, telefone FROM usuarios WHERE id = :id LIMIT 1");
    $stU->execute([':id' => $usuario_responsavel_id]);
    if (!$stU->fetch()) out(false, "Responsável inválido.");
  }

  // protocolo simples (você pode mudar)
  $protocolo = 'CH' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

  if ($id === 0) {

    $stmt = $pdo->prepare("
      INSERT INTO chamados
      (cliente_id, usuario_abertura_id, usuario_responsavel_id, assunto, descricao, prioridade, status_id, criado_em, protocolo, empresa, setor_id)
      VALUES
      (:cliente_id, :usuario_abertura_id, :usuario_responsavel_id, :assunto, :descricao, :prioridade, :status_id, NOW(), :protocolo, :empresa, :setor_id)
    ");

    $stmt->execute([
      ':cliente_id' => ($cliente_id > 0 ? $cliente_id : null),
      ':usuario_abertura_id' => $usuario_abertura_id,
      ':usuario_responsavel_id' => $usuario_responsavel_id,
      ':assunto' => $assunto,
      ':descricao' => $descricao,
      ':prioridade' => $prioridade,
      ':status_id' => $status_id,
      ':empresa' => $id_empresa,
      ':protocolo' => $protocolo,
      ':setor_id' => $setor_id,
    ]);

    $novoId = (int)$pdo->lastInsertId();

    // movimento inicial (status)
    $mov = $pdo->prepare("
      INSERT INTO chamados_movimentos (chamado_id, usuario_id, tipo, status_para, mensagem, criado_em)
      VALUES (:chamado_id, :usuario_id, 'status', :status_para, :mensagem, NOW())
    ");
    $mov->execute([
      ':chamado_id' => $novoId,
      ':usuario_id' => $usuario_abertura_id,
      ':status_para' => $status_id,
      ':mensagem' => "Chamado aberto com status: {$sta['nome']}"
    ]);

    registrarLog($pdo, 'inserir', $tabela, $novoId, "Chamado '{$protocolo}' aberto");

    /**
     * DISPAROS (WhatsApp/E-mail)
     * Sugestão: enviar para o CLIENTE e para o RESPONSÁVEL (se existir).
     * Aqui eu deixo como "tentativa" pra não quebrar o cadastro.
     */

    // dados do cliente (ajuste tabela/campos conforme seu sistema)
    $clienteNome = '';
    $clienteEmail = '';
    $clienteTelefone = '';
    if ($cliente_id > 0) {
      // ⚠️ ajuste nome da tabela/colunas se for diferente
      $stC = $pdo->prepare("SELECT nome, email, telefone FROM clientes WHERE id = :id LIMIT 1");
      $stC->execute([':id' => $cliente_id]);
      $c = $stC->fetch(PDO::FETCH_ASSOC);
      if ($c) {
        $clienteNome = (string)($c['nome'] ?? '');
        $clienteEmail = (string)($c['email'] ?? '');
        $clienteTelefone = (string)($c['telefone'] ?? '');
      }
    }

    // dados do responsável
    $respNome = '';
    $respEmail = '';
    $respTelefone = '';
    if ($usuario_responsavel_id) {
      $stR = $pdo->prepare("SELECT nome, email, telefone FROM usuarios WHERE id = :id LIMIT 1");
      $stR->execute([':id' => $usuario_responsavel_id]);
      $r = $stR->fetch(PDO::FETCH_ASSOC);
      if ($r) {
        $respNome = (string)($r['nome'] ?? '');
        $respEmail = (string)($r['email'] ?? '');
        $respTelefone = (string)($r['telefone'] ?? '');
      }
    }

    // WhatsApp (cliente)

    $baseUrl = $url_sistema;

    $linkChamado = $baseUrl . 'chamado/' . urlencode($protocolo);
    
      if (($api_whatsapp ?? 'Nenhuma') !== 'Nenhuma' && $clienteTelefone !== '') {
        $telefone_disparo = preg_replace('/\D+/', '', $clienteTelefone);

        $mensagem_whatsapp =
          "Olá, {$clienteNome}! ✅\n\n" .
          "Seu chamado foi aberto com sucesso.\n\n" .
          "📌 Protocolo: {$protocolo}\n" .
          "📝 Assunto: {$assunto}\n" .
          "⚡ Prioridade: {$prioridade}\n" .
          "📍 Status: {$sta['nome']}\n\n" .
          "🔎 Acompanhe seu chamado pelo link abaixo:\n" .
          "{$linkChamado}\n\n" .
          "Em breve nossa equipe irá retornar.";

          require_once BASE_PATH . '/painel/apis/texto_whatsapp.php';
      }
    

    // WhatsApp (responsável)
   
      if (($api_whatsapp ?? 'Nenhuma') !== 'Nenhuma' && $respTelefone !== '') {
        $telefone_disparo = preg_replace('/\D+/', '', $respTelefone);

        $mensagem_whatsapp =
        "Novo chamado atribuído ✅\n\n" .
        "📌 Protocolo: {$protocolo}\n" .
        "👤 Cliente: {$clienteNome}\n" .
        "📝 Assunto: {$assunto}\n" .
        "⚡ Prioridade: {$prioridade}\n" .
        "📍 Status: {$sta['nome']}\n\n" .
        "🔎 Acompanhe o chamado pelo link abaixo:\n" .
        "{$linkChamado}\n\n";

        require_once BASE_PATH . '/painel/apis/texto_whatsapp.php';
      }
   

    // E-mail (cliente)
   
      if ($clienteEmail !== '' && filter_var($clienteEmail, FILTER_VALIDATE_EMAIL)) {

        $assuntoEmail = "Chamado aberto ({$protocolo}) - " . ($nome_sistema ?? 'HelpDesk');
        $titulo = "Chamado aberto com sucesso";

        $mensagemHtml = "
          <p>Olá <strong>" . htmlspecialchars($clienteNome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
          <p>Seu chamado foi aberto e já entrou na fila de atendimento.</p>
          <ul style='margin:0; padding-left:18px;'>
            <li><b>Protocolo:</b> " . htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Assunto:</b> " . htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Prioridade:</b> " . htmlspecialchars($prioridade, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Status:</b> " . htmlspecialchars($sta['nome'], ENT_QUOTES, 'UTF-8') . "</li>
          </ul>
          <p style='margin-top:10px;'>Em breve retornaremos com atualizações.</p>

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

        ";

        $htmlEmail = emailTemplatePadrao($titulo, $mensagemHtml, '', '', '');

        $env = enviarEmailPadrao($clienteEmail, $clienteNome, $assuntoEmail, $htmlEmail);
        if (!(bool)($env['ok'] ?? false)) {
          error_log("Falha e-mail cliente chamado {$protocolo}: " . (string)($env['erro'] ?? ''));
        }
      }
   

    // E-mail (responsável)
   
      if ($respEmail !== '' && filter_var($respEmail, FILTER_VALIDATE_EMAIL)) {

        $assuntoEmail = "Novo chamado atribuído ({$protocolo}) - " . ($nome_sistema ?? 'HelpDesk');
        $titulo = "Novo chamado atribuído a você";

        $mensagemHtml = "
          <p>Olá <strong>" . htmlspecialchars($respNome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
          <p>Um chamado foi atribuído a você.</p>
          <ul style='margin:0; padding-left:18px;'>
            <li><b>Protocolo:</b> " . htmlspecialchars($protocolo, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Cliente:</b> " . htmlspecialchars($clienteNome, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Assunto:</b> " . htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Prioridade:</b> " . htmlspecialchars($prioridade, ENT_QUOTES, 'UTF-8') . "</li>
            <li><b>Status:</b> " . htmlspecialchars($sta['nome'], ENT_QUOTES, 'UTF-8') . "</li>
          </ul>
        ";

        $htmlEmail = emailTemplatePadrao($titulo, $mensagemHtml, '', '', '');
        $env = enviarEmailPadrao($respEmail, $respNome, $assuntoEmail, $htmlEmail);

        if (!(bool)($env['ok'] ?? false)) {
          error_log("Falha e-mail responsável chamado {$protocolo}: " . (string)($env['erro'] ?? ''));
        }
      }
   

    out(true, "Chamado aberto com sucesso!", ['id' => $novoId, 'protocolo' => $protocolo]);

   } else {

    // =========================
    // EDIÇÃO
    // =========================

    // 1) Confere se existe (e pega dados antigos pra comparar)
    $stOld = $pdo->prepare("
      SELECT
        id, protocolo, cliente_id, usuario_responsavel_id,
        assunto, descricao, prioridade, status_id, fechado_em, setor_id
      FROM chamados
      WHERE id = :id
      LIMIT 1
    ");
    $stOld->execute([':id' => $id]);
    $old = $stOld->fetch(PDO::FETCH_ASSOC);
    if (!$old) out(false, "Chamado não encontrado.");

    $protocolo = (string)($old['protocolo'] ?? '');

    $oldStatusId = (int)($old['status_id'] ?? 0);
    $oldRespId   = ($old['usuario_responsavel_id'] !== null) ? (int)$old['usuario_responsavel_id'] : null;
    $oldCliente  = ($old['cliente_id'] !== null) ? (int)$old['cliente_id'] : 0;

    $oldAssunto  = (string)($old['assunto'] ?? '');
    $oldDesc     = (string)($old['descricao'] ?? '');
    $oldPrio     = (string)($old['prioridade'] ?? 'Media');
    $oldSetor  = ($old['setor_id'] !== null) ? (int)$old['setor_id'] : 0;

    // 2) Se o novo status for "fechado", seta fechado_em. Senão, limpa.
    $novoFechadoEm = null;
    if (strtolower((string)$sta['fechado']) === 'sim') {
      $novoFechadoEm = date('Y-m-d H:i:s');
    }

    // 3) UPDATE principal
    $stmt = $pdo->prepare("
      UPDATE chamados SET
        cliente_id = :cliente_id,
        usuario_responsavel_id = :usuario_responsavel_id,
        assunto = :assunto,
        descricao = :descricao,
        prioridade = :prioridade,
        status_id = :status_id,
        fechado_em = :fechado_em,
        setor_id = :setor_id,
        atualizado_em = NOW()
      WHERE id = :id
      LIMIT 1
    ");

    $stmt->execute([
      ':cliente_id' => ($cliente_id > 0 ? $cliente_id : null),
      ':usuario_responsavel_id' => $usuario_responsavel_id,
      ':assunto' => $assunto,
      ':descricao' => $descricao,
      ':prioridade' => $prioridade,
      ':status_id' => $status_id,
      ':setor_id' => $setor_id,
      ':fechado_em' => $novoFechadoEm, // null se não for fechado
      ':id' => $id
    ]);

    // 4) Movimentos (registra somente o que mudou)
    $mov = $pdo->prepare("
      INSERT INTO chamados_movimentos
        (chamado_id, usuario_id, tipo, status_de, status_para, mensagem, criado_em)
      VALUES
        (:chamado_id, :usuario_id, :tipo, :status_de, :status_para, :mensagem, NOW())
    ");

    // 4.1) Mudou status
    if ($status_id !== $oldStatusId) {
      $mov->execute([
        ':chamado_id' => $id,
        ':usuario_id' => $usuario_abertura_id,
        ':tipo' => 'status',
        ':status_de' => $oldStatusId ?: null,
        ':status_para' => $status_id,
        ':mensagem' => "Status alterado para: {$sta['nome']}"
      ]);
    }

    // 4.2) Mudou responsável
    if ((int)($oldRespId ?? 0) !== (int)($usuario_responsavel_id ?? 0)) {

      $respOldNome = '';
      if ($oldRespId) {
        $st = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
        $st->execute([':id' => $oldRespId]);
        $respOldNome = (string)($st->fetchColumn() ?: '');
      }

      $respNewNome = '';
      if ($usuario_responsavel_id) {
        $st = $pdo->prepare("SELECT nome FROM usuarios WHERE id = :id LIMIT 1");
        $st->execute([':id' => $usuario_responsavel_id]);
        $respNewNome = (string)($st->fetchColumn() ?: '');
      }

      $msg = "Responsável alterado: "
        . ($respOldNome ? $respOldNome : "(sem responsável)")
        . " → "
        . ($respNewNome ? $respNewNome : "(sem responsável)");

      $mov->execute([
        ':chamado_id' => $id,
        ':usuario_id' => $usuario_abertura_id,
        ':tipo' => 'sistema',
        ':status_de' => null,
        ':status_para' => null,
        ':mensagem' => $msg
      ]);
    }

    // 4.3) Mudou conteúdo (assunto/descrição/prioridade/cliente)
    $mudouConteudo = false;
    $partes = [];

    if ($assunto !== $oldAssunto) {
      $mudouConteudo = true;
      $partes[] = "Assunto atualizado";
    }
    if ($descricao !== $oldDesc) {
      $mudouConteudo = true;
      $partes[] = "Descrição atualizada";
    }
    if ($prioridade !== $oldPrio) {
      $mudouConteudo = true;
      $partes[] = "Prioridade: {$oldPrio} → {$prioridade}";
    }
    if ((int)$cliente_id !== (int)$oldCliente) {
      $mudouConteudo = true;
      $partes[] = "Cliente alterado";
    }

    if ((int)$setor_id !== (int)$oldSetor) {
      $mudouConteudo = true;
      $partes[] = "Setor Alterado";
    }

    if ($mudouConteudo) {
      $mov->execute([
        ':chamado_id' => $id,
        ':usuario_id' => $usuario_abertura_id,
        ':tipo' => 'sistema',
        ':status_de' => null,
        ':status_para' => null,
        ':mensagem' => implode(" | ", $partes)
      ]);
    }

    // 5) Log do sistema
    registrarLog($pdo, 'editar', $tabela, $id, "Chamado '{$protocolo}' atualizado");

    out(true, "Chamado atualizado com sucesso!", ['id' => $id, 'protocolo' => $protocolo]);
  }


} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}

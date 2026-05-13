<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  // =========================
  // Sessão (cliente logado)
  // =========================
  $cliente_id = (int)($_SESSION['cliente_id'] ?? 0);
  if ($cliente_id <= 0) out(false, "Sessão inválida. Faça login novamente.");

  // =========================
  // Inputs do formulário
  // =========================
  $assunto   = trim($_POST['assunto'] ?? '');
  $descricao = trim($_POST['descricao'] ?? '');
  $setor_id  = isset($_POST['setor_id']) ? (int)$_POST['setor_id'] : 0;

  $prioridade = trim($_POST['prioridade'] ?? 'Media');
  $prioridadesValidas = ['Baixa','Media','Alta','Urgente'];
  if (!in_array($prioridade, $prioridadesValidas, true)) $prioridade = 'Media';

  if ($assunto === '') out(false, "Informe o assunto.");
  if (mb_strlen($assunto) > 120) out(false, "Assunto máximo 120 caracteres.");
  if ($descricao === '') out(false, "Informe a descrição.");
  if ($setor_id <= 0) out(false, "Selecione um setor.");

  // =========================
  // Valida setor
  // =========================
  $stSet = $pdo->prepare("SELECT id FROM setores WHERE id = :id LIMIT 1");
  $stSet->execute([':id' => $setor_id]);
  if (!$stSet->fetchColumn()) out(false, "Setor inválido.");

  // =========================
  // Status padrão (primeiro ativo por ordem)
  // =========================
  $stSta = $pdo->query("
    SELECT id, nome, fechado
    FROM chamados_status
    WHERE ativo = 'Sim'
    ORDER BY ordem ASC, id ASC
    LIMIT 1
  ");
  $sta = $stSta->fetch(PDO::FETCH_ASSOC);
  if (!$sta) out(false, "Nenhum status ativo cadastrado.");

  $status_id = (int)$sta['id'];

  // =========================
  // usuario_abertura_id (obrigatório por FK -> usuarios.id)
  // Abertura é do cliente, mas a FK exige um usuário válido.
  // Então pegamos automaticamente um usuário existente (prioriza Administrador).
  // =========================
  $stUA = $pdo->query("
    SELECT id
    FROM usuarios
    ORDER BY 
      CASE WHEN LOWER(nivel) = 'administrador' THEN 0 ELSE 1 END,
      id ASC
    LIMIT 1
  ");
  $usuario_abertura_id = (int)($stUA->fetchColumn() ?: 0);

  if ($usuario_abertura_id <= 0) {
    out(false, "Nenhum usuário encontrado na tabela 'usuarios' para vincular a abertura (FK). Cadastre ao menos 1 usuário.");
  }

  // =========================
  // Protocolo
  // =========================
  $protocolo = 'CH' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

  // =========================
  // Insert (empresa fica sempre 0)
  // =========================
  $stmt = $pdo->prepare("
    INSERT INTO chamados
      (cliente_id, usuario_abertura_id, usuario_responsavel_id, setor_id, assunto, descricao, prioridade, status_id, criado_em, protocolo, empresa)
    VALUES
      (:cliente_id, :usuario_abertura_id, NULL, :setor_id, :assunto, :descricao, :prioridade, :status_id, NOW(), :protocolo, 0)
  ");

  $stmt->execute([
    ':cliente_id' => $cliente_id,
    ':usuario_abertura_id' => $usuario_abertura_id,
    ':setor_id' => $setor_id,
    ':assunto' => $assunto,
    ':descricao' => $descricao,
    ':prioridade' => $prioridade,
    ':status_id' => $status_id,
    ':protocolo' => $protocolo,
  ]);

  $novoId = (int)$pdo->lastInsertId();

  // =========================
  // Movimento inicial (se sua tabela existir com esses campos)
  // =========================
  try {
    $mov = $pdo->prepare("
      INSERT INTO chamados_movimentos
        (chamado_id, usuario_id, tipo, status_para, mensagem, criado_em)
      VALUES
        (:chamado_id, :usuario_id, 'status', :status_para, :mensagem, NOW())
    ");
    $mov->execute([
      ':chamado_id' => $novoId,
      ':usuario_id' => $usuario_abertura_id,
      ':status_para' => $status_id,
      ':mensagem' => "Chamado aberto pelo cliente com status: {$sta['nome']}"
    ]);
  } catch (Throwable $ignore) {
    // não bloqueia abertura se movimentos não existir/estrutura diferente
  }

  out(true, "Chamado aberto com sucesso!", [
    'id' => $novoId,
    'protocolo' => $protocolo,
    'status_id' => $status_id
  ]);

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}
<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

$tabela = 'setores';

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  // =========================
  // INPUTS
  // =========================
  $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $empresa = isset($_POST['empresa']) ? (int)($_POST['empresa'] ?? 0) : 0;

  $nome = trim($_POST['nome'] ?? '');

  // =========================
  // PERMISSÕES
  // =========================
  $acao = $id > 0 ? 'editar' : 'criar';

  require_once __DIR__ . '/../../includes/permissoes.php';
  if (!podeFazer($acao)) {
    echo json_encode(['ok'=>false,'msg'=>"Sem permissão para $acao."]);
    exit;
  }

  // =========================
  // VALIDAÇÕES
  // =========================
  if ($nome === '') {
    out(false, "Informe o nome do setor.");
  }

  if (mb_strlen($nome) > 75) {
    out(false, "O nome do setor deve ter no máximo 75 caracteres.");
  }

  // evita duplicar setor na mesma empresa
  $sqlDup = "SELECT id FROM {$tabela} WHERE nome = :nome AND empresa = :empresa";
  if ($id > 0) $sqlDup .= " AND id <> :id";
  $sqlDup .= " LIMIT 1";

  $stDup = $pdo->prepare($sqlDup);
  $stDup->bindValue(':nome', $nome);
  $stDup->bindValue(':empresa', $empresa, PDO::PARAM_INT);
  if ($id > 0) $stDup->bindValue(':id', $id, PDO::PARAM_INT);
  $stDup->execute();

  if ($stDup->fetch()) {
    out(false, "Já existe um setor com esse nome para esta empresa.");
  }

  // =========================
  // INSERT / UPDATE
  // =========================
  if ($id === 0) {

    $stmt = $pdo->prepare("
      INSERT INTO {$tabela} (nome, empresa)
      VALUES (:nome, :empresa)
    ");
    $stmt->execute([
      ':nome'    => $nome,
      ':empresa' => $empresa,
    ]);

    $novoId = (int)$pdo->lastInsertId();

    // LOG (INSERIR)
    registrarLog(
      $pdo,
      'inserir',
      $tabela,
      $novoId,
      "Setor '{$nome}' cadastrado"
    );

    out(true, "Setor cadastrado com sucesso!");

  } else {

    // garante que existe e pertence à empresa
    $stCheck = $pdo->prepare("SELECT id FROM {$tabela} WHERE id = :id AND empresa = :empresa LIMIT 1");
    $stCheck->execute([':id' => $id, ':empresa' => $empresa]);
    if (!$stCheck->fetch()) {
      out(false, "Setor não encontrado.");
    }

    $stmt = $pdo->prepare("
      UPDATE {$tabela}
         SET nome = :nome
       WHERE id = :id AND empresa = :empresa
       LIMIT 1
    ");
    $stmt->execute([
      ':nome'    => $nome,
      ':id'      => $id,
      ':empresa' => $empresa,
    ]);

    // LOG (EDITAR)
    registrarLog(
      $pdo,
      'editar',
      $tabela,
      $id,
      "Setor '{$nome}' atualizado"
    );

    out(true, "Setor atualizado com sucesso!");
  }

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}
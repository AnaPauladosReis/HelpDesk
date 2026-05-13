<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados_status'; // ✅ status_abertura

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

function normSN($v, $default = 'Não') {
  $v = trim((string)$v);
  if ($v === '1') $v = 'Sim';
  if ($v === '0') $v = 'Não';
  if (!in_array($v, ['Sim','Não'], true)) $v = $default;
  return $v;
}

function normHex($c) {
  $c = trim((string)$c);
  if ($c === '') return '';
  if ($c[0] !== '#') $c = '#'.$c;
  if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $c)) return '';
  return $c;
}

try {

  // =========================
  // INPUTS
  // =========================
  $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  // não SaaS
  $empresa = 0;

  $nome    = trim($_POST['nome'] ?? '');
  $cor     = normHex($_POST['cor'] ?? '#6c757d');
  $ordem   = isset($_POST['ordem']) ? (int)$_POST['ordem'] : 0;

  $ativo   = normSN($_POST['ativo'] ?? 'Sim', 'Sim');
  $padrao  = normSN($_POST['padrao'] ?? 'Não', 'Não');
  $fechado = normSN($_POST['fechado'] ?? 'Não', 'Não');

  // defaults
  if ($cor === '') $cor = '#6c757d';
  if ($ordem < 0) $ordem = 0;

  // =========================
  // PERMISSÕES
  // =========================
  $acao = $id > 0 ? 'editar' : 'criar';
  require_once __DIR__ . '/../../includes/permissoes.php';
  if (!podeFazer($acao)) out(false, "Sem permissão para $acao.");

  // =========================
  // VALIDAÇÕES
  // =========================
  if ($nome === '') out(false, "Informe o nome do status.");
  if (mb_strlen($nome) > 60) out(false, "O nome do status deve ter no máximo 60 caracteres.");
  if ($cor === '' || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $cor)) out(false, "Cor inválida. Use HEX (#RRGGBB).");

  // evita duplicar nome
  $sqlDup = "SELECT id FROM {$tabela} WHERE nome = :nome";
  if ($id > 0) $sqlDup .= " AND id <> :id";
  $sqlDup .= " LIMIT 1";

  $stDup = $pdo->prepare($sqlDup);
  $stDup->bindValue(':nome', $nome);
  if ($id > 0) $stDup->bindValue(':id', $id, PDO::PARAM_INT);
  $stDup->execute();
  if ($stDup->fetch()) out(false, "Já existe um status com esse nome.");

  // =========================
  // INSERT / UPDATE
  // =========================
  if ($id === 0) {

    $stmt = $pdo->prepare("
      INSERT INTO {$tabela} (nome, cor, ordem, ativo, padrao, fechado, criado_em)
      VALUES (:nome, :cor, :ordem, :ativo, :padrao, :fechado, NOW())
    ");
    $stmt->execute([
      ':nome'    => $nome,
      ':cor'     => $cor,
      ':ordem'   => $ordem,
      ':ativo'   => $ativo,
      ':padrao'  => $padrao,
      ':fechado' => $fechado,
    ]);

    $novoId = (int)$pdo->lastInsertId();

    // ✅ garante apenas 1 status padrão
    if ($padrao === 'Sim') {
      $pdo->prepare("UPDATE {$tabela} SET padrao = 'Não' WHERE id <> :id")
          ->execute([':id' => $novoId]);
    }

    registrarLog($pdo, 'inserir', $tabela, $novoId, "Status '{$nome}' cadastrado");

    out(true, "Status cadastrado com sucesso!");

  } else {

    // garante que existe
    $stCheck = $pdo->prepare("SELECT id FROM {$tabela} WHERE id = :id LIMIT 1");
    $stCheck->execute([':id' => $id]);
    if (!$stCheck->fetch()) out(false, "Status não encontrado.");

    $stmt = $pdo->prepare("
      UPDATE {$tabela}
         SET nome = :nome,
             cor = :cor,
             ordem = :ordem,
             ativo = :ativo,
             padrao = :padrao,
             fechado = :fechado,
             atualizado_em = NOW()
       WHERE id = :id
       LIMIT 1
    ");
    $stmt->execute([
      ':nome'    => $nome,
      ':cor'     => $cor,
      ':ordem'   => $ordem,
      ':ativo'   => $ativo,
      ':padrao'  => $padrao,
      ':fechado' => $fechado,
      ':id'      => $id,
    ]);

    // ✅ garante apenas 1 status padrão
    if ($padrao === 'Sim') {
      $pdo->prepare("UPDATE {$tabela} SET padrao = 'Não' WHERE id <> :id")
          ->execute([':id' => $id]);
    }

    registrarLog($pdo, 'editar', $tabela, $id, "Status '{$nome}' atualizado");

    out(true, "Status atualizado com sucesso!");
  }

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}

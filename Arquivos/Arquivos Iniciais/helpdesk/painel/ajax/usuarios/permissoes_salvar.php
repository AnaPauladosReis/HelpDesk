<?php
@session_start();

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

function jsonOut($ok, $msg, $extra = array()) {
  if (ob_get_length()) { @ob_clean(); }
  $base = array('ok' => $ok, 'msg' => $msg);
  echo json_encode(array_merge($base, $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

set_error_handler(function ($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($e) {
  jsonOut(false, 'Erro no servidor: ' . $e->getMessage());
});

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

// =========================
// USUÁRIO
// =========================
$usuario_id = isset($_POST['usuario_id']) ? (int)$_POST['usuario_id'] : 0;
if ($usuario_id <= 0) {
  jsonOut(false, 'Usuário inválido.');
}

$stmtU = $pdo->prepare("SELECT id, nivel FROM usuarios WHERE id = :id LIMIT 1");
$stmtU->execute(array(':id' => $usuario_id));
$user = $stmtU->fetch();

if (!$user) {
  jsonOut(false, 'Usuário não encontrado.');
}

if (trim((string)$user['nivel']) === 'Administrador') {
  jsonOut(false, 'Administrador possui acesso total. Permissões não são aplicadas.');
}

// =========================
// CATÁLOGO (MENUS)
// =========================
$cfgPath = __DIR__ . '/../../config/menu.php';
if (!file_exists($cfgPath)) {
  jsonOut(false, 'Catálogo de menus não encontrado.');
}

$cfg = require $cfgPath;
$menusCatalogo = (isset($cfg['menus']) && is_array($cfg['menus'])) ? $cfg['menus'] : array();

$validIds = array();
foreach ($menusCatalogo as $m) {
  $id = isset($m['id']) ? trim((string)$m['id']) : '';
  if ($id !== '') $validIds[$id] = true;
}

// =========================
// MENUS RECEBIDOS
// =========================
$menusMarcados = isset($_POST['menus']) ? $_POST['menus'] : array();
if (!is_array($menusMarcados)) $menusMarcados = array();

$tmp = array();
foreach ($menusMarcados as $v) {
  $v = trim((string)$v);
  if ($v === '') continue;

  if (!preg_match('/^[a-zA-Z0-9_-]{1,50}$/', $v)) continue;
  if (!isset($validIds[$v])) continue;

  $tmp[] = $v;
}
$menusMarcados = array_values(array_unique($tmp));

// =========================
// AÇÕES RECEBIDAS
// =========================
$acoesPermitidas = array('criar' => true, 'editar' => true, 'excluir' => true);

$acoesMarcadas = isset($_POST['acoes']) ? $_POST['acoes'] : array();
if (!is_array($acoesMarcadas)) $acoesMarcadas = array();

$tmpA = array();
foreach ($acoesMarcadas as $a) {
  $a = trim((string)$a);
  if ($a === '') continue;

  if (!preg_match('/^[a-zA-Z0-9_-]{1,20}$/', $a)) continue;
  if (!isset($acoesPermitidas[$a])) continue;

  $tmpA[] = $a;
}
$acoesMarcadas = array_values(array_unique($tmpA));

// =========================
// SETORES RECEBIDOS
// =========================
$setoresMarcados = isset($_POST['setores']) ? $_POST['setores'] : array();
if (!is_array($setoresMarcados)) $setoresMarcados = array();

// normaliza para ints únicos
$tmpS = array();
foreach ($setoresMarcados as $sid) {
  $sid = (int)$sid;
  if ($sid <= 0) continue;
  $tmpS[$sid] = true;
}
$setoresMarcados = array_keys($tmpS);

// valida se os setores existem (protege contra ids falsos)
$setoresValidos = array();
if (count($setoresMarcados) > 0) {
  $placeholders = implode(',', array_fill(0, count($setoresMarcados), '?'));
  $stVal = $pdo->prepare("SELECT id FROM setores WHERE id IN ($placeholders)");
  $stVal->execute($setoresMarcados);
  $ids = $stVal->fetchAll(PDO::FETCH_COLUMN);

  foreach (($ids ?: array()) as $idok) {
    $setoresValidos[(int)$idok] = true;
  }

  // filtra só os existentes
  $setoresMarcados = array_values(array_filter($setoresMarcados, function($id) use ($setoresValidos){
    return isset($setoresValidos[(int)$id]);
  }));
}

// =========================
// SALVA (MENUS + AÇÕES + SETORES)
// =========================
try {
  $pdo->beginTransaction();

  // --- MENUS ---
  $del = $pdo->prepare("DELETE FROM usuarios_permissoes WHERE usuario_id = :uid");
  $del->execute(array(':uid' => $usuario_id));

  if (count($menusMarcados) > 0) {
    $ins = $pdo->prepare("INSERT INTO usuarios_permissoes (usuario_id, menu_id) VALUES (:uid, :mid)");
    foreach ($menusMarcados as $mid) {
      $ins->execute(array(':uid' => $usuario_id, ':mid' => $mid));
    }
  }

  // --- AÇÕES ---
  $delA = $pdo->prepare("DELETE FROM usuarios_acoes WHERE usuario_id = :uid");
  $delA->execute(array(':uid' => $usuario_id));

  if (count($acoesMarcadas) > 0) {
    $insA = $pdo->prepare("INSERT INTO usuarios_acoes (usuario_id, acao) VALUES (:uid, :acao)");
    foreach ($acoesMarcadas as $acao) {
      $insA->execute(array(':uid' => $usuario_id, ':acao' => $acao));
    }
  }

  // --- SETORES ---
  $delS = $pdo->prepare("DELETE FROM usuarios_setores WHERE usuario_id = :uid");
  $delS->execute(array(':uid' => $usuario_id));

  if (count($setoresMarcados) > 0) {
    $insS = $pdo->prepare("INSERT INTO usuarios_setores (usuario_id, setor_id) VALUES (:uid, :sid)");
    foreach ($setoresMarcados as $sid) {
      $insS->execute(array(':uid' => $usuario_id, ':sid' => (int)$sid));
    }
  }

  $pdo->commit();
  jsonOut(true, 'Permissões salvas com sucesso!');

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  jsonOut(false, 'Erro ao salvar permissões: ' . $e->getMessage());
}
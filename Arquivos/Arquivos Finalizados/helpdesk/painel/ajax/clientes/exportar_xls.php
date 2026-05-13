<?php
@session_start();

// segurança básica (se você já tem verificar.php, pode usar)
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

$id_empresa = (int)($_SESSION['empresa'] ?? 0);
$id_usuario = (int)($_SESSION['id'] ?? 0);
$mostrar_registros = (string)($_SESSION['registros'] ?? 'Sim'); // Sim/Não

/*
if ($id_empresa <= 0) {
  http_response_code(403);
  echo "Acesso negado.";
  exit;
}
  */

// --- helper simples p/ evitar quebrar tabela
function xlsCell($v): string {
  $v = (string)$v;
  // evita fórmula/injeção no Excel (ex: =cmd|...)
  if ($v !== '' && preg_match('/^[=\+\-@]/', $v)) {
    $v = "'" . $v;
  }
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function dataBR($dt): string {
  $dt = trim((string)$dt);
  if ($dt === '' || $dt === '0000-00-00') return '';
  $ts = strtotime($dt);
  return $ts ? date('d/m/Y', $ts) : $dt;
}

// =========================
// SQL (ajuste conforme sua regra)
// =========================
$where = "WHERE empresa = :empresa";
$params = [':empresa' => $id_empresa];

if (mb_strtolower($mostrar_registros) === 'não' || mb_strtolower($mostrar_registros) === 'nao') {
  // se seu sistema tiver campo "usuario" nos clientes
  $where .= " AND usuario = :usuario";
  $params[':usuario'] = $id_usuario;
}

$sql = "SELECT * FROM clientes {$where} ORDER BY id DESC";
$st = $pdo->prepare($sql);
$st->execute($params);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

// =========================
// Monta tabela HTML
// =========================
$dadosXls  = "<table border='1' cellpadding='4' cellspacing='0'>";
$dadosXls .= "<tr style='background:#f1f5f9;font-weight:bold;'>";
$dadosXls .= "<th>Nome</th>";
$dadosXls .= "<th>Telefone</th>";
$dadosXls .= "<th>E-mail</th>";
$dadosXls .= "<th>CPF / CNPJ</th>";
$dadosXls .= "<th>Tipo</th>";
$dadosXls .= "<th>Ativo</th>";
$dadosXls .= "<th>Data Cadastro</th>";
$dadosXls .= "<th>CEP</th>";
$dadosXls .= "<th>Endereço</th>";
$dadosXls .= "<th>Número</th>";
$dadosXls .= "<th>Complemento</th>";
$dadosXls .= "<th>Bairro</th>";
$dadosXls .= "<th>Cidade</th>";
$dadosXls .= "<th>Estado</th>";
$dadosXls .= "</tr>";

if (!$rows) {
  $dadosXls .= "<tr><td colspan='14'>Nenhum cliente encontrado.</td></tr>";
} else {
  foreach ($rows as $r) {
    $dadosXls .= "<tr>";
    $dadosXls .= "<td>" . xlsCell($r['nome'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['telefone'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['email'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['cpf_cnpj'] ?? ($r['cpf'] ?? '')) . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['tipo'] ?? ($r['tipo_pessoa'] ?? '')) . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['ativo'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell(dataBR($r['data_cadastro'] ?? ($r['data_cad'] ?? ''))) . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['cep'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['endereco'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['numero'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['complemento'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['bairro'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['cidade'] ?? '') . "</td>";
    $dadosXls .= "<td>" . xlsCell($r['estado'] ?? '') . "</td>";
    $dadosXls .= "</tr>";
  }
}

$dadosXls .= "</table>";

// =========================
// Headers de download (Excel)
// =========================
$arquivo = "clientes_" . date('Y-m-d_H-i') . ".xls";

// Importante: alguns Excels abrem melhor com HTML completo
$htmlFinal = "<html><head><meta charset='UTF-8'></head><body>{$dadosXls}</body></html>";

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $arquivo . '"');
header('Cache-Control: max-age=0');
header('Pragma: no-cache');

echo $htmlFinal;
exit;

<?php
@session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg = '', $extra = []) {
  echo json_encode(array_merge(['ok'=>$ok, 'msg'=>$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

$doc = (string)($_GET['cpf_cnpj'] ?? '');
$doc = trim($doc);

if ($doc === '') out(false, 'Informe o CPF/CNPJ.');

$docNum = preg_replace('/\D+/', '', $doc);
if (!in_array(strlen($docNum), [11,14], true)) out(false, 'CPF/CNPJ inválido.');

try {
  // Ajuste o nome do campo se necessário:
  $campo = "cpf_cnpj";

  // remove máscara do campo no SQL e compara com docNum
  $sql = "
    SELECT id, nome, email, telefone, {$campo} AS cpf_cnpj
    FROM clientes
    WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$campo},'.',''),'-',''),'/',''),'(',''),')',''),' ','') = :doc
    LIMIT 1
  ";

  $st = $pdo->prepare($sql);
  $st->execute([':doc' => $docNum]);
  $row = $st->fetch(PDO::FETCH_ASSOC);

  if ($row) {
    out(true, '', [
      'exists' => true,
      'data' => [
        'id' => (int)$row['id'],
        'nome' => $row['nome'] ?? '',
        'email' => $row['email'] ?? '',
        'telefone' => $row['telefone'] ?? '',
        'cpf_cnpj' => $row['cpf_cnpj'] ?? '',
      ]
    ]);
  }

  out(true, '', ['exists' => false]);

} catch (Throwable $e) {
  out(false, 'Erro ao buscar cliente: ' . $e->getMessage());
}
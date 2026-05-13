<?php
@session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg, $extra = []){
  echo json_encode(array_merge(['ok'=>(bool)$ok, 'msg'=>(string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}
function soNumeros($v){ return preg_replace('/\D+/', '', (string)$v); }

try{
  $nome  = trim((string)($_POST['nome'] ?? ''));
  $docRaw = trim((string)($_POST['cpf_cnpj'] ?? '')); // pode vir com máscara
  $docNum = soNumeros($docRaw);

  $email = trim((string)($_POST['email'] ?? ''));
  $tel   = trim((string)($_POST['telefone'] ?? ''));

  if ($nome === '' || mb_strlen($nome) < 3) out(false, 'Informe o nome (mínimo 3 caracteres).');
  if ($docNum === '' || !in_array(strlen($docNum), [11,14], true)) out(false, 'Informe um CPF (11) ou CNPJ (14) válido.');

  // coluna do documento na tabela clientes
  $colDocumento = 'cpf_cnpj';

  // ✅ Busca ignorando máscara no banco
  $sqlFind = "
    SELECT id
    FROM clientes
    WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE($colDocumento,'.',''),'-',''),'/',''),'(',''),')',''),' ','') = :doc
    LIMIT 1
  ";
  $st = $pdo->prepare($sqlFind);
  $st->execute([':doc' => $docNum]);
  $cli = $st->fetch(PDO::FETCH_ASSOC);

  if ($cli){
    $clienteId = (int)$cli['id'];

    // (Opcional) Atualiza dados se vierem preenchidos
    // - sem sobrescrever com vazio
    $sets = [];
    $params = [':id' => $clienteId];

    if ($nome !== '') { $sets[] = "nome = :nome"; $params[':nome'] = $nome; }
    if ($email !== '') { $sets[] = "email = :email"; $params[':email'] = $email; }
    if ($tel !== '') { $sets[] = "telefone = :tel"; $params[':tel'] = $tel; }

    // se você quiser padronizar o doc salvo (com máscara), pode atualizar também:
    // se ($docRaw !== '') { $sets[] = "$colDocumento = :docraw"; $params[':docraw'] = $docRaw; }

    if (!empty($sets)) {
      $up = $pdo->prepare("UPDATE clientes SET " . implode(', ', $sets) . " WHERE id = :id LIMIT 1");
      $up->execute($params);
    }

    out(true, 'Cliente encontrado.', ['cliente_id' => $clienteId]);
  }

  // ✅ Não existe: cria novo
  // Decide como salvar:
  // - Se você quer manter com máscara: salva $docRaw (que vem do input mascarado)
  // - Se preferir padronizar no futuro: salve só números ($docNum) e depois ajusta o buscar
  $docSalvar = ($docRaw !== '' ? $docRaw : $docNum);

  $ins = $pdo->prepare("
    INSERT INTO clientes (nome, {$colDocumento}, email, telefone)
    VALUES (:nome, :doc, :email, :telefone)
  ");
  $ins->execute([
    ':nome' => $nome,
    ':doc'  => $docSalvar,
    ':email' => ($email !== '' ? $email : null),
    ':telefone' => ($tel !== '' ? $tel : null),
  ]);

  out(true, 'Cliente criado.', ['cliente_id' => (int)$pdo->lastInsertId()]);

}catch(Throwable $e){
  out(false, 'Erro: ' . $e->getMessage());
}
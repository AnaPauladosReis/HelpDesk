<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../includes/permissoes.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

if (!podeFazer('criar')) {
  out(false, "Sem permissão para importar.");
}

// =========================
// SimpleXLSX (lib local)
// =========================
$lib = __DIR__ . '/../../../libs/simplexlsx-master/src/SimpleXLSX.php';
if (!is_file($lib)) {
  out(false, "Biblioteca SimpleXLSX não encontrada.", ['path' => $lib]);
}
require_once $lib;

use Shuchkin\SimpleXLSX;

// =========================
// Upload
// =========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, "Requisição inválida.");

if (empty($_FILES['arquivo']) || !isset($_FILES['arquivo']['tmp_name'])) {
  out(false, "Envie o arquivo .xlsx no campo 'arquivo'.");
}
if ($_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
  out(false, "Falha no upload do arquivo.");
}

$tmp = $_FILES['arquivo']['tmp_name'];
$name = (string)($_FILES['arquivo']['name'] ?? 'import.xlsx');

$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
if ($ext !== 'xlsx') {
  out(false, "Formato inválido. Envie um arquivo .xlsx");
}

// =========================
// Helpers
// =========================
function normStr($v): string {
  return trim((string)$v);
}
function nullIfEmpty($v) {
  $v = trim((string)$v);
  return $v === '' ? null : $v;
}
function normAtivo($v): ?string {
  $v = mb_strtolower(trim((string)$v));
  if ($v === '') return null;
  if ($v === '1' || $v === 'sim' || $v === 'ativo') return 'Sim';
  if ($v === '0' || $v === 'nao' || $v === 'não' || $v === 'inativo') return 'Não';
  // se vier qualquer coisa, não força
  return null;
}
function normUF($v): ?string {
  $v = strtoupper(trim((string)$v));
  if ($v === '') return null;
  return preg_match('/^[A-Z]{2}$/', $v) ? $v : null;
}
function excelDateToYmd($value): ?string {
  // tenta converter se vier como número (serial do Excel) ou string dd/mm/yyyy
  if ($value === null || $value === '') return null;

  // serial excel (ex: 45234)
  if (is_numeric($value)) {
    $ts = ((float)$value - 25569) * 86400; // base 1970
    if ($ts > 0) return gmdate('Y-m-d', (int)$ts);
  }

  $s = trim((string)$value);
  if ($s === '') return null;

  // dd/mm/yyyy
  if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $s)) {
    [$d,$m,$y] = explode('/', $s);
    return "{$y}-{$m}-{$d}";
  }

  // yyyy-mm-dd
  if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $s)) {
    return $s;
  }

  return null;
}

// =========================
// Parse XLSX
// =========================
$xlsx = SimpleXLSX::parse($tmp);
if (!$xlsx) {
  out(false, "Não foi possível ler o XLSX.", ['erro' => SimpleXLSX::parseError()]);
}

$rows = $xlsx->rows();
if (!$rows || count($rows) < 2) {
  out(false, "Arquivo vazio ou sem linhas de dados.");
}

// Cabeçalhos (linha 1)
$headers = array_map(function($h){
  $h = mb_strtolower(trim((string)$h));
  $h = str_replace(['á','à','â','ã','ä'], 'a', $h);
  $h = str_replace(['é','è','ê','ë'], 'e', $h);
  $h = str_replace(['í','ì','î','ï'], 'i', $h);
  $h = str_replace(['ó','ò','ô','õ','ö'], 'o', $h);
  $h = str_replace(['ú','ù','û','ü'], 'u', $h);
  $h = str_replace('ç', 'c', $h);
  $h = preg_replace('/\s+/', ' ', $h);
  return $h;
}, $rows[0]);

$idx = function(array $headers, array $aliases): ?int {
  foreach ($aliases as $a) {
    $pos = array_search($a, $headers, true);
    if ($pos !== false) return (int)$pos;
  }
  return null;
};

// mapeia colunas por nomes comuns (você pode ajustar fácil)
$map = [
  'nome'         => $idx($headers, ['nome']),
  'email'        => $idx($headers, ['e-mail','email']),
  'telefone'     => $idx($headers, ['telefone','celular','whatsapp']),
  'cpf_cnpj'     => $idx($headers, ['cpf / cnpj','cpf_cnpj','cpf/cnpj','cpf','cnpj']),
  'tipo'         => $idx($headers, ['tipo','tipo pessoa','tipo_pessoa']),
  'ativo'        => $idx($headers, ['ativo','status']),
  'cep'          => $idx($headers, ['cep']),
  'endereco'     => $idx($headers, ['endereco','endereço']),
  'numero'       => $idx($headers, ['numero','número']),
  'complemento'  => $idx($headers, ['complemento']),
  'bairro'       => $idx($headers, ['bairro']),
  'cidade'       => $idx($headers, ['cidade']),
  'estado'       => $idx($headers, ['estado','uf']),
  'observacoes'  => $idx($headers, ['observacoes','observações','obs']),
  'data_cadastro'=> $idx($headers, ['data cadastro','data_cadastro','data cad','data_cad']),
  'senha'        => $idx($headers, ['senha']),
];

// nome tem que existir no arquivo
if ($map['nome'] === null) {
  out(false, "Cabeçalho obrigatório não encontrado: 'Nome'.", ['cabecalhos' => $headers]);
}

// =========================
// Importação (INSERT/UPDATE)
// =========================
$ins = 0;
$upd = 0;
$skip = 0;
$erros = [];

try {
  $pdo->beginTransaction();

  for ($i = 1; $i < count($rows); $i++) {
    $linhaExcel = $i + 1; // (1-based)

    $r = $rows[$i];

    $nome = normStr($r[$map['nome']] ?? '');
    if ($nome === '') {
      $skip++;
      $erros[] = "Linha {$linhaExcel}: Nome vazio (obrigatório).";
      continue;
    }

    $email       = ($map['email'] !== null) ? nullIfEmpty($r[$map['email']] ?? null) : null;
    $telefone    = ($map['telefone'] !== null) ? nullIfEmpty($r[$map['telefone']] ?? null) : null;
    $cpf_cnpj    = ($map['cpf_cnpj'] !== null) ? nullIfEmpty($r[$map['cpf_cnpj']] ?? null) : null;
    $tipo        = ($map['tipo'] !== null) ? nullIfEmpty($r[$map['tipo']] ?? null) : null;

    $ativoNorm   = ($map['ativo'] !== null) ? normAtivo($r[$map['ativo']] ?? null) : null;
    $ativo       = $ativoNorm; // se vier vazio, fica NULL mesmo (campo permite)

    $cep         = ($map['cep'] !== null) ? nullIfEmpty($r[$map['cep']] ?? null) : null;
    $endereco    = ($map['endereco'] !== null) ? nullIfEmpty($r[$map['endereco']] ?? null) : null;
    $numero      = ($map['numero'] !== null) ? nullIfEmpty($r[$map['numero']] ?? null) : null;
    $complemento = ($map['complemento'] !== null) ? nullIfEmpty($r[$map['complemento']] ?? null) : null;
    $bairro      = ($map['bairro'] !== null) ? nullIfEmpty($r[$map['bairro']] ?? null) : null;
    $cidade      = ($map['cidade'] !== null) ? nullIfEmpty($r[$map['cidade']] ?? null) : null;
    $estado      = ($map['estado'] !== null) ? normUF($r[$map['estado']] ?? null) : null;

    $observacoes = ($map['observacoes'] !== null) ? nullIfEmpty($r[$map['observacoes']] ?? null) : null;

    $dataCadastro = null;
    if ($map['data_cadastro'] !== null) {
      $dataCadastro = excelDateToYmd($r[$map['data_cadastro']] ?? null); // Y-m-d
    }

    // senha (se vier preenchida -> hash)
    $senhaHash = null;
    if ($map['senha'] !== null) {
      $senhaPlano = trim((string)($r[$map['senha']] ?? ''));
      if ($senhaPlano !== '') {
        $senhaHash = password_hash($senhaPlano, PASSWORD_DEFAULT);
        if (!$senhaHash) {
          $skip++;
          $erros[] = "Linha {$linhaExcel}: Falha ao gerar hash da senha.";
          continue;
        }
      }
    }

    // =========================
    // Se email existe -> UPDATE (se tiver email)
    // =========================
    $idExistente = 0;

    if ($email) {
      $stE = $pdo->prepare("SELECT id FROM clientes WHERE email = :email LIMIT 1");
      $stE->execute([':email' => $email]);
      $idExistente = (int)($stE->fetchColumn() ?: 0);
    }

    if ($idExistente > 0) {

      $sql = "
        UPDATE clientes SET
          nome = :nome,
          telefone = :telefone,
          cpf_cnpj = :cpf_cnpj,
          ativo = :ativo,
          cep = :cep,
          endereco = :endereco,
          numero = :numero,
          complemento = :complemento,
          bairro = :bairro,
          cidade = :cidade,
          estado = :estado,
          tipo = :tipo,
          observacoes = :observacoes
      ";

      if ($senhaHash) $sql .= ", senha = :senha";
      if ($dataCadastro) $sql .= ", data_cadastro = :data_cadastro";

      $sql .= " WHERE id = :id LIMIT 1";

      $params = [
        ':nome'        => $nome,
        ':telefone'    => $telefone,
        ':cpf_cnpj'    => $cpf_cnpj,
        ':ativo'       => $ativo,
        ':cep'         => $cep,
        ':endereco'    => $endereco,
        ':numero'      => $numero,
        ':complemento' => $complemento,
        ':bairro'      => $bairro,
        ':cidade'      => $cidade,
        ':estado'      => $estado,
        ':tipo'        => $tipo,
        ':observacoes' => $observacoes,
        ':id'          => $idExistente,
      ];

      if ($senhaHash) $params[':senha'] = $senhaHash;
      if ($dataCadastro) $params[':data_cadastro'] = $dataCadastro . ' 00:00:00';

      $pdo->prepare($sql)->execute($params);

      registrarLog($pdo, 'editar', 'clientes', $idExistente, "Cliente '{$nome}' atualizado via importação");
      $upd++;
      continue;
    }

    // =========================
    // INSERT (empresa NÃO obrigatório -> NULL)
    // =========================
    $sqlIns = "
      INSERT INTO clientes
      (nome, email, telefone, cpf_cnpj, senha, ativo,
       cep, endereco, numero, complemento, bairro, cidade, estado, tipo, observacoes,
       ultimo_acesso, ip_ultimo_acesso, data_cadastro, empresa, foto)
      VALUES
      (:nome, :email, :telefone, :cpf_cnpj, :senha, :ativo,
       :cep, :endereco, :numero, :complemento, :bairro, :cidade, :estado, :tipo, :observacoes,
       NULL, NULL, :data_cadastro, NULL, NULL)
    ";

    $pdo->prepare($sqlIns)->execute([
      ':nome'         => $nome,
      ':email'        => $email,
      ':telefone'     => $telefone,
      ':cpf_cnpj'     => $cpf_cnpj,
      ':senha'        => $senhaHash, // pode ser NULL
      ':ativo'        => $ativo,     // pode ser NULL
      ':cep'          => $cep,
      ':endereco'     => $endereco,
      ':numero'       => $numero,
      ':complemento'  => $complemento,
      ':bairro'       => $bairro,
      ':cidade'       => $cidade,
      ':estado'       => $estado,
      ':tipo'         => $tipo,
      ':observacoes'  => $observacoes,
      ':data_cadastro'=> ($dataCadastro ? $dataCadastro . ' 00:00:00' : date('Y-m-d H:i:s')),
    ]);

    $novoId = (int)$pdo->lastInsertId();
    registrarLog($pdo, 'inserir', 'clientes', $novoId, "Cliente '{$nome}' importado via XLSX");
    $ins++;
  }

  $pdo->commit();

} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  out(false, "Erro ao importar: " . $e->getMessage());
}

out(true, "Importação concluída!", [
  'inseridos'   => $ins,
  'atualizados' => $upd,
  'pulados'     => $skip,
  'erros'       => $erros,
]);

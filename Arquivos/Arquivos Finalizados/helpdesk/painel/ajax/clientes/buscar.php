<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'clientes';

function jexit($ok, $msg = '', $data = null) {
  echo json_encode([
    'ok'   => (bool)$ok,
    'msg'  => (string)$msg,
    'data' => $data
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

function esc_url_part($v) {
  // evita path traversal básico
  $v = (string)$v;
  return str_replace(['..', '/', '\\'], '', $v);
}

try {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) jexit(false, 'ID inválido.');

  // não SaaS (empresa sempre 0 por enquanto)
  $stmt = $pdo->prepare("SELECT * FROM {$tabela} WHERE id = :id LIMIT 1");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $c = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$c) jexit(false, 'Cliente não encontrado.');

  // foto padrão
  $foto = trim((string)($c['foto'] ?? ''));
  if ($foto === '') $foto = 'sem_foto.webp';

  $fotoSafe = esc_url_part($foto);
  $fotoUrl  = "../uploads/clientes/" . $fotoSafe;

  // injeta campos auxiliares
  $c['foto']     = $foto;
  $c['foto_url'] = $fotoUrl;

  // defaults esperados pelo front
  $defaults = [
    'id'           => 0,
    'nome'         => '',
    'email'        => '',
    'telefone'     => '',
    'cpf_cnpj'     => '',
    'ativo'        => 'Sim',
    'cep'          => '',
    'endereco'     => '',
    'numero'       => '',
    'bairro'       => '',
    'cidade'       => '',
    'estado'       => '',
    'complemento'  => '',
    'foto'         => 'sem_foto.webp',
    'foto_url'     => $fotoUrl,
    'senha'        => '' // nunca retornar senha
  ];

  // mescla mantendo campos extras da tabela
  $data = array_merge($defaults, $c);

  // segurança extra: nunca retornar hash
  foreach (['senha', 'senha_hash', 'password', 'pass', 'hash'] as $k) {
    if (array_key_exists($k, $data)) $data[$k] = '';
  }

  jexit(true, 'OK', $data);

} catch (Throwable $e) {
  jexit(false, 'Erro ao buscar: ' . $e->getMessage());
}

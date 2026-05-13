<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'usuarios';

function jexit($ok, $msg = '', $data = null) {
  echo json_encode([
    'ok' => (bool)$ok,
    'msg' => (string)$msg,
    'data' => $data
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

function esc_url_part($v) {
  // evita path traversal básico
  $v = (string)$v;
  $v = str_replace(['..', '/', '\\'], '', $v);
  return $v;
}

try {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) jexit(false, 'ID inválido.');

  // ⚠️ Como você ainda não usa SaaS, não filtramos por empresa aqui.
  // Quando virar SaaS: adicionar WHERE empresa = :empresa.
  $stmt = $pdo->prepare("SELECT * FROM {$tabela} WHERE id = :id LIMIT 1");
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $u = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$u) jexit(false, 'Usuário não encontrado.');

  // garante foto padrão
  $foto = trim((string)($u['foto'] ?? ''));
  if ($foto === '') $foto = 'sem_foto.webp';

  $fotoSafe = esc_url_part($foto);

  // Ajuste de caminho:
  // buscar.php está em: painel/ajax/usuarios/buscar.php
  // página usuários carrega em: painel/index.php?p=usuarios
  // listagem usa imagens: ../uploads/perfil/...
  $fotoUrl = "../uploads/perfil/" . $fotoSafe;

  // injeta URLs prontas (útil para modal e "mostrar dados")
  $u['foto'] = $foto;         // nome do arquivo salvo no banco
  $u['foto_url'] = $fotoUrl;  // url pronta para o <img>

  // (opcional) normaliza chaves esperadas (se não existirem na tabela, ficam vazias)
  // Isso ajuda quando você for reutilizar no "mostrar dados" sem ficar checando undefined.
  $defaults = [
    'id' => 0,
    'empresa' => 0,
    'nome' => '',
    'email' => '',
    'telefone' => '',
    'cpf' => '',
    'nivel' => 'comum',
    'ativo' => 'Sim',
    'senha' => '', // nunca devolve senha real (se existir coluna senha, ignore no front)
    'cep' => '',
    'endereco' => '',
    'numero' => '',
    'bairro' => '',
    'cidade' => '',
    'estado' => '',
    'complemento' => '',
    'foto' => 'sem_foto.webp',
    'foto_url' => $fotoUrl
  ];

  // Mescla sem perder campos extras da tabela:
  // - mantém TODOS os campos retornados do SELECT *
  // - garante também os defaults acima
  $data = array_merge($defaults, $u);

  // segurança: nunca retornar hash/senha se tiver coluna
  foreach (['senha', 'senha_hash', 'password', 'pass', 'hash'] as $k) {
    if (array_key_exists($k, $data)) $data[$k] = '';
  }

  jexit(true, 'OK', $data);

} catch (Throwable $e) {
  jexit(false, 'Erro ao buscar: ' . $e->getMessage());
}

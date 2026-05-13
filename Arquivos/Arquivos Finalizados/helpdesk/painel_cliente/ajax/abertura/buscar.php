<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados';

function jexit($ok, $msg = '', $data = null) {
  echo json_encode([
    'ok' => (bool)$ok,
    'msg' => (string)$msg,
    'data' => $data
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) jexit(false, 'ID inválido.');

  $sql = "
    SELECT
      c.id,
      c.protocolo,
      c.assunto,
      c.descricao,
      c.prioridade,
      c.status_id,
      c.cliente_id,
      c.usuario_abertura_id,
      c.usuario_responsavel_id,
      c.criado_em,
      c.atualizado_em,
      c.fechado_em,

      -- NOVO
      c.setor_id,
      se.nome AS setor_nome,

      -- cliente
      cli.nome     AS cliente_nome,
      cli.email    AS cliente_email,
      cli.telefone AS cliente_telefone,

      -- responsável
      ur.nome      AS resp_nome,
      ur.email     AS resp_email,
      ur.telefone  AS resp_telefone,

      -- abertura (quem abriu)
      ua.nome      AS abertura_nome,
      ua.email     AS abertura_email,

      -- status
      st.nome      AS status_nome,
      st.cor       AS status_cor,
      st.fechado   AS status_fechado

    FROM chamados c
    LEFT JOIN setores se ON se.id = c.setor_id
    LEFT JOIN clientes cli ON cli.id = c.cliente_id
    INNER JOIN usuarios ua ON ua.id = c.usuario_abertura_id
    LEFT JOIN usuarios ur  ON ur.id = c.usuario_responsavel_id
    INNER JOIN chamados_status st ON st.id = c.status_id
    WHERE c.id = :id
    LIMIT 1
  ";

  $st = $pdo->prepare($sql);
  $st->bindValue(':id', $id, PDO::PARAM_INT);
  $st->execute();
  $row = $st->fetch(PDO::FETCH_ASSOC);

  if (!$row) jexit(false, 'Chamado não encontrado.');

  // defaults para evitar undefined no JS
  $defaults = [
    'id' => 0,
    'protocolo' => '',
    'assunto' => '',
    'descricao' => '',
    'prioridade' => 'Media',
    'status_id' => 0,
    'cliente_id' => 0,
    'usuario_abertura_id' => 0,
    'usuario_responsavel_id' => null,
    'criado_em' => '',
    'atualizado_em' => '',
    'fechado_em' => '',

    // NOVO
    'setor_id' => 0,
    'setor_nome' => '',

    'cliente_nome' => '',
    'cliente_email' => '',
    'cliente_telefone' => '',

    'resp_nome' => '',
    'resp_email' => '',
    'resp_telefone' => '',

    'abertura_nome' => '',
    'abertura_email' => '',

    'status_nome' => '',
    'status_cor' => '#6c757d',
    'status_fechado' => 'Não',
  ];

  $data = array_merge($defaults, $row);

  jexit(true, 'OK', $data);

} catch (Throwable $e) {
  jexit(false, 'Erro ao buscar: ' . $e->getMessage());
}
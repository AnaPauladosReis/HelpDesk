<?php
@session_start();
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // ✅ DEBUG (coloque true temporariamente pra ver o que está vindo)
  $DEBUG = true;

  $cliente_id = (int)($_SESSION['cliente_id'] ?? 0);

  if ($cliente_id <= 0) {
    out(false, 'Sessão expirada.', $DEBUG ? ['debug' => ['session' => $_SESSION]] : []);
  }

  // ✅ versão mais "tolerante" no tipo_autor:
  // - cobre "usuario" e "usuário"
  // - e ainda cobre quando o usuario_id está preenchido
  $sql = "
    SELECT
      c.id,
      c.protocolo,
      c.assunto,
      cli.nome AS cliente_nome,
      ult.criado_em AS ultima_resposta_em,
      ult.tipo_autor,
      ult.usuario_id,
      u.nome AS usuario_nome
    FROM chamados c
    LEFT JOIN clientes cli ON cli.id = c.cliente_id

    LEFT JOIN chamados_respostas ult
      ON ult.id = (
        SELECT r2.id
        FROM chamados_respostas r2
        WHERE r2.chamado_id = c.id
        ORDER BY r2.id DESC
        LIMIT 1
      )

    LEFT JOIN usuarios u ON u.id = ult.usuario_id

    WHERE c.cliente_id = :cliente_id
      AND ult.id IS NOT NULL
      AND (
        ult.tipo_autor IN ('usuario','usuário')
        
      )

    ORDER BY ult.criado_em DESC
    LIMIT 50
  ";

  $st = $pdo->prepare($sql);
  $st->execute([':cliente_id' => $cliente_id]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  out(true, 'ok', [
    'count' => count($rows),
    'items' => $rows,
    // debug opcional
    'debug' => $DEBUG ? [
      'cliente_id' => $cliente_id,
      'tipos_encontrados' => array_values(array_unique(array_map(fn($r)=> (string)$r['tipo_autor'], $rows))),
    ] : null
  ]);

} catch (Throwable $e) {
  out(false, 'Erro: ' . $e->getMessage());
}
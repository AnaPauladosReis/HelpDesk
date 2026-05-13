<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';

header('Content-Type: application/json; charset=utf-8');

function out($ok, $msg, $extra = []) {
    echo json_encode(array_merge(['ok'=>(bool)$ok,'msg'=>(string)$msg],$extra), JSON_UNESCAPED_UNICODE);
    exit;
}

try {

    $usuario_logado = (int)($_SESSION['id'] ?? 0);
    $nivel = strtolower(trim($_SESSION['nivel'] ?? ''));
    $empresa = (int)($_SESSION['id_empresa'] ?? 0);

    $where = [];
    $params = [];

    // ===== PERMISSÕES (MESMA LÓGICA DO LISTAR) =====
    if ($nivel !== 'administrador') {

        // busca setores permitidos
        $stSet = $pdo->prepare("
            SELECT setor_id 
            FROM usuarios_setores 
            WHERE usuario_id = :uid
        ");
        $stSet->execute([':uid' => $usuario_logado]);
        $setoresPermitidos = $stSet->fetchAll(PDO::FETCH_COLUMN);

        $params[':usuario_logado'] = $usuario_logado;

        if (!empty($setoresPermitidos)) {

            $placeholders = [];
            foreach ($setoresPermitidos as $k => $sid) {
                $ph = ":set_perm_$k";
                $placeholders[] = $ph;
                $params[$ph] = (int)$sid;
            }

            $where[] = "(
                c.usuario_responsavel_id = :usuario_logado
                OR (
                    c.usuario_responsavel_id IS NULL
                    AND c.setor_id IN (" . implode(',', $placeholders) . ")
                )
            )";

        } else {
            $where[] = "c.usuario_responsavel_id = :usuario_logado";
        }
    }

    // ===== EMPRESA =====
    $where[] = "c.empresa = :empresa";
    $params[':empresa'] = $empresa;

    // ===== ÚLTIMA RESPOSTA DO CLIENTE =====
    $where[] = "ult.tipo_autor = 'cliente'";

    $sql = "
        SELECT
            c.id,
            c.protocolo,
            cli.nome AS cliente_nome,
            ult.criado_em AS ultima_resposta_em
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
    ";

    if (count($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY ult.criado_em DESC";

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    out(true, 'ok', [
        'count' => count($rows),
        'items' => $rows
    ]);

} catch (Throwable $e) {
    out(false, $e->getMessage());
}
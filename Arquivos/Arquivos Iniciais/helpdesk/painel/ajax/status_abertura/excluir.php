<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'chamados_status'; // ✅ status_abertura

require_once __DIR__ . '/../../includes/permissoes.php';
if (!podeFazer('excluir')) {
  echo json_encode(['ok'=>false,'msg'=>'Sem permissão para excluir.'], JSON_UNESCAPED_UNICODE);
  exit;
}

function jexit($ok, $msg) {
  echo json_encode(['ok'=>(bool)$ok,'msg'=>(string)$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
    // aceita POST padrão (form/urlencoded) e também JSON
    $id = 0;

    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
    } else {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json['id'])) {
                $id = (int)$json['id'];
            }
        }
    }

    if ($id <= 0) jexit(false, 'ID inválido.');

    // confirma existência e pega nome (pra log)
    $stmt = $pdo->prepare("SELECT id, nome, padrao FROM {$tabela} WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) jexit(false, 'Status não encontrado.');

    // ✅ bloqueia exclusão se estiver em uso por chamados
    // (se ainda não existir a tabela chamados, pode comentar esse bloco por enquanto)
    $emUso = 0;
    try {
        $stUso = $pdo->prepare("SELECT COUNT(*) FROM chamados WHERE status_id = :id");
        $stUso->execute([':id' => $id]);
        $emUso = (int)$stUso->fetchColumn();
    } catch (Throwable $ignore) {
        // se a tabela chamados ainda não existe, não bloqueia
        $emUso = 0;
    }

    if ($emUso > 0) {
        jexit(false, "Não é possível excluir: este status está sendo usado em {$emUso} chamado(s).");
    }

    // ✅ opcional: impede excluir status padrão (recomendado)
    if (($row['padrao'] ?? 'Não') === 'Sim') {
        jexit(false, "Não é possível excluir o status padrão. Defina outro como padrão antes.");
    }

    // executa exclusão
    $del = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id LIMIT 1");
    $del->execute([':id' => $id]);

    registrarLog(
        $pdo,
        'excluir',
        $tabela,
        $id,
        "Status '{$row['nome']}' excluído"
    );

    jexit(true, 'Status excluído com sucesso!');

} catch (Throwable $e) {
    jexit(false, 'Erro ao excluir: ' . $e->getMessage());
}

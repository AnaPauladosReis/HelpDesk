<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'clientes';

require_once __DIR__ . '/../../includes/permissoes.php';
if (!podeFazer('excluir')) {
  echo json_encode(['ok'=>false,'msg'=>'Sem permissão para excluir.']);
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

    if ($id <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'ID inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // busca cliente para confirmar existência e pegar foto
    $stmt = $pdo->prepare("SELECT id, foto FROM {$tabela} WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['ok' => false, 'msg' => 'Cliente não encontrado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // executa exclusão
    $del = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id LIMIT 1");
    $del->execute([':id' => $id]);


    registrarLog(
        $pdo,
        'excluir',
        $tabela,
        $id,
        'Usuário excluído do sistema'
    );

    // remove foto do disco (opcional) - não remove sem_foto.webp
    $foto = trim((string)($row['foto'] ?? ''));
    if ($foto !== '' && $foto !== 'sem_foto.webp') {
        // proteção simples contra path traversal
        $foto = str_replace(['..', '/', '\\'], '', $foto);

        $path = __DIR__ . '/../../../uploads/clientes/' . $foto;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    echo json_encode(['ok' => true, 'msg' => 'Cliente excluído com sucesso!'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro ao excluir: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

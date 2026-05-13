<?php
@session_start();
require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';

header('Content-Type: application/json; charset=utf-8');

$tabela = 'usuarios';

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
        echo json_encode(['ok' => false, 'msg' => 'ID inválido.']);
        exit;
    }

    // impede excluir a si mesmo (recomendado)
    $id_logado = (int)($usuario_id ?? ($_SESSION['id'] ?? 0));
    if ($id_logado > 0 && $id === $id_logado) {
        echo json_encode(['ok' => false, 'msg' => 'Você não pode excluir o seu próprio usuário.']);
        exit;
    }

    // busca usuário (para foto)
    $stmt = $pdo->prepare("SELECT id, foto FROM {$tabela} WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['ok' => false, 'msg' => 'Usuário não encontrado.']);
        exit;
    }

    // guarda foto para remover depois do commit
    $foto = trim((string)($row['foto'] ?? ''));

    // =========================
    // TRANSAÇÃO (permissões + usuário)
    // =========================
    $pdo->beginTransaction();

    // 1) remove permissões
    $delPerm = $pdo->prepare("DELETE FROM usuarios_permissoes WHERE usuario_id = :id");
    $delPerm->execute([':id' => $id]);

    $pdo->prepare("DELETE FROM usuarios_acoes WHERE usuario_id = :id")->execute([':id' => $id]);

    // 2) remove usuário
    $delUser = $pdo->prepare("DELETE FROM {$tabela} WHERE id = :id LIMIT 1");
    $delUser->execute([':id' => $id]);

    $pdo->commit();

    registrarLog(
        $pdo,
        'excluir',
        $tabela,
        $id,
        'Usuário excluído do sistema'
    );

    // remove foto do disco (após commit) - não remove sem_foto.webp
    if ($foto !== '' && $foto !== 'sem_foto.webp') {
        $path = __DIR__ . '/../../../uploads/perfil/' . $foto;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    echo json_encode(['ok' => true, 'msg' => 'Usuário excluído com sucesso!']);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['ok' => false, 'msg' => 'Erro ao excluir: ' . $e->getMessage()]);
}

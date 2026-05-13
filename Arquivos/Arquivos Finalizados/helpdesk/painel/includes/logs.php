<?php
/**
 * Função global para registrar logs do sistema
 * Ações: login, logout, inserir, editar, excluir
 */

if (!function_exists('registrarLog')) {

    function registrarLog(PDO $pdo, string $acao, string $entidade = '', ?int $registro_id = null, string $descricao = ''): void
    {
        try {

            if (session_status() !== PHP_SESSION_ACTIVE) {
                @session_start();
            }

            $empresa    = (int)($_SESSION['id_empresa'] ?? 0);
            $usuario_id = (int)($_SESSION['id'] ?? 0);

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            // rota atual (ajax ou página)
            $rota = $_SERVER['REQUEST_URI'] ?? ($_SERVER['PHP_SELF'] ?? null);

            $stmt = $pdo->prepare("
                INSERT INTO logs
                (empresa, usuario_id, acao, entidade, registro_id, descricao, rota, ip, user_agent, criado_em)
                VALUES
                (:empresa, :usuario_id, :acao, :entidade, :registro_id, :descricao, :rota, :ip, :user_agent, NOW())
            ");

            $stmt->execute([
                ':empresa'      => $empresa,
                ':usuario_id'   => $usuario_id,
                ':acao'         => $acao,
                ':entidade'     => $entidade,
                ':registro_id'  => $registro_id,
                ':descricao'    => $descricao,
                ':rota'         => $rota,
                ':ip'           => $ip,
                ':user_agent'   => $user_agent,
            ]);

        } catch (Throwable $e) {
            // nunca quebrar o sistema por causa de log
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }
}

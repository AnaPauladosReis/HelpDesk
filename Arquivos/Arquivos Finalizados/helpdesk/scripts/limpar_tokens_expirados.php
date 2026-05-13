<?php
/**
 * scripts/limpar_tokens_expirados.php
 * Remove/Marca tokens expirados (pode rodar manual ou via cron)
 */

require_once __DIR__ . '/../conexao.php';

// Marca como usados os expirados e não usados (mantém histórico)
$stmt = $pdo->prepare("
    UPDATE recuperacao_senha
    SET usado_em = NOW()
    WHERE usado_em IS NULL
      AND expira_em < NOW()
");
$stmt->execute();

echo "OK - Tokens expirados marcados como usados. Linhas: " . $stmt->rowCount();

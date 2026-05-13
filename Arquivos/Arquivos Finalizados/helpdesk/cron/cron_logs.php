<?php
/**
 * CRON - Excluir logs antigos
 * Caminho: /cron/excluir_logs.php
 *
 * Usa a variável $dias_excluir_logs definida no conexao.php
 * Ex.: $dias_excluir_logs = 60;  // mantém 60 dias e exclui o que for mais antigo
 */

declare(strict_types=1);

// Segurança: não deixa rodar via navegador
if (php_sapi_name() !== 'cli') {
  //http_response_code(403);
  //exit('Acesso negado.');
}

@date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../conexao.php'; // ajuste se o seu conexao estiver em outro caminho

// Valida configuração
$dias = isset($dias_excluir_logs) ? (int)$dias_excluir_logs : 0;
if ($dias <= 0) {
  echo "[ERRO] dias_excluir_logs inválido (<=0). Nada foi removido.\n";
  exit(1);
}

// Cutoff: tudo antes dessa data será removido
$cutoff = (new DateTime('now'))->modify("-{$dias} days")->format('Y-m-d H:i:s');

try {
  // Se quiser excluir só de uma empresa específica, defina aqui:
  // $empresaId = 0; // 0 = todas
  $empresaId = 0;

  if ($empresaId > 0) {
    $sql = "DELETE FROM logs WHERE criado_em < :cutoff AND empresa = :empresa";
    $st = $pdo->prepare($sql);
    $st->execute([
      ':cutoff'  => $cutoff,
      ':empresa' => $empresaId
    ]);
  } else {
    $sql = "DELETE FROM logs WHERE criado_em < :cutoff";
    $st = $pdo->prepare($sql);
    $st->execute([
      ':cutoff' => $cutoff
    ]);
  }

  $apagados = $st->rowCount();

  echo "[OK] Logs removidos: {$apagados}\n";
  echo "[OK] Mantidos: últimos {$dias} dias\n";
  echo "[OK] Cutoff: {$cutoff}\n";

  exit(0);

} catch (Throwable $e) {
  echo "[ERRO] Falha ao excluir logs: " . $e->getMessage() . "\n";
  exit(1);
}
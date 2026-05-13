<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/permissoes.php';
require_once __DIR__ . '/../../includes/logs.php';

function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok'=>(bool)$ok,'msg'=>(string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

if (!podeFazer('editar')) out(false, "Sem permissão para gerar backup.");

try {
  $backupDir = __DIR__ . '/../../backups/';
  if (!is_dir($backupDir)) {
    if (!mkdir($backupDir, 0777, true)) {
      throw new Exception("Não foi possível criar a pasta de backups.");
    }
  }

  // nome do arquivo
  $data = date('Y-m-d_H-i-s');
  $file = "backup_{$banco}_{$data}.sql";
  $path = $backupDir . $file;

  // cabeçalho
  $sqlOut  = "-- Backup gerado em: " . date('d/m/Y H:i:s') . "\n";
  $sqlOut .= "-- Banco: {$banco}\n";
  $sqlOut .= "SET NAMES utf8mb4;\n";
  $sqlOut .= "SET time_zone = '+00:00';\n";
  $sqlOut .= "SET foreign_key_checks = 0;\n\n";

  // lista tabelas
  $tables = [];
  $stTables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
  while ($r = $stTables->fetch(PDO::FETCH_NUM)) {
    $tables[] = $r[0];
  }
  if (!$tables) throw new Exception("Nenhuma tabela encontrada para backup.");

  foreach ($tables as $table) {
    // CREATE TABLE
    $stCreate = $pdo->query("SHOW CREATE TABLE `{$table}`");
    $rowCreate = $stCreate->fetch(PDO::FETCH_ASSOC);
    $createSql = $rowCreate['Create Table'] ?? '';
    if ($createSql === '') continue;

    $sqlOut .= "\n-- ----------------------------\n";
    $sqlOut .= "-- Estrutura da tabela `{$table}`\n";
    $sqlOut .= "-- ----------------------------\n";
    $sqlOut .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $sqlOut .= $createSql . ";\n\n";

    // Dados
    $stCount = $pdo->query("SELECT COUNT(*) AS total FROM `{$table}`");
    $total = (int)($stCount->fetch()['total'] ?? 0);

    if ($total > 0) {
      $sqlOut .= "-- Dados da tabela `{$table}` ({$total} registros)\n";

      $stData = $pdo->query("SELECT * FROM `{$table}`");
      $cols = [];
      $colCount = $stData->columnCount();
      for ($i=0; $i<$colCount; $i++) {
        $meta = $stData->getColumnMeta($i);
        $cols[] = "`" . ($meta['name'] ?? "col{$i}") . "`";
      }
      $colsList = implode(',', $cols);

      $batch = 0;
      while ($row = $stData->fetch(PDO::FETCH_ASSOC)) {
        $values = [];
        foreach ($row as $v) {
          if ($v === null) $values[] = "NULL";
          else $values[] = $pdo->quote($v);
        }
        $sqlOut .= "INSERT INTO `{$table}` ({$colsList}) VALUES (" . implode(',', $values) . ");\n";

        $batch++;
        // flush básico pra não estourar memória em tabelas grandes
        if ($batch >= 500) {
          file_put_contents($path, $sqlOut, FILE_APPEND);
          $sqlOut = "";
          $batch = 0;
        }
      }
      $sqlOut .= "\n";
    }
  }

  $sqlOut .= "\nSET foreign_key_checks = 1;\n";
  file_put_contents($path, $sqlOut, FILE_APPEND);

  // log (no seu padrão)
  $id_user = (int)($_SESSION['id_usuario'] ?? 0);
  registrarLog($pdo, 'inserir', 'backup', 0, "Gerou backup do banco {$banco} ({$file})");

  out(true, "Backup gerado.", ['file' => $file]);

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}
<?php
// painel/includes/permissoes.php

function usuarioEhAdmin(): bool {
  $nivel = 'Cliente';
  return $nivel === 'Cliente';
}

function usuarioIdLogado(): int {
  return (int)($_SESSION['id'] ?? 0);
}

/**
 * Retorna array com os IDs permitidos: ['dashboard'=>true, 'clientes'=>true, ...]
 * Admin: retorna null (significa acesso total)
 */
function permissoesUsuario(PDO $pdo): ?array {
  if (usuarioEhAdmin()) return null;

  $uid = usuarioIdLogado();
  if ($uid <= 0) return []; // não logado (segurança)

  $stmt = $pdo->prepare("SELECT menu_id FROM usuarios_permissoes WHERE usuario_id = :id");
  $stmt->execute([':id' => $uid]);
  $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

  $map = [];
  foreach (($ids ?: []) as $m) {
    $m = trim((string)$m);
    if ($m !== '') $map[$m] = true;
  }
  return $map;
}

/** true se pode ver/acessar */
function podeAcessar(?array $perms, string $menuId): bool {
  if ($perms === null) return true; // admin
  return isset($perms[$menuId]);
}



function podeFazer(string $acao): bool {
  if (trim((string)($_SESSION['nivel'] ?? '')) === 'Administrador') return true;
  $acoes = $_SESSION['acoes'] ?? [];
  return !empty($acoes[$acao]);
}

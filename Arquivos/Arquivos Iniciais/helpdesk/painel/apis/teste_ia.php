<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../conexao.php';

// IA
$api_ia             = $_POST['api_ia']             ?? ($config['api_ia']             ?? 'Nenhuma');
$token_ia           = $_POST['token_ia']           ?? ($config['token_ia']           ?? '');

$prompt_ia = "Responda em PT-BR: diga 'ok' e me dê 1 dica rápida de produtividade.";
require_once __DIR__ . '/texto_ia.php';

echo "<div style='font-family:Arial; padding:10px'>";
echo "<b>Status:</b> " . ($ia_ok ? "✅ OK" : "❌ Falhou") . "<br>";
echo "<b>HTTP:</b> " . ($ia_http ?? '-') . "<br>";
echo "<b>Mensagem:</b> " . htmlspecialchars($ia_msg) . "<br><br>";

if ($ia_ok) {
    echo "<b>Resposta:</b><br>";
    echo "<div style='white-space:pre-wrap; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:6px;'>";
    echo htmlspecialchars($ia_text);
    echo "</div>";
} else {
    // se quiser depurar quando falhar, descomenta:
    // echo "<pre>".htmlspecialchars($ia_raw)."</pre>";
}
echo "</div>";


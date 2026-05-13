<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

$tabela = 'clientes';

require_once __DIR__ . '/../../verificar.php';
require_once __DIR__ . '/../../../conexao.php';
require_once __DIR__ . '/../../includes/logs.php';
require_once __DIR__ . '/../../funcoes/email.php';


function out($ok, $msg, $extra = []) {
  echo json_encode(array_merge(['ok' => (bool)$ok, 'msg' => (string)$msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

try {

  // =========================
  // INPUTS
  // =========================
  $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;

  $nome       = trim($_POST['nome'] ?? '');
  $telefone   = trim($_POST['telefone'] ?? '');
  $email      = trim($_POST['email'] ?? '');
  $cpf_cnpj   = trim($_POST['cpf_cnpj'] ?? '');

  $tipo       = trim($_POST['tipo'] ?? '');
  $ativo      = trim($_POST['ativo'] ?? 'Sim');

  $cep         = trim($_POST['cep'] ?? '');
  $endereco    = trim($_POST['endereco'] ?? '');
  $numero      = trim($_POST['numero'] ?? '');
  $complemento = trim($_POST['complemento'] ?? '');
  $bairro      = trim($_POST['bairro'] ?? '');
  $cidade      = trim($_POST['cidade'] ?? '');
  $estado      = strtoupper(trim($_POST['estado'] ?? ''));

  $observacoes = trim($_POST['observacoes'] ?? '');
  $enviar_notificacao = trim($_POST['enviar_notificacao'] ?? '');  

  $foto_atual = trim($_POST['foto_atual'] ?? 'sem_foto.webp');
  if ($foto_atual === '') $foto_atual = 'sem_foto.webp';

  // =========================
  // VALIDAÇÕES
  // =========================

  $acao = $id > 0 ? 'editar' : 'criar';

    require_once __DIR__ . '/../../includes/permissoes.php';
    if (!podeFazer($acao)) {
    echo json_encode(['ok'=>false,'msg'=>"Sem permissão para $acao."]);
    exit;
    }

    
  if ($nome === '') out(false, "Informe o nome.");

  // e-mail é opcional, mas se vier precisa ser válido
  if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(false, "Informe um e-mail válido.");
  }

  // normaliza ativo
  if (!in_array($ativo, ['Sim', 'Não'], true)) {
    if ($ativo === '1') $ativo = 'Sim';
    elseif ($ativo === '0') $ativo = 'Não';
    else $ativo = 'Sim';
  }

  if ($estado !== '' && !preg_match('/^[A-Z]{2}$/', $estado)) {
    out(false, "UF inválida.");
  }

  // =========================
  // E-MAIL DUPLICADO (se informado)
  // =========================
  if ($email !== '') {
    $sqlDup = "SELECT id FROM {$tabela} WHERE email = :email";
    if ($id > 0) $sqlDup .= " AND id <> :id";
    $sqlDup .= " LIMIT 1";

    $stDup = $pdo->prepare($sqlDup);
    $stDup->bindValue(':email', $email);
    if ($id > 0) $stDup->bindValue(':id', $id, PDO::PARAM_INT);
    $stDup->execute();

    if ($stDup->fetch()) out(false, "Este e-mail já está cadastrado para outro cliente.");
  }

  // =========================
  // UPLOAD FOTO
  // =========================
  $foto_final = $foto_atual;

  $uploadDir = realpath(__DIR__ . '/../../../uploads/clientes');
  if ($uploadDir === false) {
    $try = __DIR__ . '/../../../uploads/clientes';
    @mkdir($try, 0775, true);
    $uploadDir = realpath($try);
  }
  if ($uploadDir === false) out(false, "Pasta de upload não encontrada: uploads/clientes.");

  if (!empty($_FILES['foto']) && isset($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) out(false, "Falha no upload da foto.");

    $maxBytes = 2 * 1024 * 1024; // 2MB
    if ((int)$_FILES['foto']['size'] > $maxBytes) out(false, "Envie uma imagem de até 2MB.");

    $tmp = $_FILES['foto']['tmp_name'];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png'  => 'png',
      'image/webp' => 'webp',
    ];

    if (!isset($allowed[$mime])) out(false, "Formato inválido. Use JPG, PNG ou WEBP.");

    $ext = $allowed[$mime];

    // nome seguro
    $safeBase = 'c_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    $fileName = $safeBase . '.' . $ext;

    $dest = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmp, $dest)) out(false, "Não foi possível salvar a foto enviada.");

    // apaga foto antiga (se não for padrão)
    $padrao = 'sem_foto.webp';
    if ($foto_atual && $foto_atual !== $padrao) {
      $old = $uploadDir . DIRECTORY_SEPARATOR . $foto_atual;
      if (is_file($old)) @unlink($old);
    }

    $foto_final = $fileName;
  }

  // =========================
  // SENHA PADRÃO (APENAS NO INSERT)
  // =========================
  // Você disse que quer sempre a senha padrão do conexao.php.
  // Aqui usamos $teste_senha como exemplo, pois foi a variável que você mostrou.
  // Se você usar outro nome depois, é só trocar aqui.
  $senhaHashPadrao = null;
  if ($id === 0) {
    $senhaBase = (string)($teste_senha ?? '123');
    $senhaHashPadrao = password_hash($senhaBase, PASSWORD_DEFAULT);
    if (!$senhaHashPadrao) out(false, "Erro ao gerar hash da senha padrão.");
  }

  // =========================
  // INSERT / UPDATE
  // =========================
  if ($id === 0) {

    $stmt = $pdo->prepare("
      INSERT INTO {$tabela}
      (nome, email, telefone, cpf_cnpj, senha, ativo, cep, endereco, numero, complemento, bairro, cidade, estado, tipo, observacoes, ultimo_acesso, ip_ultimo_acesso, data_cadastro, empresa, foto)
      VALUES
      (:nome, :email, :telefone, :cpf_cnpj, :senha, :ativo, :cep, :endereco, :numero, :complemento, :bairro, :cidade, :estado, :tipo, :observacoes, NULL, NULL, NOW(), 0, :foto)
    ");

    $stmt->execute([
      ':nome'        => $nome,
      ':email'       => ($email !== '' ? $email : null),
      ':telefone'    => ($telefone !== '' ? $telefone : null),
      ':cpf_cnpj'    => ($cpf_cnpj !== '' ? $cpf_cnpj : null),
      ':senha'       => $senhaHashPadrao,
      ':ativo'       => $ativo,
      ':cep'         => ($cep !== '' ? $cep : null),
      ':endereco'    => ($endereco !== '' ? $endereco : null),
      ':numero'      => ($numero !== '' ? $numero : null),
      ':complemento' => ($complemento !== '' ? $complemento : null),
      ':bairro'      => ($bairro !== '' ? $bairro : null),
      ':cidade'      => ($cidade !== '' ? $cidade : null),
      ':estado'      => ($estado !== '' ? $estado : null),
      ':tipo'        => ($tipo !== '' ? $tipo : null),
      ':observacoes' => ($observacoes !== '' ? $observacoes : null),
      ':foto'        => ($foto_final !== '' ? $foto_final : 'sem_foto.webp'),
    ]);

    
    //enviar disparo api whatsapp
    if($api_whatsapp != "Nenhuma" && $telefone != "" && $enviar_notificacao == 'Sim'){
      $telefone_disparo = preg_replace('/\D+/', '', $telefone);

      $prompt_ia = "
      Você é um especialista em comunicação profissional via WhatsApp.
      
      Crie APENAS a mensagem final, pronta para envio direto no WhatsApp.
      Não inclua explicações, títulos, introduções ou separadores.
      
      Objetivo:
      Mensagem curta de boas-vindas para um novo cliente.
      
      Regras obrigatórias:
      - Idioma: português do Brasil (PT-BR)
      - Tom: profissional, amigável e acolhedor
      - Use negrito SOMENTE envolvendo o texto com asteriscos (*), conforme padrão do WhatsApp
      - O NOME DO CLIENTE deve estar em negrito
      - NÃO use negrito em mais nenhum outro trecho do texto
      - Use no máximo 2 emojis
      - Não escreva frases como \"Claro\", \"Segue\", \"Aqui está\"
      - Texto curto, claro e direto
      - Não utilize listas longas
      
      Dados:
      - Nome do cliente: {$nome}
      - Nome do sistema: {$nome_sistema}
      - Use seu Telefone ou CPF para acesso, a senha padrão é 123
      - Troque a senha quando acessar o sistema!
      - Link do sistema: {$url_sistema}acesso'
      
      Formatação obrigatória:
      - Saudação com o nome do cliente em negrito
      - Corpo da mensagem sem negrito
      - O link deve ficar em uma linha separada
      - Coloque um ícone antes do link (ex: 🔗 ou 👉)
      - Assinatura curta no final
      ";
      
      require_once __DIR__ . '/../../apis/texto_ia.php';                             
      $mensagem_whatsapp = htmlspecialchars($ia_text);
      require_once __DIR__ . '/../../apis/texto_whatsapp.php';
      

  }



  // =========================
// DISPARO E-MAIL (novo cliente)
// =========================
$emailEnvioOk = null;
$emailEnvioErro = '';
$emailEnvioDebug = '';

try {

  // só envia se o cliente marcou enviar_notificacao e tem e-mail válido
  if ($enviar_notificacao === 'Sim' && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

    // link do sistema
    $linkSistema = trim((string)($url_sistema.'acesso' ?? ''));

    // se $url_sistema não existir, monta pelo host atual
    if ($linkSistema === '') {
      $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
      $host  = $_SERVER['HTTP_HOST'] ?? '';
      $linkSistema = $host ? ($proto . '://' . $host) : '';
    }

    // senha base (padrão) que você gerou no hash
    $senhaBase = (string)($teste_senha ?? '123');

    $assunto = "Bem-vindo(a) - {$nome_sistema}";
    $titulo  = "Cadastro realizado";

    $mensagemHtml = "
      <p>Olá <strong>" . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
      <p>Seu cadastro no <strong>" . htmlspecialchars($nome_sistema, ENT_QUOTES, 'UTF-8') . "</strong> foi realizado com sucesso.</p>

      <p><b>Dados de acesso:</b></p>
      <ul style='margin:0; padding-left:18px;'>
        <li><b>CPF ou Telefone para Acesso</b></li>
        <li><b>Senha:</b> " . htmlspecialchars($senhaBase, ENT_QUOTES, 'UTF-8') . "</li>
      </ul>

      <p style='margin-top:10px;'>Por segurança, altere sua senha no primeiro acesso.</p>
    ";

    $rodapeExtra = $linkSistema !== ''
      ? "Se o botão não funcionar, copie e cole o link abaixo no navegador:<br>
         <span style='word-break:break-all;'>" . htmlspecialchars($linkSistema, ENT_QUOTES, 'UTF-8') . "</span>"
      : "";

    $htmlEmail = emailTemplatePadrao(
      $titulo,
      $mensagemHtml,
      ($linkSistema !== '' ? 'Acessar o sistema' : ''),
      ($linkSistema !== '' ? $linkSistema : ''),
      $rodapeExtra
    );

    $env = enviarEmailPadrao($email, $nome, $assunto, $htmlEmail);

    $emailEnvioOk    = (bool)($env['ok'] ?? false);
    $emailEnvioErro  = (string)($env['erro'] ?? '');
    $emailEnvioDebug = (string)($env['debug'] ?? '');

    if (!$emailEnvioOk) {
      // log servidor
      error_log("Falha e-mail novo cliente ({$email}): {$emailEnvioErro} | {$emailEnvioDebug}");

      // log sistema (opcional)
      registrarLog(
        $pdo,
        'editar',
        $tabela,
        $novoId,
        "Falha ao enviar e-mail de boas-vindas para {$email} ({$emailEnvioErro})"
      );
    }
  }

} catch (Throwable $e) {
  $emailEnvioOk = false;
  $emailEnvioErro = 'Exceção ao tentar enviar e-mail.';
  $emailEnvioDebug = $e->getMessage();
  error_log("Exceção e-mail novo cliente ({$email}): " . $e->getMessage());
}



  $novoId = (int)$pdo->lastInsertId();

        // 🔥 REGISTRA LOG (INSERIR)
        registrarLog(
            $pdo,
            'inserir',
            $tabela,
            $novoId,
            "Cliente '{$nome}' cadastrado"
        );
  
    out(true, "Cliente cadastrado com sucesso!");

  } else {

    // garante que existe
    $stCheck = $pdo->prepare("SELECT id, foto FROM {$tabela} WHERE id = :id LIMIT 1");
    $stCheck->execute([':id' => $id]);
    $existe = $stCheck->fetch(PDO::FETCH_ASSOC);
    if (!$existe) out(false, "Cliente não encontrado.");

    $sql = "
      UPDATE {$tabela} SET
        nome = :nome,
        email = :email,
        telefone = :telefone,
        cpf_cnpj = :cpf_cnpj,
        ativo = :ativo,
        cep = :cep,
        endereco = :endereco,
        numero = :numero,
        complemento = :complemento,
        bairro = :bairro,
        cidade = :cidade,
        estado = :estado,
        tipo = :tipo,
        observacoes = :observacoes,
        foto = :foto
      WHERE id = :id
      LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
      ':nome'        => $nome,
      ':email'       => ($email !== '' ? $email : null),
      ':telefone'    => ($telefone !== '' ? $telefone : null),
      ':cpf_cnpj'    => ($cpf_cnpj !== '' ? $cpf_cnpj : null),
      ':ativo'       => $ativo,
      ':cep'         => ($cep !== '' ? $cep : null),
      ':endereco'    => ($endereco !== '' ? $endereco : null),
      ':numero'      => ($numero !== '' ? $numero : null),
      ':complemento' => ($complemento !== '' ? $complemento : null),
      ':bairro'      => ($bairro !== '' ? $bairro : null),
      ':cidade'      => ($cidade !== '' ? $cidade : null),
      ':estado'      => ($estado !== '' ? $estado : null),
      ':tipo'        => ($tipo !== '' ? $tipo : null),
      ':observacoes' => ($observacoes !== '' ? $observacoes : null),
      ':foto'        => ($foto_final !== '' ? $foto_final : 'sem_foto.webp'),
      ':id'          => $id,
    ]);

    registrarLog(
      $pdo,
      'editar',
      $tabela,
      $id,
      "Cliente '{$nome}' atualizado"
  );

    out(true, "Cliente atualizado com sucesso!");
  }

} catch (Throwable $e) {
  out(false, "Erro: " . $e->getMessage());
}

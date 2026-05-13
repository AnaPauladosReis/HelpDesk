<?php
@session_start();
header('Content-Type: application/json; charset=utf-8');

$tabela = 'usuarios';

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
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $empresa   = isset($_POST['empresa']) ? (int)$_POST['empresa'] : 0;

    $nome      = trim($_POST['nome'] ?? '');
    $telefone  = trim($_POST['telefone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $senha     = (string)($_POST['senha'] ?? '');
    $cpf       = trim($_POST['cpf'] ?? '');

    $cep          = trim($_POST['cep'] ?? '');
    $endereco     = trim($_POST['endereco'] ?? '');
    $numero       = trim($_POST['numero'] ?? '');
    $complemento  = trim($_POST['complemento'] ?? '');
    $bairro       = trim($_POST['bairro'] ?? '');
    $cidade       = trim($_POST['cidade'] ?? '');
    $estado       = strtoupper(trim($_POST['estado'] ?? ''));

    $nivel     = trim($_POST['nivel'] ?? 'Comum');    
    $ativo     = trim($_POST['ativo'] ?? 'Sim');

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

    if ($nome === '') {
        out(false, "Informe o nome.");
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        out(false, "Informe um e-mail válido.");
    }
    
    if (!in_array($ativo, ['Sim', 'Não'], true)) {
        // caso venha 1/0
        if ($ativo === '1') $ativo = 'Sim';
        elseif ($ativo === '0') $ativo = 'Não';
        else $ativo = 'Sim';
    }

    if ($estado !== '' && !preg_match('/^[A-Z]{2}$/', $estado)) {
        out(false, "UF inválida.");
    }

    // se for inserir, senha é obrigatória
    if ($id === 0) {
        if (trim($senha) === '') out(false, "Informe uma senha para o novo usuário.");
        if (mb_strlen($senha) < 3) out(false, "A senha precisa ter pelo menos 3 caracteres.");
    } else {
        // se vier senha, valida mínimo
        if (trim($senha) !== '' && mb_strlen($senha) < 3) {
            out(false, "A nova senha precisa ter pelo menos 3 caracteres.");
        }
    }

    // =========================
    // E-MAIL DUPLICADO
    // =========================
    $sqlDup = "SELECT id FROM {$tabela} WHERE email = :email AND empresa = :empresa";
    if ($id > 0) $sqlDup .= " AND id <> :id";
    $sqlDup .= " LIMIT 1";

    $stDup = $pdo->prepare($sqlDup);
    $stDup->bindValue(':email', $email);
    $stDup->bindValue(':empresa', $empresa);
    if ($id > 0) $stDup->bindValue(':id', $id, PDO::PARAM_INT);
    $stDup->execute();
    if ($stDup->fetch()) {
        out(false, "Este e-mail já está cadastrado para outro usuário.");
    }

    // =========================
    // UPLOAD FOTO
    // =========================
    $foto_final = $foto_atual;

    $uploadDir = realpath(__DIR__ . '/../../../uploads/perfil');
    if ($uploadDir === false) {
        // tenta criar
        $try = __DIR__ . '/../../../uploads/perfil';
        @mkdir($try, 0775, true);
        $uploadDir = realpath($try);
    }
    if ($uploadDir === false) {
        out(false, "Pasta de upload não encontrada: uploads/perfil.");
    }

    // se enviou arquivo
    if (!empty($_FILES['foto']) && isset($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            out(false, "Falha no upload da foto.");
        }

        $maxBytes = 2 * 1024 * 1024; // 2MB
        if ((int)$_FILES['foto']['size'] > $maxBytes) {
            out(false, "Envie uma imagem de até 2MB.");
        }

        $tmp = $_FILES['foto']['tmp_name'];

        // valida mime real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            out(false, "Formato de imagem inválido. Use JPG, PNG ou WEBP.");
        }

        $ext = $allowed[$mime];

        // nome seguro (empresa_id + timestamp + rand)
        $safeBase = 'u' . $empresa . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        $fileName = $safeBase . '.' . $ext;

        $dest = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        if (!move_uploaded_file($tmp, $dest)) {
            out(false, "Não foi possível salvar a foto enviada.");
        }

        // se trocou foto e a antiga não é padrão, apaga
        $padrao = 'sem_foto.webp';
        if ($foto_atual && $foto_atual !== $padrao) {
            $old = $uploadDir . DIRECTORY_SEPARATOR . $foto_atual;
            if (is_file($old)) @unlink($old);
        }

        $foto_final = $fileName;
    }

    // =========================
    // SENHA (hash)
    // =========================
    $senhaHash = null;
    if (trim($senha) !== '') {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        if (!$senhaHash) out(false, "Erro ao gerar hash da senha.");
    }

    // =========================
    // INSERT / UPDATE
    // =========================
    if ($id === 0) {

        $stmt = $pdo->prepare("
            INSERT INTO {$tabela}
            (nome, telefone, email, senha, cpf, endereco, numero, complemento, bairro, cidade, estado, cep, foto, nivel, ativo, empresa, data_cadastro)
            VALUES
            (:nome, :telefone, :email, :senha, :cpf, :endereco, :numero, :complemento, :bairro, :cidade, :estado, :cep, :foto, :nivel, :ativo, :empresa, NOW())
        ");

        $stmt->execute([
            ':nome'        => $nome,
            ':telefone'    => $telefone ?: null,
            ':email'       => $email,
            ':senha'       => $senhaHash, // obrigatório no insert
            ':cpf'         => $cpf ?: null,
            ':endereco'    => $endereco ?: null,
            ':numero'      => $numero ?: null,
            ':complemento' => $complemento ?: null,
            ':bairro'      => $bairro ?: null,
            ':cidade'      => $cidade ?: null,
            ':estado'      => $estado ?: null,
            ':cep'         => $cep ?: null,
            ':foto'        => $foto_final ?: 'sem_foto.webp',
            ':nivel'       => $nivel,
            ':ativo'       => $ativo,
            ':empresa'     => $empresa,
        ]);

        $novoId = (int)$pdo->lastInsertId();


        // =========================
        // INSERE AÇÕES PADRÃO (criar/editar/excluir)
        // =========================
        $insA = $pdo->prepare("INSERT IGNORE INTO usuarios_acoes (usuario_id, acao) VALUES (:uid, :acao)");
        foreach (['criar', 'editar', 'excluir'] as $acaoPadrao) {
            $insA->execute([
                ':uid'  => $novoId,
                ':acao' => $acaoPadrao
            ]);
        }
      

        //enviar disparo api whatsapp
        if($api_whatsapp != "Nenhuma" && $telefone != ""){
            $telefone_disparo = preg_replace('/\D+/', '', $telefone);
                                   
            $mensagem_whatsapp =
                "Olá, {$nome}! ✅\n\n" .
                "Seu acesso ao sistema foi criado.\n\n" .
                "🔗 Link: {$url_sistema}\n" .
                "👤 E-mail: {$email}\n" .
                "🔑 Senha: {$senha}\n\n" .
                "Por segurança, altere sua senha no primeiro acesso.";

                require_once __DIR__ . '/../../apis/texto_whatsapp.php';

        }



        // =========================
// DISPARO E-MAIL (novo usuário)
// =========================
$emailEnvioOk = null;
$emailEnvioErro = '';
$emailEnvioDebug = '';

try {
    // Só tenta enviar se tem e-mail válido
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // Link do sistema
        $linkSistema = trim((string)($url_sistema ?? ''));

        // se $url_sistema não estiver definido, monta pelo host atual
        if ($linkSistema === '') {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host  = $_SERVER['HTTP_HOST'] ?? '';
            $linkSistema = $host ? ($proto . '://' . $host) : '';
        }

        $assunto = "Acesso criado - {$nome_sistema}";
        $titulo  = "Seu acesso foi criado";

        $mensagemHtml = "
            <p>Olá <strong>" . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Seu acesso ao sistema <strong>" . htmlspecialchars($nome_sistema, ENT_QUOTES, 'UTF-8') . "</strong> foi criado com sucesso.</p>

            <p><b>Dados de acesso:</b></p>
            <ul style='margin:0; padding-left:18px;'>
              <li><b>E-mail:</b> " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</li>
              <li><b>Senha:</b> " . htmlspecialchars($senha, ENT_QUOTES, 'UTF-8') . "</li>
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

        // se falhar, registra em log (sem quebrar o cadastro)
        if (!$emailEnvioOk) {
            // log no servidor
            error_log("Falha e-mail novo usuário ({$email}): {$emailEnvioErro} | {$emailEnvioDebug}");

            // log no sistema (opcional)
            registrarLog(
                $pdo,
                'editar', // ou 'inserir' se você preferir
                $tabela,
                $novoId,
                "Falha ao enviar e-mail de acesso para {$email} ({$emailEnvioErro})"
            );
        }
    }
} catch (Throwable $e) {
    $emailEnvioOk = false;
    $emailEnvioErro = 'Exceção ao tentar enviar e-mail.';
    $emailEnvioDebug = $e->getMessage();
    error_log("Exceção e-mail novo usuário ({$email}): " . $e->getMessage());
}



        $novoId = (int)$pdo->lastInsertId();

        // 🔥 REGISTRA LOG (INSERIR)
        registrarLog(
            $pdo,
            'inserir',
            $tabela,
            $novoId,
            "Usuário '{$nome}' cadastrado"
        );

        out(true, "Usuário cadastrado com sucesso!");
        

    } else {

        // garante que existe e pertence à empresa (multiempresa)
        $stCheck = $pdo->prepare("SELECT id, foto FROM {$tabela} WHERE id = :id AND empresa = :empresa LIMIT 1");
        $stCheck->execute([':id' => $id, ':empresa' => $empresa]);
        $existe = $stCheck->fetch(PDO::FETCH_ASSOC);
        if (!$existe) {
            out(false, "Usuário não encontrado.");
        }

        $sql = "
            UPDATE {$tabela} SET
              nome = :nome,
              telefone = :telefone,
              email = :email,
              cpf = :cpf,
              endereco = :endereco,
              numero = :numero,
              complemento = :complemento,
              bairro = :bairro,
              cidade = :cidade,
              estado = :estado,
              cep = :cep,
              foto = :foto,
              nivel = :nivel,
              ativo = :ativo,
              data_atualizacao = NOW()
        ";

        // só altera senha se veio preenchida
        if ($senhaHash) $sql .= ", senha = :senha";

        $sql .= " WHERE id = :id AND empresa = :empresa LIMIT 1";

        $stmt = $pdo->prepare($sql);

        $params = [
            ':nome'        => $nome,
            ':telefone'    => $telefone ?: null,
            ':email'       => $email,
            ':cpf'         => $cpf ?: null,
            ':endereco'    => $endereco ?: null,
            ':numero'      => $numero ?: null,
            ':complemento' => $complemento ?: null,
            ':bairro'      => $bairro ?: null,
            ':cidade'      => $cidade ?: null,
            ':estado'      => $estado ?: null,
            ':cep'         => $cep ?: null,
            ':foto'        => $foto_final ?: 'sem_foto.webp',
            ':nivel'       => $nivel,
            ':ativo'       => $ativo,
            ':id'          => $id,
            ':empresa'     => $empresa,
        ];

        if ($senhaHash) $params[':senha'] = $senhaHash;

        $stmt->execute($params);


        registrarLog(
            $pdo,
            'editar',
            $tabela,
            $id,
            "Usuário '{$nome}' atualizado"
        );

        out(true, "Usuário atualizado com sucesso!");
    }

} catch (Throwable $e) {
    out(false, "Erro: " . $e->getMessage());
}

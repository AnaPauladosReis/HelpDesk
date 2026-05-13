<?php
/**
 * painel/funcoes/email.php
 * Disparo padrão de e-mails do sistema (SEM Composer)
 * Usa PHPMailer localizado em /phpmailer/src
 */

/**
 * Template padrão de e-mail do sistema
 */

 require_once __DIR__ . '/crypto.php';

 
function emailTemplatePadrao(
    string $titulo,
    string $mensagemHtml,
    string $botaoTexto = '',
    string $botaoLink = '',
    string $rodapeExtra = ''
): string {

    $nomeSistema   = $GLOBALS['nome_sistema'] ?? 'Sistema';
    $corPrimaria   = $GLOBALS['cor_primaria'] ?? '#667eea';
    $corSecundaria = $GLOBALS['cor_secundaria'] ?? '#764ba2';

    $botao = '';
    if ($botaoTexto && $botaoLink) {
        $botaoLinkEsc  = htmlspecialchars($botaoLink, ENT_QUOTES, 'UTF-8');
        $botaoTextoEsc = htmlspecialchars($botaoTexto, ENT_QUOTES, 'UTF-8');

        $botao = "
            <div style='margin:22px 0; text-align:center;'>
                <a href='{$botaoLinkEsc}'
                   style='display:inline-block; padding:12px 20px;
                          border-radius:12px;
                          text-decoration:none;
                          font-weight:700;
                          color:#fff;
                          background: linear-gradient(135deg, {$corPrimaria} 0%, {$corSecundaria} 100%);'>
                    {$botaoTextoEsc}
                </a>
            </div>
        ";
    }

    return "
    <div style='background:#f3f5f7; padding:30px; font-family:Segoe UI, Tahoma, Arial;'>
        <div style='max-width:640px; margin:auto; background:#fff;
                    border-radius:18px; overflow:hidden;
                    box-shadow:0 18px 45px rgba(0,0,0,.12);'>

            <div style='padding:20px 24px;
                        background: linear-gradient(135deg, {$corPrimaria} 0%, {$corSecundaria} 100%);
                        color:#fff;'>
                <h2 style='margin:0; font-size:20px; font-weight:800;'>{$nomeSistema}</h2>
                <small style='opacity:.9;'>Notificação automática</small>
            </div>

            <div style='padding:24px; color:#212529;'>
                <h3 style='margin-top:0; font-weight:800;'>{$titulo}</h3>

                <div style='font-size:14px; line-height:1.6;'>
                    {$mensagemHtml}
                </div>

                {$botao}

                <div style='margin-top:20px; font-size:12px; color:#6c757d;'>
                    {$rodapeExtra}
                </div>
            </div>

            <div style='padding:14px 24px; border-top:1px solid #eee;
                        font-size:12px; color:#6c757d;'>
                Este é um e-mail automático. Não responda.
            </div>
        </div>
    </div>";
}

/**
 * Envia e-mail padrão usando PHPMailer (SEM Composer)
 */
function enviarEmailPadrao(
    string $paraEmail,
    string $paraNome,
    string $assunto,
    string $html,
    string $textoPlano = ''
): array {

    $paraEmail = trim($paraEmail);
    $paraNome  = trim($paraNome);

    if (!filter_var($paraEmail, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'erro' => 'E-mail do destinatário inválido.', 'debug' => ''];
    }

    // Dados do sistema (conexao.php)
    $deNome  = $GLOBALS['nome_sistema'] ?? 'Sistema';
    $deEmail = $GLOBALS['email_sistema'] ?? 'no-reply@localhost';

    /**
     * CONFIGURAÇÕES SMTP
     * >>> AJUSTE CONFORME SEU SERVIDOR <<<
     */
    $smtpHost   = $GLOBALS['smtp_host']      ?? '';
    $smtpUser   = $GLOBALS['email_sistema']  ?? '';
    $smtpPassEnc = $GLOBALS['smtp_senha'] ?? '';
    $smtpPass = '';

    if ($smtpPassEnc !== '') {
        try {
            $smtpPass = smtp_decrypt($smtpPassEnc);
        } catch (Throwable $e) {
            return ['ok' => false, 'erro' => 'Falha ao ler senha SMTP.', 'debug' => $e->getMessage()];
        }
    }

    


    $smtpPort   = (int)($GLOBALS['smtp_porta'] ?? 587);
    $smtpSecure = $GLOBALS['smtp_seguranca'] ?? '';
    

    /**
     * Caminho do PHPMailer (SEM Composer)
     * helpdesk/phpmailer/src/
     */
    $basePHPMailer = __DIR__ . '/../../phpmailer/src/';

    if (
        !file_exists($basePHPMailer . 'PHPMailer.php') ||
        !file_exists($basePHPMailer . 'SMTP.php') ||
        !file_exists($basePHPMailer . 'Exception.php')
    ) {
        return [
            'ok' => false,
            'erro' => 'PHPMailer não encontrado.',
            'debug' => $basePHPMailer
        ];
    }

    require_once $basePHPMailer . 'PHPMailer.php';
    require_once $basePHPMailer . 'SMTP.php';
    require_once $basePHPMailer . 'Exception.php';

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // SMTP
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->Port       = (int)$smtpPort;
        $mail->CharSet    = 'UTF-8';

        // Garante STARTTLS quando disponível
        $mail->SMTPAutoTLS = true;

        if ($smtpSecure) {
            $mail->SMTPSecure = $smtpSecure; // tls
        }

        /**
         * ✅ DEV/TESTE (XAMPP/Windows): ignora validação SSL
         * Isso resolve "certificate verify failed".
         * Em produção, o ideal é configurar o cacert.pem no PHP e remover isso.
         */
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        // Remetente
        $mail->setFrom($deEmail, $deNome);
        $mail->addAddress($paraEmail, $paraNome ?: $paraEmail);

        // Conteúdo
        $mail->Subject = $assunto;
        $mail->isHTML(true);
        $mail->Body = $html;

        if ($textoPlano === '') {
            $textoPlano = trim(html_entity_decode(strip_tags($html)));
        }
        $mail->AltBody = $textoPlano;

        $mail->send();

        return [
            'ok' => true,
            'erro' => '',
            'debug' => 'E-mail enviado com sucesso'
        ];

    } catch (\Throwable $e) {
        return [
            'ok' => false,
            'erro' => 'Falha ao enviar e-mail.',
            'debug' => $e->getMessage()
        ];
    }
}

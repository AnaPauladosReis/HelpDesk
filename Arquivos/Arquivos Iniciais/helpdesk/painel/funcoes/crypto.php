<?php
/**
 * painel/funcoes/crypto.php
 * Criptografia reversível para dados sensíveis (AES-256-GCM)
 * - Gera e persiste a chave automaticamente em arquivo local.
 * - NÃO precisa de SMTP_SECRET_KEY definida no sistema.
 */

function smtp_get_key(): string
{
    // Arquivo onde a chave fica salva (fora de web root seria melhor, mas aqui já resolve)
    $keyFile = __DIR__ . '/smtp_secret.key';

    // 1) Se existir arquivo, lê
    if (is_file($keyFile)) {
        $hex = trim((string)@file_get_contents($keyFile));
        if (preg_match('/^[0-9a-fA-F]{64}$/', $hex)) {
            $bin = hex2bin($hex);
            if ($bin !== false && strlen($bin) === 32) {
                return $bin;
            }
        }
        // se o arquivo existir mas estiver inválido, segue para regenerar
    }

    // 2) Gera chave nova (32 bytes = 64 hex)
    $bin = random_bytes(32);
    $hex = bin2hex($bin);

    // 3) Salva com permissão restrita (melhor esforço)
    @file_put_contents($keyFile, $hex, LOCK_EX);
    @chmod($keyFile, 0600);

    return $bin;
}

function smtp_encrypt(string $plaintext): string
{
    $plaintext = (string)$plaintext;
    if ($plaintext === '') return '';

    $key = smtp_get_key();

    $iv  = random_bytes(12); // GCM: 12 bytes recomendado
    $tag = '';

    $cipher = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($cipher === false) {
        throw new RuntimeException('Falha ao criptografar (openssl_encrypt).');
    }

    // base64(iv|tag|cipher)
    return base64_encode($iv . $tag . $cipher);
}

function smtp_decrypt(string $encoded): string
{
    $encoded = (string)$encoded;
    if ($encoded === '') return '';

    $key = smtp_get_key();

    $data = base64_decode($encoded, true);
    if ($data === false || strlen($data) < (12 + 16 + 1)) {
        throw new RuntimeException('Valor criptografado inválido.');
    }

    $iv     = substr($data, 0, 12);
    $tag    = substr($data, 12, 16);
    $cipher = substr($data, 28);

    $plain = openssl_decrypt(
        $cipher,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        ''
    );

    if ($plain === false) {
        throw new RuntimeException('Falha ao descriptografar (tag/chave inválida).');
    }

    return $plain;
}

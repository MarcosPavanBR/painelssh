<?php
declare(strict_types=1);

function secret_key(): string {
    static $key;
    if ($key !== null) return $key;
    $raw = getenv('APP_SECRET_KEY') ?: '';
    if ($raw === '') throw new RuntimeException('APP_SECRET_KEY não configurada.');
    $decoded = base64_decode($raw, true);
    if ($decoded !== false && strlen($decoded) === SODIUM_CRYPTO_SECRETBOX_KEYBYTES) $key = $decoded;
    elseif (strlen($raw) === SODIUM_CRYPTO_SECRETBOX_KEYBYTES) $key = $raw;
    else throw new RuntimeException('APP_SECRET_KEY inválida. Use 32 bytes em base64.');
    return $key;
}
function encrypt_secret(string $plain): string {
    $nonce=random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return 'v1:' . base64_encode($nonce . sodium_crypto_secretbox($plain,$nonce,secret_key()));
}
function decrypt_secret(?string $value): string {
    $value=(string)$value;
    if (!str_starts_with($value,'v1:')) return $value; // migration compatibility
    $bin=base64_decode(substr($value,3),true);
    if ($bin===false || strlen($bin)<=SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) throw new RuntimeException('Segredo corrompido.');
    $nonce=substr($bin,0,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $cipher=substr($bin,SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $plain=sodium_crypto_secretbox_open($cipher,$nonce,secret_key());
    if ($plain===false) throw new RuntimeException('Não foi possível descriptografar segredo.');
    return $plain;
}

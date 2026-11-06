<?php

namespace Tempel\Core;

/** Encrypts service credentials at rest while accepting legacy plaintext values. */
final class SecretStore
{
    private const PREFIX = 'tempel:v1:';

    public static function encrypt(string $value): string
    {
        if ($value === '' || self::isEncrypted($value) || !function_exists('openssl_encrypt')) {
            return $value;
        }

        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($value, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if (!is_string($ciphertext) || $tag === '') {
            return $value;
        }

        return self::PREFIX . base64_encode($iv . $tag . $ciphertext);
    }

    public static function decrypt(string $value): string
    {
        if (!self::isEncrypted($value)) {
            return $value;
        }

        $payload = base64_decode(substr($value, strlen(self::PREFIX)), true);
        if (!is_string($payload) || strlen($payload) < 29 || !function_exists('openssl_decrypt')) {
            return '';
        }

        $iv = substr($payload, 0, 12);
        $tag = substr($payload, 12, 16);
        $ciphertext = substr($payload, 28);
        $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return is_string($plaintext) ? $plaintext : '';
    }

    public static function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::PREFIX);
    }

    private static function key(): string
    {
        return hash('sha256', wp_salt('auth') . '|tempel-settings-secrets', true);
    }
}

<?php

declare(strict_types=1);

namespace KaririCode\Logging\Security;

class Encryptor
{
    public function __construct(private readonly string $key)
    {
        if (32 > strlen($key)) {
            throw new \InvalidArgumentException('Key must be at least 32 bytes long');
        }
    }

    public function encrypt(string $data): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
        if (false === $encrypted) {
            throw new \RuntimeException('Encryption failed: ' . openssl_error_string());
        }

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $data): string
    {
        $decoded = base64_decode($data, true);
        if (false === $decoded) {
            throw new \InvalidArgumentException('Invalid base64 encoding');
        }
        $iv = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
        if (false === $decrypted) {
            throw new \RuntimeException('Decryption failed: ' . openssl_error_string());
        }

        return $decrypted;
    }
}

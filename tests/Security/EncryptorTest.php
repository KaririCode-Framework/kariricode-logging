<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Security;

use KaririCode\Logging\Security\Encryptor;
use PHPUnit\Framework\TestCase;

final class EncryptorTest extends TestCase
{
    private Encryptor $encryptor;

    protected function setUp(): void
    {
        $key = str_repeat('a', 32); // 32 bytes key
        $this->encryptor = new Encryptor($key);
    }

    public function testEncryptDecrypt(): void
    {
        $data = 'Test data';

        $encrypted = $this->encryptor->encrypt($data);
        $decrypted = $this->encryptor->decrypt($encrypted);

        $this->assertEquals($data, $decrypted);
    }

    public function testEncryptThrowsExceptionWithInvalidKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Encryptor('invalid_key');
    }
}

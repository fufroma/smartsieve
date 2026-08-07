<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../lib/Crypt.php';
require_once __DIR__ . '/../../lib/Crypt/openssl.php';

class CryptoOpensslTest extends TestCase
{
    public function testEncryptDecryptRoundTrip(): void
    {
        $crypto = new Crypto_OPENSSL(array('key' => 'a secret key'));
        $encrypted = $crypto->encrypt('my password');
        $this->assertNotSame('my password', $encrypted);
        $this->assertSame('my password', $crypto->decrypt($encrypted));
    }

    public function testEncryptionIsRandomized(): void
    {
        $crypto = new Crypto_OPENSSL(array('key' => 'a secret key'));
        // Random IV: same plaintext must not produce the same ciphertext.
        $this->assertNotSame($crypto->encrypt('x'), $crypto->encrypt('x'));
    }

    public function testDecryptWithWrongKeyFails(): void
    {
        $crypto = new Crypto_OPENSSL(array('key' => 'key one'));
        $other = new Crypto_OPENSSL(array('key' => 'key two'));
        $this->assertSame('', $other->decrypt($crypto->encrypt('secret')));
    }

    public function testDecryptGarbageReturnsEmptyString(): void
    {
        $crypto = new Crypto_OPENSSL(array('key' => 'k'));
        $this->assertSame('', $crypto->decrypt('not base64 !!'));
        $this->assertSame('', $crypto->decrypt(base64_encode('short')));
    }
}

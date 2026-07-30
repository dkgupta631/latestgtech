<?php

namespace App\Services;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;

class QoreCardEncryptor
{
    /**
     * Encrypt a plaintext card field using the merchant's RSA public key,
     * RSA-OAEP with SHA-256 for both the hash and MGF1 function
     * (matches Node's crypto.publicEncrypt with oaepHash: 'sha256').
     *
     * @param string $plainText       e.g. card number, cvv, expiry month/year
     * @param string $publicKeyBase64 raw base64 SPKI key from gateway parameters (no PEM headers)
     * @return string base64-encoded ciphertext
     */
    public static function encrypt(string $plainText, string $publicKeyBase64): string
    {
        $pem = self::toPem($publicKeyBase64);

        $publicKey = PublicKeyLoader::load($pem)
            ->withPadding(RSA::ENCRYPTION_OAEP)
            ->withHash('sha256')
            ->withMGFHash('sha256');

        $ciphertext = $publicKey->encrypt($plainText);

        return base64_encode($ciphertext);
    }

    /**
     * Encrypt all four card fields at once.
     *
     * @return array{encrypted_card_number:string, encrypted_cvv:string, encrypted_expiration_month:string, encrypted_expiration_year:string}
     */
    public static function encryptCardData(
        string $cardNumber,
        string $cvv,
        string $expiryMonth,
        string $expiryYear,
        string $publicKeyBase64
    ): array {
        return [
            'encrypted_card_number'      => self::encrypt($cardNumber, $publicKeyBase64),
            'encrypted_cvv'              => self::encrypt($cvv, $publicKeyBase64),
            'encrypted_expiration_month' => self::encrypt($expiryMonth, $publicKeyBase64),
            'encrypted_expiration_year'  => self::encrypt($expiryYear, $publicKeyBase64),
        ];
    }

    protected static function toPem(string $publicKeyBase64): string
    {
        $clean  = preg_replace('/\s+/', '', $publicKeyBase64);
        $chunks = chunk_split($clean, 64, "\n");

        return "-----BEGIN PUBLIC KEY-----\n{$chunks}-----END PUBLIC KEY-----\n";
    }
}
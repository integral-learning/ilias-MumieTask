<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2026 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Sabine Greiser (sabine.greiser@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Signs worksheet deadlines using an installation-wide RSA keypair.
 * Signatures are verified with SHA512withRSA against the public key
 * exposed by this plugin.
 */
class ilMumieTaskCryptographyService
{
    private const KEY_BITS = 2048;
    private const SIGN_ALGO = OPENSSL_ALGO_SHA512;

    /**
     * Plain concatenation of deadline (ms), username and worksheet id, no separators.
     */
    public static function buildSignaturePayload(int $deadlineMilliseconds, string $username, string $worksheetId): string
    {
        return $deadlineMilliseconds . $username . $worksheetId;
    }

    public static function signDeadline(int $deadlineMilliseconds, string $username, string $worksheetId): string
    {
        return self::sign(
            self::buildSignaturePayload($deadlineMilliseconds, $username, $worksheetId),
            self::getOrGeneratePrivateKey(),
        );
    }

    /**
     * @param OpenSSLAsymmetricKey $privateKey
     */
    public static function sign(string $data, $privateKey): string
    {
        openssl_sign($data, $signature, $privateKey, self::SIGN_ALGO);

        return base64_encode($signature);
    }

    public static function getPublicKeyPem(): string
    {
        $details = openssl_pkey_get_details(self::getOrGeneratePrivateKey());

        return $details['key'];
    }

    /**
     * @return OpenSSLAsymmetricKey
     */
    private static function getOrGeneratePrivateKey()
    {
        $pem = ilMumieTaskCryptoKeyRepository::loadPrivateKeyPem();
        if (null !== $pem) {
            return openssl_pkey_get_private($pem);
        }

        $privateKey = openssl_pkey_new([
            'private_key_bits' => self::KEY_BITS,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        openssl_pkey_export($privateKey, $pem);
        ilMumieTaskCryptoKeyRepository::savePrivateKeyPem($pem);

        return $privateKey;
    }
}

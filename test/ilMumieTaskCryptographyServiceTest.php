<?php

use PHPUnit\Framework\TestCase;

class ilMumieTaskCryptographyServiceTest extends TestCase
{
    public function testBuildSignaturePayloadConcatenatesWithoutSeparators()
    {
        $payload = ilMumieTaskCryptographyService::buildSignaturePayload(1688374980000, 'gsso_ilias_abc123', '14981');
        $this->assertEquals('1688374980000gsso_ilias_abc12314981', $payload);
    }

    public function testSignProducesASignatureVerifiableWithTheMatchingPublicKey()
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $publicKeyPem = openssl_pkey_get_details($privateKey)['key'];
        $data = 'some-signed-data';

        $signature = ilMumieTaskCryptographyService::sign($data, $privateKey);

        $result = openssl_verify($data, base64_decode($signature), $publicKeyPem, OPENSSL_ALGO_SHA512);
        $this->assertEquals(1, $result);
    }

    public function testSignatureDoesNotVerifyAgainstTamperedData()
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $publicKeyPem = openssl_pkey_get_details($privateKey)['key'];

        $signature = ilMumieTaskCryptographyService::sign('original-data', $privateKey);

        $result = openssl_verify('tampered-data', base64_decode($signature), $publicKeyPem, OPENSSL_ALGO_SHA512);
        $this->assertEquals(0, $result);
    }
}

<?php

/**
 * MumieTask plugin.
 *
 * @copyright   2026 integral-learning GmbH (https://www.integral-learning.de/)
 * @author      Sabine Greiser (sabine.greiser@integral-learning.de)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Persists the installation-wide RSA private key used to sign worksheet deadlines.
 */
class ilMumieTaskCryptoKeyRepository
{
    public const TABLE_NAME = 'xmum_crypto_key';

    public static function loadPrivateKeyPem(): ?string
    {
        global $DIC;
        $db = $DIC->database();
        $result = $db->fetchObject($db->query('SELECT private_key_pem FROM ' . self::TABLE_NAME . ' WHERE id = 1'));

        return $result->private_key_pem ?? null;
    }

    public static function savePrivateKeyPem(string $pem): void
    {
        global $DIC;
        $DIC->database()->replace(
            self::TABLE_NAME,
            ['id' => ['integer', 1]],
            [
                'private_key_pem' => ['clob', $pem],
                'created_at' => ['integer', time()],
            ],
        );
    }
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines a class used to manage cryptographic keys used within the mumie plugins.
 *
 * @package auth_mumie
 * @copyright  2017-2023 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('Customizing/global/plugins/Services/Repository/RepositoryObject/MumieTask/classes/sso/cryptographic/mumie_cryptographic_key.php');

/**
 * This class is used to manage cryptographic keys for communication with MUMIE/Lemon servers.
 *
 * @package auth_mumie
 * @copyright  2017-2020 integral-learning GmbH (https://www.integral-learning.de/)
 * @author Tobias Goltz (tobias.goltz@integral-learning.de)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mumie_cryptography_service {
    /**
     * The identifier for the public key
     */
    const PUBLIC_KEY_NAME = "public";
    /**
     * The identifier for the private key
     */
    const PRIVATE_KEY_NAME = "private";

    /**
     * Get the public cryptographic key saved in the database
     * @return mumie_cryptographic_key|null
     */
    public static function get_public_key() : ?mumie_cryptographic_key {
        return mumie_cryptographic_key::get_by_name(self::PUBLIC_KEY_NAME);
    }

    /**
     * Get the private cryptographic key saved in the database
     * @return mumie_cryptographic_key|null
     */
    public static function get_private_key() : ?mumie_cryptographic_key {
        return mumie_cryptographic_key::get_by_name(self::PRIVATE_KEY_NAME);
    }

    /**
     * Generate cryptographic key pair, if it does not exist.
     * @return void
     */
    public static function ensure_key_pair_exist() : void {
        $publickey = self::get_public_key();
        $privatekey = self::get_private_key();

        if (is_null($publickey) || is_null($privatekey)) {
            self::generate_key_pair();
        }
    }

    /**
     * Create base64 encode signature for given data strings.
     * @param string ...$data
     * @return string
     */
    public static function sign_data(string ...$data) : string {
        self::ensure_key_pair_exist();
        openssl_sign(implode("",  $data), $signeddata, self::get_private_key()->get_key(), OPENSSL_ALGO_SHA512);
        return base64_encode($signeddata);
    }

    /**
     * Generate a cryptographic key pair and save it to the database
     * @return void
     */
    private static function generate_key_pair() : void {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $res = openssl_pkey_new($config);

        openssl_pkey_export($res, $privatekey);
        $pubkey = openssl_pkey_get_details($res);
        $pubkey = $pubkey["key"];

        self::upsert_key_pair($privatekey, $pubkey);
    }

    /**
     * Upsert a cryptographic key pair
     * @param string $privatekey
     * @param string $publickey
     * @return void
     */
    private static function upsert_key_pair(string $privatekey, string $publickey) : void {
        self::upsert_private_key($privatekey);
        self::upsert_public_key($publickey);
    }

    /**
     * Upsert the public cryptographic key
     * @param string $key
     * @return void
     */
    private static function upsert_public_key(string $key) : void {
        self::upsert_key(self::PUBLIC_KEY_NAME, $key);
    }

    /**
     * Upsert the private cryptographic key
     * @param string $key
     * @return void
     */
    private static function upsert_private_key(string $key) : void {
        self::upsert_key(self::PRIVATE_KEY_NAME, $key);
    }

    /**
     * Upsert a key with a given name
     * @param string $name
     * @param string $key
     * @return void
     */
    private static function upsert_key(string $name, string $key) : void {
        $cryptographickey = mumie_cryptographic_key::get_by_name($name);
        if (!is_null($cryptographickey)) {
            $cryptographickey->set_key($key);
            $cryptographickey->update();
        } else {
            $cryptographickey = new mumie_cryptographic_key($name, $key);
            $cryptographickey->create();
        }
    }
}

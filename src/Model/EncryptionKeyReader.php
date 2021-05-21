<?php

declare(strict_types=1);
namespace ImageRepository\Model;

/* Function to get key the public key (used to encrypt) and master key(used to generate decryption keys)*/
use ImageRepository\Exception\StaticClassAssertionError;
use PDO;

/**
 * Class to give us the keys we need for encryption. It deals with information stored in our database
 */
final class EncryptionKeyReader
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function publicKey(Database $db): string {
        return self::systemKeys($db)['publicKey'] ?? '';
    }

    /* Helper function to get users public key*/
    public static function systemKeys(Database $db): array {
        $sql = 'SELECT keysName, keyData FROM systemKeys WHERE keysName=:masterKey OR keysName=:publicKey';
        $params = [
            ':publicKey' => 'publicKey',
            ':masterKey' => 'masterKey'
        ];

        return $db->read($sql, $params, PDO::FETCH_KEY_PAIR);
    }

    /* Helper function to get users private key*/
    public static function userKey(User $user, Database $db): string {
        $sql = 'SELECT privateKey FROM userKeys WHERE memberID=:id';
        $params = [':id' => $user->id];
        $result = $db->read($sql, $params);

        return $result[0]['privateKey'];
    }
}

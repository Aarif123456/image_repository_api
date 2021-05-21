<?php

declare(strict_types=1);
namespace ImageRepository\Model\UserManagement;

use ImageRepository\Exception\{DebugPDOException,
    EncryptionFailureException,
    PDOWriteException,
    StaticClassAssertionError};
use ImageRepository\Model\{Database, EncryptionKeyReader, User};
use ImageRepository\Model\Encryption\{Encrypter, UserAttributeGenerator};

use function ImageRepository\Utils\registerUser;

/**
 * Class to create users
 */
final class UserGenerator
{
    function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws EncryptionFailureException
     * @throws DebugPDOException
     * @throws PDOWriteException
     */
    public static function createUser(User $user, Database $db, bool $debug = false): array {
        $db->beginTransaction();
        $output = registerUser($db, $user);
        if ($output['error']) {
            return $output;
        }
        /* Store the user's login info */
        $user->id = $output['id'];
        /* store user info in member table */
        self::storeUserKeys($user, $db, $debug);
        $db->commit();

        return $output;
    }

    /**
     * @throws EncryptionFailureException
     * @throws DebugPDOException
     * @throws PDOWriteException
     */
    private static function storeUserKeys(User $user, Database $db, bool $debug = false): void {
        $sql = 'INSERT INTO `userKeys` (memberID, privateKey) VALUES (:id, :privateKey)';
        $privateKey = self::generateUserPrivateKey($user, $db);
        $params = [':id' => $user->id, ':privateKey' => $privateKey];
        $db->write($sql, $params, $debug);
    }

    /**
     * @throws EncryptionFailureException
     */
    private static function generateUserPrivateKey(User $user, Database $db): string {
        $userAttributes = UserAttributeGenerator::generate($user);
        /* Get public and private key */
        $systemKeys = EncryptionKeyReader::systemKeys($db);
        $publicKey = $systemKeys['publicKey'];
        $masterKey = $systemKeys['masterKey'];

        return Encrypter::keyGeneration($publicKey, $masterKey, $userAttributes);
    }
}




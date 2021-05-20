<?php

declare(strict_types=1);
namespace ImageRepository\Model\UserManagement;

use ImageRepository\Exception\{DebugPDOException, EncryptionFailureException, PDOWriteException};
use ImageRepository\Model\{Database, EncryptionKeyReader, User};
use ImageRepository\Model\Encryption\{Encrypter, UserAttributeGenerator};

use function ImageRepository\Utils\registerUser;

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function insertUser(User $user, Database $db, bool $debug = false): array {
    $db->beginTransaction();
    $output = registerUser($db, $user);
    if ($output['error']) {
        return $output;
    }
    /* Store the user's login info */
    $user->id = $output['id'];
    /* store user info in member table */
    storeUserKeys($user, $db, $debug);
    $db->commit();

    return $output;
}

/**
 * @throws EncryptionFailureException
 */
function generateUserPrivateKey(User $user, Database $db): string {
    $userAttributes = UserAttributeGenerator::generate($user);
    /* Get public and private key */
    $systemKeys = EncryptionKeyReader::systemKeys($db);
    $publicKey = $systemKeys['publicKey'];
    $masterKey = $systemKeys['masterKey'];

    return Encrypter::keyGeneration($publicKey, $masterKey, $userAttributes);
}

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function storeUserKeys(User $user, Database $db, bool $debug = false): bool {
    $sql = 'INSERT INTO `userKeys` (memberID, privateKey) VALUES (:id, :privateKey)';
    $privateKey = generateUserPrivateKey($user, $db);
    $params = [':id' => $user->id, ':privateKey' => $privateKey];

    return $db->write($sql, $params, $debug);
}




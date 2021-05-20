<?php

declare(strict_types=1);
namespace App\Model\UserManagement;

use App\Model\{DebugPDOException, PDOWriteException, User};
use App\Model\Encryption\EncryptionFailureException;
use PDO;

use function App\Model\{getSystemKeys, safeWriteQueries};
use function App\Model\Encryption\{createUserAttributes, keygen};
use function App\Utils\registerUser;

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function insertUser(User $user, PDO $conn, bool $debug = false): array {
    $conn->beginTransaction();
    $output = registerUser($conn, $user);
    if ($output['error']) {
        return $output;
    }
    /* Store the user's login info */
    $user->id = $output['id'];
    /* store user info in member table */
    storeUserKeys($user, $conn, $debug);
    $conn->commit();

    return $output;
}

/**
 * @throws EncryptionFailureException
 */
function getUserPrivateKey(User $user, PDO $conn): string {
    $userAttributes = createUserAttributes($user);
    /* Get public and private key */
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    $masterKey = $systemKeys['masterKey'];

    return keygen($publicKey, $masterKey, $userAttributes);
}

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function storeUserKeys(User $user, PDO $conn, bool $debug = false): bool {
    $stmt = $conn->prepare(
        'INSERT INTO `userKeys` (memberID, privateKey) VALUES (:id, :privateKey)'
    );
    $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
    $privateKey = getUserPrivateKey($user, $conn);
    $stmt->bindValue(':privateKey', $privateKey);

    return safeWriteQueries($stmt, $conn, $debug);
}




<?php
declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/systemKey.php';
require_once __DIR__ . '/encryption/callApi.php';
require_once __DIR__ . '/encryption/userAttributes.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../vendor/autoload.php';
function insertUser(User $user, PDO $conn, bool $debug = false): array {
    $conn->beginTransaction();
    
    $output = registerUser($conn, $user);
    if ($output['error']) return $output;
    
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




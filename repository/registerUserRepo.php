<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/systemKey.php';
require_once __DIR__ . '/encryption/callApi.php';
require_once __DIR__ . '/encryption/userAttributes.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPAuth\Auth as PHPAuth;
use PHPAuth\Config as PHPAuthConfig;
/*NOTE: leaving account as object because it is a one time use object */
function insertUser(User $user, object $account, PDO $conn, bool $debug = false): array {
    $output = [];
    try {
        $config = new PHPAuthConfig($conn);
        $auth = new PHPAuth($conn, $config);

        $conn->beginTransaction();
        $params = [
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'isAdmin' => $user->isAdmin
        ];
        $result = $auth->register($account->email, $account->password, $account->password, $params);
        if ($result['error']) {
            $output['error'] = $result['message'];

            return $output;
        }

        $output['message'] = $result['message'];
        /* Store the user's login info */
        $output['id'] = $id = $auth->getUID($account->email);
        $user->id = $id;
        /* store user info in member table */
        storeUserKeys($user, $conn, $debug);
        $conn->commit();
    } catch (Exception $e) {
        /* remove all queries from queue if error (undo) */
        $conn->rollBack();
        if ($debug) {
            $output['debug'] = debugException($e, $conn);
        }
        $output['error'] = WRITE_QUERY_FAILED;
    }

    return $output;
}

function getUserPrivatKey(User $user, PDO $conn): string {
    $userAttributes = createUserAttributes($user);
    /* Get public and private key */
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    $masterKey = $systemKeys['masterKey'];

    return keygen($publicKey, $masterKey, $userAttributes);
}

/**
 * @throws Exception
 */
function storeUserKeys(User $user, PDO $conn, bool $debug = false): bool {
    $stmt = $conn->prepare(
        'INSERT INTO `userKeys` (memberID, privateKey) VALUES (:id, :privateKey)'
    );
    $stmt->bindValue(':id', $user->id, PDO::PARAM_INT);
    $privateKey = getUserPrivatKey($user, $conn);
    $stmt->bindValue(':privateKey', $privateKey);

    return safeWriteQueries($stmt, $conn, $debug);
}




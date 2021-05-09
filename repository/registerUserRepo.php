<?php

declare(strict_types=1);
/* TODO: Make it so we only have two types of users */

/* Imports */
require_once __DIR__ . '/error.php';
require_once __DIR__ . '/systemKey.php';
require_once __DIR__ . '/encryption/callApi.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPAuth\Auth as PHPAuth;
use PHPAuth\Config as PHPAuthConfig;

function insertUser($user, $account, $conn, $debug = false): array {
    $output = [];
    try {
        $config = new PHPAuthConfig($conn);
        $auth = new PHPAuth($conn, $config);

        $conn->beginTransaction();
        $params = [
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'isAdmin' => (int)$user->admin
        ];
        $result = $auth->register($account->email, $account->password, $account->password, $params);
        if ($result['error']) {
            $output['error'] = $result['message'];

            return $output;
        }
        
        $output['message'] = $result['message'];
        /* Store the user's login info */
        $output['id'] = $id = $auth->getUID($account->email);
        /* store user info in member table */
        storeUserKeys($id, $user, $conn, $debug);
        $conn->commit();
    } catch (Exception $e) {
        /* remove all queries from queue if error (undo) */
        $conn->rollback();
        if ($debug) {
            $output['debug'] = debugException($e, $conn);
        }
        $output['error'] = WRITE_QUERY_FAILED;
    }

    return $output;
}

/* Create user attributes for the encryption */
function createUserAttributes($id, $user): string {
    /* Set user id and give public access*/
    return "userId:$id public:true";
}

function getUserPrivatKey($id, $user, $conn, $debug = false): string {
    $userAttributes = createUserAttributes($id, $user);
    /* Get public and private key */
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];
    $masterKey = $systemKeys['masterKey'];

    return keygen($publicKey, $masterKey, $userAttributes, $debug);
}

/**
 * @throws Exception
 */
function storeUserKeys($id, $user, $conn, $debug = false): bool {
    $stmt = $conn->prepare(
        'INSERT INTO `userKeys` (memberID, privateKey) VALUES (:id, :privateKey)'
    );
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $privateKey = getUserPrivatKey($id, $user, $conn, $debug);
    $stmt->bindValue(':privateKey', $privateKey, PDO::PARAM_STR);

    return safeWriteQueries($stmt, $conn, $debug);
}




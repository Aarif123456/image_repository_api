<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../repository/registerUserRepo.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../validEndpoint.php';
/* TODO: rename to register and update read me*/
/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 */
function register(PDO $conn, bool $debug) {
    /* Make sure we have a valid request */
    checkMissingPostVars(['firstName', 'lastName', 'email', 'password']);
    /* Get user info into user object */
    $user = new User([
        'firstName' => trim($_POST['firstName']),
        'lastName' => trim($_POST['lastName']),
        'isAdmin' => (bool)($_POST['admin'] ?? false),
        'email' => $_POST['email'],
        'password' => $_POST['password'],
    ]);
    $result = insertUser($user, $conn, $debug);
    echo createQueryJSON($result);
}

safeApiRun(UNAUTHENTICATED, 'register');

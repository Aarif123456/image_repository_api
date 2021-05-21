<?php

declare(strict_types=1);
namespace ImageRepository\Api\UserManagement;

/* TODO: rename to register and update read me*/
use ImageRepository\Exception\{DebugPDOException,
    EncryptionFailureException,
    MissingParameterException,
    PDOWriteException};
use ImageRepository\Model\{Database, User, UserManagement\UserGenerator};

use function ImageRepository\Api\checkMissingPostVars;
use function ImageRepository\Views\{createQueryJSON, safeApiRun};

use const ImageRepository\Utils\UNAUTHENTICATED;

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 * @throws MissingParameterException
 */
function register(Database $db, bool $debug) {
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
    $result = UserGenerator::createUser($user, $db, $debug);
    echo createQueryJSON($result);
}

safeApiRun(UNAUTHENTICATED, '/register');

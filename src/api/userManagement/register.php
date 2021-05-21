<?php

declare(strict_types=1);
namespace ImageRepository\Api\UserManagement;

use ImageRepository\Exception\{DebugPDOException,
    EncryptionFailureException,
    MissingParameterException,
    PDOWriteException};
use ImageRepository\Model\{Database, User, UserManagement\UserGenerator};
use ImageRepository\Utils\Auth;
use ImageRepository\Views\{ErrorHandler, JsonFormatter};

use function ImageRepository\Api\checkMissingPostVars;

use const ImageRepository\Utils\UNAUTHENTICATED;

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 * @throws MissingParameterException
 */
function register(Database $db, Auth $auth, bool $debug) {
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
    JsonFormatter::printArray($result);
}

ErrorHandler::safeApiRun(UNAUTHENTICATED, '/register');

<?php

declare(strict_types=1);
namespace ImageRepository\api\UserManagement;

use ImageRepository\api\EndpointValidator;
use ImageRepository\Exception\{DebugPDOException,
    EncryptionFailureException,
    MissingParameterException,
    PDOWriteException,
    StaticClassAssertionError};
use ImageRepository\Model\{Database, User, UserManagement\UserGenerator};
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 * Class to handle registration logic
 */
final class RegisterWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws EncryptionFailureException
     * @throws DebugPDOException
     * @throws PDOWriteException
     * @throws MissingParameterException
     */
    public static function run(Database $db, Auth $auth, bool $debug) {
        /* Make sure we have a valid request */
        EndpointValidator::checkMissingPostVars(['firstName', 'lastName', 'email', 'password']);
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
}
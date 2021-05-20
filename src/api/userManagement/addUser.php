<?php

declare(strict_types=1);
namespace App\Api\UserManagement;

/* TODO: rename to register and update read me*/
use App\Model\{DebugPDOException, PDOWriteException, User};
use App\Model\Encryption\EncryptionFailureException;
use App\Views\MissingParameterException;
use PDO;

use function App\Api\checkMissingPostVars;
use function App\Model\UserManagement\insertUser;
use function App\Views\{createQueryJSON, safeApiRun};

use const App\Utils\UNAUTHENTICATED;

/**
 * @throws EncryptionFailureException
 * @throws DebugPDOException
 * @throws PDOWriteException
 * @throws MissingParameterException
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

safeApiRun(UNAUTHENTICATED, '/register');

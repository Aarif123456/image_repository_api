<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Exception\{DebugPDOException,
    EncryptionFailureException,
    MissingParameterException,
    PDOWriteException};
use ImageRepository\Model\User;
use ImageRepository\Model\UserManagement\UserGenerator;
use ImageRepository\Views\JsonFormatter;

/**
 * Class to handle registration logic
 */
final class RegisterWorker extends AbstractWorker
{
    /**
     * @throws EncryptionFailureException
     * @throws DebugPDOException
     * @throws PDOWriteException
     * @throws MissingParameterException
     */
    public function run() {
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
        $result = UserGenerator::createUser($user, $this->db, $this->auth, $this->debug);
        $output = [
            'error' => $result['error'],
            'message' => $result['message']
        ];
        if (isset($result['uid'])) $output['id'] = $result['uid'];
        JsonFormatter::printArray($output);
    }
}
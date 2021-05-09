<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/registerUserRepo.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();

/* Connect to database */
$conn = getConnection();
$debug = DEBUG;

/* Make sure we have a valid request */
if (!(isValidPostVar('firstName') && isValidPostVar('lastName') &&
    isValidPostVar('email') && isValidPostVar('password'))) {
    exitWithError(MISSING_PARAMETERS);
}
/* Get user info in a object */
$user = (object)[
    'firstName' => trim($_POST['firstName']),
    'lastName' => trim($_POST['lastName']),
    'admin' => (bool)($_POST['admin'] ?? false)
];

/* If we have the information needed to create new account then we will make it */
$account = null;
$account = (object)[
    'email' => $_POST['email'] ?? '',
    'password' => $_POST['password'] ?? '',
];

$result = insertUser($user, $account, $conn, $debug);

if (!isset($result['error']) && (!isset($result['id']) || empty($result['id']))) {
    $result['error'] = COMMAND_FAILED;
}

echo createQueryJSON($result);
$conn = null;
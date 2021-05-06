<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();

/* Connect to database */
$conn = getConnection();

if (!isValidPostVar('email')) {
    exit(MISSING_PARAMETERS);
}

$auth = getAuth($conn);
$email = $_POST['email'];
echo $auth->isEmailTaken($email) ? EMAIL_EXISTS : EMAIL_NOT_IN_TABLE;

$conn = null;

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
    exitWithError(MISSING_PARAMETERS);
}

$auth = getAuth($conn);
$email = $_POST['email'];
echo createQueryJSON(['emailTaken' => $auth->isEmailTaken($email)]);

$conn = null;

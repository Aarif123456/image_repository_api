<?php

declare(strict_types=1);
/* program to authenticate user */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
/* Set required header and session start */
requiredHeaderAndSessionStart();
/* Connect to database */
$conn = getConnection();
if (validateUser($conn)) {
    logout($conn);
}
if (!(isValidPostVar('email') && isValidPostVar('password'))) throw new Exception(MISSING_PARAMETERS);
/* Store user type in session */
$admin = $_POST['admin'] ?? false;
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$remember = $_POST['remember'] ?? true;
$loginInfo = (object)[
    'email' => $email,
    'password' => $password,
    'remember' => $remember,
    'admin' => $admin
];
/* Make sure the password is correct */
$result = login($loginInfo, $conn);
$output['message'] = $result['message'];
$output['loggedIn'] = !$result['error'];
/* Make sure user is actually an admin*/
if ($admin && !(verifyUserAdmin($conn))) {
    logout($conn);
    header('HTTP/1.0 403 Forbidden');
    /* Exit and tell the client that their user type is they are not admin */
    $output['message'] = USER_NOT_ADMIN;
}
echo createQueryJSON($output);
$conn = null;

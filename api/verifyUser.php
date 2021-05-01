<?php
/* program to authenticate user */

require_once __DIR__ . '/common/apiReturn.php';
require_once __DIR__ . '/common/authenticate.php';
require_once __DIR__ . '/repository/database.php';
require_once __DIR__ . '/repository/verifyUserRepo.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();

/* Connect to database */
$conn = getConnection();

if (validateUser($conn)) {
    logout($conn);
}

if (isValidPostVar('email') && isValidPostVar('userType') && isValidPostVar('password')) {
    /* Store user type in session */
    $userType = trim($_POST['userType']);
    $_SESSION['userType'] = $userType;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = $_POST['remember'] ?? true;

    if (!(verifyUserType($userType))) {
        /* Exit and tell the client that their user type is invalid */
        exit(invalidUserType($userType));
    }
    $rows = queryUserVerify($email, $userType, $conn);
    $row = getArrayFromResult($rows);
    $loginInfo = (object)[
        'email' => $email,
        'password' => $password,
        'userType' => $userType,
        'remember' => $remember,
        'hashedPassword' => $row['password'],
    ];
    /* Make sure the password is correct */
    if (login($loginInfo, $conn)) {
        /* If we are determining user from table then determine it */
        $determineUser = strcmp($userType, 'user') == 0;
        if ($determineUser) {
            $_SESSION['userType'] = $row['userType'];
        }
        $_SESSION['userID'] = getUserID($conn);
        $_SESSION['email'] = htmlentities($row['firstName'] . ' ' . $row['lastName']);
        echo authenticatedSuccessfully($_SESSION['userType']);
    } else {
        http_response_code(403);
        header('HTTP/1.0 403 Forbidden');
        echo INVALID_PASSWORD;
    }
} else {
    echo MISSING_PARAMETERS;
}

$conn = null;

function getArrayFromResult($rows)
{
    if (count($rows) == 0) {
        exit (EMAIL_NOT_IN_TABLE);
    }

    return $rows[0];
}

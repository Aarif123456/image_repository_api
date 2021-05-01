<?php
/* program to verify login*/

require_once __DIR__ . '/../views/apiReturn.php';
require_once __DIR__ . '/../common/authenticate.php';
require_once __DIR__ . '/../repository/database.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();

/* Connect to database */
$conn = getConnection();

if (logout($conn)) {
    echo USER_LOGGED_OUT;
} else {
    exit(INTERNAL_SERVER_ERROR);
}

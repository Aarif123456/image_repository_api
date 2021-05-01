<?php

/* Imports */
require_once __DIR__ . '/../views/apiReturn.php';
require_once __DIR__ . '/../common/constants.php';
require_once __DIR__ . '/../common/authenticate.php';
require_once __DIR__ . '/../repository/database.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();

/* Connect to database */
$conn = getConnection();

if (checkSessionInfo() && validateUser($conn)) {
    echo 'true';
} else {
    echo 'false';
}


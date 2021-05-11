<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/viewImageRepo.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../repository/File.php';

/* Set required header and session start */
requiredHeaderAndSessionStart();
if (empty($_REQUEST)) {
    $_REQUEST = json_decode(file_get_contents('php://input'), true);
}

$debug = DEBUG;
/* Connect to database */
$conn = getConnection();

/* Make sure user is logged in */
if (!validateUser($conn)) {
    redirectToLogin();
}

/* Set variables */
$user = new User(getCurrentUserInfo($conn));
$filePath = $_REQUEST['filePath'] ?? '';

/* Make sure user is contained to their folder */
$filePath = File::getUserFolder($filePath, $user->id);

$result = getFolderDetail($filePath, $user, $conn);
echo createQueryJSON($result);

$conn = null;
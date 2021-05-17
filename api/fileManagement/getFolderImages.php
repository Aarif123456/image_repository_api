<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/folderDetailRepo.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../repository/File.php';
/* Set required header and session start */
requiredHeaderAndSessionStart();
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
$result = getFolderDetail($filePath, $user, $conn);
echo createQueryJSON($result);
$conn = null;
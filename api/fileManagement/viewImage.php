<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/viewImageRepo.php';
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/User.php';
/* Set required header and session start */
requiredHeaderAndSessionStart();
header('Content-Type: text/html; charset=UTF-8');

$debug = DEBUG;
/* Connect to database */
$conn = getConnection();
/* Make sure user is logged in */
if (!validateUser($conn)) {
    redirectToLogin();
}
if (!(isValidRequestVar('fileName'))) {
    throw new Exception(MISSING_PARAMETERS);
}
/* Set variables */
$user = new User(getCurrentUserInfo($conn));
$filePath = $_REQUEST['filePath'] ?? '';
$fileName = $_REQUEST['fileName'];
$file = new FileLocationInfo([
    'name' => $fileName,
    'path' => $filePath,
    'ownerId' => $user->id
]);
$fileData = getImage($file, $user, $conn);
$fileBinary = $fileData['data'];
$mime = $fileData['mime'];
echo '<img src="' . dataUri($fileBinary, $mime) . '" alt="you Image"/>';
$conn = null;
function dataUri($fileBinary, $mime): string {
    return 'data:' . $mime . ';base64,' . base64_encode($fileBinary);
}
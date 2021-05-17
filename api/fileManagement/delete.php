<?php
declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../common/authenticate.php';
require_once __DIR__ . '/../../repository/database.php';
require_once __DIR__ . '/../../repository/deleteFilesRepo.php';
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/User.php';
/* Set required header and session start */
requiredHeaderAndSessionStart();
$debug = DEBUG;
/* Connect to database */
$conn = getConnection();
/* Make sure user is logged in */
if (!validateUser($conn)) {
    redirectToLogin();
}
if (!(isValidRequestVar('fileName') || isValidRequestVar('fileId'))) {
    throw new Exception(MISSING_PARAMETERS);
}
/* Set variables */
$user = new User(getCurrentUserInfo($conn));
$filePath = $_REQUEST['filePath'] ?? '';
$fileName = $_REQUEST['fileName'] ?? '';
$fileId = $_REQUEST['fileId'] ?? null;
$output = [];
try {
    if (!empty($fileId)) {
        $output['success'] = deleteImageWithId((int)$fileId, $user, $conn, $debug);
    } elseif (!empty($fileName)) {
        $file = new FileLocationInfo([
            'name' => $fileName,
            'path' => $filePath,
            'ownerId' => $user->id
        ]);
        $output['success'] = deleteImage($file, $user, $conn, $debug);
    }
} catch (Exception $e) {
    $output['message'] = $e->getMessage();
    $output['success'] = false;
}
echo createQueryJSON($output);


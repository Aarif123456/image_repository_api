<?php

declare(strict_types=1);
/* Imports */
require_once __DIR__ . '/../../common/constants.php';
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/../../repository/viewImageRepo.php';
require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';
require_once __DIR__ . '/../validEndpoint.php';
require_once __DIR__ . '/fileInformation.php';
/**
 * @throws NoSuchFileException
 * @throws EncryptionFailureException
 * @throws MissingParameterException
 */
function viewImage(PDO $conn, bool $debug) {
    header('Content-Type: text/html; charset=UTF-8');
    /* Make sure request is valid*/
    if (!(isValidRequestVar('fileName') || isValidRequestVar('fileId'))) {
        missingParameterExit();
    }
    /* Set variables */
    $user = new User(getCurrentUserInfo($conn));
    $ownerId = $_REQUEST['ownerId'] ?? $user->id;
    $targetUser = new User(getUser($conn, $ownerId));
    $file = getFileInformation($targetUser, $conn);
    $fileData = getImage($file, $targetUser, $conn);
    $fileBinary = $fileData['data'];
    /* TODO: maybe remove mime */
    $mime = $fileData['mime'];
    echo '<img src="' . dataUri($fileBinary, $mime) . '" alt="your image"/>';
}

function dataUri($fileBinary, $mime): string {
    return 'data:' . $mime . ';base64,' . base64_encode($fileBinary);
}

safeApiRun(AUTHORIZED_USER, 'viewImage');


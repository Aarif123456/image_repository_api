<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{EncryptionFailureException, MissingParameterException, NoSuchFileException};
use ImageRepository\Model\User;
use PDO;

use function ImageRepository\Api\{isValidRequestVar, missingParameterExit};
use function ImageRepository\Model\FileManagement\getImage;
use function ImageRepository\Utils\{getCurrentUserInfo, getUser};
use function ImageRepository\Views\safeApiRun;

use const ImageRepository\Utils\AUTHORIZED_USER;

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

safeApiRun(AUTHORIZED_USER, '/viewImage');


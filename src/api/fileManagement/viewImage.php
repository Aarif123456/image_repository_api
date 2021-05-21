<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{EncryptionFailureException, MissingParameterException, NoSuchFileException};
use ImageRepository\Model\{Database, User};
use ImageRepository\Model\FileManagement\FileReader;

use function ImageRepository\Api\{isValidRequestVar, missingParameterExit};
use function ImageRepository\Utils\{getCurrentUserInfo, getUser};
use function ImageRepository\Views\safeApiRun;

use const ImageRepository\Utils\AUTHORIZED_USER;

/**
 * @throws NoSuchFileException
 * @throws EncryptionFailureException
 * @throws MissingParameterException
 */
function viewImage(Database $db, bool $debug) {
    header('Content-Type: text/html; charset=UTF-8');
    /* Make sure request is valid*/
    if (!(isValidRequestVar('fileName') || isValidRequestVar('fileId'))) {
        missingParameterExit();
    }
    /* Set variables */
    $user = new User(getCurrentUserInfo($db));
    $ownerId = $_REQUEST['ownerId'] ?? $user->id;
    $targetUser = new User(getUser($db, $ownerId));
    $file = getFileInformation($targetUser, $db);
    $fileBinary = FileReader::getFileBytes($file, $targetUser, $db);
    /* TODO: maybe remove mime */
    $mime = FileReader::getFileMime($file, $targetUser, $db);
    echo '<img src="' . dataUri($fileBinary, $mime) . '" alt="your image"/>';
}

function dataUri($fileBinary, $mime): string {
    return 'data:' . $mime . ';base64,' . base64_encode($fileBinary);
}

safeApiRun(AUTHORIZED_USER, '/viewImage');


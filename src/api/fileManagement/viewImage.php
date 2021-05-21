<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\api\EndpointValidator;
use ImageRepository\Exception\{EncryptionFailureException, MissingParameterException, NoSuchFileException};
use ImageRepository\Model\{Database, User};
use ImageRepository\Model\FileManagement\FileReader;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\ErrorHandler;

use const ImageRepository\Utils\AUTHORIZED_USER;

/* TODO: move logic to worker class */
/**
 * @throws NoSuchFileException
 * @throws EncryptionFailureException
 * @throws MissingParameterException
 */
function viewImage(Database $db, Auth $auth, bool $debug) {
    header('Content-Type: text/html; charset=UTF-8');
    /* Make sure request is valid*/
    if (!(EndpointValidator::isValidRequestVar('fileName') || EndpointValidator::isValidRequestVar('fileId'))) {
        EndpointValidator::missingParameterExit();
    }
    /* Set variables */
    $user = new User($auth->getCurrentUserInfo());
    $ownerId = $_REQUEST['ownerId'] ?? $user->id;
    $targetUser = new User($auth->getUser($ownerId));
    $file = getFileInformation($targetUser, $db);
    $fileBinary = FileReader::getFileBytes($file, $targetUser, $db);
    /* TODO: maybe remove mime */
    $mime = FileReader::getFileMime($file, $targetUser, $db);
    echo '<img src="' . dataUri($fileBinary, $mime) . '" alt="your image"/>';
}

function dataUri($fileBinary, $mime): string {
    return 'data:' . $mime . ';base64,' . base64_encode($fileBinary);
}

ErrorHandler::safeApiRun(AUTHORIZED_USER, '/viewImage');


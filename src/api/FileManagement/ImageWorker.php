<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\api\EndpointValidator;
use ImageRepository\Exception\{EncryptionFailureException,
    MissingParameterException,
    NoSuchFileException,
    StaticClassAssertionError};
use ImageRepository\Model\{Database, User};
use ImageRepository\Model\FileManagement\FileReader;
use ImageRepository\Utils\Auth;

/**
 * Class with logic for downloading or viewing images
 */
final class ImageWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws NoSuchFileException
     * @throws EncryptionFailureException
     * @throws MissingParameterException
     */
    public static function run(Database $db, Auth $auth, bool $debug) {
        /* Make sure request is valid*/
        if (!(EndpointValidator::isValidRequestVar('fileName') || EndpointValidator::isValidRequestVar('fileId'))) {
            EndpointValidator::missingParameterExit();
        }
        /* Set variables */
        $user = User::createFromAuth($auth);
        $ownerId = $_REQUEST['ownerId'] ?? $user->id;
        $download = $_REQUEST['download'] ?? false;
        /* Create user object using the id of the image's owner */
        $targetUser = new User($auth->getUser($ownerId));
        $file = FileLocationInfoFactory::createFromApiData($targetUser, $db);
        $fileBinary = FileReader::getFileBytes($file, $targetUser, $db);
        $fileSize = strlen($fileBinary);
        $mime = FileReader::getFileMime($file, $targetUser, $db);
        /* if we want download then also add in filename*/
        $disposition = $download ? "attachment; filename=$file->name" : 'inline';
        header("Content-Disposition: $disposition");
        header("Content-Type: $mime");
        header("Content-Length: $fileSize");
        header('Connection: close');
        echo $fileBinary;
    }
}
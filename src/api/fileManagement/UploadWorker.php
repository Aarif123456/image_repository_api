<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Api\EndpointValidator;
use ImageRepository\Exception\{DebugPDOException,
    EncryptedFileNotCreatedException,
    EncryptionFailureException,
    FileAlreadyExistsException,
    FileLimitExceededException,
    FileNotSentException,
    InvalidAccessException,
    InvalidFileFormatException,
    MissingParameterException,
    PDOWriteException,
    SqlCommandFailedException,
    StaticClassAssertionError,
    UnknownErrorException};
use ImageRepository\Model\{Database, File, FileManagement\PolicySelector, User};
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 * Class to handle the logic of uploading files
 */
final class UploadWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws EncryptedFileNotCreatedException
     * @throws SqlCommandFailedException
     * @throws DebugPDOException
     * @throws FileNotSentException
     * @throws InvalidFileFormatException
     * @throws InvalidAccessException
     * @throws UnknownErrorException
     * @throws FileLimitExceededException
     * @throws EncryptionFailureException
     * @throws PDOWriteException
     * @throws MissingParameterException
     */
    public static function run(Database $db, Auth $auth, bool $debug) {
        /* Set variables */
        $user = new User($auth->getCurrentUserInfo());
        $fileAccess = $_REQUEST['fileAccess'] ?? PolicySelector::defaultAccess();
        $filePath = $_REQUEST['filePath'] ?? '';
        $fileNames = $_REQUEST['fileNames'] ?? 'images';
        /* Make sure user uploaded a file*/
        if (!EndpointValidator::isValidFileVar($fileNames)) {
            EndpointValidator::missingParameterExit();
        }
        /* Create folder where user files will be stored */
        $userFolder = File::getUserFolder($filePath, $user->id);
        if (!file_exists($userFolder)) {
            mkdir($userFolder, 0777, true);
        }
        /*Create array to track if upload was successful */
        $uploadSuccess = [];
        $files = createFiles($fileNames);
        foreach ($files['error'] as $key => $value) {
            $file = new File([
                /*File names cannot have slashes because it would mess up paths -
                * and we want to clean the input cause we might want to display the filename later */
                'name' => $files['name'][$key],
                'size' => $files['size'][$key],
                'errorStatus' => $files['error'][$key],
                'location' => $files['tmp_name'][$key],
                /* NOTE: getting the type from the file is not always safe as it can be tampered. However, users 
                * only have access to their own files. So, we choose to ignore it  */
                /* TODO: try using - maybe set in create files function image_type_to_mime_type(exif_imagetype($file))*/
                'type' => $files['type'][$key],
                'path' => $filePath,
                'access' => $fileAccess,
                'ownerId' => $user->id
            ]);
            $uploadSuccess[$file->name] = processFile($file, $user, $db);
        }
        JsonFormatter::printArray($uploadSuccess);
    }
}
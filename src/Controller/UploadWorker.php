<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

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
    UnknownErrorException};
use ImageRepository\Model\{File, FileManagement\PolicySelector, User};
use ImageRepository\Views\ErrorHandler;
use ImageRepository\Views\JsonFormatter;

/**
 * Class to handle the logic of uploading files
 */
final class UploadWorker extends AbstractWorker
{
    /**
     * @throws MissingParameterException
     */
    public function run() {
        /* Set variables */
        $user = User::createFromAuth($this->auth);
        $fileAccess = $_REQUEST['fileAccess'] ?? PolicySelector::defaultAccess();
        $filePath = $_REQUEST['filePath'] ?? '/';
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
        $files = FileProcessor::createFiles($fileNames);
        foreach ($files['error'] as $key => $value) {
            $file = new File([
                /*File names cannot have slashes because it would mess up paths -
                * and we want to clean the input cause we might want to display the filename later */
                'name' => $files['name'][$key],
                'size' => $files['size'][$key],
                'errorStatus' => $files['error'][$key],
                'location' => $files['tmp_name'][$key],
                'type' => image_type_to_mime_type(exif_imagetype($files['tmp_name'][$key])),
                'path' => $filePath,
                'access' => $fileAccess,
                'ownerId' => $user->id
            ]);
            try {
                $uploadSuccess[$file->name] = ['error' => FileProcessor::processFile($file, $user, $this->db)];
            } catch (FileAlreadyExistsException |
            EncryptedFileNotCreatedException |
            DebugPDOException |
            FileNotSentException |
            InvalidFileFormatException |
            InvalidAccessException |
            UnknownErrorException |
            FileLimitExceededException |
            EncryptionFailureException |
            PDOWriteException $e) {
                $uploadSuccess[$file->name] = [
                    'error' => true,
                    'message' => ErrorHandler::createLocalizedError($this->translator, $e)
                ];
            }
        }
        JsonFormatter::printArray($uploadSuccess);
    }
}
<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Exception\{FileAlreadyExistsException,
    FileLimitExceededException,
    FileNotSentException,
    InvalidFileFormatException,
    StaticClassAssertionError,
    UnknownErrorException};
use ImageRepository\Model\{File, FileLocationInfo};

use const ImageRepository\Utils\MAX_FILE_SIZE;

/**
 * Class to make sure files are valid
 */
final class FileValidator
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws FileNotSentException
     * @throws FileAlreadyExistsException
     * @throws UnknownErrorException
     * @throws FileLimitExceededException
     * @throws InvalidFileFormatException
     */
    public static function checkFile(File $file, bool $checkForExistingFile = true) {
        self::checkFileForError($file->errorStatus);
        self::checkFileType($file);
        self::checkFileSize($file->size);
        if ($checkForExistingFile) {
            self::checkFileExists($file);
        }
    }

    /**
     * @throws FileNotSentException
     * @throws UnknownErrorException
     * @throws FileLimitExceededException
     */
    public static function checkFileForError(int $fileErrorStatus) {
        switch ($fileErrorStatus) {
            case UPLOAD_ERR_OK:
                return;
            case UPLOAD_ERR_NO_FILE:
                throw new FileNotSentException();
            case UPLOAD_ERR_INI_SIZE: // INTENTIONAL FALL THROUGH
            case UPLOAD_ERR_FORM_SIZE:
                throw new FileLimitExceededException();
            default:
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                throw new UnknownErrorException();
        }
    }

    /**
     * @param File $file
     * @throws InvalidFileFormatException
     */
    public static function checkFileType(File $file) {
        $fileSize = $file->size;
        $fileLocation = $file->location;
        if ($fileSize < 12 || !((bool)exif_imagetype($fileLocation))) {
            throw new InvalidFileFormatException();
        }
    }

    /**
     * @throws FileLimitExceededException
     */
    public static function checkFileSize(int $size) {
        if ($size > MAX_FILE_SIZE) {
            throw new FileLimitExceededException();
        }
    }

    /**
     * @throws FileAlreadyExistsException
     */
    public static function checkFileExists(FileLocationInfo $fileInfo) {
        if (file_exists($fileInfo->getEncryptedFilePath())) {
            throw new FileAlreadyExistsException();
        }
    }

}

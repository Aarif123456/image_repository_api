<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

use ImageRepository\Exception\{FileAlreadyExistsException,
    FileLimitExceededException,
    FileNotSentException,
    InvalidFileFormatException,
    UnknownErrorException};
use ImageRepository\Model\{File, FileLocationInfo};

/**
 * @throws FileNotSentException
 * @throws UnknownErrorException
 * @throws FileLimitExceededException
 */
function checkFileForError(int $fileErrorStatus) {
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
function checkFileType(File $file) {
    $fileSize = $file->size;
    $fileLocation = $file->location;
    if ($fileSize < 12 || !((bool)exif_imagetype($fileLocation))) {
        throw new InvalidFileFormatException();
    }
}

/**
 * @throws FileAlreadyExistsException
 */
function checkFileExists(FileLocationInfo $fileInfo) {
    if (file_exists($fileInfo->getEncryptedFilePath())) {
        throw new FileAlreadyExistsException();
    }
}

/**
 * @throws FileNotSentException
 * @throws FileAlreadyExistsException
 * @throws UnknownErrorException
 * @throws FileLimitExceededException
 * @throws InvalidFileFormatException
 */
function checkFile(File $file, bool $checkForExistingFile = true) {
    checkFileForError($file->errorStatus);
    checkFileType($file);
    if ($checkForExistingFile) {
        checkFileExists($file);
    }
}
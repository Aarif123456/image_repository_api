<?php

declare(strict_types=1);

require_once __DIR__ . '/../../views/apiReturn.php';
require_once __DIR__ . '/../../views/errorHandling.php';

function checkFileForError(int $fileErrorStatus) {
    switch ($fileErrorStatus) {
        case UPLOAD_ERR_OK:
            return;
        case UPLOAD_ERR_NO_FILE:
            throw new Exception(NO_FILE_SENT);
        case UPLOAD_ERR_INI_SIZE: // INTENTIONAL FALL THROUGH
        case UPLOAD_ERR_FORM_SIZE:
            throw new Exception(FILE_SIZE_LIMIT_EXCEEDED);
        default:
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            throw new Exception(INTERNAL_SERVER_ERROR);
    }
}


/**
 * @param File $file
 * @throws Exception
 */
function checkFileType(File $file) {
    $fileSize = $file->size;
    $fileLocation = $file->location;
    if ($fileSize < 12 || !((bool)exif_imagetype($fileLocation))) {
        throw new Exception(INVALID_FILE_FORMAT);
    }
}

function checkFileExists(FileLocationInfo $fileInfo) {
    if (file_exists($fileInfo->getEncryptedFilePath())) {
        throw new Exception(FILE_ALREADY_EXISTS);
    }
}

function checkFile(File $file, bool $checkForExistingFile = true) {
    checkFileForError($file->errorStatus);
    checkFileType($file);
    if ($checkForExistingFile) {
        checkFileExists($file);
    }

}
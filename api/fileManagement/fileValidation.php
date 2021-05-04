<?php

require_once __DIR__ . '/../../views/apiReturn.php';

function checkFileForError($fileErrorStatus) {
    switch ($fileErrorStatus) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            exit(NO_FILE_SENT);
        case UPLOAD_ERR_INI_SIZE: // INTENTIONAL FALL THROUGH
        case UPLOAD_ERR_FORM_SIZE:
            exit(FILE_SIZE_LIMIT_EXCEEDED);
        default:
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            exit(INTERNAL_SERVER_ERROR);
    }
}

/*NOTE: pass in $_FILES[$fileName]['size'] or $_FILES[$fileName]['size'][key]*/
function checkFileSize($fileSize) {
    if ($fileSize > 1000000) {
        exit(FILE_SIZE_LIMIT_EXCEEDED);
    }
}

/* NOTE: Pass in $_FILES[$fileName]['tmp_name']*/
function checkFileType($fileTmpName) {
    /*We cannot trust MIME values in the php array so we check it ourselves */
    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
            $fileInfo->file($fileTmpName),
            [
                'bmp' => 'image/bmp',
                'gif' => 'image/gif',
                'ico' => 'image/vnd.microsoft.icon',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'svg' => 'image/svg+xml',
                'tif' => 'image/tiff',
                'tiff' => 'image/tiff',
                'webp' => 'image/webp'
            ],
            true
        )) {
        exit(INVALID_FILE_FORMAT);
    }
}


function checkFile($file) {
    checkFileForError($file->errorStatus);
    checkFileSize($file->size);
    checkFileType($file->location);
}
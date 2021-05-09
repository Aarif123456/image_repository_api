<?php

declare(strict_types=1);

require_once __DIR__ . '/../../views/apiReturn.php';

function checkFileForError($fileErrorStatus) {
    switch ($fileErrorStatus) {
        case UPLOAD_ERR_OK:
            return;
        case UPLOAD_ERR_NO_FILE:
            exitWithError(NO_FILE_SENT);
            break;
        case UPLOAD_ERR_INI_SIZE: // INTENTIONAL FALL THROUGH
        case UPLOAD_ERR_FORM_SIZE:
            exitWithError(FILE_SIZE_LIMIT_EXCEEDED);
            break;
        default:
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            exitWithError(INTERNAL_SERVER_ERROR);
    }
}

/*NOTE: pass in $_FILES[$fileName]['size'] or $_FILES[$fileName]['size'][key]*/
function checkFileSize($fileSize) {
//    if ($fileSize > 2097152) {
//        exitWithError(FILE_SIZE_LIMIT_EXCEEDED);
//    }
}

/**
 * @param $fileTmpName :  Pass in $_FILES[$fileName]['tmp_name']
 * @throws Exception
 */
function checkFileType($fileTmpName, $fileType) {
    /*We cannot trust MIME values in the php array so we check it ourselves */
//    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $ext = [
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
    ];
    if (false === array_search(
//            $fileInfo->file($fileTmpName),
            $fileType,
            $ext
        )) {
        throw new Exception(INVALID_FILE_FORMAT);
    }
}


function checkFile($file) {
    checkFileForError($file->errorStatus);
    checkFileSize($file->size);
    checkFileType($file->location, $file->type);
}
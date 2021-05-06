<?php

declare(strict_types=1);

require_once __DIR__ . '/../../common/constants.php';

/* We encrypt the file and delete the temporary file that holds the unencrypted version */
function encryptFile($file, $policy): bool {
    /* Get the bytes of files */
    $fileData = file_get_contents($file->location);
    /* Delete the temp file*/
    unlink($file->location);
    $encryptedFile = getFileEncrypted($fileData, $policy);

    return !(empty(file_put_contents("$file->path/$file->name", $encryptedFile)));
}

/* TODO: add the logic to get the encrypted file*/
function getFileEncrypted($uploadFile, $policy, $debug = DEBUG) {
    if ($debug) {
        echo 'Unimplemented, we need to call mCpAbe API to encrypt ';
    }

    return $uploadFile;
}
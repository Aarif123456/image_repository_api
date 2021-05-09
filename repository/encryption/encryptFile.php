<?php
declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/callApi.php';

/* We encrypt the file and delete the temporary file that holds the unencrypted version */
function encryptFile($file, $policy,  $conn, $debug = false): bool {
    /* Get the bytes of files */
    $fileData = file_get_contents($file->location);
    /* Delete the temp file*/
    unlink($file->location);
    $encryptedFile = getFileEncrypted($fileData, $policy, $conn, $debug);

    return !(empty(file_put_contents("$file->path/$file->name", $encryptedFile)));
}

function getFileEncrypted($inputFile, $policy, $conn, $debug = false): string {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];

    return encrypt($publicKey, $policy, $inputFile, $debug);
}
<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/callApi.php';
require_once __DIR__ . '/encryptionExceptionConstants.php';

function getEncryptedFileLocation(FileLocationInfo $file): string {
    return "$file->path/$file->name.Encrypted";
}

/* We encrypt the file and delete the temporary file that holds the unencrypted version */
function encryptFile(File $file, string $policy, PDO $conn, bool $debug = false) {
    /* Get the bytes of files */
    $fileData = file_get_contents($file->location);
    $encryptedFile = getFileEncrypted($fileData, $policy, $conn, $debug);
    $encryptedFileLocation = getEncryptedFileLocation($file);
    $encryptedFileSize = file_put_contents($encryptedFileLocation, $encryptedFile);
    if (empty($encryptedFileSize)) {
        unlink($encryptedFileLocation);
        throw new EncryptedFileNotCreatedException();
    }
}

/* Return encrypted version of file */
function getFileEncrypted(string $inputFile, string $policy, PDO $conn, bool $debug = false): string {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];

    return encrypt($publicKey, $policy, $inputFile);
}


<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/callApi.php';
require_once __DIR__ . '/encryptionExceptionConstants.php';

/* We encrypt the file and delete the temporary file that holds the unencrypted version */
function encryptFile(File $file, string $policy, PDO $conn) {
    $encryptedFile = getFileEncrypted($file->location, $policy, $conn);
    $encryptedFileLocation = $file->getEncryptedFilePath();
    $encryptedFileSize = file_put_contents($encryptedFileLocation, $encryptedFile);
    if (empty($encryptedFileSize)) {
        unlink($encryptedFileLocation);
        throw new EncryptedFileNotCreatedException();
    }
}

/* Return encrypted version of file */
function getFileEncrypted(string $inputFileLocation, string $policy, PDO $conn): string {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];

    return encrypt($publicKey, $policy, $inputFileLocation);
}


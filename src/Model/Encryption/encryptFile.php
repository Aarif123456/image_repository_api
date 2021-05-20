<?php

declare(strict_types=1);
namespace App\Model\Encryption;

use App\Model\File;
use PDO;

use function App\Model\getSystemKeys;

/**
 * We encrypt the file and delete the temporary file that holds the unencrypted version
 *
 * @throws EncryptedFileNotCreatedException*
 * @throws EncryptionFailureException
 */
function encryptFile(File $file, string $policy, PDO $conn) {
    $encryptedFile = getFileEncrypted($file->location, $policy, $conn);
    $encryptedFileLocation = $file->getEncryptedFilePath();
    $encryptedFileSize = file_put_contents($encryptedFileLocation, $encryptedFile);
    if (empty($encryptedFileSize)) {
        unlink($encryptedFileLocation);
        throw new EncryptedFileNotCreatedException();
    }
}

/**
 * Return encrypted version of file
 *
 * @throws EncryptionFailureException
 */
function getFileEncrypted(string $inputFileLocation, string $policy, PDO $conn): string {
    $systemKeys = getSystemKeys($conn);
    $publicKey = $systemKeys['publicKey'];

    return encrypt($publicKey, $policy, $inputFileLocation);
}


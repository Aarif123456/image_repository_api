<?php

declare(strict_types=1);
namespace ImageRepository\Model\Encryption;

use ImageRepository\Exception\{EncryptedFileNotCreatedException, EncryptionFailureException};
use ImageRepository\Model\File;
use PDO;

use function ImageRepository\Model\getSystemKeys;

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


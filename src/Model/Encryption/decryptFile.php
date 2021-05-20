<?php

declare(strict_types=1);
namespace App\Model\Encryption;

use App\Model\FileLocationInfo;

/**
 * @param FileLocationInfo $file
 * @param string $privateKey
 * @param string $publicKey
 * @return string
 * @throws EncryptionFailureException
 * @throws NoSuchFileException
 */
function getFileDecrypted(FileLocationInfo $file, string $privateKey, string $publicKey): string {
    $encryptedFileLocation = $file->getEncryptedFilePath();
    if (!file_exists($encryptedFileLocation)) {
        throw new NoSuchFileException();
    }

    return decrypt($publicKey, $privateKey, $encryptedFileLocation);
}


<?php

declare(strict_types=1);

require_once __DIR__ . '/encryptionConstants.php';
require_once __DIR__ . '/encryptionExceptionConstants.php';
require_once __DIR__ . '/callApi.php';
require_once __DIR__ . '/encryptFile.php';

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



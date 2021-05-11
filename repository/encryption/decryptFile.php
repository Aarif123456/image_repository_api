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
 * @param false $debug
 * @return string
 * @throws Exception
 */
function getFileDecrypted(FileLocationInfo $file, string $privateKey, string $publicKey, bool $debug = false): string {
    $encryptedFileLocation = $file->getEncryptedFilePath();
    if(!file_exists($encryptedFileLocation)){
        throw new NoSuchFileException();
    }
    $encryptedFile = file_get_contents($encryptedFileLocation);

    return decrypt($publicKey, $privateKey, $encryptedFile);
}



<?php

declare(strict_types=1);
namespace ImageRepository\Model\Encryption;

use ImageRepository\Exception\{EncryptionFailureException, NoSuchFileException, StaticClassAssertionError};
use ImageRepository\Model\FileLocationInfo;

/**
 * Class decrypts file the given file with the public and private key
 */
final class FileDecrypter
{
    function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @param FileLocationInfo $file
     * @param string $privateKey
     * @param string $publicKey
     * @return string
     * @throws EncryptionFailureException
     * @throws NoSuchFileException
     */
    public static function run(FileLocationInfo $file, string $privateKey, string $publicKey): string {
        $encryptedFileLocation = $file->getEncryptedFilePath();
        if (!file_exists($encryptedFileLocation)) {
            throw new NoSuchFileException();
        }

        return Encrypter::decrypt($publicKey, $privateKey, $encryptedFileLocation);
    }
}

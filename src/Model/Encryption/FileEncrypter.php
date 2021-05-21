<?php

declare(strict_types=1);
namespace ImageRepository\Model\Encryption;

use ImageRepository\Exception\{EncryptedFileNotCreatedException, EncryptionFailureException, StaticClassAssertionError};
use ImageRepository\Model\File;

/**
 * Class to encrypt files
 */
final class FileEncrypter
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * We encrypt the file and delete the temporary file that holds the unencrypted version
     *
     * @throws EncryptedFileNotCreatedException
     * @throws EncryptionFailureException
     */
    public static function run(File $file, string $policy, string $publicKey) {
        $encryptedFile = Encrypter::encrypt($publicKey, $policy, $file->location);
        $encryptedFileLocation = $file->getEncryptedFilePath();
        $encryptedFileSize = file_put_contents($encryptedFileLocation, $encryptedFile);
        if (empty($encryptedFileSize)) {
            unlink($encryptedFileLocation);
            throw new EncryptedFileNotCreatedException();
        }
    }
}


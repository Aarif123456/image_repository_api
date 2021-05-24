<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Exception\{DebugPDOException,
    EncryptedFileNotCreatedException,
    EncryptionFailureException,
    FileAlreadyExistsException,
    FileLimitExceededException,
    FileNotSentException,
    InvalidAccessException,
    InvalidFileFormatException,
    PDOWriteException,
    SqlCommandFailedException,
    StaticClassAssertionError,
    UnknownErrorException};
use ImageRepository\Model\{Database, EncryptionKeyReader, File, User};
use ImageRepository\Model\Encryption\FileEncrypter;
use ImageRepository\Model\FileManagement\{FileManager, PolicySelector};

use const ImageRepository\Utils\DEBUG;

/**
 * Helper class to process the received file
 */
final class FileProcessor
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    public static function createFiles(string $fileNames): array {
        /* If we already sent back and array of images then we are good and just need to create  a reference to the array */
        if (is_array($_FILES[$fileNames]['error']) || is_object($_FILES[$fileNames]['error'])) {
            $filesVariable = &$_FILES[$fileNames];
        } else {
            /* Otherwise format the one file so it looks like a request with one file */
            $filesVariable = [];
            foreach ($_FILES[$fileNames] as $key => $value) {
                $filesVariable[$key] = [0 => $value];
            }
        }

        return $filesVariable;
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws EncryptedFileNotCreatedException
     * @throws SqlCommandFailedException
     * @throws DebugPDOException
     * @throws FileNotSentException
     * @throws InvalidFileFormatException
     * @throws InvalidAccessException
     * @throws UnknownErrorException
     * @throws FileLimitExceededException
     * @throws EncryptionFailureException
     * @throws PDOWriteException
     */
    public static function processFile(File $file, User $user, Database $db, bool $debug = DEBUG): array {
        FileValidator::checkFile($file);
        /* Encrypt the file*/
        $policy = PolicySelector::getPolicy($file->access, $user);
        $publicKey = EncryptionKeyReader::publicKey($db);
        FileEncrypter::run($file, $policy, $publicKey);
        /* Add a reference to the file in our database */
        $output = ['error' => empty(FileManager::addFile($file, $user, $db, $debug))];
        if ($output['error']) {
            throw new SqlCommandFailedException();
        }
        /*if we have successfully encrypted our file then remove them temporary non encrypted version */
        unlink($file->location);

        return $output;
    }
}


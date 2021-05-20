<?php

declare(strict_types=1);
namespace ImageRepository\Api\FileManagement;

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
    UnknownErrorException};
use ImageRepository\Model\{File, User};
use PDO;

use function ImageRepository\Model\FileManagement\insertFile;

use const ImageRepository\Utils\DEBUG;

function createFiles(string $fileNames): array {
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
function processFile(File $file, User $user, PDO $conn, bool $debug = DEBUG): array {
    checkFile($file);
    $output = ['error' => empty(insertFile($file, $user, $conn, $debug))];
    if ($output['error']) {
        throw new SqlCommandFailedException();
    }
    /*if we have successfully encrypted our file then remove them temporary non encrypted version */
    unlink($file->location);

    return $output;
}
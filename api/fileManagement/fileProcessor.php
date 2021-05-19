<?php

declare(strict_types=1);
require_once __DIR__ . '/../../repository/File.php';
require_once __DIR__ . '/../../repository/uploadFileRepo.php';
require_once __DIR__ . '/../../repository/User.php';
require_once __DIR__ . '/fileValidation.php';
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
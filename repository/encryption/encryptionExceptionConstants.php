<?php

declare(strict_types=1);
require_once __DIR__ . '/../../common/CustomException.php';
const ENCRYPTED_FILE_NOT_CREATED = 'Unable to save encrypted file';
const NO_SUCH_FILE = 'No file with that name exists in the current folder';
const INTERNAL_ENCRYPTION_FAILURE = 'Failure in encryption or decryption call';
class EncryptedFileNotCreatedException extends CustomException
{
    protected $message = ENCRYPTED_FILE_NOT_CREATED;
}

class NoSuchFileException extends CustomException
{
    protected $message = NO_SUCH_FILE;
}

class EncryptionFailureException extends CustomException
{
    protected $message = INTERNAL_ENCRYPTION_FAILURE;
}
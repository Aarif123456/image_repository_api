<?php

declare(strict_types=1);
/*TODO: make classes of exception and then just handle their message in the view folder
Reference: https://www.php.net/manual/en/language.exceptions.php
*/
const ENCRYPTED_FILE_NOT_CREATED = 'Unable to save encrypted file';
const NO_SUCH_FILE = 'No file with that name exists in the current folder';
const INTERNAL_ENCRYPTION_FAILURE = 'Failure in encryption or decryption call';
class EncryptedFileNotCreatedException extends Exception
{
    public function __construct(string $message = null, int $code = 0) {
        if (!$message) {
            throw new $this(get_class($this) . ': ' . ENCRYPTED_FILE_NOT_CREATED);
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . ': ' . $this->message;
    }
}

class NoSuchFileException extends Exception
{
    public function __construct(string $message = null, int $code = 0) {
        if (!$message) {
            throw new $this(get_class($this) . ': ' . NO_SUCH_FILE);
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . ': ' . $this->message;
    }
}

class EncryptionFailureException extends Exception
{
    public function __construct(string $message = null, int $code = 0) {
        if (!$message) {
            throw new $this(get_class($this) . ': ' . INTERNAL_ENCRYPTION_FAILURE);
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . ': ' . $this->message;
    }
}
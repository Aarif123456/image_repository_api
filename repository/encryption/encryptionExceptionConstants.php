<?php

declare(strict_types=1);
require_once __DIR__ . '/../../common/CustomException.php';
class EncryptedFileNotCreatedException extends CustomException
{
}

class NoSuchFileException extends CustomException
{
}

class EncryptionFailureException extends CustomException
{
}
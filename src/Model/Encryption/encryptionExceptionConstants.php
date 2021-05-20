<?php

declare(strict_types=1);
namespace App\Model\Encryption;

use App\Utils\CustomException;

class EncryptedFileNotCreatedException extends CustomException
{
}

class NoSuchFileException extends CustomException
{
}

class EncryptionFailureException extends CustomException
{
}
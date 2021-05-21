<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\Model\File;
use PHPUnit\Framework\TestCase;

final class FileMockProvider extends TestCase
{
    public static function getMockFile($fileLocation, $fileEncryptedLocation) {
        $file = (new FileMockProvider)->createMock(File::class);
        $file->method('getEncryptedFilePath')->willReturn($fileEncryptedLocation);
        $file->path = $fileLocation;
        $file->location = $fileLocation;

        return $file;
    }
}

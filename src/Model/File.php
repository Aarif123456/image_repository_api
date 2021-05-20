<?php

declare(strict_types=1);
namespace ImageRepository\Model;

class FileLocationInfo
{
    public string $name;
    public string $path;
    public string $realPath;

    /* Make sure we have the file location */
    public function __construct(array $properties = []) {
        $this->name = htmlentities(str_replace(['/', '\\'], '', basename($properties['name'])));
        $this->path = str_replace('..', '', $properties['path']);
        $ownerId = (int)$properties['ownerId'];
        /* Make sure user is contained to their folder */
        $this->realPath = self::getUserFolder($this->path, $ownerId) . "/$this->name";
    }

    /* Handle getting the actual file path for the user */
    public static function getUserFolder(string $filePath, int $ownerId): string {
        return "userFiles/$ownerId/" . str_replace('..', '', $filePath);
    }

    public function getEncryptedFilePath(): string {
        return "$this->realPath.Encrypted";
    }
}

class File extends FileLocationInfo
{
    public int $size;
    public int $errorStatus;
    public string $location;
    public string $type;
    public ?int $access;

    public function __construct(array $properties = []) {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
        /* Make sure some variables are guaranteed to be there and are processed appropriately*/
        parent::__construct($properties);
    }
}
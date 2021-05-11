<?php

declare(strict_types=1);

class FileLocationInfo {
    public string $name;
    public string $path;
    public string $realPath;

    /* Make sure we have the file location */
    public function __construct(array $properties = []){
        $this->name = $properties['name'];
        /* str_replace('..', '') */
        $this->path = str_replace('..', '', $properties['path']);
        $ownerId =  (int)$properties['ownerId'];
        /* Make sure user is contained to their folder */
        $this->realPath = self::getUserFolder($this->path, $ownerId);
    }
    
    /* Handle getting the actual file path for the user */
    public function getRealPath(): string {
        return $this->realPath;
    }

    public function getEncryptedFilePath(): string {
        return "$this->realPath/$this->name.Encrypted";
    }

    public static function getUserFolder(string $filePath, int $ownerId): string {
        return "userFiles/$ownerId/". str_replace('..', '', $filePath);
    }
}


class File extends FileLocationInfo {
    public int $size;
    public int $errorStatus;
    public string $location;
    public string $type;
    public ?int $access;

    public function __construct(array $properties = []) {
        parent::__construct($properties);
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
<?php

declare(strict_types=1);

class FileLocationInfo {
    public string $name;
    public string $path;

    /* Make sure we have the file location */
    public function __construct(array $properties = []) {
        $this->name = $properties['name'];
        $this->path = $properties['path'];
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
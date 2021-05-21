<?php

declare(strict_types=1);
namespace ImageRepository\Model;

class File extends FileLocationInfo
{
    public int $size;
    public int $errorStatus;
    public string $location;
    public string $type;
    public int $access;

    public function __construct(array $properties = []) {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
        /* Make sure some variables are guaranteed to be there and are processed appropriately*/
        parent::__construct($properties);
    }
}
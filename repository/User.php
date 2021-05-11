<?php

declare(strict_types=1);

class User {
    public string $email;
    public bool $isactive;
    public int $id;
    /*when the user was created */
    public DateTime $dt;
    public string $firstName;
    public string $lastName;
    public bool $isAdmin;

    public function __construct(array $properties = []) {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
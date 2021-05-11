<?php

declare(strict_types=1);

class User {
    public string $email;
    public bool $isactive;
    public int $id;
    /*when the user was created */
    public string $dt;
    public string $firstName;
    public string $lastName;
    public bool $isAdmin;

    public function __construct(array $properties ) {
        $this->email = $properties['email'];
        $this->isactive = (bool)$properties['isactive'];
        $this->id = $properties['id'];
        $this->dt = $properties['dt'];
        $this->firstName = $properties['firstName'];
        $this->lastName = $properties['email'];
        $this->isAdmin = (bool)$properties['isAdmin'];

    }
}
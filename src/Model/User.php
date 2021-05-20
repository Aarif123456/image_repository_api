<?php

declare(strict_types=1);
namespace ImageRepository\Model;

use ImageRepository\Exception\InvalidPropertyException;

class User
{
    public string $email;
    public bool $isactive;
    public int $id;
    /*when the user was created */
    public string $dt;
    public string $firstName;
    public string $lastName;
    public string $password;
    public bool $isAdmin;

    public function __construct(array $properties) {
        $this->email = $properties['email'];
        $this->isactive = (bool)($properties['isactive'] ?? false);
        $this->id = (int)($properties['id'] ?? 0);
        $this->dt = (string)($properties['dt'] ?? date('Y/m/d'));
        $this->firstName = $properties['firstName'];
        $this->lastName = $properties['lastName'];
        $this->isAdmin = (bool)($properties['isAdmin'] ?? false);
        $this->password = $properties['password'] ?? '';
    }

    /**
     * @throws InvalidPropertyException
     */
    public function __get(string $property) {
        /* Certain fields are not supposed to be used if they are empty */
        if (property_exists($this, $property)) {
            if (strcmp($property, 'id') === 0 || strcmp($property, 'email') === 0
                || strcmp($property, 'firstName') === 0 || strcmp($property, 'lastName') === 0
                || strcmp($property, 'password') === 0
            ) {
                if (empty($this->$property)) {
                    throw new InvalidPropertyException();
                }
            }

            return $this->$property;
        }
        throw new InvalidPropertyException("The property '$property' does not exist in the User class");
    }
}
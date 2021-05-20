<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

/* shared data used to generate test cases */
/* Create Cartesian test cases */
function cartesian(array $input): array {
    $result = [[]];
    foreach ($input as $key => $values) {
        $append = [];
        foreach ($result as $product) {
            foreach ($values as $item) {
                $product[$key] = $item;
                $append[] = $product;
            }
        }
        $result = $append;
    }

    return $result;
}

define('VALID_USER_INFO', [
    [
        'email' => 'testUser@testing.com',
        'isactive' => true,
        'id' => 0,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'Test first name',
        'lastName' => '',
        'isAdmin' => false,
    ],
    [
        'email' => 'testUser2@testing.com',
        'isactive' => true,
        'id' => 1,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'Another one',
        'lastName' => '',
        'isAdmin' => false,
    ],
    [
        'email' => 'testUser3@testing.com',
        'isactive' => false,
        'id' => 3,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'I am not active',
        'lastName' => 'and not an admin',
        'isAdmin' => false,
    ],
    [
        'email' => 'testUser4@testing.com',
        'isactive' => false,
        'id' => 4,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'I am not active',
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'testUser5@testing.com',
        'isactive' => true,
        'id' => 5,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'I am active',
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'testUser@hotmail.com',
        'id' => 7,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'no isactive field',
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'testUser@hotmail.com',
        'isactive' => true,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'no id',
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'testUser@hotmail.com',
        'isactive' => true,
        'id' => 5,
        'firstName' => 'no date time stamp',
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'testUser@hotmail.com',
        'isactive' => true,
        'id' => 5,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'no isAdmin field',
        'lastName' => '',
    ]
]);
define('INVALID_USER_INFO', [
    [
        'isactive' => true,
        'id' => 6,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'no email',
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'noFirstName@gmail.com',
        'isactive' => true,
        'id' => 5,
        'dt' => (string)date('Y/m/d'),
        'lastName' => '',
        'isAdmin' => true,
    ],
    [
        'email' => 'testUser@hotmail.com',
        'isactive' => true,
        'id' => 5,
        'dt' => (string)date('Y/m/d'),
        'firstName' => 'Don\'t have last name',
        'isAdmin' => true,
    ]
]);
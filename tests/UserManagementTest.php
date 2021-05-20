<?php

declare(strict_types=1);
namespace App\Tests;

use App\Model\{InvalidPropertyException, User};
use PHPUnit\Framework\TestCase;

/* https://devqa.io/user-registration-test-cases-scenarios/
* Sanity test to make sure we didn't accidentally break our login and registration page
* I am using a library to handle the registration which is why I am not going to test 
* these functions heavily. 
* TODO: test login, register, logout, -> forget password
*/
/* This class tests the encryption endpoint
 * the point of this class is to ensure that we can securely generate encrypted files that can 
 * only be read by the intended group. We will also be testing different components that 
 * make this possible.
 */
final class UserManagementTest extends TestCase
{

    public function getTestUsers(): array {
        $validUsers = cartesian([
            'userInfo' => VALID_USER_INFO,
            'isValid' => [true]
        ]);
        $invalidUsers = cartesian([
            'userInfo' => INVALID_USER_INFO,
            'isValid' => [false]
        ]);

        return array_merge($validUsers, $invalidUsers);
    }

    /**
     * @testdox Make sure we can create new users that are valid
     * @dataProvider getTestUsers
     * @covers ::User
     */
    public function testUserCreation(array $userInfo, bool $isValid): User {
        if (!$isValid) {
            $this->expectException(InvalidPropertyException::class);
            $this->expectError();
        }
        $user = new User($userInfo);
        foreach ($userInfo as $property) {
            if (!empty($userInfo[$property])) {
                if (!empty($property)) {
                    $this->assertEquals($user->$property, $userInfo[$property]);
                }
            }
        }
        /* Both our boolean should treated as negative by default */
        $this->assertEquals($user->isactive, $userInfo['isactive'] ?? false);
        $this->assertEquals($user->isAdmin, $userInfo['isAdmin'] ?? false);

        return $user;
    }

    /* TODO: figure out how to test with database*/
    // /** 
    //  * @testdox Make sure we can generate an account with a valid user
    //  * @depends testSystemPropertiesGeneration
    //  * @depends testSystemKeyGeneration
    //  */
    // public function testAccountCreation(string $properties, array $setupReturn) {
    // }
    // /**
    //  * @testdox Make sure valid users can login 
    //  * @depends testSystemPropertiesGeneration
    //  * @depends testSystemKeyGeneration
    //  */
    // public function testLogin(string $properties, array $setupReturn) {
    // }
}

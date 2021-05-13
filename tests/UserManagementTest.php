<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
/* https://devqa.io/user-registration-test-cases-scenarios/
* Sanity test to make sure we didn't accidentally break our login and registration page
* I am using a library to handle the registration which is why I am not going to test 
* these functions heavily. 
* TODO: test login, register, logout, -> forget password
*/

//require_once __DIR__ . '/../api/userManagement/addUser.php';
//require_once __DIR__ . '/../api/userManagement/loginUser.php';
require_once __DIR__ . '/../repository/User.php';
require_once __DIR__ . '/../repository/error.php';

/* This class tests the encryption endpoint
 * the point of this class is to ensure that we can securely generate encrypted files that can 
 * only be read by the intended group. We will also be testing different components that 
 * make this possible.
 */
final class UserManagementTest extends TestCase {

    function getTestUsers(): array{
        return [
            ['Testing active user who is not an admin' => [
                        'email' =>'testUser@testing.com',
                        'isactive' => true,
                        'id' => 0,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'Test first name',
                        'lastName' => '',
                        'isAdmin' => false,
                    ], true],
            ['Testing active user who is not an admin another active user with identical properties to make sure they can be differentiated by certain functions' => [
                        'email' =>'testUser2@testing.com',
                        'isactive' => true,
                        'id' => 1,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'Another one',
                        'lastName' => '',
                        'isAdmin' => false,
                    ], true],
            ['Testing inactive non-admin users' =>[
                        'email' =>'testUser3@testing.com',
                        'isactive' => false,
                        'id' => 3,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'I am not active',
                        'lastName' => 'and not an admin',
                        'isAdmin' => false,
                    ], true],
            ['Testing inactive admin users' =>[
                        'email' =>'testUser4@testing.com',
                        'isactive' => false,
                        'id' => 4,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'I am not active',
                        'lastName' => '',
                        'isAdmin' => true,
                    ], true],
            ['Testing active admin users' =>[
                        'email' =>'testUser5@testing.com',
                        'isactive' => true,
                        'id' => 5,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'I am active',
                        'lastName' => '',
                        'isAdmin' => true,
                    ], true],
            ['Trying create a user without an email - this should fail ' => [
                        'isactive' => true,
                        'id' => 6,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'no email',
                        'lastName' => '',
                        'isAdmin' => true,
                    ], false],
            ['Trying without is an inactive field. This should be defaulted as false' =>[
                        'email' =>'testUser@hotmail.com',
                        'id' => 7,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'no isactive field',
                        'lastName' => '',
                        'isAdmin' => true,
                    ], true],
             ['Trying create a user without an id - this should fail ' =>[
                        'email' =>'testUser@hotmail.com',
                        'isactive' => true,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'no id',
                        'lastName' => '',
                        'isAdmin' => true,
                    ], true],
             ['Trying create a user without an date timestamp. This should default to the current time ' =>[
                        'email' =>'testUser@hotmail.com',
                        'isactive' => true,
                        'id' => 5,
                        'firstName' => 'no date time stamp',
                        'lastName' => '',
                        'isAdmin' => true,
                    ], true],
            ['Trying create a user without an first name - this should fail ' => [
                        'email' =>'noFirstName@gmail.com',
                        'isactive' => true,
                        'id' => 5,
                        'dt' =>  (string)date('Y/m/d'),
                        'lastName' => '',
                        'isAdmin' => true,
                    ], false],
            ['Trying create a user without an last name - this should fail ' => [
                        'email' =>'testUser@hotmail.com',
                        'isactive' => true,
                        'id' => 5,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'Don\'t have last name',
                        'isAdmin' => true,
                    ], false],
            ['Trying without is an isAdmin field. This should be defaulted as false' => [
                        'email' =>'testUser@hotmail.com',
                        'isactive' => true,
                        'id' => 5,
                        'dt' =>  (string)date('Y/m/d'),
                        'firstName' => 'no isAdmin field',
                        'lastName' => '',
                    ], true],

        ];
    }
    /**
    * @testdox Make sure we can create new users that are valid
    * @dataProvider getTestUsers
    */
    public function testUserCreation(array $userInfo, bool $isValid): User {
        if(!$isValid){
            $this->expectException(InvalidPropertyException::class);
            $this->expectError();
        }
        $user = new User($userInfo);
        foreach ($userInfo as $property){
            if(!empty($userInfo[$property])){
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

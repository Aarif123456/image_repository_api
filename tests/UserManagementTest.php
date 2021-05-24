<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\api\User\LogoutWorker;
use ImageRepository\api\UserManagement\{LoginWorker, RegisterWorker};
use ImageRepository\Exception\{DebugPDOException,
    EncryptionFailureException,
    InvalidPropertyException,
    MissingParameterException,
    PDOWriteException,
    UnauthorizedAdminException};
use ImageRepository\Model\{Database, User};
use ImageRepository\Utils\Auth;
use PHPUnit\Framework\TestCase;

/* https://devqa.io/user-registration-test-cases-scenarios/
* Sanity test to make sure we didn't accidentally break our login and registration page
* I am using a library to handle the registration which is why I am not going to test 
* these functions heavily. 
* TODO: forget password
*/
/* This class tests the user management system
 * the point of this class is to ensure the users can register, login and 
 * have access to the correct actions
 */
final class UserManagementTest extends TestCase
{
    private string $testPassword = '@kSaBZvMg\'h9_b3L;s&>ud+;0=A=WA=Z2Ld;}C+3EsmdpgFN&6c@IDD7`x*tld:Y\IdGh(=f[N4{?R<uH0^2[';
    private Database $db;
    private Auth $auth;

    public function __construct($name = null, array $data = [], $dataName = '') {
        $this->db = new Database();
        $this->auth = new Auth($this->db->conn);
        parent::__construct($name, $data, $dataName);
    }

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

    /**
     * @testdox Make sure we can generate an account with a valid user
     * @dataProvider getTestUsers
     * @depends      testUserCreation
     * @throws DebugPDOException
     * @throws PDOWriteException
     * @throws MissingParameterException
     * @throws EncryptionFailureException
     * @runInSeparateProcess
     * @covers ::RegisterWorker
     */
    public function testAccountCreation(array $userInfo) {
        /* Set request variables */
        $_POST['firstName'] = $userInfo['firstName'] ?? '' ?: 'first name';
        $_POST['lastName'] = $userInfo['lastName'] ?? '' ?: 'last name';
        $_POST['email'] = $userInfo['email'] ?? '';
        $_POST['password'] = $this->testPassword;
        $_POST['admin'] = $userInfo['isAdmin'] ?? false;
        if (empty($_POST['email']) || empty($_POST['password'] || empty($_POST['firstName']))) {
            $this->expectException(MissingParameterException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }
        RegisterWorker::run($this->db, $this->auth, false);
        /* Empty out post request  */
        $_POST = [];
    }

    /**
     * @testdox Make sure valid users can login
     * @dataProvider getTestUsers
     * @depends      testAccountCreation
     * @throws UnauthorizedAdminException
     * @throws MissingParameterException
     * @runInSeparateProcess
     * @covers ::LoginWorker
     */
    public function testLogin(array $userInfo) {
        $_POST['email'] = $userInfo['email'] ?? '';
        $_POST['password'] = $this->testPassword;
        /* We are going to always get admin access */
        $_POST['admin'] = true;
        if (empty($_POST['email']) || empty($_POST['password'])) {
            $this->expectException(MissingParameterException::class);
        } /* We shouldn't have admin access automatically... */
        elseif (empty($userInfo['isAdmin'] ?? false)) {
            $this->expectException(UnauthorizedAdminException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }
        LoginWorker::run($this->db, $this->auth, false);
        $_POST = [];
    }

    /**
     * @testdox Make sure valid users can login
     * @depends testLogin
     * @covers ::LogoutWorker
     * @runInSeparateProcess
     * @doesNotPerformAssertions
     */
    public function testLogout() {
        LogoutWorker::run($this->db, $this->auth, false);
    }

    /**
     * @testdox Clean up and delete users. Note if you do this yourself, you will have to remove the
     *  following constraints from the database to allow users to get deleted.
     * 1. files_ibfk_1
     * 2. userKeys_ibfk_1
     *
     * @dataProvider getTestUsers
     * @depends      testLogout
     * @covers       Auth::deleteUserForced
     * @runInSeparateProcess
     */
    public function testDeleteUser(array $userInfo) {
        $sql = 'SELECT id FROM users WHERE email=:email';
        $params = [
            ':email' => $userInfo['email'] ?? ''
        ];
        $idArr = $this->db->read($sql, $params)[0] ?? ['id' => 0];
        $this->assertFalse($this->auth->deleteUserForced($idArr['id']));
    }
}

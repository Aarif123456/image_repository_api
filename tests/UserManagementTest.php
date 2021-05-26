<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\Controller\{LoginWorker, LogoutWorker, RegisterWorker};
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
    private LoginWorker $loginWorker;
    private LogoutWorker $logoutWorker;
    private RegisterWorker $registerWorker;

    public function __construct($name = null, array $data = [], $dataName = '') {
        $this->db = new Database();
        $this->auth = new Auth($this->db->conn);
        $this->loginWorker = new LoginWorker($this->db, false);
        $this->logoutWorker = new LogoutWorker($this->db, false);
        $this->registerWorker = new RegisterWorker($this->db, false);
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
        }
        $this->registerWorker->run();
        /* Empty out post request */
        $_POST = [];
        $output = (array)json_decode($this->getActualOutput());
        $this->assertFalse($output['error'] ?? true);
        $this->assertNotEmpty($output['message']);
        $this->assertNotEmpty($output['id']);
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
        }
        $this->loginWorker->run();
        $output = (array)json_decode($this->getActualOutput());
        $this->assertFalse($output['error'] ?? true);
        $this->assertNotEmpty($output['message']);
        $_POST = [];
    }

    /**
     * @testdox Make sure valid users can login
     * @dataProvider getTestUsers
     * @depends      testLogin
     * @covers ::LogoutWorker
     * @runInSeparateProcess
     */
    public function testLogout(array $userInfo) {
        $loginInfo = (object)[
            'email' => $userInfo['email'] ?? '',
            'password' => $this->testPassword,
            'remember' => false,
            'admin' => $userInfo['isAdmin'] ?? false
        ];
        $output = $this->auth->login($loginInfo);
        $this->logoutWorker->run();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => $output['error']]),
            $this->getActualOutput()
        );
    }

    /**
     * @testdox Clean up and delete users.
     *
     * @dataProvider getTestUsers
     * @depends      testLogout
     * @covers       Auth::deleteUserForced
     * @throws DebugPDOException
     * @throws PDOWriteException
     * @runInSeparateProcess
     */
    public function testDeleteUser(array $userInfo) {
        $sql = 'SELECT id FROM users WHERE email=:email';
        $params = [
            ':email' => $userInfo['email'] ?? ''
        ];
        $id = ($this->db->read($sql, $params)[0] ?? ['id' => 0])['id'];
        $deleteSql = 'DELETE FROM userKeys WHERE memberID=:id';
        $this->db->write($deleteSql, [':id' => $id], true);
        $this->assertFalse($this->auth->deleteUserForced($id));
    }
}

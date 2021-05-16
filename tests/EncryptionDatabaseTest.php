<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../repository/User.php';
require_once __DIR__ . '/../repository/File.php';
require_once __DIR__ . '/../repository/uploadFileRepo.php';
require_once __DIR__ . '/../repository/viewImageRepo.php';
require_once __DIR__ . '/../repository/databaseConstants.php';
require_once __DIR__ . '/../repository/encryption/encryptFile.php';
require_once __DIR__ . '/../repository/encryption/decryptFile.php';
require_once __DIR__ . '/../repository/encryption/userAttributes.php';
require_once __DIR__ . '/../repository/encryption/encryptionExceptionConstants.php';
require_once __DIR__ . '/helper.php';

/* This class make sure that the encryption system works using the information that we 
 * have saved locally in constants or stored in our database 
 */

final class EncryptionDatabaseTest extends TestCase {
    /* Hold the private key of the user */
    public static array $userKeys = [];
    /* Tracks what user we are testing */
    public static int $lastUserId = 0;
    private static string $publicKey = '', $masterKey = '';
    private string $inputFile = __DIR__ . '/test.png';

    private function encryptedFileLocationCreator(int $accessId, int $userId): string {
        return "$this->inputFile.$userId.$accessId.ENCRYPTED";
    }

    /**************************************************************************/
    /* Data providers: data used to test */
    /**
     * @testdox Create users for testing
     */
    public function userProvider(): array {
        return [new User([
            'email' => 'testUser@testing.com',
            'isactive' => true,
            'id' => 0,
            'dt' => (string)date('Y/m/d'),
            'firstName' => 'Test first name',
            'lastName' => '',
            'isAdmin' => false,
        ]),
            new User([
                'email' => 'testUser2@testing.com',
                'isactive' => true,
                'id' => 1,
                'dt' => (string)date('Y/m/d'),
                'firstName' => 'Another one',
                'lastName' => '',
                'isAdmin' => false,
            ]),
            new User([
                'email' => 'testUser3@testing.com',
                'isactive' => false,
                'id' => 3,
                'dt' => (string)date('Y/m/d'),
                'firstName' => 'I am not active',
                'lastName' => 'and not an admin',
                'isAdmin' => false,
            ]),
            new User([
                'email' => 'testUser4@testing.com',
                'isactive' => false,
                'id' => 4,
                'dt' => (string)date('Y/m/d'),
                'firstName' => 'I am not active',
                'lastName' => 'and a admin',
                'isAdmin' => true,
            ]),
            new User([
                'email' => 'testUser5@testing.com',
                'isactive' => true,
                'id' => 5,
                'dt' => (string)date('Y/m/d'),
                'firstName' => 'I am active',
                'lastName' => 'and a admin',
                'isAdmin' => true,
            ]),
        ];
    }

    /**
     * @testdox Uniqueness of id is guaranteed by database and
     * email validated during registration.
     */
    public function userAccessProvider(): array {
        $validAccess = [PRIVATE_ACCESS, PUBLIC_ACCESS];
        $users = $this->userProvider();

        return cartesian(['user' => $users,
            'access' => $validAccess
        ]);
    }

    /**
     * @testdox Make invalid access id are rejected .
     */
    public function notWorkingAccessProvider(): array {
        $invalidAccess = [0, 3, 100, -1];
        $users = $this->userProvider();
        $output = [];
        foreach ($invalidAccess as $accessId) {
            foreach ($users as $user) {
                array_push($output, [$user, $accessId]);
            }
        }

        return $output;
    }

    /**************************************************************************/
    /* Mock objects */
    /**
     * @testdox One function to make it easy to change as we change the SQL
     */
    public static function mockedSQL(string $query) {
        switch ($query) {
            /* Getting system keys */
            case 'SELECT keysName, keyData FROM systemKeys WHERE keysName=:masterKey OR keysName=:publicKey':
                $result = [[
                    'keysName' => 'publicKey',
                    'keyData' => self::$publicKey
                ],
                    [
                        'keysName' => 'masterKey',
                        'keyData' => self::$masterKey
                    ]
                ];
                break;
            case 'SELECT privateKey FROM userKeys WHERE memberID=:id':
                $result = [['privateKey' => self::$userKeys[self::$lastUserId]]];
                break;
            default:
                fwrite(STDERR, "Warning unrecognized query: \"$query\"");
                fwrite(STDERR, print_r($query, true));
                $result = [];
                break;
        }
        $stmt = (new EncryptionDatabaseTest)->createStub(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('closeCursor')->willReturn(true);
        $stmt->method('bindValue')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($result);

        return $stmt;
    }

    private function getMockFile($fileLocation, $fileEncryptedLocation) {
        $file = $this->createMock(File::class);
        $file->method('getEncryptedFilePath')->willReturn($fileEncryptedLocation);
        $file->path = $fileLocation;
        $file->location = $fileLocation;

        return $file;
    }

    /**
     * @testdox One function to make it easy to change as we change the SQL
     */
    private function getMockedPDO() {
        $conn = $this->createMock(PDO::class);
        $conn->method('prepare')->will($this->returnCallback('EncryptionDatabaseTest::mockedSQL'));

        return $conn;
    }

    /**************************************************************************/
    /* Test cases */
    /**
     * @testdox Make sure we can generate the keys needed for the encryption decryption process
     * @covers ::setup
     */
    public function testSystemKeyGeneration(): array {
        /* Make sure program uses system properties */
        $setupReturn = setup();
        $this->assertNotEmpty($setupReturn);
        $this->assertArrayHasKey('publicKey', $setupReturn);
        $this->assertArrayHasKey('masterKey', $setupReturn);
        /* Push keys to be used in future queries */
        self::$publicKey = $setupReturn['publicKey'];
        self::$masterKey = $setupReturn['masterKey'];

        return $setupReturn;
    }

    /**
     * @testdox Create attributes for different users
     * @dataProvider userAccessProvider
     * @depends      testSystemKeyGeneration
     * @covers ::createUserAttributes
     */
    public function testUserAttributes(User $user): string {
        $userAttributes = createUserAttributes($user);
        $this->assertStringNotContainsString('$', $userAttributes);
        $this->assertStringContainsString((string)($user->id), $userAttributes);

        return $userAttributes;
    }

    /**
     * @testdox Make sure appropriate exception is thrown for wrong incorrect arguments!
     * @dataProvider notWorkingAccessProvider
     * @covers ::getPolicy
     */
    public function testInvalidPolicy(User $user, int $accessId): string {
        $this->expectException(InvalidAccessException::class);

        return getPolicy($accessId, $user);
    }

    /**
     * @testdox Make sure the correct policy are generated for different users
     * @dataProvider userAccessProvider
     * @covers ::getPolicy
     * @throws InvalidAccessException
     */
    public function testValidPolicy(User $user, int $accessId): string {
        $policy = getPolicy($accessId, $user);
        /* We don't want accidentally read in a variable name instead of evaluating */
        $this->assertStringNotContainsString('$', $policy);
        $this->assertNotEmpty($policy);

        return $policy;
    }

    /**
     * @testdox Make sure we can generate keys if we pass in attributes with the correct format
     * @dataProvider userAccessProvider
     * @depends      testSystemKeyGeneration
     * @covers ::keygen
     * @throws EncryptionFailureException
     */
    public function testValidUserKeyGeneration(User $user, int $_accessId): void {
        $userAttributes = $this->testUserAttributes($user);
        $privateKey = keygen(self::$publicKey, self::$masterKey, $userAttributes);
        self::$userKeys[$user->id] = $privateKey;
        $this->assertNotEmpty($privateKey);
        self::$lastUserId = $user->id;
    }

    /**
     * @testdox Make sure we cannot generate keys if we pass in attributes with the
     * incorrect format and we get back and appropriate response
     * @covers ::keygen
     */
    public function testInvalidUserKeyGeneration(): void {
        $this->expectException(EncryptionFailureException::class);
        keygen(self::$publicKey, self::$masterKey, 'This is an invalid attribute...');
    }

    /**
     * @dataProvider userAccessProvider
     * @depends      testValidUserKeyGeneration
     * @depends      testValidPolicy
     * @covers ::encryptFile
     * @covers ::getFileEncrypted
     * @throws EncryptedFileNotCreatedException
     */
    public function testFileEncrypted(User $user, int $accessId): File {
        $policy = $this->testValidPolicy($user, $accessId);
        $encryptedFile = $this->encryptedFileLocationCreator($accessId, $user->id);
        $file = $this->getMockFile($this->inputFile, $encryptedFile);
        $conn = $this->getMockedPDO();
        encryptFile($file, $policy, $conn);
        $encryptedFileBytes = file_get_contents($encryptedFile);
        $this->assertNotEmpty($encryptedFileBytes);
        $this->assertNotEquals(file_get_contents($this->inputFile), $encryptedFileBytes);

        return $file;
    }

    /**
     * @testdox Make sure files can only be decrypted by the intended user
     * @dataProvider userAccessProvider
     * @depends      testFileEncrypted
     * @depends      testValidUserKeyGeneration
     * @throws NoSuchFileException
     * @covers ::getFileDecrypted
     */
    public function testFileValidDecrypted(User $user, int $accessId): void {
        $encryptedFile = $this->encryptedFileLocationCreator($accessId, $user->id);
        $file = $this->getMockFile($this->inputFile, $encryptedFile);
        /* Go through all users and check if we can decrypt if we have access and vice-versa*/
        foreach (self::$userKeys as $curUserId => $privateKey) {
            /* If file is private only people with access can expect */
            if (isFilePrivate($accessId) && $user->id !== $curUserId) {
                $this->expectException(EncryptionFailureException::class);
            }
            /* Check if we can decrypt */
            $decryptedFileBytes = getFileDecrypted($file, $privateKey, self::$publicKey);
            $this->assertTrue(strcmp(file_get_contents($this->inputFile), $decryptedFileBytes) === 0);
        }

    }

}
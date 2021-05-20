<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\Exception\{EncryptedFileNotCreatedException,
    EncryptionFailureException,
    InvalidAccessException,
    NoSuchFileException};
use ImageRepository\Model\{File, FileManagement\PolicySelector, User};
use ImageRepository\Model\Encryption\{Encrypter, FileDecrypter, FileEncrypter, UserAttributeGenerator};
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/dataProviders.php';

/* This class make sure that the encryption system works using the information that we
 * have saved locally in constants or stored in our database 
 */

final class EncryptionDatabaseTest extends TestCase
{
    public static SQLMockProvider $mockedSQL;
    private string $inputFile = __DIR__ . '/test.png';

    public function __construct($name = null, array $data = [], $dataName = '') {
        self::$mockedSQL = new SQLMockProvider();
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @testdox Uniqueness of id is guaranteed by database and
     * email validated during registration.
     */
    public function userAccessProvider(): array {
        $validAccess = [PolicySelector::PRIVATE_ACCESS, PolicySelector::PUBLIC_ACCESS];
        $users = $this->userProvider();

        return cartesian([
            'user' => $users,
            'access' => $validAccess
        ]);
    }

    /**************************************************************************/
    /* Data providers: data used to test */
    /**
     * @testdox Create users for testing
     */
    public function userProvider(): array {
        return array_map(function (array $userInfo) {
            return new User($userInfo);
        }, VALID_USER_INFO);
    }

    /**
     * @testdox Make invalid access id are rejected .
     */
    public function notWorkingAccessProvider(): array {
        $invalidAccess = [0, 3, 100, -1];
        $users = $this->userProvider();

        return cartesian([
            'user' => $users,
            'access' => $invalidAccess
        ]);
    }

    /**
     * @testdox Make sure we can generate the keys needed for the encryption decryption process
     * @covers Encrypter::setup
     * @throws EncryptionFailureException
     */
    public function testSystemKeyGeneration(): array {
        /* Make sure program uses system properties */
        $setupReturn = Encrypter::setup();
        $this->assertNotEmpty($setupReturn);
        $this->assertArrayHasKey('publicKey', $setupReturn);
        $this->assertArrayHasKey('masterKey', $setupReturn);
        /* Push keys to be used in future queries */
        self::$mockedSQL->publicKey = $setupReturn['publicKey'];
        self::$mockedSQL->masterKey = $setupReturn['masterKey'];

        return $setupReturn;
    }


    /**************************************************************************/
    /* Test cases */
    /**
     * @testdox Make sure appropriate exception is thrown for wrong incorrect arguments!
     * @dataProvider notWorkingAccessProvider
     * @covers ::PolicySelector::getPolicy
     */
    public function testInvalidPolicy(User $user, int $accessId): string {
        $this->expectException(InvalidAccessException::class);

        return PolicySelector::getPolicy($accessId, $user);
    }

    /**
     * @testdox Make sure we can generate keys if we pass in attributes with the correct format
     * @dataProvider userAccessProvider
     * @depends      testSystemKeyGeneration
     * @covers       Encrypter::keyGeneration
     * @throws EncryptionFailureException
     */
    public function testValidUserKeyGeneration(User $user): void {
        $userAttributes = $this->testUserAttributes($user);
        $privateKey = Encrypter::keyGeneration(self::$mockedSQL->publicKey, self::$mockedSQL->masterKey,
            $userAttributes);
        self::$mockedSQL->userKeys[$user->id] = $privateKey;
        $this->assertNotEmpty($privateKey);
        self::$mockedSQL->lastUserId = $user->id;
    }

    /**
     * @testdox Create attributes for different users
     * @dataProvider userAccessProvider
     * @depends      testSystemKeyGeneration
     * @covers       UserAttributeGenerator::generate
     */
    public function testUserAttributes(User $user): string {
        $userAttributes = UserAttributeGenerator::generate($user);
        $this->assertStringNotContainsString('$', $userAttributes);
        $this->assertStringContainsString((string)($user->id), $userAttributes);

        return $userAttributes;
    }

    /**
     * @testdox Make sure we cannot generate keys if we pass in attributes with the
     * incorrect format and we get back and appropriate response
     * @covers  Encrypter::keyGeneration
     */
    public function testInvalidUserKeyGeneration(): void {
        $this->expectException(EncryptionFailureException::class);
        Encrypter::keyGeneration(self::$mockedSQL->publicKey, self::$mockedSQL->masterKey,
            'This is an invalid attribute...');
    }

    /**
     * @dataProvider userAccessProvider
     * @depends      testValidUserKeyGeneration
     * @depends      testValidPolicy
     * @covers       FileEncrypter::run
     * @throws EncryptedFileNotCreatedException
     * @throws InvalidAccessException
     * @throws EncryptionFailureException
     */
    public function testFileEncrypted(User $user, int $accessId): File {
        $policy = $this->testValidPolicy($user, $accessId);
        $encryptedFile = $this->encryptedFileLocationCreator($accessId, $user->id);
        $file = FileMockProvider::getMockFile($this->inputFile, $encryptedFile);
        FileEncrypter::run($file, $policy, self::$mockedSQL->publicKey);
        $encryptedFileBytes = file_get_contents($encryptedFile);
        $this->assertNotEmpty($encryptedFileBytes);
        $this->assertNotEquals(file_get_contents($this->inputFile), $encryptedFileBytes);

        return $file;
    }

    /**
     * @testdox Make sure the correct policy are generated for different users
     * @dataProvider userAccessProvider
     * @covers ::PolicySelector::getPolicy
     * @throws InvalidAccessException
     */
    public function testValidPolicy(User $user, int $accessId): string {
        $policy = PolicySelector::getPolicy($accessId, $user);
        /* We don't want accidentally read in a variable name instead of evaluating */
        $this->assertStringNotContainsString('$', $policy);
        $this->assertNotEmpty($policy);

        return $policy;
    }

    private function encryptedFileLocationCreator(int $accessId, int $userId): string {
        return "$this->inputFile.$userId.$accessId.ENCRYPTED";
    }

    /**
     * @testdox Make sure files can only be decrypted by the intended user
     * @dataProvider userAccessProvider
     * @depends      testFileEncrypted
     * @depends      testValidUserKeyGeneration
     * @throws NoSuchFileException
     * @covers ::FileDecrypter::run
     */
    public function testFileValidDecrypted(User $user, int $accessId): void {
        $encryptedFile = $this->encryptedFileLocationCreator($accessId, $user->id);
        $file = FileMockProvider::getMockFile($this->inputFile, $encryptedFile);
        /* Go through all users and check if we can decrypt if we have access and vice-versa*/
        foreach (self::$mockedSQL->userKeys as $curUserId => $privateKey) {
            /* If file is private only people with access can expect */
            if (PolicySelector::isFilePrivate($accessId) && $user->id !== $curUserId) {
                $this->expectException(EncryptionFailureException::class);
            }
            /* Check if we can decrypt */
            $decryptedFileBytes = FileDecrypter::run($file, $privateKey, self::$mockedSQL->publicKey);
            $this->assertTrue(strcmp(file_get_contents($this->inputFile), $decryptedFileBytes) === 0);
        }
    }

}
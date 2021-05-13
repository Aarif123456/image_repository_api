<?php declare(strict_types=1);

require_once __DIR__ . '/../repository/encryption/callApi.php';
require_once __DIR__ . '/../repository/encryption/encryptionExceptionConstants.php';

use PHPUnit\Framework\TestCase;

/* This class tests the encryption endpoint
 * the point of this class is to ensure that we can securely generate encrypted files that can 
 * only be read by the intended group. We will also be testing different components that 
 * make this possible.
 */
final class EncryptionTest extends TestCase {
    private string $inputFile = __DIR__ . '/test.png';

    /**
    * @testdox Make sure we can generate new system properties if we ever want to in the future 
    */
    public function testSystemPropertiesGeneration(): string {
        $type = 'a';
        $properties = generateProperties($type);

        $this->assertNotEmpty($properties);
        $this->assertArrayHasKey('properties', $properties);
        /* Make sure we actually get back the properties */
        $properties = $properties['properties'];
        $this->assertNotEmpty($properties);

        $propertiesVal = json_decode($properties);
        $this->assertNotEmpty($propertiesVal);

        $propertiesVal->type = $type;

        return $properties;
    }

    /** 
     * @testdox Make sure we can generate the keys needed for the encryption decryption process
     * @depends testSystemPropertiesGeneration
     */
    public function testSystemKeyGeneration(string $properties): array {
        /* Make sure program uses system properties */
        $setupReturn = setup($properties);
        $this->assertNotEmpty($setupReturn);
        $this->assertArrayHasKey('publicKey', $setupReturn);
        $this->assertArrayHasKey('masterKey', $setupReturn);

        return $setupReturn;
    }

    /** 
     * @testdox Make sure we can generate keys if we pass in attributes with the correct format
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     */
    public function testValidUserKeyGeneration(string $properties, array $setupReturn) {
        $masterKey = $setupReturn['masterKey'] ?? '';
        $publicKey = $setupReturn['publicKey'] ?? '';
        $privateKey = keygen($publicKey, $masterKey, 'userId:1 public:true', $properties);
        $this->assertNotEmpty($privateKey);

        return $privateKey;
    }

    /** 
     * @testdox Make another valid keys with attributes we expect to fail on decryption
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     */
    public function testAlternateUserKeyGeneration(string $properties, array $setupReturn) {
        $masterKey = $setupReturn['masterKey'] ?? '';
        $publicKey = $setupReturn['publicKey'] ?? '';
        $privateKey = keygen($publicKey, $masterKey, 'userId:2 public:true', $properties);
        $this->assertNotEmpty($privateKey);

        return $privateKey;
    }


    /** 
     * @testdox Make sure we cannot generate keys if we pass in attributes with the
     * incorrect format and we get back and appropriate response
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     */
    public function testInvalidUserKeyGeneration(string $properties, array $setupReturn): void {
        $this->expectException(EncryptionFailureException::class);
        $masterKey = $setupReturn['masterKey'] ?? '';
        $publicKey = $setupReturn['publicKey'] ?? '';
        keygen($publicKey, $masterKey, 'This is an invalid attribute...', $properties);

    }

    /**
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     */
    public function testFileEncrypted(string $properties, array $setupReturn): string {
        $publicKey = $setupReturn['publicKey'] ?? '';
        $policy = 'userId:1 public:true 2of2';
        $encryptedFileBytes = encrypt($publicKey, $policy, $this->inputFile, $properties);

        $encryptedFile = $this->inputFile . '.ENCRYPTED';
        $filesize = file_put_contents($encryptedFile, $encryptedFileBytes);
        $this->assertNotEmpty($filesize);

        $this->assertNotEquals(file_get_contents($this->inputFile), $encryptedFileBytes);

        return $encryptedFile;
    }

    /** 
     * @testdox Make sure we have get a proper response for passing back invalid policy
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     */
    public function testInvalidPolicyFileEncrypted(string $properties, array $setupReturn): void {
        $this->expectException(EncryptionFailureException::class);
        $publicKey = $setupReturn['publicKey'] ?? '';
        $policy = 'This is an invalid policy....';
        $encryptedFileBytes = encrypt($publicKey, $policy, $this->inputFile, $properties);

    }

    /**
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @depends testValidUserKeyGeneration
     * @depends testFileEncrypted
     */
    public function testFileValidDecrypted(string $properties, array $setupReturn, string $privateKey, string $encryptedFile): string {
        $publicKey = $setupReturn['publicKey'] ?? '';

        $decryptedFile = decrypt($publicKey, $privateKey, $encryptedFile, $properties);

        $this->assertTrue(strcmp(file_get_contents($this->inputFile), $decryptedFile) === 0);

        return $decryptedFile;
    }

    /** 
     * @testdox If we aren't allowed to access the file then we should be able to access it...
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @depends testAlternateUserKeyGeneration
     * @depends testFileEncrypted
     */
    public function testFileInvalidDecrypted(string $properties, array $setupReturn, string $privateKey, string $encryptedFile): void {
        $this->expectException(EncryptionFailureException::class);
        $publicKey = $setupReturn['publicKey'] ?? '';
        $decryptedFile = decrypt($publicKey, $privateKey, $encryptedFile, $properties);

    }
}

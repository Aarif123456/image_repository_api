<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\Exception\EncryptionFailureException;
use ImageRepository\Model\Encryption\{Encrypter};
use PHPUnit\Framework\TestCase;

/* This class tests the encryption endpoint
 * the point of this class is to ensure that we can securely generate encrypted files that can 
 * only be read by the intended group. We will also be testing different components that 
 * make this possible.
 */
final class EncryptionTest extends TestCase
{
    private string $inputFile = __DIR__ . '/test.jpg';

    /**
     * @testdox Make sure we can generate new system properties if we ever want to in the future
     * @covers Encrypter::generateProperties
     * @throws EncryptionFailureException
     */
    public function testSystemPropertiesGeneration(): string {
        $type = 'a';
        $properties = Encrypter::generateProperties($type);
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
     * @covers  Encrypter::setup
     * @throws EncryptionFailureException
     */
    public function testSystemKeyGeneration(string $properties): array {
        /* Make sure program uses system properties */
        $setupReturn = Encrypter::setup($properties);
        $this->assertNotEmpty($setupReturn);
        $this->assertArrayHasKey('publicKey', $setupReturn);
        $this->assertArrayHasKey('masterKey', $setupReturn);

        return $setupReturn;
    }

    /**
     * @testdox Make sure we can generate keys if we pass in attributes with the correct format
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @covers  Encrypter::keyGeneration
     * @throws EncryptionFailureException
     */
    public function testValidUserKeyGeneration(string $properties, array $setupReturn): string {
        $masterKey = $setupReturn['masterKey'] ?? '';
        $publicKey = $setupReturn['publicKey'] ?? '';
        $privateKey = Encrypter::keyGeneration($publicKey, $masterKey, 'userId:1 public:true', $properties);
        $this->assertNotEmpty($privateKey);

        return $privateKey;
    }

    /**
     * @testdox Make another valid keys with attributes we expect to fail on decryption
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @covers  Encrypter::keyGeneration
     * @throws EncryptionFailureException
     */
    public function testAlternateUserKeyGeneration(string $properties, array $setupReturn): string {
        $masterKey = $setupReturn['masterKey'] ?? '';
        $publicKey = $setupReturn['publicKey'] ?? '';
        $privateKey = Encrypter::keyGeneration($publicKey, $masterKey, 'userId:2 public:true', $properties);
        $this->assertNotEmpty($privateKey);

        return $privateKey;
    }

    /**
     * @testdox Make sure we cannot generate keys if we pass in attributes with the
     * incorrect format and we get back and appropriate response
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @covers  Encrypter::keyGeneration
     */
    public function testInvalidUserKeyGeneration(string $properties, array $setupReturn): void {
        $this->expectException(EncryptionFailureException::class);
        $masterKey = $setupReturn['masterKey'] ?? '';
        $publicKey = $setupReturn['publicKey'] ?? '';
        Encrypter::keyGeneration($publicKey, $masterKey, 'This is an invalid attribute...', $properties);
    }

    /**
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @covers  Encrypter::encrypt
     * @throws EncryptionFailureException
     */
    public function testFileEncrypted(string $properties, array $setupReturn): string {
        $publicKey = $setupReturn['publicKey'] ?? '';
        $policy = 'userId:1 public:true 2of2';
        $encryptedFileBytes = Encrypter::encrypt($publicKey, $policy, $this->inputFile, $properties);
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
     * @covers  Encrypter::encrypt
     */
    public function testInvalidPolicyFileEncrypted(string $properties, array $setupReturn): void {
        $this->expectException(EncryptionFailureException::class);
        $publicKey = $setupReturn['publicKey'] ?? '';
        $policy = 'This is an invalid policy....';
        Encrypter::encrypt($publicKey, $policy, $this->inputFile, $properties);
    }

    /**
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @depends testValidUserKeyGeneration
     * @depends testFileEncrypted
     * @covers  Encrypter::decrypt
     * @throws EncryptionFailureException
     */
    public function testFileValidDecrypted(
        string $properties,
        array $setupReturn,
        string $privateKey,
        string $encryptedFile
    ): string {
        $publicKey = $setupReturn['publicKey'] ?? '';
        $decryptedFile = Encrypter::decrypt($publicKey, $privateKey, $encryptedFile, $properties);
        $this->assertTrue(strcmp(file_get_contents($this->inputFile), $decryptedFile) === 0);
        unlink($encryptedFile);

        return $decryptedFile;
    }

    /**
     * @testdox If we aren't allowed to access the file then we should be able to access it...
     * @depends testSystemPropertiesGeneration
     * @depends testSystemKeyGeneration
     * @depends testAlternateUserKeyGeneration
     * @depends testFileEncrypted
     * @covers  Encrypter::decrypt
     */
    public function testFileInvalidDecrypted(
        string $properties,
        array $setupReturn,
        string $privateKey,
        string $encryptedFile
    ): void {
        $this->expectException(EncryptionFailureException::class);
        $publicKey = $setupReturn['publicKey'] ?? '';
        Encrypter::decrypt($publicKey, $privateKey, $encryptedFile, $properties);
    }

}

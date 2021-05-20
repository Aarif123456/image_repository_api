<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\Model\{File};
use PDOStatement;
use PHPUnit\Framework\TestCase;

/**************************************************************************/
/* Mock objects */
final class FileMockProvider extends TestCase
{
    public static function getMockFile($fileLocation, $fileEncryptedLocation) {
        $file = (new FileMockProvider)->createMock(File::class);
        $file->method('getEncryptedFilePath')->willReturn($fileEncryptedLocation);
        $file->path = $fileLocation;
        $file->location = $fileLocation;

        return $file;
    }
}

final class SQLMockProvider extends TestCase
{
    /* Tracks what user we are testing */
    private static SQLMockProvider $singleton;
    public int $lastUserId = 0;
    /* Hold the private key of the user */
    public string $publicKey = '', $masterKey = '';
    public array $userKeys = [];

    public function __construct($name = null, array $data = [], $dataName = '') {
        self::$singleton = $this;
        self::$singleton->lastUserId = 0;
        self::$singleton->publicKey = '';
        self::$singleton->masterKey = '';
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @testdox One function to make it easy to change as we change the SQL
     */
    public static function handleSqlQuery(string $query) {
        switch ($query) {
            /* Getting system keys */
            case 'SELECT keysName, keyData FROM systemKeys WHERE keysName=:masterKey OR keysName=:publicKey':
                $result = [
                    [
                        'keysName' => 'publicKey',
                        'keyData' => self::$singleton->publicKey
                    ],
                    [
                        'keysName' => 'masterKey',
                        'keyData' => self::$singleton->masterKey
                    ]
                ];
                break;
            case 'SELECT privateKey FROM userKeys WHERE memberID=:id':
                $result = [['privateKey' => self::$singleton->userKeys[self::$singleton->lastUserId]]];
                break;
            default:
                fwrite(STDERR, "Warning unrecognized query: \"$query\"");
                fwrite(STDERR, print_r($query, true));
                $result = [];
                break;
        }
        $stmt = self::$singleton->createStub(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('closeCursor')->willReturn(true);
        $stmt->method('bindValue')->willReturn(true);
        $stmt->method('fetchAll')->willReturn($result);

        return $stmt;
    }

    /**
     * @testdox One function to make it easy to change as we change the SQL
     */
//    public static function getMockedPDO() {
//        /* TODO: fix up to work with new class using map */
//        $db = self::$singleton->createMock(PDO::class);
//        $db->method('prepare')->will(self::$singleton->returnCallback('SQLMockProvider::handleSqlQuery'));
//
//        return $db;
//    }
}
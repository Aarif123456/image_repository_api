<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

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

final class SQLMockProvider extends TestCase {
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
                $result = [[
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
    public static function getMockedPDO() {
        $conn = self::$singleton->createMock(PDO::class);
        $conn->method('prepare')->will(self::$singleton->returnCallback('SQLMockProvider::handleSqlQuery'));

        return $conn;
    }
}
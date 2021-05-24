<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\api\FileManagement\{DeleteWorker, UploadWorker};
use ImageRepository\Exception\{DebugPDOException,
    DeleteFailedException,
    EncryptedFileNotCreatedException,
    EncryptionFailureException,
    FileAlreadyExistsException,
    FileLimitExceededException,
    FileNotSentException,
    InvalidAccessException,
    InvalidFileFormatException,
    MissingParameterException,
    NoSuchFileException,
    PDOWriteException,
    SqlCommandFailedException,
    UnknownErrorException};
use ImageRepository\Model\Database;
use ImageRepository\Utils\Auth;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/dataProviders.php';

/* The point of this class is to make sure we are able to properly manage the files we own securely
* This means we should be able to do the following
* 1. Upload a file: which means it should be in our account (database 
* and see in folder). A user should not be able to go out of their folder
* 2. View image: we should be able view files when we have access. - match original 
* 3. Manage folder structure: We should be able to put our file in any folder and make new folders as we want.
* 4. Delete files: We should be able to delete files in our account and not be able to 
  delete files that we don't own
* If we don't have any files we should not get an error
* The scope of these test is at the API level
* Make sure errors always set error variable to true
*/

final class FileManagementTest extends TestCase
{
    private static string $imgFile = __DIR__ . '/test.jpg';
    private static string $imgFile2 = __DIR__ . '/test1.jpg';
    private static string $giantFile = __DIR__ . '/giantFile.jpg';
    private static string $fileNames = 'images';
    private object $loginInfo;
    private Database $db;
    private Auth $auth;

    /**************************************************************************/
    /* Data providers: data used to test */
    /**************************************************************************/
    public function __construct($name = null, array $data = [], $dataName = '') {
        $this->db = new Database();
        $this->auth = new Auth($this->db->conn);
        $this->loginInfo = (object)[
            'email' => 'shopifyAccount@shopify.com',
            'password' => 'M/_4nNk^I+uqLk"uP%|MP4#a]Z&m$&v.+2mzTk/(nQJ<Y\>eN77gO^I{y9.MYWPwBDI0T8u>Hj7HL/]&.\'/<It\'f6q6u&/a.7<i',
            'remember' => false
        ];
        resizeImageToSize(self::$imgFile, 1024 * 1024 * 7, self::$giantFile);
        copy(self::$imgFile, self::$imgFile2);
        parent::__construct($name, $data, $dataName);
    }

    public static function tearDownAfterClass(): void {
        unlink(self::$giantFile);
        unlink(self::$imgFile2);
        parent::tearDownAfterClass();
    }

    protected function setUp(): void {
        $this->auth->login($this->loginInfo);
    }

    protected function tearDown(): void {
        $this->auth->logout();
    }

    public function validFileProvider(): array {
        return [
            /* Single file in array test */
            'Single file in array' => [
                [
                    'error' => [UPLOAD_ERR_OK],
                    'name' => ['test.php'],
                    'size' => [filesize(self::$imgFile2)],
                    'tmp_name' => [self::$imgFile2],
                    'type' => ['image/png']
                ]
            ],
            /* Giant file test */
            'Max file size test' => [
                [
                    'error' => [UPLOAD_ERR_OK],
                    'name' => ['maxSizeValid.jpg'],
                    'size' => [filesize(self::$giantFile)],
                    'tmp_name' => [self::$giantFile],
                    'type' => ['image/webp']
                ]
            ],
        ];
    }
    /* Upload test cases:
        - category - single, multiple, guest, out of folder (using .. or /)
            - type - image, non-image(html, php), giant, super small, 
        - Upload giant file https://en.wikipedia.org/wiki/File:Pieter_Bruegel_the_Elder_-_The_Fall_of_the_Rebel_Angels_-_Google_Art_Project.jpg

        https://www.softwaretestingo.com/file-upload-test-case/'
    */
    /**
     * @testdox Make sure we can upload a single file
     * @covers ::UploadWorker
     * @runInSeparateProcess
     * @doesNotPerformAssertions
     * @throws FileAlreadyExistsException
     * @throws EncryptedFileNotCreatedException
     * @throws SqlCommandFailedException
     * @throws DebugPDOException
     * @throws FileNotSentException
     * @throws InvalidFileFormatException
     * @throws InvalidAccessException
     * @throws UnknownErrorException
     * @throws FileLimitExceededException
     * @throws EncryptionFailureException
     * @throws PDOWriteException
     * @throws MissingParameterException
     */
    public function testUploadSingleFile(): array {
        $_REQUEST['fileNames'] = self::$fileNames;
        $backupLocation = self::$imgFile . '.backup';
        copy(self::$imgFile, $backupLocation);
        $fileInfo = [
            'error' => UPLOAD_ERR_OK,
            'name' => 'test.js',  /* Wrong name and type */
            'size' => filesize(self::$imgFile), /* Actual size */
            'tmp_name' => self::$imgFile, /* Actual file */
            'type' => 'application/javascript'
        ];
        $_FILES = [
            self::$fileNames => $fileInfo
        ];
        UploadWorker::run($this->db, $this->auth, false);
        if (copy($backupLocation, self::$imgFile)) unlink($backupLocation);

        return $fileInfo;
    }

    /**
     * @testdox Delete file test for a single file
     * @covers ::DeleteWorker
     * @depends testUploadSingleFile
     * @runInSeparateProcess
     * @doesNotPerformAssertions
     * @throws DebugPDOException
     * @throws DeleteFailedException
     * @throws PDOWriteException
     * @throws NoSuchFileException
     * @throws MissingParameterException
     */
    public function testDeleteSingleFiles(array $fileInfo) {
        $_REQUEST['fileName'] = $fileInfo['name'];
        DeleteWorker::run($this->db, $this->auth, false);
    }

    /**
     * @testdox Make sure we can upload valid files
     * @dataProvider validFileProvider
     * @covers ::UploadWorker
     * @runInSeparateProcess
     * @doesNotPerformAssertions
     * @throws FileAlreadyExistsException
     * @throws EncryptedFileNotCreatedException
     * @throws SqlCommandFailedException
     * @throws DebugPDOException
     * @throws FileNotSentException
     * @throws InvalidFileFormatException
     * @throws InvalidAccessException
     * @throws UnknownErrorException
     * @throws FileLimitExceededException
     * @throws EncryptionFailureException
     * @throws PDOWriteException
     * @throws MissingParameterException
     */
    public function testUploadFile(array $inputFiles) {
        $_REQUEST['fileNames'] = self::$fileNames;
        $backupLocations = [];
        foreach ($inputFiles['tmp_name'] as $key => $value) {
            $backupLocations[$key] = $value . "$key.backup";
            copy($value, $backupLocations[$key]);
        }
        $_FILES = [
            self::$fileNames => $inputFiles
        ];
        UploadWorker::run($this->db, $this->auth, false);
        foreach ($backupLocations as $key => $backupLocation) {
            if (copy($backupLocation, $inputFiles['tmp_name'][$key])) unlink($backupLocation);
        }
    }

    /**
     * @testdox Delete file test - also acts as a cleaner
     * @dataProvider validFileProvider
     * @covers ::DeleteWorker
     * @depends      testUploadFile
     * @runInSeparateProcess
     * @doesNotPerformAssertions
     * @throws DebugPDOException
     * @throws DeleteFailedException
     * @throws PDOWriteException
     * @throws NoSuchFileException
     * @throws MissingParameterException
     */
    public function testDeleteMultipleFiles(array $inputFiles) {
        foreach ($inputFiles['name'] as $value) {
            $_REQUEST['fileName'] = $value;
            DeleteWorker::run($this->db, $this->auth, false);
        }
    }

}
<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use ImageRepository\Controller\{DeleteWorker, FolderImagesWorker, ImageWorker, UploadWorker};
use ImageRepository\Exception\{DebugPDOException,
    DeleteFailedException,
    EncryptionFailureException,
    MissingParameterException,
    NoSuchFileException,
    PDOWriteException};
use ImageRepository\Model\Database;
use ImageRepository\Model\FileLocationInfo;
use ImageRepository\Model\FileManagement\FileReader;
use ImageRepository\Model\User;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\Translator;
use PHPUnit\Framework\TestCase;

use const ImageRepository\Utils\MAX_FILE_SIZE;

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
/* TODO: test upload with non image - php and HTML files */
/* TODO: create class to test access control - like we shouldn't be able to leave our folder
Upload test cases: go out of folder (using .. or /)
View image: view files without access
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
    private Translator $translator;
    private DeleteWorker $deleteWorker;
    private FolderImagesWorker $folderImagesWorker;
    private ImageWorker $imageWorker;
    private UploadWorker $uploadWorker;

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
        $this->deleteWorker = new DeleteWorker($this->db, false);
        $this->folderImagesWorker = new FolderImagesWorker($this->db, false);
        $this->imageWorker = new ImageWorker($this->db, false);
        $this->uploadWorker = new UploadWorker($this->db, false);
        $this->translator = new Translator($this->db->conn);
        parent::__construct($name, $data, $dataName);
    }

    public static function tearDownAfterClass(): void {
        if (file_exists(self::$giantFile)) unlink(self::$giantFile);
        if (file_exists(self::$imgFile2)) unlink(self::$imgFile2);
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

    /**
     * @testdox Test upload to make sure it deals with large files accordingly
     * @covers ::UploadWorker
     * @runInSeparateProcess
     * @throws MissingParameterException
     */
    public function testInvalidSizeUploadFile(): array {
        $_REQUEST['fileNames'] = self::$fileNames;
        $fileInfo = [
            'error' => UPLOAD_ERR_OK,
            'name' => 'test.js',
            'size' => MAX_FILE_SIZE + 1,
            'tmp_name' => self::$imgFile,
            'type' => 'application/javascript'
        ];
        $_FILES = [
            self::$fileNames => $fileInfo
        ];
        $this->uploadWorker->run();
        $this->assertJsonStringEqualsJsonString(
            json_encode([
                $fileInfo['name'] => [
                    'error' => true,
                    'message' => $this->translator->FILE_SIZE_LIMIT_EXCEEDED
                ]
            ]),
            $this->getActualOutput()
        );

        return $fileInfo;
    }

    /**
     * @testdox Make sure we can upload a single file
     * @covers ::UploadWorker
     * @runInSeparateProcess
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
        $this->uploadWorker->run();
        if (copy($backupLocation, self::$imgFile)) unlink($backupLocation);
        $this->assertJsonStringEqualsJsonString(
            json_encode([$fileInfo['name'] => ['error' => false]]),
            $this->getActualOutput()
        );

        return $fileInfo;
    }

    /**
     * @testdox Delete file test for a single file
     * @covers ::DeleteWorker
     * @depends testUploadSingleFile
     * @runInSeparateProcess
     * @throws DebugPDOException
     * @throws DeleteFailedException
     * @throws PDOWriteException
     * @throws NoSuchFileException
     * @throws MissingParameterException
     */
    public function testDeleteSingleFiles(array $fileInfo) {
        $user = User::createFromAuth($this->auth);
        $file = new FileLocationInfo([
            'name' => $fileInfo['name'],
            'path' => '',
            'ownerId' => $user->id
        ]);
        $fileInfo = FileReader::getFileMetaData($file, $user, $this->db);
        $_REQUEST['fileId'] = $fileInfo['fileID'];
        $this->deleteWorker->run();
        $this->assertJsonStringEqualsJsonString(
            json_encode(['error' => false]),
            $this->getActualOutput()
        );
    }

    /**
     * @testdox Make sure we can upload valid files
     * @dataProvider validFileProvider
     * @covers ::UploadWorker
     * @runInSeparateProcess
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
        $this->uploadWorker->run();
        /* Make sure we print out files with no errors */
        $output = [];
        foreach ($inputFiles['name'] as $fileName) {
            $output[$fileName] = ['error' => false];
        }
        $this->assertJsonStringEqualsJsonString(
            json_encode($output),
            $this->getActualOutput()
        );
        /* restore files the upload automatically deletes for security */
        foreach ($backupLocations as $key => $backupLocation) {
            if (copy($backupLocation, $inputFiles['tmp_name'][$key])) unlink($backupLocation);
        }
    }

    /**
     * @testdox See if we can view the image and it matches our decrypted version
     * @dataProvider validFileProvider
     * @covers ::ImageWorker
     * @depends      testUploadFile
     * @runInSeparateProcess
     * @throws NoSuchFileException
     * @throws EncryptionFailureException
     * @throws MissingParameterException
     */
    public function testImageViewWithName(array $inputFiles) {
        foreach ($inputFiles['name'] as $key => $value) {
            $this->expectOutputString(file_get_contents($inputFiles['tmp_name'][$key]));
            $_REQUEST['fileName'] = $value;
            $this->imageWorker->run();
        }
    }

    /**
     * @testdox See if we can download the image and it matches our decrypted version
     * @dataProvider validFileProvider
     * @covers ::ImageWorker
     * @depends      testUploadFile
     * @runInSeparateProcess
     * @throws NoSuchFileException
     * @throws EncryptionFailureException
     * @throws MissingParameterException
     */
    public function testImageDownloadWithName(array $inputFiles) {
        $_REQUEST['download'] = true;
        foreach ($inputFiles['name'] as $key => $value) {
            $this->expectOutputString(file_get_contents($inputFiles['tmp_name'][$key]));
            $_REQUEST['fileName'] = $value;
            $this->imageWorker->run();
        }
    }

    /**
     * @testdox See if we can view the image and it matches our decrypted version
     * @dataProvider validFileProvider
     * @covers ::ImageWorker
     * @depends      testUploadFile
     * @runInSeparateProcess
     * @throws NoSuchFileException
     * @throws EncryptionFailureException
     * @throws MissingParameterException
     */
    public function testImageViewWithId(array $inputFiles) {
        $user = User::createFromAuth($this->auth);
        foreach ($inputFiles['name'] as $key => $value) {
            $file = new FileLocationInfo([
                'name' => $value,
                'path' => '',
                'ownerId' => $user->id
            ]);
            $fileInfo = FileReader::getFileMetaData($file, $user, $this->db);
            $_REQUEST['fileId'] = $fileInfo['fileID'];
            $this->expectOutputString(file_get_contents($inputFiles['tmp_name'][$key]));
            $this->imageWorker->run();
        }
    }

    /**
     * @testdox See if we can download the image and it matches our decrypted version
     * @dataProvider validFileProvider
     * @covers ::ImageWorker
     * @depends      testUploadFile
     * @runInSeparateProcess
     * @throws NoSuchFileException
     * @throws EncryptionFailureException
     * @throws MissingParameterException
     */
    public function testImageDownloadWithId(array $inputFiles) {
        $_REQUEST['download'] = true;
        $user = User::createFromAuth($this->auth);
        foreach ($inputFiles['name'] as $key => $value) {
            $file = new FileLocationInfo([
                'name' => $value,
                'path' => '',
                'ownerId' => $user->id
            ]);
            $fileInfo = FileReader::getFileMetaData($file, $user, $this->db);
            $_REQUEST['fileId'] = $fileInfo['fileID'];
            $this->expectOutputString(file_get_contents($inputFiles['tmp_name'][$key]));
            $this->imageWorker->run();
        }
    }

    /**
     * @testdox We should be able to view the file in our folder
     * @dataProvider validFileProvider
     * @covers ::FolderImagesWorker
     * @depends      testUploadFile
     * @runInSeparateProcess
     */
    public function testFilesInFolder(array $inputFiles) {
        /* Be in the root folder*/
        $_REQUEST['folderPath'] = '/';
        $this->folderImagesWorker->run();
        $output = (array)json_decode($this->getActualOutput());
        $this->assertNotEmpty($output);
        foreach ($inputFiles['name'] as $fileName) {
            $found = false;
            foreach ($output as $fileInfo) {
                if ($fileInfo->fileName === $fileName) {
                    $found = true;
                    $this->assertEquals($this->auth->getUserID(), $fileInfo->memberID);
                    $this->assertNotEmpty($fileInfo->fileID);
                    $this->assertNotEmpty($fileInfo->fileSize);
                    $this->assertNotEmpty($fileInfo->uploaded);
                    $this->assertEquals('image/jpeg', $fileInfo->mime);
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * @testdox Delete file test - also acts as a cleaner
     * @dataProvider validFileProvider
     * @covers ::DeleteWorker
     * @depends      testImageViewWithName
     * @depends      testImageDownloadWithName
     * @depends      testImageViewWithId
     * @depends      testImageDownloadWithId
     * @depends      testFilesInFolder
     * @runInSeparateProcess
     * @throws DebugPDOException
     * @throws DeleteFailedException
     * @throws PDOWriteException
     * @throws NoSuchFileException
     * @throws MissingParameterException
     */
    public function testDeleteMultipleFiles(array $inputFiles) {
        foreach ($inputFiles['name'] as $value) {
            $_REQUEST['fileName'] = $value;
            $this->expectOutputString(json_encode(['error' => false]));
            $this->deleteWorker->run();
            $this->assertJsonStringEqualsJsonString(
                json_encode(['error' => false]),
                $this->getActualOutput()
            );
        }
    }

    /**
     * @testdox make sure our folder is empty
     * @dataProvider validFileProvider
     * @covers ::FolderImagesWorker
     * @depends      testDeleteMultipleFiles
     * @runInSeparateProcess
     */
    public function testFolderEmptyAfterDelete() {
        /* Be in the root folder*/
        $_REQUEST['folderPath'] = '';
        $this->folderImagesWorker->run();
        $this->assertJsonStringEqualsJsonString(
            json_encode([]),
            $this->getActualOutput()
        );
    }

}
<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\api\EndpointValidator;
use ImageRepository\Exception\{DebugPDOException,
    DeleteFailedException,
    MissingParameterException,
    NoSuchFileException,
    PDOWriteException,
    StaticClassAssertionError};
use ImageRepository\Model\{Database, User};
use ImageRepository\Model\FileManagement\FileManager;
use ImageRepository\Utils\Auth;
use ImageRepository\Views\JsonFormatter;

/**
 * Class that handles the logic for deleting the file
 */
final class DeleteWorker
{
    private function __construct() {
        throw new StaticClassAssertionError();
    }

    /**
     * @throws DebugPDOException
     * @throws DeleteFailedException
     * @throws PDOWriteException
     * @throws NoSuchFileException
     * @throws MissingParameterException
     */
    public static function run(Database $db, Auth $auth, bool $debug) {
        if (!(EndpointValidator::isValidRequestVar('fileName') || EndpointValidator::isValidRequestVar('fileId'))) {
            EndpointValidator::missingParameterExit();
        }
        /* Set variables */
        $user = new User($auth->getCurrentUserInfo());
        $file = FileLocationInfoFactory::createFromApiData($user, $db);
        /* If we have a file to delete then delete it */
        if ($file === null || !FileManager::deleteFile($file, $user, $db, $debug)) throw new DeleteFailedException();
        JsonFormatter::printArray(['error' => false]);
    }

}
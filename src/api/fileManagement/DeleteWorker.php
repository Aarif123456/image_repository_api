<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\api\EndpointValidator;
use ImageRepository\Exception\{DebugPDOException,
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
        $output = [];
        $file = getFileInformation($user, $db);
        if ($file !== null) $output['error'] = !FileManager::deleteFile($file, $user, $db, $debug);
        JsonFormatter::printArray($output);
    }

}
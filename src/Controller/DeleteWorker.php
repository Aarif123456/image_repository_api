<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Exception\{DebugPDOException,
    DeleteFailedException,
    MissingParameterException,
    NoSuchFileException,
    PDOWriteException};
use ImageRepository\Model\FileManagement\FileManager;
use ImageRepository\Model\User;
use ImageRepository\Views\JsonFormatter;

/**
 * Class that handles the logic for deleting the file
 */
final class DeleteWorker extends AbstractWorker
{

    /**
     * @throws DebugPDOException
     * @throws DeleteFailedException
     * @throws PDOWriteException
     * @throws NoSuchFileException
     * @throws MissingParameterException
     */
    public function run() {
        if (!(EndpointValidator::isValidRequestVar('fileName') || EndpointValidator::isValidRequestVar('fileId'))) {
            EndpointValidator::missingParameterExit();
        }
        /* Set variables */
        $user = User::createFromAuth($this->auth);
        $file = FileLocationInfoFactory::createFromApiData($user, $this->db);
        /* If we have a file to delete then delete it */
        if ($file === null || !FileManager::deleteFile($file, $user, $this->db,
                $this->debug)) {
            throw new DeleteFailedException();
        }
        JsonFormatter::printArray(['error' => false]);
    }

}
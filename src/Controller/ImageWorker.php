<?php

declare(strict_types=1);
namespace ImageRepository\Controller;

use ImageRepository\Exception\{EncryptionFailureException, MissingParameterException, NoSuchFileException};
use ImageRepository\Model\FileManagement\FileReader;
use ImageRepository\Model\User;

/**
 * Class with logic for downloading or viewing images
 */
final class ImageWorker extends AbstractWorker
{
    /**
     * @throws NoSuchFileException
     * @throws EncryptionFailureException
     * @throws MissingParameterException
     */
    public function run() {
        /* Make sure request is valid*/
        if (!(EndpointValidator::isValidRequestVar('fileName') || EndpointValidator::isValidRequestVar('fileId'))) {
            EndpointValidator::missingParameterExit();
        }
        /* Set variables */
        $user = User::createFromAuth($this->auth);
        $ownerId = $_REQUEST['ownerId'] ?? $user->id;
        $download = $_REQUEST['download'] ?? false;
        /* Create user object using the id of the image's owner */
        $targetUser = new User($this->auth->getUser($ownerId));
        $file = FileLocationInfoFactory::createFromApiData($targetUser, $this->db);
        if ($file == null) throw new NoSuchFileException();
        $fileBinary = FileReader::getFileBytes($file, $targetUser, $this->db);
        $fileSize = strlen($fileBinary);
        $mime = FileReader::getFileMime($file, $targetUser, $this->db);
        /* if we want download then also add in filename*/
        $disposition = $download ? "attachment; filename=$file->name" : 'inline';
        header("Content-Disposition: $disposition");
        header("Content-Type: $mime");
        header("Content-Length: $fileSize");
        header('Connection: close');
        echo $fileBinary;
    }
}
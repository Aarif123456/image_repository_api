<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Controller\FolderImagesWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new FolderImagesWorker();
$worker->safeRun(AUTHORIZED_USER);
<?php

declare(strict_types=1);
namespace ImageRepository\api\FileManagement;

use ImageRepository\Controller\DeleteWorker;

use const ImageRepository\Utils\AUTHORIZED_USER;

$worker = new DeleteWorker();
$worker->safeRun(AUTHORIZED_USER);
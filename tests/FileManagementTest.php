<?php

declare(strict_types=1);
namespace ImageRepository\Tests;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/dataProviders.php';

/* The point of this class is to make sure we are able to properly manage the files we own securely
* This means we should be able to do the following
* 1. Upload a file: which means it should be in our account (database 
* and see in folder). A user should not be able to go out of their folder
* 2. View image: we should be able view files when .
* 3. Manage folder structure: We should be able to put our file in any folder and make new folders as we want.
* 4. Delete files: We should be able to delete files in our account and not be able to 
  delete files that we don't own
* If we don't have any files we should not get an error
* The scope of these test is at the API level
* Make sure errors always set error variable to true
*/

final class FileManagementTest extends TestCase
{

    /**************************************************************************/
    /* Data providers: data used to test */
    /**************************************************************************/
    /* Test cases */
    /**
     * @testdox Make sure we can generate the keys needed for the encryption decryption process
     * @covers ::setup
     */
}
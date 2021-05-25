[![BCH compliance](https://bettercodehub.com/edge/badge/Aarif123456/image_repository_api?branch=main)](https://bettercodehub.com/)
[![MIT License](https://img.shields.io/github/license/Aarif123456/image_repository_api?style=for-the-badge)](https://lbesson.mit-license.org/)
![Lines of code](https://img.shields.io/tokei/lines/github/Aarif123456/image_repository_api?style=for-the-badge)
![Top Language](https://img.shields.io/github/languages/top/Aarif123456/image_repository_api?style=for-the-badge)

# What is this? 🤔

An API built to be used by the for the [Image Repository](https://abdullaharif.tech/image_repository)

# API for the Image Repository #

API URL: https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/ENDPOINT_NAME

### Information in API description ###

* Endpoint name
* Description
* File name - Since, the code will always be up to date.
* Parameter list -including which method to use such as POST, GET or REQUEST(both)

## Format ##

<details>
<summary> </summary>

    1. Description: 
    2. fileName.php --> /api/fileName
    3. Parameter list:
    4. return 

</details>

# Endpoints #

### File Management

<details>
<summary>Upload file </summary>

    1. Description: A logged in user should be able to upload a file securely. The policy will allow the user to control who can view their file. 
    2. FileManagement/upload.php --> /api/FileManagement/upload
    3. Parameter list: filePath(Optional), fileNames(optional), file, policy
        If no filePath is passed in, we will assume the file will be located in the user's root directory.
        The fileNames variable refers to name of the variable that holds the submitted files. By default the variable will be called 'images'.
        You can upload multiple images at once, but you have to make sure every image is less then or equal to 7 MB.
    4. {['file1Name' => ['error': boolean], 'file2Name' => ['error': boolean]... 'filenName' => ['error': boolean]]}

</details>

<details>
<summary>Delete file </summary>

    1. Description: A logged in user should be able to delete any files they have uploaded.
    2. FileManagement/delete.php --> /api/FileManagement/delete
    3. Parameter list: filePath(Optional), fileName or fileId
        If no filePath is passed in, we will assume the file will be located in the user's root directory.
    4. {error: boolean, message?: string}

</details>

<details>
<summary>Get images in folder</summary>

    1. Description: A logged in user should be able to view the files on their account
    2. FileManagement/folderImages.php --> /api/FileManagement/folderImages
    3. Parameter list: folderPath(Optional)
        If no folderPath is passed in, we will assume the user wants to view the files in the root directory.
    4. {[fileInfo]}
        fileInfo = 
                {
                    "fileID": number,
                    "memberID": number,
                    "fileName": string,
                    "filePath": string,
                    "fileSize": number,
                    "uploaded": string(sql Date-time),
                    "accessID": number,
                    "mime": string
                }

</details>

<details>
<summary>View/Download image</summary>

    1. Description: A user should be able to view images that are open to them
    2. FileManagement/image.php --> /api/FileManagement/image
    3. Parameter list: ownerId(Optional), filePath(Optional), download(optional), fileName or fileId,
        If no filePath is passed in, we will assume the file will be located in the user's root directory. 
        The ownerId will be assumed to be the user by default. But, you can pass in another user and get back a file on their account assuming you have access.
        If you pass in the fileId then ownerId and filePath will be ignored completely.
        The download option controls whether we want to force a download for the file.
    4. For view the page will return an image, for download it should prompt a download.

</details>

## User Management

<details>
<summary> Logout </summary>

    1. Description: Logout the user
    2. user/logout.php --> /api/user/logout
    3. Parameter list:
    4. Output: {error: boolean}
        Tells us if we successfully logged out.

</details>

<details>
<summary> Login </summary>

    1. Description: Log the user in and then store cookie
    2. UserManagement/login.php  --> /api/UserManagement/login
    3. Parameter list:
        Accepts POST variable:  email, password, remember, admin(optional)
            Email and password are the users login info.
            Remember toggle how long we store the authenticated cookie
            Admin tells the endpoint if the user is claiming to be an admin. It is false by default 
    4. Output: {error: boolean, message: string}
        Error tells us if the login was successful and the message is a user friendly message.

</details>

<details>
<summary> Register </summary>

    1. Description: Users can register for their own account
    2. UserManagement/register.php --> /api/UserManagement/register
    3. Parameter list:
        Accepts POST variable: firstName, lastName, email, password, admin(optional)  
    4. Output: {error: boolean, message: string, id?: number }
        Error tells us if the registration was successful and the message is a user friendly message. If user was created we also return the user's Id

</details>

# Building :construction:

## Technology stack :gear:

**PHPAuth**: [PHPAuth](https://github.com/PHPAuth/PHPAuth) is a package to handle everything related to authentication.
It includes features such as automatically emailing the user for password resets and blocking attackers by IP. \
**PDO**: [PDO](https://www.php.net/manual/en/book.pdo.php) PHP data objects are used securely access databases without
being locked to one type of database \
**PHPUnit**: [PHPUnit](https://phpunit.de/) is a testing framework used to easily create unit tests for my program.
Tests can be found in the "tests" folder.

## Database design ## 

![Database ER diagram](https://i.imgur.com/INi6Iro.png)

## TODO ##

<details>
<summary>List sub-folders </summary>

    1. Description: Return a list of all folders in the selected folder
    2. FileManagement/listSubfolder.php -> /api/FileManagement/listSubfolder
    3. Parameter list: folderPath

</details>

<details>
<summary>Search file </summary>

    1. Description: Allow the user to search for files by different attributes such as image tags, file name or uploader. Can be used to show the user their own files as well
    2. Search/file.php -> /api/Search/file
    3. Parameter list: searchType, keyword

</details>

<details>
<summary>Change file permission </summary>

    1. Description: Allow the user to choose what files to share with the public and what to keep private
    2. FileManagement/filePermission.php -> /api/FileManagement/filePermission
    3. Parameter list: Only allow post request 

</details>

<details>
<summary>Reset Password </summary>

    1. Description: Email the user a link so they can reset their password.
    2. UserManagement/resetPassword.php -> /api/UserManagement/resetPassword
    3. Parameter list: email

</details>

<!--  
turn everything into a function to make it testable
Figure out how to throw errors and control what message is sent back

Philosophy use static function where possible - benefit of auto loading classes 
but don't really on class variables 
-->
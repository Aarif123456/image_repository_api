# What is this? 🤔

An API built to be used by the for the [Image Repository](https://arif115.myweb.cs.uwindsor.ca/imagerepository/docs)

# API for the Image Repository#

API url: https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/ENDPOINT_NAME

### Information in API description ###

* Endpoint name
* Description
* File name - Since, code will always be updated even if the read me isn't
* Parameter list -including which method to use such as POST, GET or REQUEST(both)

## Format ##

<details>
<summary> </summary>

    1. Description: 
    2. fileName.php --> /api/fileName
    3. Parameter list:

</details>

# Endpoints #


## Pending Testing ##

### File Management

<details>
<summary>Upload file </summary>

    1. Description:  A logged in user should be able to upload a file securely. The policy will allows the user to control who can see their file. 
    2. fileManagement/upload.php --> /api/fileManagement/upload
    3. Parameter list: filePath(Optional), fileName, file, policy
        If no filePath is passed in, we will assume the fill will be in the users roots directory 

</details>

<details>
<summary>Delete file </summary>

    1. Description:  A logged in user should be able to delete any files they uploaded.
    2. fileManagement/delete.php --> /api/fileManagement/delete
    3. Parameter list: filePath(Optional), fileName
        If no filePath is passed in, we will assume the fill will be in the users roots directory 

</details>


## User Management

<details>
<summary> logout user</summary>

    1. Description: Logout the user
    2. user/logout.php --> /api/user/logout
    3. Parameter list:

</details>

<details>
<summary> Verify users: </summary>

    1. Description: Log the user in and then store the cookie
    2. userManagement/loginUser.php  --> /api/userManagement/loginUser
    3. Parameter list:
        Accepts POST variable:  email, password, remember

</details>

<details>
<summary> Registering user </summary>

    1. Description: Users can register for their own account
    2. userManagement/addUser.php --> /api/userManagement/addUsers
    3. Parameter list:
        Accepts POST variable: firstName, lastName, email, password, admin(optional)  
</details>


# Building :construction:
## Technology stack :gear:
**PHPAuth**: [PHPAuth](https://github.com/PHPAuth/PHPAuth) is a package to handle everything related to authentication. It includes features such as automatically emailing the user for password resets and blocking attackers by IP. \
**PDO**: [PDO](https://www.php.net/manual/en/book.pdo.php) PHP data objects are used securely access databases without being locked to one type of database \
**PHPUnit**: [PHPUnit](https://phpunit.de/) is a testing framework used to easily create unit tests for my program. Tests can be found in the "tests" folder.

## Database design ## 
![Database ER diagram](https://i.imgur.com/INi6Iro.png)

## TODO ##
<details>
<summary>List sub-folders </summary>

    1. Description: Return a list of all folders in the selected folder
    2. fileManagement/listSubfolder.php -> /api/fileManagement/listSubfolder
    3. Parameter list: folderPath
</details>

<details>
<summary>Search file </summary>

    1. Description: Allow the user to search for files by different attributes such as image tags, file name or uploader. Can be used to show the user their own files as well
    2. search/searchImage.php -> /api/search/searchImage
    3. Parameter list: searchType, keyword
</details>

<details>
<summary>Change file permission </summary>

    1. Description: Allow the user to choose what files to share with the public and what to keep private
    2. fileManagement/setFilePermission.php -> /api/fileManagement/setFilePermission
    3. Parameter list:  
</details>

<details>
<summary>Reset Password </summary>

    1. Description: Email the user a link so they can reset their password.
    2. userManagement/resetPassword.php -> /api/userManagement/resetPassword
    3. Parameter list: email
</details>

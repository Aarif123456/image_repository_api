# What is this? 🤔

An API built to be used by the for the [Image Repository](https://abdullaharif.tech/imagerepository/)

# API for the Image Repository#

API url: https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/ENDPOINT_NAME

### Information in API description ###

* Endpoint name
* Description
* File name - Since, code will always be updated even if the read me isn't
* Parameter list -including which method to use such as POST, GET or REQUEST(both)
* httpie command with expected result format to validate the listed information about the endpoint

## Format ##

<details>
<summary> </summary>

    1. Description: 
    2. fileName.php --> /api/fileName
    3. Parameter list:
    4. httpie command:

</details>

## TODO ##

<details>

<summary>Get file access </summary>

    1. Description:  The system return back the description and name of the types of access the system support. This includes public access which means anyone can see the file and private access which means only the uploader can see the file.
    2. fileManagement/getFileAccess.php --> /api/fileManagement/getFileAccess
    3. Parameter list: 
    4. httpie command:

</details>

<!-- <summary>Search File </summary>

    1. Description: Allow the user to search for files by different attributes such as image tags, file name or uploader. Can be used to show the user their own files as well
    2. fileManagement/search.php -> /api/fileManagement/search
    3. Parameter list: searchType, keyword
    4. httpie command:
</details>
 -->


<!-- ______________________________________________________ -->

## Pending Testing ##

### File Management

<details>
<summary>Upload file </summary>

    1. Description:  A logged in user should be able to upload a file securely. The policy will allows the user to control who can see their file. 
    2. fileManagement/upload.php --> /api/fileManagement/upload
    3. Parameter list: filePath(Optional), fileName, file, policy
        If no filePath is passed in, we will assume the fill will be in the users roots directory 
    4. httpie command:

<!-- TODO: Need to implement logic to make sure we tell the user we overwrote a file - and we should make sure multiple copies fake references to the file don't exist in the database -->
</details>

<details>
<summary>Delete file </summary>

    1. Description:  A logged in user should be able to delete any files they uploaded.
    2. fileManagement/delete.php --> /api/fileManagement/delete
    3. Parameter list: filePath(Optional), fileName
        If no filePath is passed in, we will assume the fill will be in the users roots directory 
    4. httpie command:

</details>


## User Management

<details>
<summary> Check Email </summary>

    1. Description: Check if email is in database
    2. userManagement/checkEmail.php  --> /api/userManagement/checkEmail
    3. Parameter list: 
         Accepts POST variable: email
    4. httpie command:
        http --session=/tmp/session.json --form POST https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/userManagement/checkEmail email='shopify@hogwarts.com'

</details>

## User Information

<details>
<summary> check if user is logged in</summary>

    1. Description: returns true if user is logged in and false if they are not 
    2. user/isLoggedIn.php --> /api/user/isLoggedIn
    3. Parameter list:
    4. httpie command: 
         http --session=/tmp/session.json --form POST https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/user/isLoggedIn

</details>

<details>
<summary> logout user</summary>

    1. Description: Logout the user
    2. user/logout.php --> /api/user/logout
    3. Parameter list:
    4. httpie command: 
         http --session=/tmp/session.json --form POST https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/user/logout

</details>

# Endpoints #

<details>
<summary> Verify users: </summary>

    1. Description: Log the user in and then store the cookie
    2. userManagement/loginUser.php  --> /api/userManagement/loginUser
    3. Parameter list:
        Accepts POST variable:  email, password, remember
    4. httpie command:
        http --session=/tmp/session.json --form POST https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/userManagement/loginUser email='abdullahmeo11@gmail.com' password='imageRepo' remember=false

</details>

<details>
<summary> Registering user </summary>

    1. Description: Users can register for their own account
    2. userManagement/addUser.php --> /api/userManagement/addUsers
    3. Parameter list:
        Accepts POST variable: firstName, lastName, email, password, admin(optional)  
    4. httpie command:
        http --session=/tmp/session.json --form POST https://arif115.myweb.cs.uwindsor.ca/imagerepository/api/userManagement/addUser email='shopifyAccount@shopify.com' password='ruby in rails'  firstName='Tobias' lastName='Lutke'

</details>
<!-- Link PHP Auth module -->
<!-- Link picture to the tables you added -->
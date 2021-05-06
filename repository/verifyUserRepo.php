<?php

declare(strict_types=1);

/* Imports */
require_once __DIR__ . '/error.php';

function queryUserVerify($email, $userType, $conn) {
    // get query
    $query = getQueryForUserType($userType);
    // if no query return back null
    if (empty($query)) {
        return null;
    }
    /* Prepare statement */
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);

    // otherwise return back result from query
    return getExecutedResult($stmt);
}


function getQueryForUserType($userType): string {
    $userVerifyQuery = '
                            SELECT 
                                id,
                                firstName,
                                lastName,
                                password,
                                \'user\' as userType 
                            FROM 
                                users
                                INNER JOIN members ON id=memberID 
                            WHERE 
                                email = :email';
    $adminVerifyQuery = "
                            SELECT 
                                id,
                                firstName,
                                lastName,
                                password,
                                'admin' as userType 
                            FROM 
                                users
                                INNER JOIN members ON id=memberID 
                                INNER JOIN professor ON memberID=adminID 
                            WHERE 
                                email = :email";
    switch ($userType) {
        case 'user':
            return $userVerifyQuery;
        case 'admin':
            return $adminVerifyQuery;
        default:
            return '';
    }
}


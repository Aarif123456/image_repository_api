DROP TABLE IF EXISTS `translations`;
CREATE TABLE `translations`
(
    `translation_key`   varchar(80) NOT NULL,
    `translation_group` varchar(80) DEFAULT NULL,
    `comment`           varchar(80) DEFAULT NULL,
    `en_CA`             LONGTEXT,
    PRIMARY KEY (`translation_key`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


INSERT INTO `translations`(`translation_key`, `translation_group`, `comment`, `en_CA`)
VALUES ('COMMAND_FAILED', 'Exception', NULL, 'Query failed to execute, ensure you use the correct values'),
       ('ENCRYPTED_FILE_NOT_CREATED', 'Exception', NULL, 'Unable to save encrypted file'),
       ('FILE_ALREADY_EXISTS', 'Exception', NULL, 'File already exists'),
       ('FILE_SIZE_LIMIT_EXCEEDED', 'Exception', NULL, 'Exceeded file size limit'),
       ('INTERNAL_ENCRYPTION_FAILURE', 'Exception', NULL, 'Failure in encryption or decryption call'),
       ('INTERNAL_SERVER_ERROR', 'Exception', NULL, 'Something went wrong :('),
       ('INVALID_ACCESS_TYPE', 'Exception', NULL, 'Invalid file access policy.'),
       ('INVALID_FILE_FORMAT', 'Exception', NULL, 'Invalid file format.'),
       ('INVALID_PROPERTY', 'Exception', NULL, 'This property has not been initialized properly'),
       ('MISSING_PARAMETERS', 'Exception', NULL,
        'Request is missing values. Please consult the documentation to ensure you are passing all the required arguments'),
       ('NO_FILE_SENT', 'Exception', NULL, 'No file sent.'),
       ('NO_SUCH_FILE', 'Exception', NULL, 'No file with that name exists in the current folder'),
       ('PDO_ERROR', 'Exception', NULL, 'Internal database failure :('),
       ('PHP_EXCEPTION', 'Exception', NULL, 'The following exception was thrown:'),
       ('SQL_ERROR', 'Exception', NULL, 'The following SQL error was detected:'),
       ('UNAUTHORIZED_NO_LOGIN', 'Exception', NULL, 'User is not logged in'),
       ('USER_NOT_ADMIN', 'Exception', NULL, 'User is not an admin.'),
       ('WRITE_QUERY_FAILED', 'Exception', NULL, 'Failed to update the database');

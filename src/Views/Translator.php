<?php

declare(strict_types=1);
namespace ImageRepository\Views;

use PDO;
use PDOException;

/**
 * Class to return translated string
 */
class Translator
{
    public array $dictionary;
    public string $translationTable = 'translations';

    public function __construct(PDO $conn, string $language = '') {
        $this->dictionary = [];
        /* Determine site language */
        $siteLanguage = empty($language) ? 'en_CA' : $language;
        try {
            // check table exists in database
            $conn->query("SELECT * FROM $this->translationTable LIMIT 1;");
            $query = "SELECT `translation_key`, `$siteLanguage` as `lang` FROM $this->translationTable";
            $this->dictionary = $conn->query($query)->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            $this->dictionary = $this->getFallbackDictionary();
        }
    }

    /**
     * In case something goes wrong it's always good to have a back up
     *
     * @return array
     */
    protected function getFallbackDictionary(): array {
        $lang = [];
        $lang['COMMAND_FAILED'] = 'Query failed to execute';
        $lang['ENCRYPTED_FILE_NOT_CREATED'] = 'Unable to save encrypted file';
        $lang['FILE_ALREADY_EXISTS'] = 'File already exists';
        $lang['FILE_SIZE_LIMIT_EXCEEDED'] = 'Exceeded file size limit';
        $lang['INTERNAL_ENCRYPTION_FAILURE'] = 'Failure in encryption or decryption call';
        $lang['INTERNAL_SERVER_ERROR'] = 'Something went wrong :(';
        $lang['INVALID_ACCESS_TYPE'] = 'Invalid file access policy.';
        $lang['INVALID_FILE_FORMAT'] = 'Invalid file format.';
        $lang['INVALID_PROPERTY'] = 'This property has not been initialized properly';
        $lang['MISSING_PARAMETERS'] = 'Request is missing values. Please consult the documentation to ensure you are passing all the required arguments';
        $lang['NO_FILE_SENT'] = 'No file sent.';
        $lang['NO_SUCH_FILE'] = 'No file with that name exists in the current folder';
        $lang['PDO_ERROR'] = 'Internal database failure :(';
        $lang['PHP_EXCEPTION'] = 'The following exception was thrown:';
        $lang['SQL_ERROR'] = 'The following SQL error was detected:';
        $lang['UNAUTHORIZED_NO_LOGIN'] = 'User is not logged in';
        $lang['USER_NOT_ADMIN'] = 'User is not an admin.';
        $lang['WRITE_QUERY_FAILED'] = 'Failed to update the database';

        return $lang;
    }

    /**
     * Magic property getter
     *
     * @param string $key
     *
     * @return string
     */
    public function __get(string $key): string {
        return $this->dictionary[$key] ?? '';
    }

    /**
     * @return array
     */
    public function getAll(): array {
        return $this->dictionary;
    }

}

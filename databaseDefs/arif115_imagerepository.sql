SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `fileAccess`
(
    `accessID`   tinyint(1)                          NOT NULL,
    `accessType` varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `fileAccess` (`accessID`, `accessType`)
VALUES (1, 'Private'),
       (2, 'Public');

CREATE TABLE `files`
(
    `fileID`   int(10)                             NOT NULL,
    `memberID` int(11)                             NOT NULL,
    `fileName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `filePath` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `fileSize` int(11)                             NOT NULL DEFAULT 0,
    `uploaded` timestamp                           NOT NULL DEFAULT current_timestamp(),
    `accessID` tinyint(1)                          NOT NULL DEFAULT 1,
    `mime`     varchar(50) COLLATE utf8_unicode_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `imageTags`
(
    `tagNumber` int(5)                              NOT NULL,
    `fileID`    int(10)                             NOT NULL,
    `tag`       varchar(25) COLLATE utf8_unicode_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `phpauth_attempts`
(
    `id`         int(11)  NOT NULL,
    `ip`         char(39) NOT NULL,
    `expiredate` datetime NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

CREATE TABLE `phpauth_config`
(
    `setting` varchar(100) NOT NULL,
    `value`   varchar(100) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

INSERT INTO `phpauth_config` (`setting`, `value`)
VALUES ('allow_concurrent_sessions', '0'),
       ('attack_mitigation_time', '+30 minutes'),
       ('attempts_before_ban', '30'),
       ('attempts_before_verify', '5'),
       ('bcrypt_cost', '10'),
       ('cookie_domain', 'arif115.myweb.cs.uwindsor.ca'),
       ('cookie_forget', '+30 minutes'),
       ('cookie_http', '0'),
       ('cookie_name', 'phpauth_session_cookie'),
       ('cookie_path', '/'),
       ('cookie_remember', '+1 month'),
       ('cookie_renew', '+5 minutes'),
       ('cookie_samesite', 'None'),
       ('cookie_secure', '1'),
       ('custom_datetime_format', 'Y-m-d H:i'),
       ('emailmessage_suppress_activation', '0'),
       ('emailmessage_suppress_reset', '0'),
       ('mail_charset', 'UTF-8'),
       ('password_min_score', '3'),
       ('recaptcha_enabled', '0'),
       ('recaptcha_secret_key', ''),
       ('recaptcha_site_key', ''),
       ('request_key_expiration', '+10 minutes'),
       ('site_activation_page', 'activate'),
       ('site_activation_page_append_code', '0'),
       ('site_email', 'no-reply@imagerepository.com'),
       ('site_key',
        'L?^8\\,Z,+Q#jQXA+"Ce-|i0%\';!pIt^IUGhI,j|l8F(oUtR+,/2g`Am?~K?b,:{4/k\\nJB5$:U*Vu\'adRW\\b~^utaOtYWH1mQrY'),
       ('site_language', 'en_GB'),
       ('site_name', 'Image Repository'),
       ('site_password_reset_page', 'reset'),
       ('site_password_reset_page_append_code', '0'),
       ('site_timezone', 'America/Toronto'),
       ('site_url', 'https://abdullaharif.tech/imagerepository'),
       ('smtp', '0'),
       ('smtp_auth', '1'),
       ('smtp_debug', '0'),
       ('smtp_host', 'smtp.example.com'),
       ('smtp_password', 'password'),
       ('smtp_port', '25'),
       ('smtp_security', NULL),
       ('smtp_username', 'imagerepository@arif115.myweb.cs.uwindsor.ca'),
       ('table_attempts', 'phpauth_attempts'),
       ('table_emails_banned', 'phpauth_emails_banned'),
       ('table_requests', 'phpauth_requests'),
       ('table_sessions', 'phpauth_sessions'),
       ('table_translations', 'phpauth_translation_dictionary'),
       ('table_users', 'users'),
       ('translation_source', 'sql'),
       ('verify_email_max_length', '100'),
       ('verify_email_min_length', '5'),
       ('verify_email_use_banlist', '1'),
       ('verify_password_min_length', '3');

CREATE TABLE `phpauth_emails_banned`
(
    `id`     int(11) NOT NULL,
    `domain` varchar(100) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `phpauth_requests`
(
    `id`     int(11)                                                                    NOT NULL,
    `uid`    int(11)                                                                    NOT NULL,
    `token`  char(20) CHARACTER SET latin1 COLLATE latin1_general_ci                    NOT NULL,
    `expire` datetime                                                                   NOT NULL,
    `type`   enum ('activation','reset') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

CREATE TABLE `phpauth_sessions`
(
    `id`         int(11)                                                 NOT NULL,
    `uid`        int(11)                                                 NOT NULL,
    `hash`       char(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
    `expiredate` datetime                                                NOT NULL,
    `ip`         varchar(39)                                             NOT NULL,
    `device_id`  varchar(36) DEFAULT NULL,
    `agent`      varchar(200)                                            NOT NULL,
    `cookie_crc` char(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `phpauth_translation_dictionary`
(
    `translation_key`   varchar(80) NOT NULL,
    `translation_group` varchar(80) DEFAULT NULL,
    `comment`           varchar(80) DEFAULT NULL,
    `en_GB`             longtext    DEFAULT NULL,
    `ru_RU`             longtext    DEFAULT NULL,
    `ar_TN`             longtext    DEFAULT NULL,
    `cs_CZ`             longtext    DEFAULT NULL,
    `da_DK`             longtext    DEFAULT NULL,
    `de_DE`             longtext    DEFAULT NULL,
    `es_MX`             longtext    DEFAULT NULL,
    `fa_IR`             longtext    DEFAULT NULL,
    `fr_FR`             longtext    DEFAULT NULL,
    `gr_GR`             longtext    DEFAULT NULL,
    `hu_HU`             longtext    DEFAULT NULL,
    `id_ID`             longtext    DEFAULT NULL,
    `it_IT`             longtext    DEFAULT NULL,
    `nl_BE`             longtext    DEFAULT NULL,
    `nl_NL`             longtext    DEFAULT NULL,
    `no_NB`             longtext    DEFAULT NULL,
    `pl_PL`             longtext    DEFAULT NULL,
    `ps_AF`             longtext    DEFAULT NULL,
    `pt_BR`             longtext    DEFAULT NULL,
    `ro_RO`             longtext    DEFAULT NULL,
    `se_SE`             longtext    DEFAULT NULL,
    `sk_SK`             longtext    DEFAULT NULL,
    `sl_SI`             longtext    DEFAULT NULL,
    `sr_RS`             longtext    DEFAULT NULL,
    `th_TH`             longtext    DEFAULT NULL,
    `tr_TR`             longtext    DEFAULT NULL,
    `uk_UA`             longtext    DEFAULT NULL,
    `vi_VN`             longtext    DEFAULT NULL
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  ROW_FORMAT = DYNAMIC;

CREATE TABLE `systemKeys`
(
    `keysName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
    `keyData`  mediumtext COLLATE utf8_unicode_ci  NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

INSERT INTO `systemKeys` (`keysName`, `keyData`)
VALUES ('masterKey',
        'AAAAFHKDnrFCRbep5AQSutf+BL99XDBdAAAAgJbnHWCtXfg52ckezRyYk/4vpTcCH+TAcX8nChEZ4khnJEXtaOdCc6Vw1R5d7rM3f2fJT0Wbeyx5OPQ2rbWLmT+76DcCHcPqwsVEGE96Wt80agOmAYyylD79jPNDobeAZiWsTo+1eZOxF8e6Uy6aoVqxB4cY2gaOE4nOxTxXc3ag'),
       ('publicKey',
        'AAAABWR1bW15AAAAgC88gJUTHEY4JPCB0+ktBy5OkO4d0kEoXafuCW0JE9fPJR3yIslDygKRNzNt9E7cmS3T5ivxxRCWN3m6434S3DwmytxCgmPYiLjpGqmcVokgO/wQmhdIgQhFP4PD7t6SbaYiAnHMbi4H79PPLY7OVFvETmPpGwaX+iU1RxQYYQgYAAAAgHzuEgXXTEzn9Pn0xR03GA1bATbYIfDTsQ/YGrkXIiqcEzy53e6lDOT1lt6DpWxxpsiU2tIDe8VnIa9pvqy617t2kohXj14aIGn3NX1zDIhYrRpRF6j+K/8ivDn62Pqu7jbg2zCKwdiXcvx6Ez68yuEPvRPHK6DQQtN2uMmmh0YcAAAAgInfvq6I3h+rwRkdMo6X9u3sjfBR/WweG2cv/WE3JzkJUkgVsTwP4PjYgvUVCc7Tp6EHUayrdhTc30iaome3o2M6tcJeXzQHNBdJRlO8EhOP3/1b4dTC7yHgEp8plDP+g73w87M9IXGE5768u4yFqX4+BojuxDdlHyA4hI558XDCAAAAgM1aAjL2BDJbYrvWpaxByDAXQ2Rno63S8a44Hdsc8UIuqAsPA2lHo5rKU3gGCar3o4ImzXf0w7kKblfyj3wlN45q279IroE4IxfokJaLbHUNHEZOOCdFJ09yJXHmLzsD3FvUigRX9USu9N5ScHn9dJW3mxzgqKdijUwZaBs3oMdS');

CREATE TABLE `userKeys`
(
    `memberID`   int(11)    NOT NULL,
    `privateKey` mediumblob NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

CREATE TABLE `users`
(
    `id`        int(11)      NOT NULL,
    `email`     varchar(100)                                                DEFAULT NULL,
    `password`  varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
    `isactive`  tinyint(1)   NOT NULL                                       DEFAULT 0,
    `dt`        timestamp    NOT NULL                                       DEFAULT current_timestamp(),
    `firstName` varchar(255) NOT NULL                                       DEFAULT '',
    `lastName`  varchar(255)                                                DEFAULT NULL,
    `isAdmin`   tinyint(1)   NOT NULL                                       DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;


ALTER TABLE `fileAccess`
    ADD PRIMARY KEY (`accessID`),
    ADD KEY `fileAccess` (`accessID`);

ALTER TABLE `files`
    ADD PRIMARY KEY (`fileID`),
    ADD UNIQUE KEY `uniqueFilePath` (`memberID`, `fileName`, `filePath`),
    ADD KEY `files` (`fileID`),
    ADD KEY `files_ibfk_2` (`accessID`);

ALTER TABLE `imageTags`
    ADD PRIMARY KEY (`tagNumber`),
    ADD KEY `fileID` (`fileID`);

ALTER TABLE `phpauth_attempts`
    ADD PRIMARY KEY (`id`),
    ADD KEY `ip` (`ip`);

ALTER TABLE `phpauth_config`
    ADD UNIQUE KEY `setting` (`setting`);

ALTER TABLE `phpauth_emails_banned`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `phpauth_requests`
    ADD PRIMARY KEY (`id`),
    ADD KEY `type` (`type`),
    ADD KEY `token` (`token`),
    ADD KEY `uid` (`uid`);

ALTER TABLE `phpauth_sessions`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `phpauth_translation_dictionary`
    ADD PRIMARY KEY (`translation_key`);

ALTER TABLE `systemKeys`
    ADD PRIMARY KEY (`keysName`);

ALTER TABLE `userKeys`
    ADD PRIMARY KEY (`memberID`),
    ADD KEY `members` (`memberID`);

ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD KEY `email` (`email`);


ALTER TABLE `fileAccess`
    MODIFY `accessID` tinyint(1) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 3;

ALTER TABLE `files`
    MODIFY `fileID` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `imageTags`
    MODIFY `tagNumber` int(5) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phpauth_attempts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phpauth_emails_banned`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phpauth_requests`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `phpauth_sessions`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `files`
    ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`memberID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`accessID`) REFERENCES `fileAccess` (`accessID`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `imageTags`
    ADD CONSTRAINT `imageTags_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `files` (`fileID`) ON DELETE CASCADE;

ALTER TABLE `userKeys`
    ADD CONSTRAINT `userKeys_ibfk_1` FOREIGN KEY (`memberID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;

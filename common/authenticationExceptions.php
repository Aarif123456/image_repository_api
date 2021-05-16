<?php
declare(strict_types=1);

const UNAUTHORIZED_NO_LOGIN = 'user is not logged in'; 
class UnauthorizedUserException extends Exception {
    public function __construct(string $message = null, int $code = 0) {
        if (!$message) {
            throw new $this(get_class($this) . ': ' . UNAUTHORIZED_NO_LOGIN);
        }
        parent::__construct($message, $code);
    }

    public function __toString() {
        return get_class($this) . ': ' . $this->message;
    }
}
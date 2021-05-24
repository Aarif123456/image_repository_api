<?php

declare(strict_types=1);
namespace ImageRepository\Utils;

use ImageRepository\Exception\UnauthorizedUserException;
use ImageRepository\Model\User;
use PDO;
use PHPAuth\{Auth as PHPAuth, Config as PHPAuthConfig};

/**
 * Class to handle authentication
 */
final class Auth
{
    private PHPAuthConfig $config;
    private PHPAuth $auth;

    public function __construct(PDO $conn) {
        $this->config = new PHPAuthConfig($conn);
        $this->auth = new PHPAuth($conn, $this->config);
    }

    /**
     * @throws UnauthorizedUserException
     */
    public static function unauthorizedExit() {
        header('HTTP/1.0 401 Unauthorized');
        throw new UnauthorizedUserException();
    }

    /*Make sure user is validated */
    public function isUserAuthorized(int $authorizationLevel): bool {
        switch ($authorizationLevel) {
            /* If endpoint is authenticated anyone can use it */
            case UNAUTHENTICATED:
                return true;
            case AUTHORIZED_USER:
                return $this->isUserLoggedIn();
            case AUTHORIZED_ADMIN:
                return $this->isUserLoggedIn() && $this->isUserAnAdmin();
            default:
                return false;
        }
    }

    public function isUserLoggedIn(): bool {
        return $this->auth->isLogged();
    }

    public function isUserAnAdmin(): bool {
        return (bool)($this->getCurrentUserInfo()['isAdmin'] ?? false);
    }

    public function getCurrentUserInfo(): array {
        return $this->auth->getCurrentUser() ?: [];
    }

    public function deleteUserForced(int $userId): bool {
        $info = $this->auth->deleteUserForced($userId);

        return $info['error'] ?? true;
    }

    public function getUserID() {
        return $this->auth->getCurrentUID();
    }

    /* Login public function a bit ugly because we have multiple domains*/
    public function getUser(int $userId): array {
        return $this->auth->getUser($userId);
    }

    public function login(object $loginInfo): array {
        $loginInfo = $this->auth->login($loginInfo->email, $loginInfo->password, $loginInfo->remember);
        if (!$loginInfo['error']) {
            $arrCookieOptions = [
                'expires' => $loginInfo['expire'],
                'path' => $this->config->cookie_path,
                'domain' => $this->config->cookie_domain,
                'secure' => $this->config->cookie_secure,
                'httponly' => $this->config->cookie_http,
                'samesite' => $this->config->cookie_samesite
            ];
            setcookie($loginInfo['cookie_name'], $loginInfo['hash'], $arrCookieOptions);
        }

        return $loginInfo;
    }

    public function registerUser(User $user): array {
        $params = [
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'isAdmin' => (int)$user->isAdmin
        ];

        return $this->auth->register($user->email, $user->password, $user->password, $params);
    }

    public function logout(): bool {
        return $this->auth->logout($this->auth->getCurrentSessionHash());
    }

    public function resetPassword(string $email): array {
        return $this->auth->requestReset($email, true);
    }

}


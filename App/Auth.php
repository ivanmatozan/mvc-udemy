<?php

namespace App;

use App\Model\RememberedLogin;
use App\Model\User;

/**
 * Authentication
 */
class Auth
{
    /**
     * Login the user
     *
     * @param \App\Model\User $user
     * @param bool $rememberMe
     *
     * @return void
     */
    public static function login(User $user, bool $rememberMe)
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;

        if ($rememberMe) {
            if ($user->rememberLogin()) {
                setcookie('remember_me', $user->rememberToken, $user->expiryTimestamp, '/');
            }
        }
    }

    /**
     * Logout the user
     *
     * @return void
     */
    public static function logout()
    {
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        static::forgetLogin();
    }

    /**
     * Remember the originally requested page in the session
     *
     * @return void
     */
    public static function rememberRequestPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }

    /**
     * Return the originally requested page to return to after
     * requiring login, or default to the homepage
     *
     * @return string
     */
    public static function getReturnToPage(): string
    {
        return $_SESSION['return_to'] ?? '/';
    }

    /**
     * Get the current logged in user, from the session or the remember me cookie
     *
     * @return mixed The User model or null if not logged in
     */
    public static function getUser()
    {
        if (isset($_SESSION['user_id'])) {
            return User::findById($_SESSION['user_id']);
        } else {
            return static::loginWithRememberMeCookie();
        }
    }

    /**
     * Login the user from a remembered login cookie
     *
     * @return mixed The User model if login cookie found, null otherwise
     */
    protected static function loginWithRememberMeCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $rememberedLogin = RememberedLogin::findByToken($cookie);

            if ($rememberedLogin && !$rememberedLogin->hasExpired()) {
                $user = $rememberedLogin->getUser();

                static::login($user, false);

                return $user;
            }
        }
    }

    /**
     * Forget remembered login, if present
     *
     * @return void
     */
    protected static function forgetLogin()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $rememberedLogin = RememberedLogin::findByToken($cookie);

            if ($rememberedLogin) {
                $rememberedLogin->delete();
            }

            setcookie('remember_me', '', time() - 3600); // set to expire in the past
        }
    }
}
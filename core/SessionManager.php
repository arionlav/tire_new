<?php
namespace core;

use config\App;

/**
 * Class SessionManager is responsible for handling sessions
 *
 * @package core
 */
class SessionManager
{
    /**
     * @static
     * @var int Time for session alive without actions
     */
    private static $sessionStillAlive = 3000;

    /**
     * @static
     * @var int Time for session id alive
     */
    private static $sessionIdStillAlive = 5;

    /**
     * Start session or continues existing session
     *
     * We can define in the calling script, whether the request a result of user activity
     * And if not - to call the method startSession() with param $isUserActivity = false
     *
     * @static
     * @param string    $nameSession Session name
     * @param bool|true $isUserActivity
     * @return bool
     */
    public static function startSession($nameSession, $isUserActivity = true)
    {
        if (session_id()) {
            // if session was started from anywhere
            return true;
        }

        self::configPhpIni();

        session_name($nameSession);

        if (! session_start()) {
            App::redirect(['security/login', 'e' => 6]);
        }

        $currentTime = time();

        self::checkSessionLifiCycle($currentTime, $isUserActivity);

        self::checkSessionIdLifeCycle($currentTime);

        return true;
    }

    /**
     * Check access
     *
     * @static
     * @return bool
     */
    public static function checkAccess()
    {
        // $_SESSION['login'] and $_SESSION['id'] set after user enter right password
        // and there we set $_SESSION['whoThat'] as $checkStr
        $checkStr = \hash('sha256', $_SESSION['login'] . $_SERVER['HTTP_USER_AGENT'] . $_SESSION['id']);

        if (
            $_SESSION['whoThat'] !== $checkStr
            && ! strpos($_SERVER{'REQUEST_URI'}, 'security/login')
        ) {
            static::destroySession();
            App::redirect(['security/login', 'e' => 1]);
        } else {
            if (strpos($_SERVER{'REQUEST_URI'}, 'admin/') !== false) {
                if (! $_SESSION['privileges'] || $_SESSION['privileges'] < 1) {
                    static::destroySession();
                    App::redirect(['security/login', 'e' => 1]);
                } else {
                    return true;
                }
            } else {
                return true;
            }
        }
    }


    /**
     * Destroy session
     *
     * @static
     */
    public static function destroySession()
    {
        if (session_id()) {
            session_unset();
            setcookie(session_name(), session_id(), time() - 60 * 60 * 24);
            session_destroy();
        }
    }

    /**
     * Set config for php.ini
     *
     * @static
     */
    private static function configPhpIni()
    {
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.hash_function', 'sha256');
        ini_set('session.cookie_lifetime', 0);
    }

    /**
     * Check Session lifecycle
     *
     * @static
     * @param int  $currentTime    Current time
     * @param bool $isUserActivity User activity flag
     * @return bool
     */
    private static function checkSessionLifiCycle($currentTime, $isUserActivity)
    {
        if (self::$sessionStillAlive) {
            if (
                isset($_SESSION['lastActivity'])
                && $currentTime - $_SESSION['lastActivity'] >= self::$sessionStillAlive
            ) {
                static::destroySession();
                App::redirect(['security/login', 'e' => 5]);
            } else {
                if ($isUserActivity) {
                    $_SESSION['lastActivity'] = $currentTime;
                }
            }
        }

        return true;
    }

    /**
     * Check Session lifecycle id
     *
     * @param int $currentTime Current time
     * @return bool
     */
    private static function checkSessionIdLifeCycle($currentTime)
    {
        if (self::$sessionIdStillAlive) {
            if (isset($_SESSION['startTime'])) {
                if ($currentTime - $_SESSION['startTime'] >= self::$sessionIdStillAlive) {
                    session_regenerate_id(true); // change session id

                    $_SESSION['startTime'] = $currentTime;
                }
            } else {
                $_SESSION['startTime'] = $currentTime;
            }
        }

        return true;
    }
}

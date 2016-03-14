<?php
namespace app\models;

use core\helpers\GenerateException;
use core\helpers\Query;

/**
 * Class ModelSecurity provide logic for user login
 *
 * @package app\models
 */
class ModelSecurity
{
    /**
     * Check incoming login and password
     *
     * @param array $post Input values
     * @return array|bool
     */
    public function checkLogin(array $post)
    {
        $user = static::getUser($post['login']);

        if (! empty($user)) {
            $passwordHash = static::getHash($post['password'], $user['salt'], $user['iterationCount']);

            ($passwordHash == $user['password'])
                ? $result = $user // "please, welcome!"
                : $result = false; // password is wrong
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get user by login
     *
     * @param string $login Username
     * @return array
     */
    public static function getUser($login)
    {
        $query = new Query();

        $sth = $query
            ->select([
                'id',
                'login',
                'password',
                'salt',
                'iterationCount',
                'role'
            ])
            ->from('user_users')
            ->whereBindStmt([
                'login' => ':login'
            ])
            ->prepareBindStatement();

        $sth->bindParam(':login', $login);

        return $query->executeBindStmtOne($sth);
    }

    /**
     * Create password hash
     *
     * @param string  $password       Original password
     * @param string  $salt           Salt
     * @param integer $iterationCount The number of iterations
     * @return string Password hash
     */
    public static function getHash($password, $salt, $iterationCount)
    {
        $passwordHash = '';

        if ($iterationCount and $salt != '') {
            for ($i = 0; $i < $iterationCount; $i++) {
                $passwordHash = \hash('sha256', $password . $salt);
            }
        } else {
            GenerateException::getException('Hash password does not create, wrong input value', __CLASS__, __LINE__);
        }

        return $passwordHash;
    }

    /**
     * Check incoming login
     *
     * @param string $login Incoming login
     * @return array|null
     */
    public function checkEnterLogin($login)
    {
        $host = null;

        if (! preg_match("/^[a-zA-Z0-9]+$/", $login)) {
            $host = ['security/login', 'e' => 3];
        } elseif (strlen($login) < 3 or strlen($login) > 30) {
            $host = ['security/login', 'e' => 4];
        }

        return $host;
    }
}

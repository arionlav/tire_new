<?php
namespace app\models;

use core\helpers\GenerateException;
use core\helpers\Query;

/**
 * Class ModelAdmin provide logic for handling actions in admin panel
 *
 * @package app\models
 */
class ModelAdmin
{
    /**
     * Get all user roles from database
     *
     * @return array All user roles from database
     * @throws GenerateException
     */
    public function getRole()
    {
        $query = new Query();

        $roles = $query
            ->select([
                'idRole',
                'nameRole'
            ])
            ->from('user_role')
            ->all();

        if (empty($roles)) {
            GenerateException::getException('Roles does not found, check SQL syntax', __CLASS__, __LINE__);
        }

        return $roles;
    }

    /**
     * Insert new user
     *
     * @param array $post      Input values
     * @param array $roleArray All user roles from database
     * @return string
     * @throws GenerateException
     */
    public function insertUser($post, $roleArray)
    {
        if ($post['login'] and $post['password']) {
            $checkLogin = $this->checkLogin($post['login']);
        } else {
            $returnMsg = 'Задайте имя пользователя и пароль';

            return $returnMsg;
        }

        if ($checkLogin === true) {
            $roleName     = $this->getRoleName($roleArray, $post['idRole']);
            $resultInsert = $this->insertNewUser($post['login'], $post['password'], $post['idRole']);

            if ($resultInsert === false) {
                GenerateException::getException('Insert new user impossible, check values.', __CLASS__, __LINE__);
            }

            $returnMsg = "Пользователь \"{$post['login']}\" со статусом \"{$roleName}\" добавлен в базу!";
        } else {
            $returnMsg = 'Такой пользователь уже есть! Задайте другое имя';
        }

        return $returnMsg;
    }

    /**
     * Check incoming username to originality
     *
     * @param string $login Incoming username
     * @return bool
     */
    private function checkLogin($login)
    {
        $loginsFromDb = $this->getAllLogin();

        $result = true;

        foreach ($loginsFromDb as $l) {
            if ($l['login'] == $login) {
                $result = false;
                break;
            }
        }

        return $result;
    }

    /**
     * Get all username
     *
     * @return array All username from database
     * @throws GenerateException
     */
    public function getAllLogin()
    {
        $query = new Query();

        $loginAll = $query
            ->select([
                'login'
            ])
            ->from('user_users')
            ->all();

        if (empty($loginAll)) {
            GenerateException::getException('Any loginAll does not found, check SQL syntax', __CLASS__, __LINE__);
        }

        return $loginAll;
    }

    /**
     * Get user role by role id
     *
     * @param array  $roleArray All user roles
     * @param string $role      User role id
     * @return string Role name
     * @throws GenerateException
     */
    private function getRoleName($roleArray, $role)
    {
        $roleName = '';

        foreach ($roleArray as $ra) {
            if ($ra['idRole'] == $role) {
                $roleName = $ra['nameRole'];
                break;
            }
        }

        if (! $roleName) {
            GenerateException::getException('We can\'t take role name. Check input value.');
        }

        return $roleName;
    }

    /**
     * Insert new user
     *
     * @param string $login    Incoming login
     * @param string $password Incoming password
     * @param string $role     Id role
     * @return true|GenerateException
     */
    private function insertNewUser($login, $password, $role)
    {
        $iCount = rand(40, 100);
        $salt   = uniqid(mt_rand(), true);

        $passwordHash = ModelSecurity::getHash($password, $salt, $iCount);

        $query = new Query();

        $result = $query
            ->insertInto('user_users', [
                'login'          => ':login',
                'password'       => ':password',
                'salt'           => ':salt',
                'iterationCount' => ':iterationCount',
                'role'           => ':role'
            ])
            ->prepareBindStatement()
            ->execute([
                'login'          => $login,
                'password'       => $passwordHash,
                'salt'           => $salt,
                'iterationCount' => $iCount,
                'role'           => $role,
            ]);

        return $result;
    }

    /**
     * Change password
     *
     * @param array $post Input values
     * @return string
     */
    public function changePassword($post)
    {
        $userLogin   = $_SESSION['login'];
        $oldPass     = $post['oldPass'];
        $newPass     = $post['newPass'];
        $confirmPass = $post['confirmNewPass'];
        $returnMsg   = '';

        if ($newPass and $newPass == $confirmPass) {
            $iCount  = rand(40, 100);
            $salt    = uniqid(mt_rand(), true);
            $user    = ModelSecurity::getUser($userLogin);
            $oldSalt = $user['salt'];

            $oldIterationCount = $user['iterationCount'];
            $oldPassRight      = $user['password'];

            $oldPassHash = ModelSecurity::getHash($oldPass, $oldSalt, $oldIterationCount);
            // If old password is good
            if ($oldPassHash === $oldPassRight) {
                if ($newPass) {
                    $passHash = ModelSecurity::getHash($newPass, $salt, $iCount);
                    if ($this->insertNewPassword($userLogin, $passHash, $salt, $iCount)) {
                        $returnMsg = 'Пароль изменен!';
                    }
                } else {
                    $returnMsg = 'Введите новый пароль. Без него нельзя.';
                }
            } else {
                $returnMsg = 'Старый пароль введен неправильно. Попробуйте еще раз.';
            }
        } else {
            $returnMsg = 'Ошибка при повторном вводе пароля. Попробуйте еще раз.';
        }

        return $returnMsg;
    }

    /**
     * Insert new password
     *
     * @param string $login          Username
     * @param string $passwordHash   Password hash
     * @param string $salt           Salt for password
     * @param string $iterationCount The number of iteration
     * @return bool
     */
    private function insertNewPassword($login, $passwordHash, $salt, $iterationCount)
    {
        $query = new Query();

        $sth = $query
            ->updateBindParam('user_users', [
                'password'       => ':password',
                'salt'           => ':salt',
                'iterationCount' => ':iterationCount'
            ])
            ->whereBindStmt([
                'login' => ':login'
            ])
            ->prepareBindStatement();

        $result = $sth
            ->execute([
                'password'       => $passwordHash,
                'salt'           => $salt,
                'iterationCount' => $iterationCount,
                'login'          => $login
            ]);

        return $result;
    }

    /**
     * Remove user
     *
     * @param array $post Input values
     * @return string Message with result
     * @throws GenerateException
     */
    public function deleteUser($post)
    {
        $userLogin = $_SESSION['login'];
        $login     = $post['login'];
        if ($userLogin != $login) {
            $resultDeleting = $this->deleteUserByLogin($login);

            if ($resultDeleting === false) {
                GenerateException::getException('Deleting user wrong', __CLASS__, __LINE__);
            }

            $returnMsg = "Пользователь \"$login\" удален!";
        } else {
            $returnMsg = 'Не надо себя удалять. Остальных, пожалуйста!';
        }

        return $returnMsg;
    }

    /**
     * Remove user by login
     *
     * @param string $login Username
     * @return bool
     */
    private function deleteUserByLogin($login)
    {
        $query = new Query();

        $result = $query
            ->deleteFrom('user_users')
            ->whereBindStmt([
                'login' => ':login'
            ])
            ->prepareBindStatement()
            ->execute(['login' => $login]);

        return $result;
    }

    /**
     * Change the user role for selected user
     *
     * @param array $post      Input values
     * @param array $roleArray All user roles
     * @return string Message with result
     * @throws GenerateException
     */
    public function changeRole($post, $roleArray)
    {
        $userLogin   = $_SESSION['login'];
        $login       = $post['login'];
        $newRole     = $post['idRole'];
        $newRoleName = '';

        if ($userLogin != $login) {
            $result = $this->changeRoleByLogin($login, $newRole);

            if ($result === false) {
                GenerateException::getException('Change user role wrong', __CLASS__, __LINE__);
            }

            if (! empty($roleArray)) {
                foreach ($roleArray as $arr) {
                    if ($arr['idRole'] == $newRole) {
                        $newRoleName = $arr['nameRole'];
                    }
                }
            }

            $returnMsg = "Теперь пользователь \"$login\" получил должность \"$newRoleName\"";
        } else {
            $returnMsg = 'Себя понижать в должности не нужно, Вы уже самый главный. Других, пожалуйста.';
        }

        return $returnMsg;
    }

    /**
     * @param string $login   Username
     * @param string $newRole New user role id
     * @return bool
     */
    private function changeRoleByLogin($login, $newRole)
    {
        $query = new Query();

        $sth = $query
            ->updateBindParam('user_users', [
                'role' => ':role'
            ])
            ->whereBindStmt([
                'login' => ':login'
            ])
            ->prepareBindStatement();

        $result = $sth
            ->execute([
                'role'  => $newRole,
                'login' => $login
            ]);

        return $result;
    }
}

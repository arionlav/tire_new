<?php
namespace app\controllers;

use config\App;
use core\Controller;
use app\models\ModelAdmin;

/**
 * Class AdminController is responsible for handling admin panel actions
 *
 * @package app\controllers
 */
class AdminController extends Controller
{
    /**
     * @inheritdoc
     */
    function __construct()
    {
        parent::__construct();
        $this->model = new ModelAdmin();
    }

    /**
     * Main page on admin panel
     */
    public function actionIndex()
    {
        $this->view->render('index');
    }

    /**
     * Add user
     */
    public function actionAddUser()
    {
        $roleArray = $this->model->getRole();
        $returnMsg = '';

        if ($post = App::post()) {
            $returnMsg = $this->model->insertUser($post, $roleArray);
        }

        $this->view->render('addUser', [
            'roleArray' => $roleArray,
            'returnMsg' => $returnMsg
        ]);
    }

    /**
     * Change password
     */
    public function actionChangePassword()
    {
        $returnMsg = '';

        if ($post = App::post()) {
            $returnMsg = $this->model->changePassword($post);
        }

        $this->view->render('changePassword', [
            'returnMsg' => $returnMsg
        ]);
    }

    /**
     * Delete users
     */
    public function actionDeleteUser()
    {
        $returnMsg = '';

        if ($post = App::post()) {
            $returnMsg = $this->model->deleteUser($post);
        }

        $users = $this->model->getAllLogin();

        $this->view->render('deleteUser', [
            'users'     => $users,
            'returnMsg' => $returnMsg
        ]);
    }

    /**
     * Change the role for user
     */
    public function actionChangeRole()
    {
        $returnMsg = '';
        $users     = $this->model->getAllLogin();
        $roleArray = $this->model->getRole();

        if ($post = App::post()) {
            $returnMsg = $this->model->changeRole($post, $roleArray);
        }

        $this->view->render('changeRole', [
            'users'     => $users,
            'returnMsg' => $returnMsg,
            'roleArray' => $roleArray
        ]);
    }
}

<?php
namespace app\controllers;

use core\Controller;
use app\models\ModelSecurity;
use config\App;

/**
 * Class SecurityController is responsible for handling user's login
 *
 * @package app\controllers
 */
class SecurityController extends Controller
{
    /**
     * @inheritdoc
     */
    function __construct()
    {
        parent::__construct();
        $this->model = new ModelSecurity();
    }

    /**
     * Login page
     *
     * @param array $params Params from GET request
     */
    public function actionLogin($params)
    {
        if ($post = App::post()) {
            $host = $this->model->checkEnterLogin($post['login']);

            if ($host == '') {
                $user = $this->model->checkLogin($post);

                if ($user) {
                    $host = ['site/index'];

                    // set the session variables if user exists
                    $_SESSION['privileges'] = $user['role'];
                    $_SESSION['login']      = $post['login'];
                    $_SESSION['id']         = \hash('sha256', $user['salt']);
                    $_SESSION['whoThat']    = \hash('sha256',
                        $post['login'] . $_SERVER['HTTP_USER_AGENT'] . $_SESSION['id']);

                } else {
                    $host = ['security/login', 'e' => 2];
                }
            }

            App::redirect($host);
        }

        $this->view->render('login', [
            'params' => $params
        ]);
    }
}

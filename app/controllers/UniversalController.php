<?php
namespace app\controllers;

use app\models\handlers\Helper;
use app\models\handlers\Universal;
use app\models\ModelGeneral;
use core\Controller;
use app\models\ModelSite;
use config\App;

/**
 * Class UniversalController is responsible of creating new handler
 *
 * @package app\controllers
 */
class UniversalController extends Controller
{
    /**
     * @inheritdoc
     */
    function __construct()
    {
        parent::__construct();
        $this->model = new ModelSite();
    }

    /**
     * The main page
     */
    public function actionCreateHandler()
    {
        $items = ModelGeneral::showItems();
        $lists = Helper::getLists();

        $this->view->render('create', [
            'items' => $items,
            'lists' => $lists
        ]);
    }

    /**
     * Create and run the new handler
     */
    public function actionCreateHandlerSuccess()
    {
        $post = App::post();

        $change = $this->model->getPriceChange($post);

        $obj = new Universal();

        $obj->run(-1, $change, serialize($post));

        $requestList    = $this->model->getNameListById(Helper::$list);
        $postShowResult = $this->model->createPostArray(Helper::$list, $requestList);

        // set the session variable for searching by price list id
        $_SESSION['post'] = serialize($postShowResult);

        App::redirect(['search/result', 'mode' => 2]);
    }
}

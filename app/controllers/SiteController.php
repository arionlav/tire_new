<?php
namespace app\controllers;

use app\models\handlers\Helper;
use app\models\ModelGeneral;
use core\Controller;
use app\models\ModelSite;
use config\App;
use core\helpers\GenerateException;

/**
 * Class SiteController is responsible for handling index page and adding price lists
 *
 * @package app\controllers
 */
class SiteController extends Controller
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
     * Main page on the site
     */
    public function actionIndex()
    {
        $this->view->render('index');
    }

    /**
     * Select excel file with price list
     */
    public function actionAddPrice()
    {
        $items = ModelGeneral::showItems();
        $lists = Helper::getLists();

        $this->view->render('addPrice', [
            'items' => $items,
            'lists' => $lists
        ]);
    }


    /**
     * Create needed object and run method run() for handling price list
     * Then, redirect to the 'search/result' route (use mode parameter = 2)
     *
     * @throws GenerateException
     */
    public function actionAddPriceSuccess()
    {
        if ($post = App::post()) {
            $priceChange = $this->model->getPriceChange($post);

            if ($this->model->getClassName($post['idList'])) {
                $object = new $this->model->method($this->model->idList, $priceChange, $this->model->post);

                $object->run($this->model->idList, $priceChange, $this->model->post);

                $requestList    = $this->model->getNameListById($this->model->idList);
                $postShowResult = $this->model->createPostArray($this->model->idList, $requestList);

                // set the session variable for searching by price list id
                $_SESSION['post'] = serialize($postShowResult);

                $this->model->dropAllFilesFromFolder();

                App::redirect(['search/result', 'mode' => 2]);
            } else {
                GenerateException::getException('Method does not define', __CLASS__, __LINE__);
            }
        } else {
            GenerateException::getException('Use only POST method', __CLASS__, __LINE__);
        }
    }

    /**
     * Show some error for users
     *
     * @param array $params Params from GET request
     */
    public function actionError($params)
    {
        $this->view->render('error', [
            'errorMsg' => $params['e']
        ]);
    }
}

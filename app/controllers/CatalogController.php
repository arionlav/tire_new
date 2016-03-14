<?php
namespace app\controllers;

use app\models\handlers\Helper;
use app\models\ModelGeneral;
use core\Controller;
use app\models\ModelCatalog;
use config\App;
use core\helpers\GenerateException;

/**
 * Class CatalogController is responsible for handling actions for modification default data in database
 *
 * @package app\controllers
 */
class CatalogController extends Controller
{
    /**
     * @inheritdoc
     */
    function __construct()
    {
        parent::__construct();
        $this->model = new ModelCatalog();
    }

    /**
     * Main page in section
     */
    public function actionIndex()
    {
        $items = ModelGeneral::showItems();

        $this->view->render('index', [
            'items' => $items
        ]);
    }

    /**
     * Apply changes and redirect to the route 'catalog/index'
     *
     * @throws GenerateException
     */
    public function actionAccept()
    {
        if (! $post = App::post()) {
            GenerateException::getException('Method doesn\'t POST');
        }

        if (is_array($post) and ! empty($post)) {
            if ($post['action'] == 1) {
                $this->model->insertData($post);
            } elseif ($post['action'] == 2) {
                $this->model->deleteData($post);
            } else {
                GenerateException::getException('Unknown parameter value for \'action\' input at POST');
            }

            App::redirect(['catalog/index']);
        } else {
            GenerateException::getException('Wrong array POST');
        }
    }

    /**
     * Show the course
     * When the form was sent, change the course and all positions which were load in USD
     */
    public function actionCourse()
    {
        if ($post = App::post()) {
            $courseOld = Helper::getCourse();

            $this->model->truncateCourseTable('course');
            $this->model->insertNewCourse($post['course']);
            $this->model->updatePriceInUsd($post['course'], $courseOld);
        }

        $courseNow = Helper::getCourse();

        $this->view->render('course', [
            'courseNow' => $courseNow
        ]);
    }
}

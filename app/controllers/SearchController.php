<?php
namespace app\controllers;

use app\models\handlers\LoadExcel;
use app\models\ModelGeneral;
use core\Controller;
use app\models\ModelSearch;
use config\App;
use core\helpers\GenerateException;

/**
 * Class SearchController is responsible for handling actions at the search in database
 *
 * @package app\controllers
 */
class SearchController extends Controller
{
    /**
     * @inheritdoc
     */
    function __construct()
    {
        parent::__construct();
        $this->model = new ModelSearch();
    }

    /**
     * The main page on the search menu
     */
    public function actionIndex()
    {
        $items = ModelGeneral::showItems();

        $this->view->render('index', [
            'items' => $items
        ]);
    }

    /**
     * Show search result
     *
     * @param array $params Params from GET request
     */
    public function actionResult(array $params)
    {
        $post = $this->model->getPost($params);

        $pageDatatables = $this->model->checkForNull($params['pageTables']);
        $page           = $this->model->checkForNull($params['page']);
        $tires          = $this->model->getSearchingTires($post);
        $arrayData      = $this->model->getArraysData($tires);

        $tiresChunk = array_chunk($tires, App::$rowOnSearchPage);

        $this->view->render('result', [
            'tires'          => $tires,
            'page'           => $page,
            'mode'           => $params['mode'],
            'pageDatatables' => $pageDatatables,
            'params'         => $params,
            'post'           => $post,
            'arrayData'      => $arrayData,
            'countTires'     => count($tires),
            'tiresChunk'     => $tiresChunk
        ]);
    }

    /**
     * Load the selected position in modal window for modification
     *
     * @param null|array $params Params from GET request
     */
    public function actionModify($params)
    {
        $tire = $this->model->selectTiresFromDB($params['id']);

        $items = ModelGeneral::showItems();

        (is_null($params['page']))
            ? $page = -1
            : $page = $params['page'];

        $this->view->renderAjax('modify', [
            'items'          => $items,
            'tire'           => $tire,
            'id'             => $params['id'],
            'page'           => $page,
            'pageDatatables' => $params['pageDatatables']
        ]);
    }

    /**
     * Apply changes for selected row and redirect to the 'search/result' route
     *
     * @throws GenerateException
     */
    public function actionModifySuccess()
    {
        if ($post = App::post()) {
            $this->model->insertNewValue($post);

            ($post['page'] == -1)
                ? App::redirect([
                    'search/result',
                    'mode'       => 2,
                    'pageTables' => $post['pageDatatables']
                ])
                : App::redirect([
                    'search/result',
                    'pageTables' => $post['pageDatatables'],
                    'mode'       => 2,
                    'page'       => $post['page']
                ]);
        } else {
            GenerateException::getException('Use only POST method', __CLASS__, __LINE__);
        }
    }

    /**
     * Delete selected row and redirect to the 'search/result' route
     *
     * @param null|array $params Params from GET request
     * @throws GenerateException
     */
    public function actionDeleteRow($params)
    {
        $this->model->deleteRow($params['id']);

        ($params['page'] == -1)
            ? App::redirect([
                'search/result',
                'mode' => 2,
                'pageTables' => $params['pageDatatables']
            ])
            : App::redirect([
                'search/result',
                'pageTables' => $params['pageDatatables'],
                'mode' => 2,
                'page' => $params['page']
            ]);
    }

    /**
     * Load search result to .xlsx file
     */
    public function actionLoadExcel()
    {
        $post  = unserialize($_SESSION['post']);
        $tires = $this->model->getSearchingTires($post);

        LoadExcel::load($tires);
    }
}

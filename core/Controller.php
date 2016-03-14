<?php
namespace core;

/**
 * Class Controller
 *
 * @package core
 */
class Controller
{
    /**
     * @var object Model
     */
    public $model;
    /**
     * @var View
     */
    public $view;

    /**
     * Create view object as variable $this->view
     * Create model object as variable $this->model in overridden controller class
     */
    function __construct()
    {
        $this->view = new View();
    }
}

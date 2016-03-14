<?php
namespace core;

use \config\App;
use core\helpers\GenerateException;

/**
 * Class Route is router requests
 *
 * @package core
 */
class Route
{
    /**
     * @static
     * @var string Default controller name
     */
    public static $controllerName = 'Site';

    /**
     * @static
     * Create an object of the correct class and run correct method
     */
    public static function start()
    {
        // Controller and action by default
        $controller = strtolower(self::$controllerName);
        $action     = 'index';
        $runParams  = null;

        // Explode by slash request URI
        $routes = explode('/', $_SERVER['REQUEST_URI']);

        // If our project is in the subfolder, the controller at explode method is shifted on +1
        (App::$pathToRoot == '/' . $routes[1]) ? $i = 2 : $i = 1;

        // Get controller name
        if (! empty($routes[$i])) {
            $controller = self::$controllerName = $routes[$i];
        }

        // Get action name and params if it is
        if (! empty($routes[$i + 1])) {
            if (! strpos($routes[$i + 1], '?')) {
                $action = $routes[$i + 1];
            } else {
                $actionArray  = explode('?', $routes[$i + 1]);
                $action       = $actionArray[0];
                $actionParams = explode('&', $actionArray[1]);
                $params       = [];

                foreach ($actionParams as $a) {
                    $params[] = explode('=', $a);
                }

                foreach ($params as $p) {
                    $runParams[$p[0]] = $p[1];
                }
            }
        }

        // Add prefixes and convert in camelCase
        $action     = static::createCamelName($action);
        $controller = static::createCamelName($controller);

        $controller     = App::$controllersNamespace . $controller . 'Controller';
        $action         = 'action' . $action;
        $controllerFile = $_SERVER{'DOCUMENT_ROOT'} . App::$pathToRoot . str_replace('\\', '/', $controller) . ".php";

        // Create controller object and run action
        if (method_exists($controller, $action) && file_exists($controllerFile)) {
            $controllerObject = new $controller;

            (is_null($runParams))
                ? $controllerObject->$action()
                : $controllerObject->$action($runParams);
        } else {
            GenerateException::getException('Route wrong!');
        }
    }

    /**
     * Create from input string camelCase type string, explode by '-'
     *
     * @static
     * @param string $name Incoming string
     * @return string in camelCase type
     */
    public static function createCamelName($name)
    {
        if (strstr($name, '-')) {
            $strBefore = ucfirst(strstr($name, '-', true));
            $strAfter  = ucfirst(ltrim(strstr($name, '-'), '-'));
            $newName   = $strBefore . $strAfter;

            return static::createCamelName($newName);
        }

        return ucfirst($name);
    }
}

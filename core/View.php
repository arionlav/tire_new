<?php
namespace core;

use config\App;
use core\helpers\GenerateException;

/**
 * Class View
 * Control creating the view
 *
 * @package core
 */
class View
{
    /**
     * Render the view
     *
     * @param string      $content       File with view for request. Location is app/view/{name controller in
     *                                   lowercase}/{$content}
     * @param null|array  $data          (name-value pairs) variables that should be made available in the view
     * @param null|string $otherViewRoot If need use view from other root
     * @param string      $template      Path to main template
     * @throws GenerateException If view file does not exist
     */
    public function render($content, array $data = null, $otherViewRoot = null, $template = '/app/views/layouts/main')
    {
        if (is_file($pathToFile = $_SERVER{'DOCUMENT_ROOT'} . App::$pathToRoot . $template . '.php')) {
            if (is_array($data) and ! empty($data)) {
                extract($data);
            }

            require_once $pathToFile;
        } else {
            GenerateException::getException('Check path ' . $pathToFile . ', file does not exists');
        }
    }

    /**
     * Render view in Ajax request without main.php, as is
     *
     * @param string     $content File with view for request. Location is app/view/{name controller in
     *                            lowercase}/{$content}
     * @param null|array $data    (name-value pairs) variables that should be made available in the view
     * @throws GenerateException If view file does not exist
     */
    public function renderAjax($content, $data = null)
    {
        $pathToView = strtolower(Route::$controllerName);

        if (is_file($pathToFile =
            $_SERVER{'DOCUMENT_ROOT'} . App::$pathToRoot . '/app/views/' . $pathToView . '/' . $content . '.php')) {
            if (is_array($data)) {
                extract($data);
            }

            require_once $pathToFile;
        } else {
            GenerateException::getException('Check path ' . $pathToFile . ', file does not exists');
        }
    }


    /**
     * Return title for page, which define in view file as variable $title
     *
     * @param string $content File with view for request. Location is app/view/{name controller in
     *                        lowercase}/{$content}
     * @return string
     */
    public function getTitle($content)
    {
        $filePath = 'app/views/' . Route::$controllerName . '/' . $content . '.php';
        if (is_file($filePath)) {
            $fileStr = file_get_contents($filePath);
        } else {
            return App::$defaultTitle;
        }

        preg_match('|\$title.+?=(.+);.+|u', $fileStr, $m);

        $title = trim(strtr($m[1], ['\'' => '', '"' => '']));

        return $title;
    }
}

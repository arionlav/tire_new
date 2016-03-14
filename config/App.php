<?php
namespace config;

use core\helpers\GenerateException;

/**
 * Class App contain application params
 *
 * @package config
 */
class App
{
    /**
     * @static
     * @var Config Instance of application
     */
    public static $app;

    /**
     * @static
     * @var string Path to root folder
     */
    public static $pathToRoot = '/tire_new';

    /**
     * @static
     * @var string Path for loading price lists
     */
    public static $pathToLoadFiles = 'uploads';

    /**
     * @static
     * @var string Namespace for controllers
     */
    public static $controllersNamespace = '\app\controllers\\';

    /**
     * @static
     * @var string Count rows on the search result page
     */
    public static $rowOnSearchPage = 500;

    /**
     * @static
     * @var string Default tag title
     */
    public static $defaultTitle = 'Обработчик прайсов';

    /**
     * @var string Mysql. User name
     */
    const DB_USER = 'root';

    /**
     * @var string Mysql. Password
     */
    const DB_PASS = '';

    /**
     * @var string Mysql. Host
     */
    const DB_HOST = 'localhost';

    /**
     * Create URL.
     * Example: from App::url(['security/login', 'param' => 2, 'id' => 11], 'content')
     * URL: {self::$pathToRoot}/security/login?param=2&id=11#content
     *
     * @static
     * @param array       $urlEnter Route params
     * @param null|string $hash     If need add hash to link
     * @return string URL
     */
    public static function url(array $urlEnter, $hash = null)
    {
        $urlFinal = '';

        $i = 0;
        foreach ($urlEnter as $uKey => $uVal) {
            if ($uKey) {
                if ($i == 0) {
                    $urlFinal .= '?' . $uKey . '=' . $uVal;
                    $i++;
                } else {
                    $urlFinal .= '&' . $uKey . '=' . $uVal;
                }
            } else {
                $url      = explode('/', $urlEnter[0]);
                $urlFinal = self::$pathToRoot . '/' . $url[0] . '/' . $url[1];
            }
        }

        if (! is_null($hash)) {
            $urlFinal .= '#' . $hash;
        }

        return $urlFinal;
    }


    /**
     * Redirect on the page $host
     * Example: From App::redirect(['security/login', 'param' => 2, 'id' => 11]);
     * Redirect to: {self::$pathToRoot}/security/login?param=2&id=11
     *
     * @param array $host Route params
     */
    public static function redirect(array $host)
    {
        $url = static::url($host);
        header('Location:' . $url);
        exit;
    }

    /**
     * Get $_POST array
     *
     * @static
     * @return false|array $_POST
     * @throws GenerateException
     */
    public static function post()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST)) {
                GenerateException::getException('Method is post, but Post array are empty. Something wrong.');
            }

            $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

            return $post;
        } else {
            return false;
        }
    }
}

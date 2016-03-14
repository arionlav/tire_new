<?php

/**
 * Class Autoloader
 * Autoload classes
 */
class Autoloader
{
    /**
     * @static
     * @param string $className Class name
     * @return bool
     */
    static public function loader($className)
    {
        $filename = str_replace('\\', '/', $className) . ".php";

        if (file_exists($filename)) {
            require_once $filename;

            if (class_exists($className)) {
                return true;
            }
        }
        return false;
    }
}

spl_autoload_register('Autoloader::loader');
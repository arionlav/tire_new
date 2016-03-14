<?php
namespace core\helpers;

/**
 * Class GenerateException
 */

class GenerateException extends \Exception
{
    /**
     * @param string $message Exception message
     * @param string $class   Class name where was exception
     * @param string $line    Number line where was exception
     * @throws \Exception
     */
    public static function getException($message, $class = '', $line = '')
    {
        if ($class) {
            $message .= '. Class: ' . $class;
        }
        if ($line) {
            $message .= ' Line: ' . $line;
        }
        try {
            throw new \Exception($message);
        } catch (\Exception $e) {
            echo "<pre>$e</pre>";
        }

        exit;
    }
}

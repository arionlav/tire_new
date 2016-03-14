<?php
namespace app\models;

use app\models\handlers\Helper;
use core\Model;

/**
 * Class ModelGeneral has general methods for application
 *
 * @package app\models
 */
class ModelGeneral extends Model
{
    /**
     * Create array with default value from database for tables $arrKey and table list
     *
     * @static
     * @return array from database with default value
     */
    public static function showItems()
    {
        $items  = [];
        $arrKey = [
            'brand',
            'width',
            'height',
            'radius',
            'indexPower',
            'indexSpeed',
            'camera',
            'season',
            'group',
            'isIt',
            'money'
        ];

        if (! empty($arrKey)) {
            foreach ($arrKey as $key) {
                $arr         = Helper::getValueFromDB($key, true);
                $items[$key] = static::populateItems($arr);
            }
        }

        $arr           = Helper::getLists(true);
        $items['list'] = static::populateItems($arr);

        return $items;
    }

    /**
     * Prepare beautiful array
     *
     * @static
     * @param array $arr Array in natural language mode sorting with standard value for table
     * @return array Ready to concat array with main array in static::showItems()
     */
    private static function populateItems(array $arr)
    {
        $result = [];
        if (! empty($arr)) {
            foreach ($arr as $aKey => $aVal) {
                $result[$aKey] = $aVal;
            }
        }

        return $result;
    }
}

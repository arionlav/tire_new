<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;
use core\helpers\Query;

require_once $_SERVER{'DOCUMENT_ROOT'} . App::$pathToRoot . '/core/outsider/PHPExcel/Classes/PHPExcel.php';

/**
 * Class Helper provide general methods for handling price lists
 *
 * @package app\models\handlers
 */
class Helper
{
    /**
     * @static
     * @var string Price list id
     */
    public static $list;
    /**
     * @var string Full string
     */
    public $str;
    /**
     * @var string Model
     */
    public $model;
    /**
     * @var string Width id
     */
    public $width;
    /**
     * @var string Height id
     */
    public $height;
    /**
     * @var string Radius id
     */
    public $radius;
    /**
     * @var string Index power id
     */
    public $indexPower;
    /**
     * @var string Index speed id
     */
    public $indexSpeed;
    /**
     * @var string Brand id
     */
    public $brand;
    /**
     * @var string Camera id
     */
    public $camera;
    /**
     * @var string Season id
     */
    public $season;
    /**
     * @var string Tires availability
     */
    public $isIt;
    /**
     * @var string Price
     */
    public $price;
    /**
     * @var string Price movement
     */
    public $priceMove;
    /**
     * @var string Currency id
     */
    public $money;
    /**
     * @var string Tire group id
     */
    public $group;
    /**
     * @var string Comment
     */
    public $other;
    /**
     * @var string The date of manufacture
     */
    public $year;
    /**
     * @var array Prices before adding new price list
     */
    public $oldPrice;
    /**
     * @var array Contain all rows from excel file
     */
    public $file2array;
    /**
     * @var string The course
     */
    public $course;
    /**
     * @var null|\PDOStatement Prepare statement to insert new rows in table 'tires'
     */
    private $sth = null;

    /**
     * Load excel file on the server and return array with all rows from
     *
     * @param string $path       Path for loading price lists
     * @param string $listOnFile The list name in the file
     * @return array
     * @throws GenerateException
     */
    public function loadExcelFile($path, $listOnFile = '')
    {
        if (! is_uploaded_file($_FILES['userfile']['tmp_name'])) {
            GenerateException::getException('File does not load', __CLASS__, __LINE__);
        }

        $tmp_name = $_FILES['userfile']['tmp_name'];
        $name     = $_FILES['userfile']['name'];
        $name     = $this->translit($name);

        move_uploaded_file($tmp_name, "$path/$name");

        // file to array
        $objPHPExcel = \PHPExcel_IOFactory::load("$path/$name");
        $fileToArray = [];

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $worksheetTitle     = $worksheet->getTitle();
            $highestRow         = $worksheet->getHighestRow();
            $highestColumn      = $worksheet->getHighestColumn();
            $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);

            if ($worksheetTitle == $listOnFile || $listOnFile == '') {
                for ($row = 1; $row <= $highestRow; ++$row) {
                    for ($col = 0; $col < $highestColumnIndex; ++$col) {
                        $cell = $worksheet->getCellByColumnAndRow($col, $row);
                        $val  = $cell->getFormattedValue();

                        $fileToArray[$row][] = $val;
                    }
                }
            }
        }

        if (! $fileToArray) {
            GenerateException::getException('Creating array from file wrong', __CLASS__, __LINE__);
        }

        return $fileToArray;
    }

    /**
     * Get prices before replace them
     *
     * @return array
     */
    public function getOldPrice()
    {
        $query = new Query();

        $oldPrice = $query
            ->select([
                'str',
                'price'
            ])
            ->from('tires')
            ->where(['list' => self::$list])
            ->all();

        return $oldPrice;
    }

    /**
     * Remove all rows from database with downloaded price list, before adding new version this price list
     *
     * @return true
     */
    public function deleteCurrentList()
    {
        $query = new Query();

        $query
            ->deleteFrom('tires')
            ->where([
                'list' => self::$list
            ])
            ->executeQuery();

        return true;
    }

    /**
     * Remove header rows
     *
     * @param int $count Count removable rows
     * @return bool
     */
    public function deleteUnusedRows($count)
    {
        if (is_array($this->file2array)) {
            for ($i = 1; $i <= $count; $i++) {
                unset($this->file2array[$i]);
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * Insert new rows in the database
     *
     * @return true
     */
    public function insertRows()
    {
        if (! $this->model) {
            return false;
        }

        if (is_null($this->sth)) {
            $this->sth = $this->prepareSql();
        }

        $resultInsert = $this->sth
            ->execute([
                'list'       => self::$list,
                'str'        => $this->str,
                'width'      => $this->width,
                'height'     => $this->height,
                'radius'     => $this->radius,
                'indexPower' => $this->indexPower,
                'indexSpeed' => $this->indexSpeed,
                'brand'      => $this->brand,
                'model'      => $this->model,
                'camera'     => $this->camera,
                'season'     => $this->season,
                'isIt'       => $this->isIt,
                'price'      => $this->price,
                'priceMove'  => $this->priceMove,
                'money'      => $this->money,
                'gr'         => $this->group,
                'other'      => $this->other,
                'year'       => $this->year
            ]);

        if ($resultInsert === false) {
            GenerateException::getException('Insert rows failed, check values', __CLASS__, __LINE__);
        }

        return true;
    }

    /**
     * Prepare PDO Statement for all inserting rows
     *
     * @return \PDOStatement
     */
    private function prepareSql()
    {
        $query = new Query();

        $prepareSql = $query
            ->insertInto('tires', [
                'list'       => ':list',
                'str'        => ':str',
                'width'      => ':width',
                'height'     => ':height',
                'radius'     => ':radius',
                'indexPower' => ':indexPower',
                'indexSpeed' => ':indexSpeed',
                'brand'      => ':brand',
                'model'      => ':model',
                'camera'     => ':camera',
                'season'     => ':season',
                'isIt'       => ':isIt',
                'price'      => ':price',
                'priceMove'  => ':priceMove',
                'money'      => ':money',
                'gr'         => ':gr',
                'other'      => ':other',
                'year'       => ':year'
            ])
            ->prepareBindStatement();

        return $prepareSql;
    }

    /**
     * Change price in agreement with input price factor
     *
     * @param array $priceChange Price change factor
     * @return float|int|string
     */
    public function priceChange(array $priceChange)
    {
        $price = 0;
        if ($priceChange['priceMethod'] == 'oneForAll') {
            $price = $this->price * $priceChange['oneForAllText'];
        } elseif ($priceChange['priceMethod'] == 'custom') {
            $price = $this->price * $priceChange['allOtherText'];

            foreach ($priceChange as $pc) {
                if (! is_array($pc)) {
                    continue;
                }
                if ($pc['forWho'] == 'brand') {
                    if ($this->brand == $pc['forWhoId']) {
                        $price = $this->price * $pc['value'] / $priceChange['allOtherText'];
                        break;
                    }
                } elseif ($pc['forWho'] == 'group') {
                    if ($this->group == $pc['forWhoId']) {
                        $price = $this->price * $pc['value'] / $priceChange['allOtherText'];
                        break;
                    }
                }
            }
        }

        return $price;
    }

    /**
     * Get values 'id{$key}' and 'name{$key}' (for example idBrand and nameBrand) from database
     *
     * @static
     * @param string $key     Index value (for example Brand, Width...)
     * @param bool   $natSort For using natural mode
     * @return array
     * @throws GenerateException
     */
    public static function getValueFromDB($key, $natSort = false)
    {
        $keyBig = ucfirst($key);

        if ($key == 'isIt') {
            $idField   = 'isIt';
            $nameField = 'isIt';
            $key       = 'tires';
        } elseif ($key == 'radius') {
            $idField   = 'idRadius';
            $nameField = 'radius';
        } elseif ($key == 'group') {
            $idField   = 'id' . $keyBig;
            $nameField = 'name' . $keyBig;
            $key       = 'gr';
        } else {
            $idField   = 'id' . $keyBig;
            $nameField = 'name' . $keyBig;
        }

        $query = new Query();

        $arr = $query
            ->select([
                $idField,
                $nameField
            ])
            ->from($key)
            ->orderBy($nameField)
            ->all();

        if (! is_array($arr) or empty($arr)) {
            GenerateException::getException('SQL query return empty array for ' . $key . '. Must be populate',
                __CLASS__, __LINE__);
        }

        if ($natSort === true) {
            $arr = static::natSort($arr, $idField, $nameField);
        }

        return $arr;
    }

    /**
     * Get all price lists from database
     *
     * @static
     * @param bool $natSort For using natural mode
     * @return array
     * @throws GenerateException
     */
    public static function getLists($natSort = false)
    {
        $query = new Query();

        $lists = $query
            ->select([
                'idList',
                'nameList',
                'post',
                'method',
                'idForExcel'
            ])
            ->from('list')
            ->orderBy('nameList')
            ->all();

        if (! is_array($lists) or empty($lists)) {
            GenerateException::getException('SQL query return empty array for lists. Must be populate', __CLASS__,
                __LINE__);
        }

        if ($natSort === true) {
            $lists = static::natSort($lists, 'idList', 'nameList');
        }

        return $lists;
    }

    /**
     * Sort array with natural language mode
     *
     * @static
     * @param array  $arr  Input array
     * @param string $id   Column with id value from database
     * @param string $name Column with name value from database
     * @return array
     */
    private static function natSort($arr, $id, $name)
    {
        $arrFinal = [];
        foreach ($arr as $a) {
            $arrFinal[$a[$id]] = $a[$name];
        }
        natsort($arrFinal);

        return $arrFinal;
    }


    /**
     * Take value $str and compare with standard value from database in table $key
     *
     * @param string $str Input value
     * @param string $key Index for value name
     * @return string Founded value
     */
    public function findValueInDb($str, $key)
    {
        $bigKey = ucfirst($key);

        ($key == 'radius')
            ? $nameField = $key
            : $nameField = 'name' . $bigKey;

        $arr         = $this->getValueFromDB($key);
        $lastElement = count($arr) - 1;

        if ($key === 'brand') {
            $this->$key = 1;
            $name       = 'Не указан';
        } else {
            $this->$key = $arr[$lastElement]['id' . $bigKey];
            $name       = $arr[$lastElement][$nameField];
        }

        foreach ($arr as $a) {
            if ($str == $a[$nameField]) {
                $this->$key = $a['id' . $bigKey];
                $name       = $a[$nameField];
                break;
            }
        }

        return $name;
    }

    /**
     * Get course from database
     *
     * @return string Course in database
     * @throws GenerateException
     */
    public static function getCourse()
    {
        $query = new Query();

        $course = $query
            ->select([
                'nameCourse'
            ])
            ->from('course')
            ->one();

        if (! is_array($course) or empty($course)) {
            GenerateException::getException('SQL query return wrong array. Must be populate', __CLASS__, __LINE__);
        }

        return $course['nameCourse'];
    }

    /**
     * Get price movement
     * Set variable $this->priceMove
     *
     * @param string $str The basic string
     */
    public function checkPriceMove($str)
    {
        $this->priceMove = 0;
        if (! empty($this->oldPrice)) {
            foreach ($this->oldPrice as $o) {
                if ($o['price'] == '') {
                    continue;
                }

                if ($o['str'] == $str) {
                    $this->priceMove = $this->price - $o['price'];
                    $this->priceMove = number_format($this->priceMove, 1, '.', '');
                    break;
                }
            }
        }
    }

    /**
     * Create price from incoming value and set them in $this->price variable
     *
     * @param string     $data        Incoming price
     * @param null|array $priceChange Price change factor
     * @param string     $mode        Currency. If price list in usd, use 'usd'
     */
    public function createPrice($data, $priceChange = null, $mode = 'hrn')
    {
        $arrPriceRow = mb_convert_encoding($data, 'UTF-8');
        $arrPriceRow = str_replace(",", '.', $arrPriceRow);
        $this->price = preg_replace("/[^x\d|*\.]/", "", $arrPriceRow);

        if ($mode == 'usd') {
            $this->price *= $this->course;
        }

        if (! is_null($priceChange)) {
            $this->price = $this->priceChange($priceChange);
        }

        $this->price = number_format($this->price, 2, '.', '');
    }

    /**
     * Change cyrillic to latin symbol
     *
     * @param string $str Incoming string
     * @return string
     */
    public function translit($str)
    {
        $converter = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
            'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'А' => 'A', 'Б' => 'B', 'В' => 'V',
            'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
            'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
            'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
            'Ь' => '', 'Ы' => 'Y', 'Ъ' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
        ];

        $string = strtr($str, $converter);

        return $string;
    }

    /**
     * if user set the number column as A-Z, convert them in number
     *
     * @param string $str The number column as A-Z
     * @return string
     */
    public function translitRow($str)
    {
        $converter = [
            'A' => 0, 'B' => 1, 'C' => 2, 'D' => 3, 'E' => 4, 'F' => 5, 'G' => 6, 'H' => 7, 'I' => 8, 'J' => 9,
            'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18,
            'T' => 19, 'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25
        ];

        $string = strtr($str, $converter);

        return $string;
    }

    /**
     * Change cyrillic symbols the same as latin in latin
     *
     * @param string $str Incoming string
     * @return string
     */
    public function translitIndex($str)
    {
        $converter =
            ['А' => 'A', 'В' => 'B', 'Е' => 'E', 'К' => 'K', 'М' => 'M', 'Н' => 'H', 'О' => 'O', 'Р' => 'P', 'С' => 'C',
             'Т' => 'T', 'Х' => 'X'
            ];
        $string    = strtr($str, $converter);

        return $string;
    }

    /**
     * Change country name in russian language
     *
     * @param string $str Incoming country name
     * @return string
     */
    public function translitCountry($str)
    {
        $converter = ['Indonesia'     => 'Индонезия', 'Romania' => 'Румыния', 'Portugal' => 'Португалия',
                      'Slovakia'      => 'Словакия', 'Czech Republic' => 'Чехия', 'Poland' => 'Польша', 'USA' => 'США',
                      'Spain'         => 'Испания', 'France' => 'Франция', 'Turkey' => 'Турция', 'Japan' => 'Япония',
                      'Italy'         => 'Италия', 'Germany' => 'Германия', 'Slovenia' => 'Словения',
                      'Hungary'       => 'Венгрия',
                      'Korea'         => 'Южная Корея', 'China' => 'Китай', 'Finland' => 'Финляндия',
                      'Russia'        => 'Россия',
                      'Great Britain' => 'Англия', 'Luxembourg' => 'Люксембург', 'Serbia' => 'Сербия',
                      'Holland'       => 'Голландия', 'Philippines' => 'Филиппины', 'Egypt' => 'Египет'
        ];

        $string = strtr($str, $converter);

        return $string;
    }
}

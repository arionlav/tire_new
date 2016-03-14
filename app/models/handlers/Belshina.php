<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Belshina provide logic for handling the Belshina price list
 *
 * @package app\models\handlers
 */
class Belshina extends Helper
{
    /**
     * @var string Width
     */
    private $widthStr;
    /**
     * @var string Height
     */
    private $heightStr;
    /**
     * @var string Radius
     */
    private $radiusStr;

    /**
     * Create config and run $this->goHandler() method
     *
     * @param int   $list        Price list id
     * @param array $priceChange Price change factor
     */
    public function run($list, $priceChange)
    {
        self::$list = $list;

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles);

        if (! strstr($this->file2array[2][0], 'БЕЛШИНА') and ! strstr($this->file2array[9][0], 'БЕЛШИНА')) {
            App::redirect(['site/error', 'e' => "Возникла ошибка. Обратитесь к системному администратору."]);
        }

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(10);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for Belshina price list
     *
     * @param array $priceChange Price change factor
     * @return true
     * @throws GenerateException
     */
    private function goHandler($priceChange)
    {
        if (empty($this->file2array)) {
            GenerateException::getException('Rows in file does not find', __CLASS__, __LINE__);
        }

        foreach ($this->file2array as $arr) {
            $this->str = $arr[0];

            $groupStr = mb_strtolower($this->str, 'UTF-8');

            $this->group = '5';
            if (strpos($groupStr, 'легков')) {
                $this->group = '1';
            } elseif (strpos($groupStr, 'легкогруз')) {
                $this->group = '3';
            } elseif (
                strpos($groupStr, 'груз')
                or strpos($groupStr, 'строит')
                or strpos($groupStr, 'c/x')
            ) {
                $this->group = '2';
            }

            if (! preg_match('/^ ?Автошина/u', $this->str)) {
                continue;
            }

            $indexes = $this->str;

            // Width
            preg_match('/([0-9]+)[.,]?([0-9]+)?(\/?)([0-9]+)?(.+)/u', $this->str, $matches);
            ($matches[2] != '00' and $matches[2] != '')
                ? $this->widthStr = $matches[1] . '.' . $matches[2]
                : $this->widthStr = $matches[1];

            // Radius
            preg_match('/([Rх-])([0-9]+)[.,]?([0-9]+)?([СС]?)(.+)/u', $this->str, $matchesRadius);
            ($matchesRadius[3] != '00' and $matchesRadius[3] != '')
                ? $this->radiusStr = $matchesRadius[2] . '.' . $matchesRadius[3]
                : $this->radiusStr = $matchesRadius[2];

            ($matchesRadius[4] != '')
                ? $this->radiusStr .= 'C'
                : $this->radiusStr;

            // Height
            ($matches[3] == '/')
                ? $this->heightStr = $matches[4]
                : $this->heightStr = '0';

            // Model
            preg_match('/[0-9].+?([A-ZА-Я].+)/u', $this->str, $matchesModel);
            $modelStr = $matchesModel[1];
            if (preg_match('/^R[ ?0-9]/u', $matchesModel[1])) {
                preg_match('/^R.+? ([A-ZА-Я].+)/u', $matchesModel[1], $matchesModelExtR);
                $modelStr = $matchesModelExtR[1];
            }
            $exceptionWords = [
                ' нс', ' н/с', ' НС', ' Сер',
                ' СЕР', ' сер', ' с/х', ' С/Х', ' с/ х',
                ' б/к', ' Б/К', ' о/с', ' О/С', ' без', ' БЕЗ'
            ];

            foreach ($exceptionWords as $ext) {
                if (stristr($modelStr, $ext)) {
                    $modelStr = stristr($modelStr, $ext, true);
                }
            }
            $this->model = trim($modelStr);

            // If string type is: 'Автошина 18х7-8 Ф-65-1'
            if (preg_match('|([0-9]+)х([0-9]+)-([0-9]+)([СС]?)|u', $this->str, $matchesNewParam)) {
                $this->setSize($matchesNewParam);
            }

            // If received (215/65R15С), rewrite all parameters
            if (preg_match('/([0-9]{3})\/?([0-9]{2})[R-]([0-9]+)([СС]?)/u', $this->str, $matchesNewParam)) {
                $this->setSize($matchesNewParam);
            }

            // Compare values with values from database
            $this->findValueInDb($this->widthStr, 'width');

            $this->findValueInDb($this->heightStr, 'height');

            $this->findValueInDb($this->radiusStr, 'radius');

            // Camera
            $cameraStr = mb_strtolower($this->str, 'UTF-8');
            (stristr($cameraStr, 'б/к'))
                ? $this->camera = '3'
                : $this->camera = '1';

            // Comments (other)
            switch ($arr[1]) {
                case ' ':
                    $this->other = '';
                    break;
                case '':
                    $this->other = '';
                    break;
                default:
                    $this->other = 'Применение: ' . $arr[1];
            }

            // Currency
            $this->money = 1;

            // Indexes
            $indexPowerStr = '';
            $indexSpeedStr = '';
            preg_match('/(.+? .+?) (.+)/u', $indexes, $matches);
            $strWithoutBrackets = $matches[2];

            if (preg_match('/ ([0-9]{1,3} ?)([A-ZА-Я][0-9]?)/u', $strWithoutBrackets, $matchesIndexes2)) {
                $indexPowerStr = trim($matchesIndexes2[1]);
                $indexSpeedStr = $this->translitIndex(trim($matchesIndexes2[2]));
            }

            $this->findValueInDb($indexPowerStr, 'indexPower');

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Brand
            $this->brand = 35;

            // Season
            $this->season = 0;

            // Availability
            $this->isIt = '';

            // Create price and check price movement
            $this->createPrice($arr[2], $priceChange);

            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }

    /**
     * Set variables:
     * $this->width,
     * $this->height,
     * $this->radius,
     * $this->model
     *
     * @param array $matchesNewParam Array with params
     */
    private function setSize($matchesNewParam)
    {
        $this->widthStr  = $matchesNewParam[1];
        $this->heightStr = $matchesNewParam[2];
        $this->radiusStr = $matchesNewParam[3];

        (strstr($matchesNewParam[4], 'C') or strstr($matchesNewParam[4], 'С'))
            ? $this->radiusStr .= 'C'
            : $this->radiusStr;

        $modelStrReplace = " ({$this->widthStr}x{$this->heightStr}-{$this->radiusStr}) ";
        $this->model     = strtr($this->model, [$modelStrReplace => ' ']);
    }
}

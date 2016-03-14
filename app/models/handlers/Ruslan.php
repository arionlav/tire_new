<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Ruslan provide logic for handling the Ruslan price list
 *
 * @package app\models\handlers
 */
class Ruslan extends Helper
{
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

        if (
            $this->file2array[0][1] != 'Прайс-лист'
            and $this->file2array[3][2] != 'Размер'
                and $this->file2array[3][3] != 'Производитель'
        ) {
            App::redirect(['site/error', 'e' => "Возникла ошибка. Обратитесь к системному администратору."]);
        }

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(4);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for Ruslan price list
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
            if ($arr[4] == '5. ЛИТЫЕ ДИСКИ') {
                break;
            }

            if (is_null($arr[2]) or $arr[2] == '') {
                // this is subtopic
                if (is_null($arr[4]) or $arr[4] == '') {
                    continue;
                }

                $seasonStr = mb_strtolower($arr[4], 'UTF-8');

                if (
                    strpos($seasonStr, 'лет') !== false
                    or strpos($seasonStr, 'літ') !== false
                    or strpos($seasonStr, 'summ') !== false
                ) {
                    $this->season = 2;
                } elseif (
                    strpos($seasonStr, 'зим') !== false
                    or strpos($seasonStr, 'wint') !== false
                ) {
                    $this->season = 1;
                } elseif (
                    strpos($seasonStr, 'всесезон') !== false
                    or strpos($seasonStr, 'джип') !== false
                    or strpos($seasonStr, 'вс/с') !== false
                ) {
                    $this->season = 3;
                } else {
                    $this->season = 0;
                }

                if (
                    strpos($seasonStr, 'грузов') !== false
                    or strpos($seasonStr, 'сельхозтехника') !== false
                ) {
                    $this->group = 2;
                } else {
                    $this->group = 1;
                }
                continue;
            }

            $arr[2] = trim(preg_replace('/\s\s+/', ' ', $arr[2])); // Clean double spaces

            $widthStr  = '';
            $heightStr = '';
            $radiusStr = '';

            if (preg_match('|([0-9]+)([,\.]?[0-9]+)?/?([0-9]+\.?[0-9]+?)?[/R]([0-9]+\.?[0-9]+?)([CС]?)|u', $arr[2],
                $matches)) {
                ($matches[2] != '' and $matches[2] != '.00')
                    ? $widthStr = $matches[1] . $matches[2]
                    : $widthStr = $matches[1];

                $heightStr = $matches[3];
                $radiusStr = $matches[4];

                if ($matches[5] != '') {
                    $radiusStr .= 'C';
                }
            } elseif (preg_match('|([0-9]+)([,\.]?[0-9]+?)[ -R]([0-9]+\.?[0-9]+?)|u', $arr[2], $matches)) {
                ($matches[2] != '' and $matches[2] != '00')
                    ? $widthStr = $matches[1] . $matches[2]
                    : $widthStr = $matches[1];

                $radiusStr = $matches[3];
            }
            if (strpos($arr[2], 'х') !== false) {
                if (preg_match('|([0-9]{4})х([0-9]{3})-([0-9]{3})|u', $arr[2], $matches)) {
                    $widthStr  = $matches[1];
                    $heightStr = $matches[2];
                    $radiusStr = $matches[3];
                }
            }

            if (strpos($widthStr, ',') !== false) {
                $widthStr = strtr($widthStr, [',' => '.']);
            }

            $this->findValueInDb($widthStr, 'width');

            if ($this->width == '1') {
                continue;
            }

            $this->findValueInDb($heightStr, 'height');

            $this->findValueInDb($radiusStr, 'radius');

            // Indexes
            $indexPowerStr = $indexSpeedStr = '';

            if (preg_match('|.+ ([0-9]{2,3})/?([0-9]{2,3})?([A-ZА-Я])$|u', $arr[4], $matchesModel)) {
                // Index power
                ($matchesModel[2] == '')
                    ? $indexPowerStr = $matchesModel[1]
                    : $indexPowerStr = $matchesModel[1] . '/' . $matchesModel[2];

                $this->findValueInDb($indexPowerStr, 'indexPower');

                // Index speed
                $indexSpeedStr = $this->translitIndex($matchesModel[3]);

                $this->findValueInDb($indexSpeedStr, 'indexSpeed');
            }

            // Brand
            $brandStr = $arr[3];

            if ($brandStr == 'BELSHINA') {
                $brandStr = 'БЕЛШИНА';
            } elseif ($brandStr == 'ROSAVA') {
                $brandStr = 'РОСАВА';
            } else {
                $this->findValueInDb($brandStr, 'brand');
            }

            // Model
            $modelStr   = $arr[4];
            $indexesCut = $indexPowerStr . $indexSpeedStr;

            $this->model = trim(strtr($modelStr, ["{$brandStr}" => '', "{$indexesCut}" => '']));

            if (strpos($this->model, 'ОБОДНАЯ ЛЕНТА') !== false) {
                continue;
            }

            // Light and vantage group we are defines at the start in subtopic,
            // but if there is light vantage tire, change it
            if (strpos($radiusStr, 'C') !== false) {
                $this->group = 3;
            }

            // Camera is undefined
            $this->camera = '1';

            // Currency
            $this->money = 1;

            // Note (other)
            $this->other = 'Страна производства: ' . $arr[5];

            // Availability
            $this->isIt = $arr[7];

            // Price
            $this->createPrice($arr[6], $priceChange);

            // Basic string
            $this->str =
                "{$widthStr}/{$heightStr}R{$radiusStr} {$indexPowerStr}{$indexSpeedStr} {$this->model} {$brandStr}";

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

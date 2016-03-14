<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Premiorri provide logic for handling the Premiorri price list
 *
 * @package app\models\handlers
 */
class Premiorri extends Helper
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

        if ($this->file2array[1][6] != 'Базовая цена' and $this->file2array[1][7] != 'Минимальная розничная цена') {
            App::redirect(['site/error', 'e' => "Возникла ошибка. Обратитесь к системному администратору."]);
        }

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(8);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for Premiorri price list
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
            if (strpos($arr[0], 'КАМЕР') !== false) {
                break;
            }

            if (is_string($arr[0]) and $arr[0] != '') {
                $zeroStr = mb_strtoupper($arr[0], 'UTF-8');

                preg_match('|(WINT)?(SUMMER)?(ЗИМ)?(ЛЕТ)?(ВСЕСЕЗОН)?|u', $zeroStr, $matchesZero);

                // If isset $matchesZero[0], there are season and group
                if ($matchesZero[0] != '') {
                    // Explode on the 2 part by space or -
                    preg_match('|(.+?)[ -](.+)|u', $zeroStr, $matchesSeasonGroup);
                    // and check first part. Take the season
                    if (
                        strpos($matchesSeasonGroup[1], 'ЛЕТ') !== false
                        or strpos($matchesSeasonGroup[1], 'SUMMER') !== false
                    ) {
                        $this->season = '2';
                    } elseif (
                        strpos($matchesSeasonGroup[1], 'ЗИМ') !== false
                        or strpos($matchesSeasonGroup[1], 'WINT') !== false
                    ) {
                        $this->season = '1';
                    } elseif (strpos($matchesSeasonGroup[1], 'ВСЕСЕЗОН') !== false) {
                        $this->season = '3';
                    } else {
                        $this->season = '0';
                    }

                    // Then, look in second part, there should ba base a group, or some garbage
                    if (strpos($matchesSeasonGroup[2], 'ЛЕГКОГРУЗ') !== false) {
                        $this->group = '3';
                    } elseif (strpos($matchesSeasonGroup[2], 'ГРУЗ') !== false) {
                        $this->group = '2';
                    } elseif (strpos($matchesSeasonGroup[2], 'ВНЕДОРОЖ') !== false) {
                        $this->group = '4';
                    } else {
                        $this->group = '1';
                    }

                    $this->brand = '1';
                    continue;
                } else { //If the season there is not written, so it is a brand
                    $brandStr = $zeroStr;

                    $this->findValueInDb($brandStr, 'brand');
                }
            }

            if ($arr[3] == 'Solazo') {
                $this->brand = '16'; // PREMIORRI
            }

            // Width
            preg_match('|([0-9.,]+/?[0-9]+)(.+)|u', $arr[2], $matchesSize);
            $heightStr = null;

            if (strpos($matchesSize[1], '/') !== false) {
                $arraySize = explode('/', $matchesSize[1]);

                (strpos($arraySize[0], ',') !== false)
                    ? $widthStr = strtr($arraySize[0], ',', '.')
                    : $widthStr = $arraySize[0];

                $heightStr = $arraySize[1];
            } else {
                (strpos($matchesSize[1], ',') !== false)
                    ? $widthStr = strtr($matchesSize[1], ',', '.')
                    : $widthStr = $matchesSize[1];
            }

            $widthName = $this->findValueInDb($widthStr, 'width');

            // Height
            $heightName = 'Не указано';

            if (is_string($heightStr)) {
                $heightName = $this->findValueInDb($heightStr, 'height');
            }

            // Radius
            $radiusName = 'Не указано';

            if (! is_null($arr[1]) or $arr[1] !== '') {
                preg_match('|[0-9]{2}|u', $arr[1], $matchesRadius);

                $radiusStr = $matchesRadius[0];

                if (strpos($matchesSize[2], 'C') !== false or strpos($matchesSize[2], 'С') !== false) {
                    $radiusStr .= 'C';
                }

                $radiusName = $this->findValueInDb($radiusStr, 'radius');
            }

            if (! $this->radius) {
                preg_match('|[0-9]{2}|u', $matchesSize[2], $matchesRadius2);

                $radiusStr = $matchesRadius2[0];

                if (strpos($matchesSize[2], 'C') !== false or strpos($matchesSize[2], 'С') !== false) {
                    $radiusStr .= 'C';
                }

                $radiusName = $this->findValueInDb($radiusStr, 'radius');
            }

            // Index power
            preg_match('|[0-9]{1,3}|u', $arr[4], $matchesIndexes);
            $indexPowerStr = $matchesIndexes[0];

            $indexPowerName = $this->findValueInDb($indexPowerStr, 'indexPower');

            // Index speed
            $arr4translit = mb_strtoupper($this->translitIndex($arr[4]), 'UTF-8');

            preg_match('|[A-Z]|u', $arr4translit, $matchesIndexes);

            $indexSpeedStr = $matchesIndexes[0];

            $indexSpeedName = $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Camera is undefined
            $this->camera = '1';

            // Comments (other)
            $this->other = '';

            if ($arr[5] != '') {
                $this->other = 'Рисунок: ' . $arr[5];
            }

            // Model
            $this->model = $arr[3];

            // Currency
            $this->money = 1;

            $this->str =
                "Шина {$widthName}/{$heightName}R{$radiusName} {$indexPowerName}{$indexSpeedName} {$arr[3]} {$arr[5]}";
            if (strpos($this->str, 'Не указано') !== false) {
                $this->str = strtr($this->str, ['Не указано' => '-']);
            }

            $this->createPrice($arr[9], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

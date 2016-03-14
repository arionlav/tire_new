<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Tehno provide logic for handling the Tehno price list
 *
 * @package app\models\handlers
 */
class Tehno extends Helper
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

        if ($this->file2array[3][1] != 'Технооптторг') {
            App::redirect(['site/error', 'e' => "Возникла ошибка. Обратитесь к системному администратору."]);
        }

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(13);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for Tehno price list
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

        // the first in the subtopic are going Rosava, it is not define
        $this->brand = '79';

        foreach ($this->file2array as $arr) {
            $this->str = trim(preg_replace('/\s\s+/', ' ', $arr[1])); // Clean double spaces

            // Determine subtopic
            if (! $arr[2]) {
                // Brand
                if (preg_match('|^Шины (.+)|u', $this->str, $matchesHead)) {
                    $brandStr = strtoupper($matchesHead[1]);

                    if (strstr($this->str, 'для лег.груз. и микроавт.')) {
                        $brandStr     = 0;
                        $this->season = 0;
                    }

                    if (strpos($brandStr, 'CORDIANT') !== false) {
                        $brandStr = 'CORDIANT';
                    }

                    $this->findValueInDb($brandStr, 'brand');

                    continue;
                }

                // Season
                $seasonStr = mb_strtolower($this->str, 'UTF-8');
                if (
                    strpos($seasonStr, 'лет') !== false
                    or strpos($seasonStr, 'summ') !== false
                ) {
                    $this->season = 2;
                } elseif (
                    strpos($seasonStr, 'зим') !== false
                    or strpos($seasonStr, 'wint') !== false
                ) {
                    $this->season = 1;
                } elseif (strpos($seasonStr, 'всесезон') !== false) {
                    $this->season = 3;
                } else {
                    $this->season = 0;
                }

                // Group
                $groupStr = mb_strtolower($this->str, 'UTF-8');
                if (strpos($groupStr, 'легков') !== false) {
                    $this->group = 1;
                } elseif (
                    strpos($groupStr, 'легкогруз') !== false
                    or strpos($groupStr, 'лег.груз.') !== false
                ) {
                    $this->group = 3;
                } elseif (
                    strpos($groupStr, 'груз') !== false
                    or strpos($groupStr, 'строит') !== false
                    or strpos($groupStr, 'с/х') !== false
                    or strpos($groupStr, 'вантаж') !== false
                ) {
                    $this->group = 2;
                } elseif (
                    strpos($groupStr, 'внедорож') !== false
                    or strpos($groupStr, 'джипы') !== false
                ) {
                    $this->group = 4;
                } else {
                    $this->group = 5;
                }

                continue;
            }
            // Explode basic string by spaces
            preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX]?)([0-9]+)?(.+)|u', $arr[1], $matches);

            ($matches[2] != '00' and $matches[2] != '')
                ? $widthStr = $matches[1] . '.' . $matches[2]
                : $widthStr = $matches[1];

            $this->findValueInDb($widthStr, 'width');

            // Height
            preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX ]?)([0-9]+)?([.,])?([0-9]+)?(.+)|ux', $arr[1], $matchesHeight);

            if (preg_match('|([/xхX ])|u', $matchesHeight[3], $matchesDelimiter)) { // Is height there
                ($matchesHeight[5] != '00' and $matchesHeight[5] != '') // If divisional
                    ? $heightStr = $matchesHeight[4] . '.' . $matchesHeight[6]
                    : $heightStr = $matchesHeight[4];
            } else {
                $heightStr = 0;
            }

            $this->findValueInDb($heightStr, 'height');

            // Radius
            preg_match('/([RхxX-])([0-9]{2})[.,]?([0-9])?([CС]?)(.+)/u', $arr[1], $matchesRadius);
            $radiusStr = '';

            if ($matchesRadius[3] != '00' and $matchesRadius[3] != '') {
                $radiusPreStr = $matchesRadius[2] . '.' . $matchesRadius[3];

                if (preg_match("|$radiusPreStr |", $arr[1])) {
                    $radiusStr = $radiusPreStr;
                }
            } else {
                $radiusStr = $matchesRadius[2];
            }

            if ($matchesRadius[4] !== '' and $radiusStr) {
                $radiusStr .= 'C';
            }

            if ($matchesRadius[3] != '' and ! preg_match('|[.,]|', $radiusStr)) {
                $matchesRadius[5] = $matchesRadius[3] . $matchesRadius[5];
            }

            $this->findValueInDb($radiusStr, 'radius');

            // if there is not a radius
            if (! $matchesRadius[5]) {
                preg_match('|(.+?) (.+)|u', $arr[1], $matchesRadiusSecond);
                $matchesRadius[5] = $matchesRadiusSecond[2];
            }

            // Index power
            $indexesStr = trim($matchesRadius[5]);
            preg_match('|([0-9]{2,3})/?([0-9]{2,3})?([A-ZА-Я]) .+?|u', $indexesStr, $matchesIndexes);

            if ($matchesIndexes[2] == '') {
                $indexPowerStr = $matchesIndexes[1];
            } else {
                $indexPowerStr = $matchesIndexes[1] . '/' . $matchesIndexes[2];
            }

            $this->findValueInDb($indexPowerStr, 'indexPower');

            // Index speed
            $indexSpeedStr = mb_strtoupper($this->translitIndex($matchesIndexes[3]), 'UTF-8');

            if (preg_match('|^R |u', $indexesStr)) {
                $this->indexSpeed = '8';
            } else {
                $this->findValueInDb($indexSpeedStr, 'indexSpeed');
            }

            // Model
            if ($indexPowerStr == null or $indexPowerStr == '') {
                $indexPowerStr = 'something';
            }

            if ($matchesIndexes[3] == null or $matchesIndexes[3] == '') {
                $matchesIndexes[3] = 'something';
            }

            // Unnecessary word
            $modelStartStr = $matchesRadius[5];
            $minusWord     = [
                'TL'
            ];

            foreach ($minusWord as $asw) {
                if (stristr($modelStartStr, $asw)) {
                    $modelStartStr = stristr($modelStartStr, $asw, true);
                }
            }

            $this->model = strtr(trim($modelStartStr), [
                'АШК' => '', 'КШЗ' => '', '"' => '', 'ВлТР' => '', 'Blizzak' => '', 'Blizak' => '', 'БШК' => '',
                'ОШЗ' => '', ','   => '', "{$indexPowerStr}" => '', "{$matchesIndexes[3]} " => ''
            ]);
            $this->model = trim(preg_replace('/\s\s+/', ' ', $this->model)); // Clean double spaces

            // Camera is undefined
            $this->camera = '1';

            // Currency
            $this->money = 1;

            // Availability
            $this->isIt = $arr[8];

            // Note (other)
            (stristr($this->str, 'шип'))
                ? $this->other = 'Шип'
                : $this->other = '';

            // Price
            $this->createPrice($arr[4], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            // Custom rows
            if (strpos($this->str, 'Cordiant') !== false) {
                $this->brand = '36';
            }
            if (strpos($this->str, 'БШК') !== false) {
                $this->brand = '35';
            }
            if (strpos($this->str, 'ROSAVA') !== false) {
                $this->brand = '79';
            }
            if (strpos($this->str, '(215/90-15C)') !== false) {
                $this->width  = '14';
                $this->height = '16';
                $this->radius = '16';
                $this->model  = strtr($this->model, ['(215/90-15C) ' => '']);
            }
            if (strpos($this->model, 'KW31') !== false) {
                $this->indexPower = '105';
                $this->indexSpeed = '8';
                $this->model      = 'KW31';
            }
            if (strpos($this->str, '31x10,50R15 Dueler A/T 697 109S TL Bridgestone') !== false) {
                $this->model = 'Dueler A/T 697 S';
            }
            $this->insertRows();
        }

        return true;
    }
}

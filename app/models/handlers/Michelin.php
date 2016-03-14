<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Michelin provide logic for handling the Michelin price list
 *
 * @package app\models\handlers
 */
class Michelin extends Helper
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

        if ($this->file2array[1][0] != 'MICHELIN') {
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
     * The handler for Michelin price list
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
            if (is_null($arr[2]) or $arr[2] == '') {
                continue;
            }

            // Explode basic string to spaces
            $this->str = $arr[2] = trim(preg_replace('/\s\s+/', ' ', $arr[2])); // Clean double spaces
            preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX]?)([0-9]+)?(.+)|u', $arr[2], $matches);
            ($matches[2] != '00' and $matches[2] != '')
                ? $widthStr = $matches[1] . '.' . $matches[2]
                : $widthStr = $matches[1];

            $this->findValueInDb($widthStr, 'width');

            // Height
            preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX ]?)([0-9]+)?([.,])?([0-9]+)?(.+)|ux', $arr[2], $matchesHeight);

            if (preg_match('|([/xхX ])|u', $matchesHeight[3], $matchesDelimiter)) { // Is height there
                ($matchesHeight[5] != '00' and $matchesHeight[5] != '') // If divisional
                    ? $heightStr = $matchesHeight[4] . '.' . $matchesHeight[6]
                    : $heightStr = $matchesHeight[4];
            } else {
                $heightStr = 0;
            }

            $this->findValueInDb($heightStr, 'height');

            // Radius
            preg_match('/([Rхx-]) ?([0-9]{2})[.,]?([0-9])? ?([CС]?)(.+)/u', $arr[2], $matchesRadius);
            $radiusStr = '';

            if ($matchesRadius[3] != '00' and $matchesRadius[3] != '') {
                $radiusPreStr = $matchesRadius[2] . '.' . $matchesRadius[3];

                if (preg_match("|$radiusPreStr |", $arr[2])) {
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

            // If radius is undefined, for indexes
            if (! $matchesRadius[5]) {
                preg_match('|(.+?) (.+)|u', $arr[1], $matchesRadiusSecond);
                $matchesRadius[5] = $matchesRadiusSecond[2];
            }

            // Indexes
            $indexesStr = trim($matchesRadius[5]);

            preg_match('|([0-9]{2,3})/?([0-9]{2,3})? ?([A-ZА-Я]) ?.+?|u', $indexesStr, $matchesIndexes);

            if ($matchesIndexes[1] == '10' and $matchesIndexes[3] == 'P') {
                preg_match('|10PR ([0-9]{2,3})/?([0-9]{2,3})? ?([A-ZА-Я]) ?.+?|u', $indexesStr, $matchesIndexes);
            }

            if ($matchesIndexes[2] == '') {
                $indexPowerStr = $matchesIndexes[1];
            } else {
                $indexPowerStr = $matchesIndexes[1] . '/' . $matchesIndexes[2];
            }

            $this->findValueInDb($indexPowerStr, 'indexPower');

            // Index speed
            $indexSpeedStr = mb_strtoupper($this->translitIndex($matchesIndexes[3]), 'UTF-8');

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Model
            if ($indexPowerStr == null or $indexPowerStr == '') {
                $indexPowerStr = 'something';
            }

            if ($matchesIndexes[3] == null or $matchesIndexes[3] == '') {
                $matchesIndexes[3] = 'something';
            }

            // Unnecessary word
            $modelStartStr = strtr(trim($matchesRadius[5]), [
                ','                     => '',
                "{$indexPowerStr}"      => '',
                "{$matchesIndexes[3]} " => ''
            ]);

            $this->model = $modelStartStr;

            if (preg_match('|(\(.+\))|u', $this->model, $matchesModelBrace)) {
                $this->model = strtr($this->model, [$matchesModelBrace[1] => ' ']);
            }

            $this->model = trim(preg_replace('/\s\s+/', ' ', $this->model)); // Clean double spaces

            // Brand
            if ($arr[2] == 'GOOD/YEAR' or $arr[2] == 'Good year') {
                $this->brand = '47'; // GOODYEAR
            } else {
                $brandStr = $this->translitIndex(mb_strtoupper($arr[0], 'UTF-8'));

                $this->findValueInDb($brandStr, 'brand');
            }

            // Season
            $this->season = '2';

            // Group
            $this->group = '5';

            // Camera is undefined
            $this->camera = '1';

            // Currency
            $this->money = 1;

            // Note do not insert
            $this->other = '';
            if ($arr[11]) {
                $this->other = $arr[11];
            }

            if ($arr[10] and $this->other) {
                $this->other .= '; Начало продаж: ' . $arr[10];
            } elseif ($arr[10]) {
                $this->other = 'Начало продаж: ' . $arr[10];
            }

            // Price
            $this->createPrice($arr[9], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

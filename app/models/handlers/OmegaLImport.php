<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class OmegaLImport provide logic for handling the 'Omega passenger tires, import' price list
 *
 * @package app\models\handlers
 */
class OmegaLImport extends Helper
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

        $listOnFile = 'Импорт легк.';

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles, $listOnFile);

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(11);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for 'Omega passenger tires, import' price list
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
            if (is_null($arr[1]) or $arr[1] == '') {
                continue;
            }

            // Basic string
            $this->str = $arr[1];

            // Explode basic string to spaces
            preg_match('|(.+?) (.+?) (.+?) (.+)|u', trim($this->str), $matches);

            // Radius
            $radiusStr = $arr[2];

            if (strstr($matches[2], 'C') or strstr($matches[2], 'С')) {
                $radiusStr .= 'C';
            }

            $this->findValueInDb($radiusStr, 'radius');

            // Index power
            preg_match('|([0-9]+/?[0-9]+)([A-ZА-Я]+)?|u', $matches[3], $matchesIndexes);

            $indexPowerStr = $matchesIndexes[1];

            $this->findValueInDb($indexPowerStr, 'indexPower');

            // Index speed
            $indexSpeedStr = $matchesIndexes[2];
            $indexSpeedStr = $this->translitIndex($indexSpeedStr);

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Height
            $heightStr = $arr[4];

            if ($heightStr == '') {
                preg_match('|[Xx/]([0-9]+)[.,]?([0-9]+?)|u', $arr[1], $matchesSize);

                ($matchesSize[2] != '00' and $matchesSize[2] != '')
                    ? $heightStr = $matchesSize[1] . '.' . $matchesSize[2]
                    : $heightStr = $matchesSize[1];
            }

            $this->findValueInDb($heightStr, 'height');

            // Width
            $widthStr = $arr[3];

            if ($widthStr == '') {
                preg_match('|([0-9]+)|u', $arr[1], $matchesSize);

                $widthStr = $matchesSize[1];
            }

            $this->findValueInDb($widthStr, 'width');

            // Brand
            $brandStr = mb_strtoupper($arr[6], 'UTF-8');

            $this->findValueInDb($brandStr, 'brand');

            // Season
            $seasonStr = mb_strtolower($arr[5], 'UTF-8');

            $this->season = '0';
            if (strpos($seasonStr, 'всесезон') !== false) {
                $this->season = '3';
            } elseif (strpos($seasonStr, 'лет') !== false) {
                $this->season = '2';
            } elseif (strpos($seasonStr, 'зим') !== false) {
                $this->season = '1';
            }

            // Model
            preg_match('|(.+?) (\(.+)|u', $matches[4], $matchesModel);
            $this->model = $matchesModel[1];

            if (strpos($matches[4], 'V905 W.DRIVE') !== false) {
                $this->model = 'V905 W.DRIVE';
            }

            if (strpos($matches[4], 'W200 XL(Starfire)') !== false) {
                $this->model = 'W200 XL';
            }

            // Group
            $grStr = mb_strtolower($arr[8], 'UTF-8');

            if (strpos($grStr, 'легков') !== false) {
                $this->group = '1';
            } elseif (strpos($grStr, 'грузов') !== false) {
                $this->group = '2';
            } elseif (strpos($grStr, 'легкогруз') !== false) {
                $this->group = '3';
            } elseif (strpos($grStr, 'внедор') !== false) {
                $this->group = '4';
            }

            // Comments (other)
            $this->other = '';

            if ($arr[7] != '') {
                $this->other = 'Страна производства: ' . $this->translitCountry($arr[7]);
            }

            // custom
            if ($arr[1] == 'Шина 195R14C 8PR 106/104Q Achilles LTR-80 (Achilles)') {
                $this->indexPower = '275';
                $this->indexSpeed = '7';
                $this->model      = 'Achilles LTR-80';
            }

            // Camera is undefined
            $this->camera = '1';

            // Currency
            $this->money = 1;

            // Availability
            $this->isIt = $arr[12];

            // Price
            $this->createPrice($arr[9], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

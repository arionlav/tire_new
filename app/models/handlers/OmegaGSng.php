<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class OmegaGSng provide logic for handling the 'Omega Vantage, SNG' price list
 *
 * @package app\models\handlers
 */
class OmegaGSng extends Helper
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

        $listOnFile = 'Груз. шины СНГ';

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles, $listOnFile);

        if (
            ! strstr($this->file2array[1][0], 'ТПК ОМЕГА-Автопоставка')
            and $this->file2array[2][0] != 'Грузовые шины СНГ'
        ) {
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
     * The handler for 'Omega Vantage, SNG' price list
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
            if (
                is_null($arr[1]) & is_null($arr[2])
                or $arr[1] == '' & $arr[2] == ''
                or ! preg_match('/^Шина/u', $arr[2])
            ) {
                continue;
            }

            // Basic string
            $this->str = $arr[2];

            // Width
            preg_match('/([0-9]+)[.,]?([0-9]+)?(\/?)([0-9]+)?(.+)/u', $arr[2], $matches);

            ($matches[2] != '00' and $matches[2] != '')
                ? $widthStr = $matches[1] . '.' . $matches[2]
                : $widthStr = $matches[1];

            $this->findValueInDb($widthStr, 'width');

            // Radius
            preg_match('/([Rх-])([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/u', $arr[2], $matchesRadius);

            ($matchesRadius[3] != '00' and $matchesRadius[3] != '')
                ? $radiusStr = $matchesRadius[2] . '.' . $matchesRadius[3]
                : $radiusStr = $matchesRadius[2];

            if ($matchesRadius[4] != '' & $radiusStr) {
                $radiusStr .= 'C';
            }

            $this->findValueInDb($radiusStr, 'radius');

            // Height
            ($matches[3] == '/')
                ? $heightStr = $matches[4]
                : $heightStr = '0';

            $this->findValueInDb($heightStr, 'height');

            // Indexes and model
            preg_match('/(\(.+?( ?).+?\))/u', $arr[2], $matchesStr);

            if ($matchesStr[2] == ' ') {
                $newModel = strtr($matchesStr[1], ' ', '-');
                $arr[2]   = strtr($arr[2], [$matchesStr[0] => $newModel]);
            }

            $this->model = '';

            if (! preg_match('/ R[0-9]{2}/u', $arr[2])) {
                preg_match('/(.+?) (.+?) (.+?) (.+?) (.+)/u', $arr[2], $matchesIndexes);
            } else {
                preg_match('/(.+? .+?) (.+?) (.+?) (.+?) (.+)/u', $arr[2], $matchesIndexes);
            }

            if (preg_match('/(^[0-9]{1,3}\/?[0-9]+?)([A-ZА-Я]{1,2}[0-9]?),?/u', $matchesIndexes[3], $matchesModel)) {
                $indexPowerStr = $matchesModel[1];
                $indexSpeedStr = $matchesModel[2];

                $this->model = trim(strtr($matchesIndexes[4], ['(' => ' ', ')' => ' ', ',' => ' ']));
            } elseif (preg_match('/(^[0-9]{1,3}\/?[0-9]+?)([A-ZА-Я]{1,2}[0-9]?),?/u', $matchesIndexes[4],
                $matchesModel)) {
                $indexPowerStr = $matchesModel[1];
                $indexSpeedStr = $matchesModel[2];

                $this->model = trim(strtr($matchesIndexes[3], ['(' => ' ', ')' => ' ', ',' => ' ']));
            } else {
                $indexPowerStr = 0;
                $indexSpeedStr = 0;

                $modelPreStr1 = trim(strtr($matchesIndexes[3], ['(' => ' ', ')' => ' ', ',' => ' ']));
                $modelPreStr2 = trim(strtr($matchesIndexes[4], ['(' => ' ', ')' => ' ', ',' => ' ']));
                if (preg_match('/(^[A-Za-zА-Яа-я].+)/u', $modelPreStr1, $matchesModel)) {
                    $this->model = $modelPreStr1;

                    if ($this->model == 'R' . $this->radius) {
                        $this->model = $modelPreStr2;
                    }
                } elseif (preg_match('/(^[A-Za-zА-Яа-я].+)/u', $modelPreStr2, $matchesModel)) {
                    $this->model = $modelPreStr2;
                }
            }

            // if the string of the type 'Шина 9,00-16 Бел ПТ-5М нс.10 с камерой без ободной ленты (БШК)'
            if (strpos($arr[2], 'Бел ПТ-5М') !== false) {
                $this->model = 'Бел ПТ-5М';
                // if the string of the type 'Шина 420/70 R24 (16.9R24) 130А8 Бел-90...' and model is '16.9R24'
            } elseif (strpos($arr[2], '(16.9R24) 130А8 Бел-90') !== false) {
                $this->model = 'Бел-90';
                // if the string of the type 'Шина 12,0-16 125A6 8PR Бел-104 (БШК)'
            } elseif (strpos($arr[2], 'Бел-104') !== false) {
                $this->model = 'Бел-104';
                // if the string of the type 'Шина 18,4R38 (460/85R38) 146A8 10PR Ф-111 (БШК)'
            } elseif (strpos($arr[2], '10PR Ф-111') !== false) {
                $this->model = 'Ф-111';
                // if the string of the type 'Шина 16,9R38 (420/85R38) 141A8 Ф-52 PR8 с камерой без ободной ленты (БШК)'
            } elseif (strpos($arr[2], '141A8 Ф-52') !== false) {
                $this->model = 'Ф-52';
                // if the string of the type
                // 'Шина 400/70-21 145G 12сл. (1100*400-533) КАМА-401 с рег. давл. с камерой и ободной лентой (НкШЗ)'
            } elseif (strpos($arr[2], 'КАМА-401') !== false) {
                $this->model = 'КАМА-401';
                // if the string of the type 'Шина 30,5L32 162A6 12PR ФБЕЛ-179М (БШК)'
            } elseif (strpos($arr[2], 'ФБЕЛ-179М') !== false) {
                $this->model = 'ФБЕЛ-179М';
            }

            $this->findValueInDb($indexPowerStr, 'indexPower');

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Camera
            $strCamera = mb_strtolower($arr[2], 'UTF-8');

            $arrEscapeWith    = [' c камерой', ' с камерой', ' камерная'];
            $arrEscapeWithout = [' бескамерная', ' без камеры', ' безкамерная'];

            foreach ($arrEscapeWithout as $aew) {
                if (strpos($strCamera, $aew) !== false) {
                    $this->camera = 3;
                }
            }

            foreach ($arrEscapeWith as $aew) {
                if (strpos($strCamera, $aew) !== false) {
                    $this->camera = 2;
                }
            }

            if (! $this->camera) {
                $this->camera = 1;
            }

            // Brand
            if (strpos($arr[2], 'НкШЗ') !== false) {
                $this->brand = 2;
            }

            if (! $this->brand) {
                $this->brand = 1;
            }

            // Season
            $this->season = 0;

            // Currency
            $this->money = 1;

            // Group
            $this->group = 2;

            // Availability
            $this->isIt = $arr[8];

            // Comments (other)
            $otherStr = trim($arr[6]);

            switch ($otherStr) {
                case ' ':
                    $this->other = '';
                    break;
                case '' :
                    $this->other = '';
                    break;
                default :
                    $this->other = 'Применяемость: ' . $otherStr;
            }

            // Price
            $this->createPrice($arr[5], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

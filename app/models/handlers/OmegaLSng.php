<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class OmegaLSng provide logic for handling the 'Omega passenger tires, SNG' price list
 *
 * @package app\models\handlers
 */
class OmegaLSng extends Helper
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

        $listOnFile = 'Легк. шины СНГ';

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles, $listOnFile);

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(10);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for 'Omega passenger tires, SNG' price list
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
            // If we caught the subtopic in the first cell, write brand and return in foreach
            if (is_string($arr[0]) & $arr[1] == '' & $arr[2] == '') {
                $brandStr = mb_strtoupper(trim($subStr = substr($arr[0], strpos($arr[0], ' ТМ ') + 5)), 'UTF-8');

                $this->findValueInDb($brandStr, 'brand');

                continue;
            }

            // Basic string
            $this->str = $arr[2];

            // Explode basic string by spaces
            preg_match('|(.+?) (.+?) (.+?) (.+?) (\(.+)|u', trim($this->str), $matches);

            // Model
            $arrEscape =
                [' бескамерная', ' Без камеры', ' БЕЗ КАМЕРЫ', ' безкамерная', ' c камерой', ' с камерой', ' камерная'];
            // If there are no indexes, they are not specified in the cell,
            // so they are not in the basic string, it means less spaces
            if (! $arr[4]) {
                preg_match('|(.+?) (.+?) (.+?) (\(.+)|u', trim($arr[2]), $matchesModel);
                $this->model = $matchesModel[3];
                $this->getModel($arrEscape, $matchesModel[3]);
            } else {
                $this->getModel($arrEscape, $matches[4]);
            }

            // Make out $matches[2] = 215/65R16C or 175R16C or 215/65
            $heightTireStr = 0;
            if (strpos($matches[2], '/') !== false) {
                // If we find slash - excellent! But if is there R or it is written through the space?
                if (strpos($matches[2], 'R') !== false) {
                    // Ooo, there is R here! Variant 215/65R16C. Therefore we use this regexp
                    preg_match('|(.+?)[/](.+?)R(.+)|u', $matches[2], $matchesSecond);
                    $heightTireStr = $matchesSecond[2];
                    if (strpos($matches[2], 'C') !== false or strpos($matches[2], 'С') !== false) {
                        $arr[3] .= 'C';
                    }
                } else {
                    // There is no R there (variant 215/65), we write regexp without it
                    preg_match('|(.+?)[/](.+)|u', $matches[2], $matchesSecond);

                    $heightTireStr = $matchesSecond[2];

                    if (strpos($matches[2], 'C') !== false or strpos($matches[3], 'С') !== false) {
                        $arr[3] .= 'C';
                    }
                    // If there is 215/65, therefore extra space and model selection works wrong. Rewrite it
                    preg_match('|(.+?) (.+?) (.+?) (.+?) (.+?) (\(.+)|u', trim($arr[2]), $matchesModel);

                    $this->getModel($arrEscape, $matchesModel[5]);
                }
            } else {
                // If there is a slash, therefor must be simple R, height not specified, it is variant 175R16C
                preg_match('|(.+?)[R](.+)|u', $matches[2], $matchesSecond);

                if (strpos($matches[2], 'C') !== false or strpos($matches[2], 'С') !== false) {
                    $arr[3] .= 'C';
                }
            }

            // Index power
            preg_match('|([0-9]+/?[0-9]+)([A-ZА-Я]+)?|u', $arr[4], $matchesIndexes);

            $indexPowerStr = $matchesIndexes[1];

            $this->findValueInDb($indexPowerStr, 'indexPower');

            // Index speed
            $indexSpeedStr = $matchesIndexes[2];
            $indexSpeedStr = $this->translitIndex($indexSpeedStr);

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Width
            $widthStr = $matchesSecond[1];

            $this->findValueInDb($widthStr, 'width');

            // Radius
            $radiusStr = $arr[3];

            $this->findValueInDb($radiusStr, 'radius');

            // Height
            $this->findValueInDb($heightTireStr, 'height');

            // Camera
            $exceptionCamera = ['c камерой', 'с камерой', 'камерная'];
            foreach ($exceptionCamera as $arrayFor) {
                if (strpos($arr[2], $arrayFor) !== false) {
                    $this->camera = 2;
                    break;
                }
            }

            $exceptionCamera = ['бескамерная', 'безкамерная', 'БЕЗ КАМЕРЫ', 'Без камеры'];
            foreach ($exceptionCamera as $arrayFor) {
                if (strpos($arr[2], $arrayFor) !== false) {
                    $this->camera = 3;
                    break;
                }
            }

            if (! $this->camera) {
                $this->camera = 1;
            }

            // Season
            if (
                strpos($arr[5], 'Всесезон') !== false
                or strpos($arr[5], 'всесезон') !== false
                or strpos($arr[5], 'ВСЕСЕЗОН') !== false
            ) {
                $this->season = 3;
            } elseif (
                strpos($arr[5], 'Лет') !== false
                or strpos($arr[5], 'ЛЕТ') !== false
                or strpos($arr[5], 'лет') !== false
            ) {
                $this->season = 2;
            } elseif (
                strpos($arr[5], 'Зим') !== false
                or strpos($arr[5], 'ЗИМ') !== false
                or strpos($arr[5], 'зим') !== false
            ) {
                $this->season = 1;
            } else {
                $this->season = 0;
            }

            // Group
            $this->group = 1;

            // Currency
            $this->money = 1;

            // custom
            if (strstr($arr[2], 'Шина 175/80R16 88Q И-511 камерная БЕЗ КАМЕРЫ (НкШЗ)')) {
                $this->radius     = 5;
                $this->indexPower = 86;
                $this->indexSpeed = 7;
                $this->model      = 'И-511';
            } elseif ($arr[2] == 'Шина 195R14C 106/104P КАМА-EURO НК-131(НкШЗ)') {
                $this->width  = '12';
                $this->radius = '19';
                $this->model  = 'НК-131';
            }

            // Availability
            $this->isIt = $arr[9];

            // Price
            $this->createPrice($arr[8], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }

    /**
     * Clean the model string
     *
     * @param array  $arrEscape    Array with unnecessary words
     * @param string $matchesModel String with model
     */
    private function getModel(array $arrEscape, $matchesModel)
    {
        if (! empty($arrEscape)) {
            foreach ($arrEscape as $arrayFor) {
                if (strstr($matchesModel, $arrayFor)) {
                    $matchesModel = substr($matchesModel, 0, strpos($matchesModel, $arrayFor));
                }
            }
        }

        $this->model = $matchesModel;
    }
}

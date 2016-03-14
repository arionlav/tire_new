<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class OmegaGImport provide logic for handling the 'Omega Vantage, Import' price list
 *
 * @package app\models\handlers
 */
class OmegaGImport extends Helper
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

        $listOnFile = 'Импорт груз.';

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles, $listOnFile);

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(8);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for 'Omega Vantage, Import' price list
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

            // Width
            preg_match('/([0-9]+)[.,]?([0-9]+)?(\/?)([0-9]+)?(.+)/', $arr[1], $matches);

            ($matches[2] != '00' and $matches[2] != '')
                ? $widthStr = $matches[1] . '.' . $matches[2]
                : $widthStr = $matches[1];

            $this->findValueInDb($widthStr, 'width');

            // Radius
            preg_match('/([Rх-])([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $arr[1], $matchesRadius);

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

            // Indexes
            preg_match('/(.+? .+?) (.+) (\(.+)/u', $arr[1], $matches);

            $indexPowerStr = '';
            $indexSpeedStr = '';
            $strToCut      = '';

            if (strstr($matches[2], '/')) {
                // If in the basic string after sizes is the slash, perhaps there are indexes
                preg_match('|(.+?)/(.+?) |', $matches[2], $matchesIndexesSecond); // Explode by slash and stop on space

                if (preg_match('|[0-9]{2,3}$|',
                    $matchesIndexesSecond[1])) { // If there are only a numbers, there is 122/111A8
                    preg_match('/([0-9]{1,3}[\/?0-9]+?) ?([A-ZА-Я][0-9]? ),?/u', $matches[2], $matchesIndexes);

                    $indexPowerStr = trim($matchesIndexes[1]);

                    if (preg_match('|([0-9]{1,3})/$|', $indexPowerStr, $matchesIndexesThird)) {
                        $indexPowerStr = $matchesIndexesThird[1];
                    }

                    $indexSpeedStr = trim($matchesIndexes[2]);
                    $strToCut      = $matchesIndexes[0];

                } elseif (
                    preg_match('|([0-9]{2,3}) ?([A-ZА-Я][0-9]?)|u', $matchesIndexesSecond[1], $matchesIndexesThird)
                    & preg_match('|([0-9]{2,3})([A-ZА-Я][0-9]?)|u', $matchesIndexesSecond[2], $matchesIndexesFourth)
                ) {
                    // If there is double index type 118A6/114A8, we'll do the type 118/114 and speed А6/А8
                    $strToCutBeforeTranslit  = $matchesIndexesThird[2];
                    $strToCutBeforeTranslit2 = $matchesIndexesFourth[2];
                    $matchesIndexesThird[2]  = $this->translitIndex($matchesIndexesThird[2]);
                    $matchesIndexesFourth[2] = $this->translitIndex($matchesIndexesFourth[2]);

                    (trim($matchesIndexesThird[1]) == trim($matchesIndexesFourth[1]))
                        ? $indexPowerStr = trim($matchesIndexesThird[1])
                        : $indexPowerStr = trim($matchesIndexesThird[1]) . '/' . trim($matchesIndexesFourth[1]);

                    (trim($matchesIndexesThird[2]) == trim($matchesIndexesFourth[2]))
                        ? $indexSpeedStr = trim($matchesIndexesThird[2])
                        : $indexSpeedStr = trim($matchesIndexesThird[2]) . '/' . trim($matchesIndexesFourth[2]);

                    $strToCut = $matchesIndexesThird[1] . $strToCutBeforeTranslit . '/'
                                . $matchesIndexesFourth[1] . $strToCutBeforeTranslit2;

                } elseif (
                    preg_match('|([0-9]{2,3})([A-ZА-Я][0-9]?)|u', $matchesIndexesSecond[1], $matchesIndexesThird)
                    & preg_match('|(^[A-ZА-Я]$)|u', $matchesIndexesSecond[2], $matchesIndexesFourth)
                ) {
                    // if there are these indexes: 113A8/B, make double index of speed
                    $indexPowerStr = trim($matchesIndexesThird[1]);
                    $indexSpeedStr = trim($matchesIndexesThird[2]) . '/' . trim($matchesIndexesFourth[1]);
                    $strToCut      = $indexPowerStr . $indexSpeedStr;
                }

            } else {
                // If there is no slash, will find simple index
                $strWithoutBrackets = $matches[2];

                if (preg_match('|(\(.+?\))|u', $strWithoutBrackets, $matchesBracket)) {
                    $strWithoutBrackets = trim(strtr($strWithoutBrackets, [$matchesBracket[1] => '']));
                }

                if (preg_match('/^(?![A-ZА-Я])([0-9]{1,3})([A-ZА-Я][0-9]?),? /u', $strWithoutBrackets,
                    $matchesIndexes)) {
                    $indexPowerStr = trim($matchesIndexes[1]);
                    $indexSpeedStr = trim($matchesIndexes[2]);
                    $strToCut      = $indexPowerStr . $indexSpeedStr;
                }

                if (preg_match('/ (?![A-ZА-Я])([0-9]{1,3})([A-ZА-Я][0-9]?),? /u', $strWithoutBrackets,
                    $matchesIndexes2)) {
                    $indexPowerStr = trim($matchesIndexes2[1]);
                    $indexSpeedStr = trim($matchesIndexes2[2]);
                    $strToCut      = $indexPowerStr . $indexSpeedStr;
                }

                if (preg_match('|\[([0-9]{1,3})\] ?([A-ZА-Я][0-9]?)|u', $strWithoutBrackets, $strWithBrackets)) {
                    $indexPowerStr = trim($strWithBrackets[1]);
                    $indexSpeedStr = $this->translitIndex(trim($strWithBrackets[2]));
                    $strToCut      = "[$indexPowerStr] $indexSpeedStr";
                }

                if (preg_match('|(\([0-9]{2,3})([A-ZА-Я])\)|u', $matches[2], $matchesIndexInBracket)) {
                    $indexPowerStr = trim($matchesIndexInBracket[1]);
                    $indexSpeedStr = trim($matchesIndexInBracket[2]);
                    $strToCut      = $indexPowerStr . $indexSpeedStr;
                }
            }

            if (preg_match('|([0-9]{2,3})([A-ZА-Я]) ?\(?([0-9]{2,3})([A-ZА-Я])\)? |u', $matches[2],
                $matchesWithoutSlash)) {
                $matchesWithoutSlash[2] = $this->translitIndex($matchesWithoutSlash[2]);
                $matchesWithoutSlash[4] = $this->translitIndex($matchesWithoutSlash[4]);

                (trim($matchesWithoutSlash[1]) == trim($matchesWithoutSlash[3]))
                    ? $indexPowerStr = trim($matchesWithoutSlash[1])
                    : $indexPowerStr = trim($matchesWithoutSlash[1]) . '/' . trim($matchesWithoutSlash[3]);

                (trim($matchesWithoutSlash[2]) == trim($matchesWithoutSlash[4]))
                    ? $indexSpeedStr = trim($matchesWithoutSlash[2])
                    : $indexSpeedStr = trim($matchesWithoutSlash[2]) . '/' . trim($matchesWithoutSlash[4]);

                $strToCut = $matchesWithoutSlash[0];
            }
            // if the string of the type 'Шина 315/80R22,5 156/150L(154/150М) X MULTIWAY 3D XZE (Michelin)'
            if (preg_match('|([0-9]{3}/[0-9]{3})([A-ZА-Я])\(([0-9]{3}/[0-9]{3})([A-ZА-Я])\) |u', $matches[2],
                $matchesCustomOne)) {
                $indexPowerStr = $matchesCustomOne[1];
                $indexSpeedStr = $matchesCustomOne[2];
                $strToCut      = $matchesCustomOne[0];
            }
            // if the string of the type 'Шина 315/70R22,5 154/150(152/148) L/M ZR104 (Yokohama)'
            if (preg_match('|([0-9]{3}/[0-9]{3})\([0-9]{3}/[0-9]{3}\) ([A-ZА-Я])/([A-ZА-Я]) |u', $matches[2],
                $matchesCustomTwo)) {
                $indexPowerStr = $matchesCustomTwo[1];
                $indexSpeedStr = $matchesCustomTwo[2];
                $strToCut      = $matchesCustomTwo[0];
            }
            // if the string of the type 'Шина 275/70R22,5 148/145J 152/148E NU 301 (НкШЗ)'
            if (preg_match('|([0-9]{3}/[0-9]{3})([A-ZА-Я]) ([0-9]{3}/[0-9]{3})([A-ZА-Я])|u', $matches[2],
                $matchesCustomThree)) {
                $indexPowerStr = $matchesCustomThree[1];
                $indexSpeedStr = $matchesCustomThree[2];
                $strToCut      = $matchesCustomThree[0];
            }

            $indexSpeedStr = $this->translitIndex($indexSpeedStr);

            $this->findValueInDb($indexPowerStr, 'indexPower');

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Brand
            $brandStr = mb_strtoupper(trim(strtr($matches[3], ['(' => ' ', ')' => ' ', ',' => ' '])), 'UTF-8');

            if ($brandStr == 'НКШЗ') {
                $this->brand = 2; // КАМА
            } else {
                $this->findValueInDb($brandStr, 'brand');
            }

            // Model
            $this->model = trim(strtr($matches[2], ["$strToCut" => '']));

            if ($this->model == '') {
                $this->model = $matches[2];
            }
            // Is there something in the brackets of the model?
            if (preg_match('|(\(.+?\))|u', $this->model, $matchesBracket)) {
                $this->model = trim(strtr($this->model, [$matchesBracket[1] => '']));
            }

            $this->model = preg_replace('/\s\s+/', ' ', $this->model); // Clean double spaces

            // Comments (other)
            $otherStr = trim($arr[3]);
            switch ($otherStr) {
                case ' ':
                    $this->other = '';
                    break;
                case '' :
                    $this->other = '';
                    break;
                default :
                    $this->other = 'Ось, Применение: ' . $otherStr;
            }

            // Season
            $this->season = 0;

            // Camera is undefined
            $this->camera = 1;

            // Currency
            $this->money = 1;

            // Group
            $this->group = 2;

            // Availability
            $this->isIt = $arr[7];

            // Price
            $this->createPrice($arr[5], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

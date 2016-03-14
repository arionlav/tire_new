<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class OmegaSHImport provide logic for handling the 'Omega agriculture tires, import' price list
 *
 * @package app\models\handlers
 */
class OmegaSHImport extends Helper
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

        $listOnFile = 'Импорт СХ,КГШ,Индустр.';

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles, $listOnFile);

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(9);

        $this->goHandler($priceChange);
    }

    /**
     * The handler for 'Omega agriculture tires, import' price list
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
            if (! preg_match('/^ ?Шина/u', $arr[3])) {
                continue;
            }

            // Basic string
            $this->str = $arr[3];

            // Width
            preg_match('/([0-9]+)[.,]?([0-9]+)?([\/xхX]?)([0-9]+)?(.+)/', $arr[3], $matches);

            ($matches[2] != '00' and $matches[2] != '')
                ? $widthStr = $matches[1] . '.' . $matches[2]
                : $widthStr = $matches[1];

            $this->findValueInDb($widthStr, 'width');

            // Height
            preg_match('/([0-9]+)[.,]?([0-9]+)?([\/xхX]?)([0-9]+)?([.,])?([0-9]+)?(.+)/', $arr[3], $matchesHeight);

            if (preg_match('|([/xхX])|', $matchesHeight[3], $matchesDelimiter)) { // Is height there
                ($matchesHeight[5] != '00' and $matchesHeight[5] != '') // If divisional
                    ? $heightStr = $matchesHeight[4] . '.' . $matchesHeight[6]
                    : $heightStr = $matchesHeight[4];
            } else {
                $heightStr = '0';
            }

            $this->findValueInDb($heightStr, 'height');

            // Radius
            preg_match('/([RхxX-])([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $arr[3], $matchesRadius);

            if ($matchesDelimiter[1] == $matchesRadius[1] & $matchesDelimiter[1] == 'x') {
                preg_match('/x.+?(x)([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $arr[3], $matchesRadius);
            } elseif ($matchesDelimiter[1] == $matchesRadius[1] & $matchesDelimiter[1] == 'х') {
                preg_match('/х.+?(х)([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $arr[3], $matchesRadius);
            }

            if (preg_match('|([0-9]+)[.,]?([0-9]+)?([xхX-])([0-9]+)[.,]?([0-9]+)?([xх-])([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)|u',
                $arr[3], $matchesRadiusSecond)
            ) {
                $matchesRadius[2] = $matchesRadiusSecond[7];
                $matchesRadius[3] = $matchesRadiusSecond[8];
                $matchesRadius[4] = $matchesRadiusSecond[9];
            }

            ($matchesRadius[3] != '00' and $matchesRadius[3] != '')
                ? $radiusStr = $matchesRadius[2] . '.' . $matchesRadius[3]
                : $radiusStr = $matchesRadius[2];

            if ($matchesRadius[4] != '' & $radiusStr) {
                $radiusStr .= 'C';
            }

            $this->findValueInDb($radiusStr, 'radius');

            // Indexes
            preg_match('/(.+? .+?) (.+)/u', $arr[3], $matches);
            $indexPowerStr = '';
            $indexSpeedStr = '';
            $strToCut      = '';

            if (strstr($matches[2], '/')) {
                // If in the basic string after sizes is the slash, there are indexes
                // Explode by slash and stop on the space
                preg_match('|(.+?)/(.+?) |', $matches[2], $matchesIndexesSecond);

                if (preg_match('|[0-9]{2,3}$|', $matchesIndexesSecond[1])) {
                    // If there are only a numbers, there is 122/111A8
                    preg_match('/([0-9]{1,3}[\/?0-9]+?)([A-ZА-Я][0-9]? ),?/u', $matches[2], $matchesIndexes);

                    $indexPowerStr = trim($matchesIndexes[1]);

                    if (preg_match('|([0-9]{1,3})/$|', $indexPowerStr, $matchesIndexesThird)) {
                        $indexPowerStr = $matchesIndexesThird[1];
                    }
                    $indexSpeedStr = trim($matchesIndexes[2]);
                    $strToCut      = $matchesIndexes[0];
                } elseif (
                    preg_match('|([0-9]{2,3})([A-ZА-Я][0-9]?)|u', $matchesIndexesSecond[1], $matchesIndexesThird)
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

                if (! $indexPowerStr and ! $indexSpeedStr) {
                    $strWithoutBrackets = $matches[2];

                    if (preg_match('|(\(.+?\))|u', $strWithoutBrackets, $matchesBracket)) {
                        $strWithoutBrackets = trim(strtr($strWithoutBrackets, [$matchesBracket[1] => '']));
                    }

                    if (preg_match('/(?![A-ZА-Я])([0-9]{1,3})([A-ZА-Я][0-9]?),? /u', $strWithoutBrackets,
                        $matchesIndexes2)) {
                        $indexPowerStr = trim($matchesIndexes2[1]);
                        $indexSpeedStr = trim($matchesIndexes2[2]);
                        $strToCut      = $indexPowerStr . $indexSpeedStr;
                    }
                }
            } else {
                // If there is no slash, we are looking for a simple index
                $strWithoutBrackets = $matches[2];

                if (preg_match('|(\(.+?\))|u', $strWithoutBrackets, $matchesBracket)) {
                    $strWithoutBrackets = trim(strtr($strWithoutBrackets, [$matchesBracket[1] => '']));
                }

                if (preg_match('/ (?![A-ZА-Я])([0-9]{1,3})([A-ZА-Я][0-9]?),? /u', $strWithoutBrackets,
                    $matchesIndexes2)) {
                    $indexPowerStr = trim($matchesIndexes2[1]);
                    $indexSpeedStr = trim($matchesIndexes2[2]);
                    $strToCut      = $indexPowerStr . $indexSpeedStr;
                }

                if (preg_match('/^(?![A-ZА-Я])([0-9]{1,3})([A-ZА-Я][0-9]?),? /u', $strWithoutBrackets,
                    $matchesIndexes)) {
                    $indexPowerStr = trim($matchesIndexes[1]);
                    $indexSpeedStr = trim($matchesIndexes[2]);
                    $strToCut      = $indexPowerStr . $indexSpeedStr;
                }
            }

            // If we have a lonely speed index
            if (preg_match('|^([A-Z][1-8]?) |', $matches[2], $matchesSpeed)) {
                $indexSpeedStr = $matchesSpeed[1];
                $matches[2]    = trim(strtr($matches[2], ["$indexSpeedStr" => '']));
            }

            if (preg_match('| ?([0-9]{2,3})/?([A-ZА-Я][0-9]?)[ /]+\(?([0-9]{2,3})([A-ZА-Я][0-9]?)\)? |u',
                $matches[2], $matchesWithoutSlash)) {
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

            if (strpos($matches[2], '12A6E') !== false) {
                $indexPowerStr = '12';
                $indexSpeedStr = 'A6';
                $strToCut      = '12A6E';
            }

            if (strpos($matches[2], '103А6100А8') !== false) {
                $indexPowerStr = '103/100';
                $indexSpeedStr = 'A6/A8';
                $strToCut      = '103А6100А8';
            }

            if (strpos($matches[2], '161A8(161B)') !== false) {
                $indexPowerStr = '161';
                $indexSpeedStr = 'A8/B';
                $strToCut      = '161A8(161B)';
            }

            if (strpos($matches[2], '154/1428') !== false) {
                $indexPowerStr = '154/142';
                $indexSpeedStr = '';
                $strToCut      = '154/1428';
            }

            if (strpos($matches[2], '173D176A8') !== false) {
                $indexPowerStr = '173/176';
                $indexSpeedStr = 'D/A8';
                $strToCut      = '173D176A8';
            }

            $this->findValueInDb($indexPowerStr, 'indexPower');

            $indexSpeedStr = $this->translitIndex($indexSpeedStr);

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Model
            $this->model = $matches[2];

            if (! $strToCut) {
                $strToCut = 'something';
            }

            if (preg_match('|^[A-ZА-Я]$|ux', $strToCut)) {
                $strToCut = ' ' . $strToCut . ' ';
            }

            $this->model = strtr($this->model, [
                '28PR' => '', '20PR' => '', '40PR' => '', '18PR' => '',
                '16PR' => '', '12PR' => '', '14PR' => '', '10PR' => '',
                '2PR'  => '', '4PR' => '', '6PR' => '', '8PR' => '', "$strToCut" => ''
            ]);

            $minusWord = ['без камеры', 'с камерой', 'бескамерная', 'безкамерная'];
            foreach ($minusWord as $mw) {
                if (stristr($this->model, $mw)) {
                    $this->model = stristr($this->model, $mw, true);
                }
            }

            if ($this->model == '') {
                $this->model = $matches[2];
            }

            if (preg_match('|\(([A-ZА-Я].+?)\)(.+?) \(.+\)$|u', $matches[2], $matchesModelBrackets)) {
                $this->model = $matchesModelBrackets[1];
            }

            // Delete all content in the brackets
            for ($j = 1; $j <= 2; $j++) {
                if (preg_match('|(\(.+?\))|u', $this->model, $matchesBracket)) {
                    $this->model = trim(strtr($this->model, [$matchesBracket[1] => '']));
                }
            }

            if (preg_match('|^[,/](.+)|u', $this->model, $matchesModelComa)) { // if it starts with a comma
                $this->model = $matchesModelComa[1];
            }

            if (preg_match('|(.+),$|u', $this->model,
                $matchesModelComa)) { // if the is a comma at the end of the string
                $this->model = $matchesModelComa[1];
            }

            $this->model = trim(preg_replace('/\s\s+/', ' ', $this->model)); // Clean double spaces

            if (strpos($arr[3], 'TT (Continental)') !== false and strpos($this->model, 'TT') === false) {
                $this->model .= ' TT';
            }

            // Brand
            preg_match('/(.+) (\(.+\)$)/u', $arr[3], $matches);

            $brandStr = mb_strtoupper(trim(strtr($matches[2], ['(' => ' ', ')' => ' ', ',' => ' '])), 'UTF-8');

            if ($brandStr == 'НКШЗ') {
                $this->brand = 2; // КАМА
            } else {
                $this->findValueInDb($brandStr, 'brand');
            }

            if ($this->model == '') {
                $this->model = $brandStr;
            }

            // Camera
            $this->camera = 1;

            $ourStr = $arr[3];

            $strCamera = mb_strtolower($ourStr, 'UTF-8');

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

            // Comments (other)
            $otherStr = trim($arr[8]);
            switch ($otherStr) {
                case ' ':
                    $this->other = '';
                    break;
                case '':
                    $this->other = '';
                    break;
                default:
                    $this->other = 'Применение: ' . $otherStr;
            }

            // Season
            $this->season = 0;

            // Currency
            $this->money = 1;

            // Group
            $this->group = 2;

            // Availability
            $this->isIt = $arr[7];

            // Price
            $this->createPrice($arr[6], $priceChange);

            // Create price and check price movement
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Slava provide logic for handling the Slavick price list
 *
 * @package app\models\handlers
 */
class Slava extends Helper
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
            $this->file2array[2][1] != 'Найменування'
            and $this->file2array[2][3] != 'Ціна прод.'
                and $this->file2array[2][4] != 'Залишок'
        ) {
            App::redirect(['site/error', 'e' => "Возникла ошибка. Обратитесь к системному администратору."]);
        }

        // Get prices before replace them
        $this->oldPrice = $this->getOldPrice();

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Remove rows with header
        $this->deleteUnusedRows(3);

        $this->course = static::getCourse();

        $this->goHandler($priceChange);
    }

    /**
     * The handler for Slavick price list
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
            if (is_null($arr[3]) or $arr[3] == '') {
                continue;
            }

            // Basic string
            $this->str = $arr[1];

            // Explode basic string to spaces
            $arr[1] = trim(preg_replace('/\s\s+/', ' ', $arr[1])); // Clean double spaces

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
            preg_match('/([RхxX-]) ?([0-9]{2})[.,]?([0-9])? ?([CС]?)(.+)/u', $arr[1], $matchesRadius);
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
            $modelStartStr =
                strtr(trim($matchesRadius[5]), ['8PR' => '', '10PR' => '', '6PR' => '', '4 x 4' => '4x4', ',' => '',
                                                "{$indexPowerStr}" => '', "{$matchesIndexes[3]} " => '']);

            $minusWord = ['сп', 'kor.', 'usa.', 'перед', 'лето'];

            foreach ($minusWord as $asw) {
                if (stristr($modelStartStr, $asw)) {
                    $modelStartStr = stristr($modelStartStr, $asw, true);
                }
            }
            $this->model = $modelStartStr;
            $this->model = trim(preg_replace('/\s\s+/', ' ', $this->model)); // Clean double spaces

            // Brand
            if ($arr[2] == 'GOOD/YEAR' or $arr[2] == 'Good year') {
                $this->brand = '47'; // GOODYEAR
            } else {
                $brandStr = $this->translitIndex(mb_strtoupper($arr[2], 'UTF-8'));

                $this->findValueInDb($brandStr, 'brand');
            }

            // Season
            $this->season = '2';

            // Availability
            $this->isIt = $arr[4];

            // Group
            switch ($this->radius) {
                case '2':
                    $this->group = '1';
                    break; // 13
                case '29':
                    $this->group = '3';
                    break; // 13c
                case '3':
                    $this->group = '1';
                    break; // 14
                case '44':
                    $this->group = '2';
                    break; // 14.5
                case '19':
                    $this->group = '3';
                    break; // 14C
                case '4':
                    $this->group = '1';
                    break; // 15
                case '50':
                    $this->group = '2';
                    break; // 15.5
                case '16':
                    $this->group = '3';
                    break; // 15C
                case '5':
                    $this->group = '1';
                    break; // 16
                case '38':
                    $this->group = '2';
                    break; // 16.5
                case '18':
                    $this->group = '3';
                    break; // 16C
                case '6':
                    $this->group = '1';
                    break; // 17
                case '7':
                    $this->group = '2';
                    break; // 17.5
                case '20':
                    $this->group = '3';
                    break; // 17C
                case '8':
                    $this->group = '1';
                    break; // 18
                case '21':
                    $this->group = '3';
                    break; // 18C
                case '9':
                    $this->group = '1';
                    break; // 19
                case '10':
                    $this->group = '2';
                    break; // 19.5
                case '11':
                    $this->group = '1';
                    break; // 20
                case '12':
                    $this->group = '1';
                    break; // 21
                case '13':
                    $this->group = '1';
                    break; // 22
                case '14':
                    $this->group = '2';
                    break; // 22.5
                default:
                    $this->group = '5';
            }
            // If this is R20
            if ($this->radius == '11') {
                // If an index of speed is less of L
                if ($this->indexSpeed <= 3 and $this->indexSpeed > 17) {
                    // If the height is a fractional
                    // If width 10, 11 or 12, then it is a vantage tire
                    if (
                        $this->height == '1'
                        or $this->height == '2'
                        or $this->height == '3'
                        or $this->height == '21'
                        or $this->height == '22'
                        or $this->height == '23'
                        or $this->height == '25'
                        or $this->height == '29'
                        or $this->height == '30'
                        or $this->height == '32'
                        or $this->width == '55'
                        or $this->width == '56'
                        or $this->width == '57'
                    ) {
                        $this->group = '2';
                    } else {
                        $this->group = '1';
                    }
                } else {
                    $this->group = '1';
                }
            }

            // Camera is undefined
            $this->camera = '1';

            // Currency
            $this->money = 2;

            // Note are empty
            $this->other = '';

            // Price
            $this->createPrice($arr[3], $priceChange, 'usd');

            // Create price and check price movement
            $this->checkPriceMove($arr[1]);

            $this->insertRows();
        }

        return true;
    }
}

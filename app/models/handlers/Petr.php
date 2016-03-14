<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;

/**
 * Class Petr provide logic for handling the Petr price list
 *
 * @package app\models\handlers
 */
class Petr extends Helper
{
    /**
     * @var float Summer (2) Bridgestone (61)
     */
    private $factorSummerBridge;
    /**
     * @var float Summer (2) Yokohama (38)
     */
    private $factorSummerYokohama;
    /**
     * @var float Vantage (2)
     */
    private $factorVantage;
    /**
     * @var float Summer (2) all
     */
    private $factorSummer;
    /**
     * @var float Winter (1) all
     */
    private $factorWinter;

    /**
     * Create config and run $this->goHandler() method
     *
     * @param int $list Price list id
     */
    public function run($list)
    {
        self::$list = $list;

        // PRICE FACTOR
        $this->factorSummerBridge   = 1.12; // Summer (2) Bridgestone (61) - 1.12
        $this->factorSummerYokohama = 1.13; // Лето (2) Yokohama (38) - 1.13
        $this->factorVantage        = 1.07; // Vantage (2) - 1.07
        $this->factorSummer         = 1.1; // Summer (2) all - 1.1
        $this->factorWinter         = 1.15; // Winter (1) all - 1.15

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles);

        if (
            $this->file2array[2][1] != 'Фірма'
            and $this->file2array[2][2] != 'Товар'
                and $this->file2array[2][6] != 'Замовлення'
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

        $this->goHandler();
    }

    /**
     * The handler for Petr price list
     *
     * @return true
     * @throws GenerateException
     */
    private function goHandler()
    {
        if (empty($this->file2array)) {
            GenerateException::getException('Rows in file does not find', __CLASS__, __LINE__);
        }

        foreach ($this->file2array as $arr) {
            if (is_null($arr[0]) or $arr[0] == '') {
                continue;
            }

            // Basic string
            $this->str = $arr[2];

            // Explode basic string by spaces
            $arr[2] = trim(preg_replace('/\s\s+/', ' ', $arr[2])); // Clean double spaces
            $arr[2] = strtr($arr[2], ['\'' => '']);

            preg_match('|([0-9]+)/?([0-9]+)? ?Z?R(.+?) \[?(.+?)\]? (.+?) (.+)|u', trim($arr[2]), $matches);

            $customStr = 0;

            if (preg_match('|([0-9]{2})X(.+)? - ([0-9]{2}) (.+)? 6PR \[([0-9]{2})([A-Z])\]|u', $arr[2],
                $matchesCustom)) {
                $customStr = 1;
            }

            // Width
            $widthStr = $matches[1];

            if ($customStr == 1) {
                $widthStr = $matchesCustom[1];
            }

            $this->findValueInDb($widthStr, 'width');

            // Height
            $heightStr = $matches[2];

            if ($customStr == 1) {
                $heightStr = $matchesCustom[2];
            }

            $this->findValueInDb($heightStr, 'height');

            // Radius
            $radiusStr = $matches[3];

            if ($customStr == 1) {
                $radiusStr = $matchesCustom[3];
            } else {
                if (strpos($radiusStr, 'С') !== false) {
                    preg_match('|[0-9]+|u', $radiusStr, $matchesRC);
                    $matchesRC[0] .= 'C';
                    $radiusStr = $matchesRC[0];
                }
            }

            $this->findValueInDb($radiusStr, 'radius');

            // Index power
            $indexPowerStr = $matches[4];

            if ($customStr == 1) {
                $indexPowerStr = $matchesCustom[5];
            }

            $this->findValueInDb($indexPowerStr, 'indexPower');

            // Index speed
            $indexSpeedStr = $this->translitIndex($matches[5]);

            if ($customStr == 1) {
                $indexSpeedStr = $this->translitIndex($matchesCustom[6]);
            }

            $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Brand
            if ($arr[1] == 'GOOD/YEAR') {
                $this->brand = '47'; // GOODYEAR
            } else {
                $brandStr = mb_strtoupper($arr[1], 'UTF-8');

                $this->findValueInDb($brandStr, 'brand');
            }

            // Model
            $this->model = $matches[6];

            if ($customStr == 1) {
                $this->model = $matchesCustom[4];
            } elseif (! $this->indexSpeed) {
                preg_match('|([0-9]+)/?([0-9]+) Z?R(.+?) \[?(.+?)\]? (.+)|u', trim($arr[2]), $matchesModel);
                $this->model = $matchesModel[5];
            }

            // Comments (other)
            switch ($arr[7]) {
                case ' ':
                    $this->other = '';
                    break;
                case '':
                    $this->other = '';
                    break;
                default:
                    $this->other = 'Страна производства: ' . $this->translitCountry($arr[7]);
            }

            // Season
            $seasonStr = mb_strtoupper($arr[3], 'UTF-8');
            if (strpos($seasonStr, 'ЗИМ') !== false) {
                $this->season = '1';
            } elseif (strpos($seasonStr, 'ЛІТ') !== false) {
                $this->season = '2';
            } elseif (strstr($seasonStr, 'СЕЗ') !== false) {
                $this->season = '3';
            } else {
                $this->season = '0';
            }

            // Group
            (strstr($seasonStr, 'ВАНТАЖ'))
                ? $this->group = '2'
                : $this->group = '1';
            // Group by the radius
            if (
                $this->radius == '29'
                or $this->radius == '19'
                or $this->radius == '16'
                or $this->radius == '18'
                or $this->radius == '20'
                or $this->radius == '21'
            ) {
                $this->group = '3';
            }

            // Camera is undefined
            $this->camera = '1';

            // Currency
            $this->money = 2;

            // Availability
            $this->isIt = $arr[4];

            // Price
            $this->createPrice($arr[5], null, 'usd');

            // Correct our price with special factor
            if ($this->season == '2' & $this->brand == '61') {
                $this->price /= $this->factorSummerBridge;
            } elseif ($this->season == '2' & $this->brand == '38') {
                $this->price /= $this->factorSummerYokohama;
            } elseif ($this->group == '2') {
                $this->price /= $this->factorVantage;
            } elseif ($this->season == '2') {
                $this->price /= $this->factorSummer;
            } elseif ($this->season == '1') {
                $this->price /= $this->factorWinter;
            }

            $this->price = number_format($this->price, 2, '.', '');

            // Create price and check price move
            $this->checkPriceMove($this->str);

            $this->insertRows();
        }

        return true;
    }
}

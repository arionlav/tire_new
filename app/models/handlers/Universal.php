<?php
namespace app\models\handlers;

use config\App;
use core\helpers\GenerateException;
use core\helpers\Query;

/**
 * Class Universal provide logic for handling the price list by Universal method
 *
 * @package app\models\handlers
 */
class Universal extends Helper
{
    /**
     * @var float Course for Ascania price list
     */
    private $course4Ascania;
    /**
     * @var string First word in basic string
     */
    private $strFirstWord;

    /**
     * Create config and run $this->goHandler() method
     *
     * @param int    $flag        'Insert' or 'Add' mode
     * @param array  $priceChange Price change factor
     * @param string $data        Serialize type
     * @return int Price list id
     */
    public function run($flag, $priceChange, $data)
    {
        $post       = unserialize($data);
        $listOnFile = $post['pageName'];
        $startValue = $post['startValue'];
        $listName   = $post['listName'];
        $listCity   = $post['listCity'];

        $this->strFirstWord = $post['firstWordInStartStr'];

        if ($flag == -1) { // insert mode
            if (! $this->checkInputListName($listName)) {
                return false;
            }
            // If we are there, name of new price list is unique and does not empty, write it
            $this->insertNewListData($listName, $listCity, $data);
            // Take from this new price list id
            self::$list = $this->setListId();
        } else { // add mode
            self::$list = $flag;

            // Get prices before replace them
            $this->oldPrice = $this->getOldPrice();
        }

        // Remove from database all rows for current price list
        $this->deleteCurrentList();

        // Get array $file2array with all rows from excel file
        $this->file2array = $this->loadExcelFile(App::$pathToLoadFiles, $listOnFile);

        // Take course for Ascania light tires before removing header
        $this->course4Ascania = $this->getAscaniaCourse();

        // Remove rows with header
        if ($startValue != '') {
            if (! $this->deleteUnusedRows($startValue)) {
                return false;
            }
        }

        $this->goHandler($priceChange, $post, $flag);

        return self::$list;
    }

    /**
     * The handler
     *
     * @param array $priceChange Price change factor
     * @param array $post        Rules for handler
     * @param int   $flag        If != -1, run $this->checkPriceMove($this->str)
     * @return true
     * @throws GenerateException
     */
    private function goHandler($priceChange, $post, $flag)
    {
        if (empty($this->file2array)) {
            GenerateException::getException('Rows in file does not find', __CLASS__, __LINE__);
        }

        $strRowStr    = $post['str'];
        $widthMethod  = $post['widthIs'];
        $widthRowStr  = $post['widthRow'];
        $heightMethod = $post['heightIs'];
        $heightRowStr = $post['heightRow'];
        $strRow       = '';

        foreach ($this->file2array as $arr) {
            // The basic string
            // If we are set column for the basic string, write it, or If we are not set this column,
            // collect the basic string in the end
            // If set start word for basic string and we are not find them, return in foreach
            if ($strRowStr != '') {
                // define the column number
                $strRow = $this->setRow($strRowStr);

                $this->str = trim($arr[$strRow]);
            } else {
                $this->str = '';
            }

            // Width
            $widthStr = '';
            if ($widthMethod == 'widthIsRow') {
                if ($widthRowStr != '') {
                    // define the column number
                    $widthRow = $this->setRow($widthRowStr);

                    // Set regexp, check if there column with other sizes
                    if (preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX]?)([0-9]+)?(.+)?|u', $arr[$widthRow],
                        $matchesWidth)) {
                        ($matchesWidth[2] != '00' and $matchesWidth[2] != '')
                            ? $widthStr = $matchesWidth[1] . '.' . $matchesWidth[2]
                            : $widthStr = $matchesWidth[1];
                    } else {
                        $widthStr = $arr[$widthRow];
                    }

                } else {
                    $widthStr = 'Не указано';
                    if ($post['widthSearchStrToo']) {
                        $widthMethod = 'widthIsStr';
                    } // If the checkbox: Find in basic string - is enable
                }
            }

            if ($widthMethod == 'widthIsStr') { // If set: Find width in the basic string
                preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX]?)([0-9]+)?(.+)|u', $this->str, $matches);
                ($matches[2] != '00' and $matches[2] != '')
                    ? $widthStr = $matches[1] . '.' . $matches[2]
                    : $widthStr = $matches[1];
            }

            $widthName = $this->findValueInDb($widthStr, 'width');

            // Height
            $heightStr        = '';
            $matchesDelimiter = [];
            if ($heightMethod == 'heightIsRow') { // for column
                if ($heightRowStr != '') {
                    // define the column number
                    $heightRow = $this->setRow($heightRowStr);

                    if (preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX]?)([0-9]+)?([.,])?([0-9]+)?(.+)|u', $arr[$heightRow],
                        $matchesHeight)) {
                        $heightStr = trim($arr[$heightRow]);
                        if (preg_match('|([/xхX])|u', $matchesHeight[3], $matchesDelimiter)) { // Is height there
                            ($matchesHeight[5] != '00' and $matchesHeight[5] != '') // If divisional
                                ? $heightStr = $matchesHeight[4] . '.' . $matchesHeight[6]
                                : $heightStr = $matchesHeight[4];
                        }
                    } else {
                        $heightStr = $arr[$heightRow];
                    }
                } else {
                    $heightStr = 'Не указано';
                }

                if (! $heightStr) {
                    if ($post['heightSearchStrToo']) {
                        $heightMethod = 'heightIsStr';
                    }
                }
            }

            if ($heightMethod == 'heightIsStr') { // Set: Find height in the basic string
                preg_match('|([0-9]+)[.,]?([0-9]+)?([/xхX]?)([0-9]+)?([.,])?([0-9]+)?(.+)|u', $this->str,
                    $matchesHeight);
                if (preg_match('|([/xхX])|u', $matchesHeight[3], $matchesDelimiter)) { // Is height there
                    ($matchesHeight[5] != '00' and $matchesHeight[5] != '') // If divisional
                        ? $heightStr = $matchesHeight[4] . '.' . $matchesHeight[6]
                        : $heightStr = $matchesHeight[4];
                } else {
                    $heightStr = 0;
                }
            }
            $heightName = $this->findValueInDb($heightStr, 'height');

            // Radius
            $radiusMethod = $post['radiusIs'];
            $radiusRowStr = $post['radiusRow'];
            $radiusStr    = '';
            if ($radiusMethod == 'radiusIsRow') {
                if ($radiusRowStr != '') {
                    // define the column number
                    $radiusRow = $this->setRow($radiusRowStr);

                    preg_match('/([RхxX-]) ?([0-9]+)[.,]?([0-9]+)? ?([CС]?)(.+)?/u', $arr[$radiusRow], $matchesRadius);

                    if ($matchesDelimiter[1] == $matchesRadius[1] & $matchesDelimiter[1] == 'x') {
                        preg_match('/x.+?(x)([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $arr[$radiusRow], $matchesRadius);
                    } elseif ($matchesDelimiter[1] == $matchesRadius[1] & $matchesDelimiter[1] == 'х') {
                        preg_match('/х.+?(х)([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $arr[$radiusRow], $matchesRadius);
                    }

                    if (preg_match(
                        '|([0-9]+)[.,]?([0-9]+)?([xхX-])([0-9]+)[.,]?([0-9]+)?([xх-])([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)|u',
                        $arr[$radiusRow], $matchesRadiusSecond)) {
                        $matchesRadius[2] = $matchesRadiusSecond[7];
                        $matchesRadius[3] = $matchesRadiusSecond[8];
                        $matchesRadius[4] = $matchesRadiusSecond[9];
                    }

                    ($matchesRadius[3] != '00' and $matchesRadius[3] != '')
                        ? $radiusStr = $matchesRadius[2] . '.' . $matchesRadius[3]
                        : $radiusStr = $matchesRadius[2];

                    if ($matchesRadius[4] !== '' and $radiusStr) {
                        $radiusStr .= 'C';
                    }

                    // If in the column simple define radius, may be with 'C'
                    if (preg_match('|^"?([0-9]{1,2}[.,]?[0-9]+?[CС]?)$|u', $arr[$radiusRow], $matchesRadius)) {
                        $radiusStr = $matchesRadius[1];
                        if (strpos($radiusStr, ',') !== false) {
                            $radiusStr = strtr($radiusStr, [',' => '.']);
                        }
                        if (strpos($radiusStr, 'С') !== false) {
                            $radiusStr = $this->translit($matchesRadius[1]);
                        }
                    }
                } else {
                    $radiusStr = 'Не указано';
                }
                if (! $radiusStr) {
                    if ($post['radiusSearchStrToo']) {
                        $radiusMethod = 'radiusIsStr';
                    }
                }
            }
            if ($radiusMethod == 'radiusIsStr') { // If set: Find radius in the basic string
                preg_match('/([RхxX-]) ?([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/u', $this->str, $matchesRadius);

                if ($matchesDelimiter[1] == $matchesRadius[1] & $matchesDelimiter[1] == 'x') {
                    preg_match('/x.+?(x)([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $this->str, $matchesRadius);
                } elseif ($matchesDelimiter[1] == $matchesRadius[1] & $matchesDelimiter[1] == 'х') {
                    preg_match('/х.+?(х)([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)/', $this->str, $matchesRadius);
                }

                if (preg_match(
                    '|([0-9]+)[.,]?([0-9]+)?([xхX-])([0-9]+)[.,]?([0-9]+)?([xх-])([0-9]+)[.,]?([0-9]+)?([CС]?)(.+)|u',
                    $this->str, $matchesRadiusSecond)) {
                    $matchesRadius[2] = $matchesRadiusSecond[7];
                    $matchesRadius[3] = $matchesRadiusSecond[8];
                    $matchesRadius[4] = $matchesRadiusSecond[9];
                }

                ($matchesRadius[3] != '00' and $matchesRadius[3] != '')
                    ? $radiusStr = $matchesRadius[2] . '.' . $matchesRadius[3]
                    : $radiusStr = $matchesRadius[2];

                if ($matchesRadius[4] !== '' and $radiusStr) {
                    $radiusStr .= 'C';
                }
            }

            $radiusName = $this->findValueInDb($radiusStr, 'radius');

            // Indexes
            $indexPowerMethod = $post['indexPowerIs'];
            $indexPowerRowStr = $post['indexPowerRow'];
            $indexSpeedRowStr = $post['indexSpeedRow'];

            if ($indexPowerMethod == 'indexPowerIsRow') {
                if ($indexPowerRowStr != '') {
                    // define the column number
                    $indexPowerRow = $this->setRow($indexPowerRowStr);

                    preg_match('|([/?0-9]+)|u', $arr[$indexPowerRow], $matchesIndexPower);
                    $indexPowerStr = $matchesIndexPower[1];
                } else {
                    $indexPowerStr = 'Не указано';
                }

                if ($indexSpeedRowStr != '') {
                    // define the column number
                    $indexSpeedRow = $this->setRow($indexSpeedRowStr);

                    preg_match('|([A-ZА-Я][0-9]?)|u', $arr[$indexSpeedRow], $matchesIndexSpeed);
                    $indexSpeedStrFirst = trim($this->translitIndex($matchesIndexSpeed[1]));
                    $indexSpeedStr      = $indexSpeedStrFirst;
                } else {
                    $indexSpeedStr = 'Не указано';
                }

                $strToCut = $indexPowerStr . $indexSpeedStr;

            } else { // If set: Find indexes in the basic string, use regexp
                preg_match('/(.+? .+?) (.+)/u', $this->str, $matches);
                $indexPowerStr = '';
                $indexSpeedStr = '';
                $strToCut      = '';
                if (strpos($matches[2], '/') !== false) {
                    // if in the basic string is slash after sizes, it is the indexes
                    preg_match('|(.+?)/(.+?) |', $matches[2],
                        $matchesIndexesSecond); // explode by slash and stop on the space

                    if (preg_match('|[0-9]{2,3}$|',
                        $matchesIndexesSecond[1])) { // If there is only numbers, it is 122/111A8
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
                        // If there is type of index 118A6/114A8, make from it 118/114 and speed А6/А8
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
                        // If there is type of index 113A8/B, make from it double index of speed
                        $indexPowerStr = trim($matchesIndexesThird[1]);
                        $indexSpeedStr = trim($matchesIndexesThird[2]) . '/' . trim($matchesIndexesFourth[1]);
                        $strToCut      = $indexPowerStr . $indexSpeedStr;
                    }

                    // If there is type of index ...[112/115] T ..., drop brackets and write as is
                    if (strpos($matchesIndexesSecond[1], '[') !== false and strpos($matchesIndexesSecond[2], ']')
                                                                            !== false
                    ) {
                        $indexPowerStr = trim(strtr($matchesIndexesSecond[1], '[', ' ')) . '/'
                                         . trim(strtr($matchesIndexesSecond[2], ']', ' '));

                        preg_match('|\[.+?\] ?([A-ZА-Я][0-9]?)|u', $matches[2], $matchesSpeedSecond);

                        $indexSpeedStr = $this->translitIndex(trim($matchesSpeedSecond[1]));
                        $strToCut      = "[$indexPowerStr] $indexSpeedStr";
                    }
                } else {
                    // If there is not a slash, find simple index
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

                // If there is lonely index of speed at the start
                if (preg_match('|^([A-Z][1-8]?) |', $matches[2], $matchesSpeed)) {
                    $indexSpeedStr = $matchesSpeed[1];
                    $matches[2]    = trim(strtr($matches[2], ["$indexSpeedStr" => '']));
                }
            }

            $indexPowerName = $this->findValueInDb($indexPowerStr, 'indexPower');
            $indexSpeedName = $this->findValueInDb($indexSpeedStr, 'indexSpeed');

            // Model
            $modelMethod = $post['modelIs'];
            $modelRowStr = $post['modelRow'];

            if ($modelMethod == 'modelIsRow') {
                if ($modelRowStr != '') {
                    // define the column number
                    $modelRow = $this->setRow($modelRowStr);

                    $this->model = $arr[$modelRow];
                } else {
                    $this->model = 'Не указано';
                }
            } else {
                preg_match('/(.+? .+?) (.+)/u', $this->str, $matches);
                $this->model = strtr($matches[2], [
                    $strToCut     => '',
                    'без камеры'  => '',
                    'БЕЗ КАМЕРЫ'  => '',
                    'с камерой'   => '',
                    'бескамерная' => '',
                    'безкамерная' => ''
                ]);

                if ($this->model == '') {
                    $this->model = $matches[2];
                }

                // Delete all content in braces
                if ($post['braceYesNo']) {
                    if (preg_match('|(\(.+\))|u', $this->model, $matchesModelBrace)) {
                        $this->model = strtr($this->model, [$matchesModelBrace[1] => ' ']);
                    }
                }

                // May be we must delete just braces?
                if ($post['braceYesNoSame']) {
                    $this->model = trim(strtr($this->model, ['(' => ' ', ')' => ' ', ',' => ' ']));
                }

                // If set unnecessary words
                if ($post['modelWithoutWords'] != '') {
                    $minusWords      = $post['modelWithoutWords'];
                    $arrayMinusWords = explode(',', $minusWords);

                    foreach ($arrayMinusWords as $key => $amw) {
                        if ($amw == '') {
                            continue;
                        }
                        if (stristr($this->model, $amw)) {
                            $this->model = stristr($this->model, trim($amw), true);
                        }
                    }
                }

                if (preg_match('|^,(.+)|u', $this->model, $matchesModelComa)) { // if start is with comma
                    $this->model = $matchesModelComa[1];
                }

                $this->model = trim(preg_replace('/\s\s+/', ' ', $this->model)); // Clean double spaces

                if (preg_match('|(.+),$|u', $this->model, $matchesModelComa)) { // if is comma in the end
                    $this->model = $matchesModelComa[1];
                }
            }

            // Brand
            $brandArray  = $this->getValueFromDB('brand');
            $brandMethod = $post['brandIs'];
            $brandStr    = '';

            $brandRowStrOneForAllBottom = $post['brandRowOneForAllBottom'];

            if ($brandMethod == 'brandIsRow') { // on the column
                $brandRowStr = $post['brandRow'];
                if ($brandRowStr != '') {
                    // define the column number
                    $brandRow = $this->setRow($brandRowStr);

                    $brandStr = trim(mb_strtoupper($arr[$brandRow], 'UTF-8'));
                } else {
                    $brandStr = 'Не указан';
                }
            } elseif ($brandMethod == 'brandIsStr') {
                if ($post['brandIsStrIn'] == 'brandAfterSize') {
                    preg_match('/(.+? .+?) (.+) (\(.+\)$)/u', trim($this->str), $matches);
                    $brandStr = mb_strtoupper(trim(strtr($matches[3], ['(' => ' ', ')' => ' ', ',' => ' '])), 'UTF-8');

                } elseif ($post['brandIsStrIn'] == 'brandAnyware') {
                    foreach ($brandArray as $braa) {
                        if (strstr($this->str, $braa['nameBrand'])) {
                            $brandStr = $braa['nameBrand'];
                            break;
                        }
                    }
                }
            } elseif ($brandMethod == 'brandIsLonely') {
                $brandRowStr = $post['brandRowLonely'];
                // We need to determine is it subtopic or simple string
                // We think that if there is not define radius and width it is possibly to subtopic

                if ($this->width == 1 and $this->radius == 0) {
                    // Get array from database with all brands and look in the specified column
                    if ($brandRowStr != '') {
                        // define the column number
                        $brandRow = $this->setRow($brandRowStr);

                        $brandArrayExample = $brandArray;
                        $arr[$brandRow]    = mb_strtoupper($arr[$brandRow], 'UTF-8');

                        if ($arr[$brandRow] == 'GOOD/YEAR') {
                            $arr[$brandRow] = 'GOODYEAR';
                        }

                        foreach ($brandArrayExample as $bae) {
                            if (strpos($arr[$brandRow], $bae['nameBrand']) !== false) {
                                $brandStr = $bae['nameBrand'];
                                break;
                            } else {
                                $brandStr = 0;
                            }
                        }
                    } // Else let it stay brand value as $brandStr
                }
            } elseif ($brandMethod == 'brandIsOneForAllBottom') {
                if ($brandRowStrOneForAllBottom != '') {
                    // define the column number
                    $brandRow = $this->setRow($brandRowStrOneForAllBottom);

                    $brandArrayExample = $brandArray;
                    $arr[$brandRow]    = mb_strtoupper($arr[$brandRow], 'UTF-8');

                    foreach ($brandArrayExample as $bae) {
                        if (strstr($arr[$brandRow], $bae['nameBrand'])) {
                            $brandStr = $bae['nameBrand'];
                            break;
                        }
                    }
                    // If it looking for subtopic, reset brand
                    if ($this->width == 1 and $this->radius == 0) {
                        $brandStr = 'Не указан';
                    }
                } // else let it stay brand value as $brandStr
            } elseif ($brandMethod == 'brandIsRowAnywere') {
                if ($post['brandIsRowAnywere'] != '') {
                    // define the column number
                    $brandRow = $this->setRow($post['brandIsRowAnywere']);

                    $brandStrPre = strtoupper($arr[$brandRow]);

                    foreach ($brandArray as $braa) {
                        if (strstr($brandStrPre, $braa['nameBrand'])) {
                            $brandStr = $braa['nameBrand'];
                            break;
                        }
                    }
                }
            }

            if (
                $brandStr == 'НКШЗ'
                or $brandStr == 'НКЗШ'
                or $brandStr == 'БШК'
            ) {
                $brandStr = 'КАМА';
            } elseif ($brandStr == 'GOOD/YEAR') {
                $brandStr = 'GOODYEAR';
            } elseif (strpos($brandStr, 'ГРУЗОВАЯ') !== false) {
                $brandStr = trim(stristr($brandStr, 'ГРУЗОВАЯ', true));
            }

            $this->brand = $brandArray[0]['idBrand'];
            foreach ($brandArray as $ba) {
                if ($brandStr == $ba['nameBrand']) {
                    $this->brand = $ba['idBrand'];
                    break;
                }
            }

            if ($brandMethod == 'brandOne') {
                $this->brand = $post['idBrand'];
            }

            // If the basic string is not define, collect its from parameters
            if ($strRowStr == '') {
                $this->str =
                    "Шина {$widthName}/{$heightName}R{$radiusName} {$indexPowerName}{$indexSpeedName} {$arr[3]} {$arr[5]}";

                if (strstr($this->str, 'Не указано')) {
                    $this->str = strtr($this->str, ['Не указано' => '-']);
                }
            }

            // Group
            $groupMethod = $post['groupIs'];
            $groupRowStr = $post['groupRow'];

            if ($groupMethod == 'groupIsRow') { // for column
                if ($groupRowStr != '') {
                    // define the column number
                    $groupRow = $this->setRow($groupRowStr);

                    $groupStr = mb_strtolower($arr[$groupRow], 'UTF-8');
                    if (strpos($groupStr, 'легков') !== false) {
                        $this->group = 1;
                    } elseif (
                        strpos($groupStr, 'груз') !== false
                        or strpos($groupStr, 'строит') !== false
                        or strpos($groupStr, 'с/х') !== false
                    ) {
                        $this->group = 2;
                    } elseif (strpos($groupStr, 'легкогруз') !== false) {
                        $this->group = 3;
                    } elseif (strpos($groupStr, 'внедорож') !== false) {
                        $this->group = 4;
                    } else {
                        $this->group = 5;
                    }
                } else {
                    $this->group = 5;
                }

            } elseif ($groupMethod == 'groupIsStr') { // if group one for all price list
                $this->group = $post['idGroup'];
            } elseif ($groupMethod == 'groupIsLonely') { // if set in subtopic, determine is it subtopic
                $groupRowStr = $post['groupRowIsLonely'];
                if ($this->width == 1 and $this->radius == 0) { // It is subtopic. Check there words
                    if ($groupRowStr != '') {
                        // define the column number
                        $groupRow = $this->setRow($groupRowStr);

                        $groupStr = mb_strtolower($arr[$groupRow], 'UTF-8');
                        if (strpos($groupStr, 'легков') !== false) {
                            $this->group = 1;
                        } elseif (
                            strpos($groupStr, 'груз') !== false
                            or strpos($groupStr, 'строит') !== false
                            or strpos($groupStr, 'с/х') !== false
                        ) {
                            $this->group = 2;
                        } elseif (strpos($groupStr, 'легкогруз') !== false) {
                            $this->group = 3;
                        } elseif (strpos($groupStr, 'внедорож') !== false) {
                            $this->group = 4;
                        } else {
                            $this->group = 5;
                        }
                    } else {
                        $this->group = 5;
                    }
                } // If the width and the radius defined, we don't go to check up, the group must be save
            }

            // Season
            $seasonMethod = $post['seasonIs'];
            $seasonRowStr = $post['seasonRow'];

            $seasonRowStrLonely = $post['seasonRowLonely'];

            if ($seasonMethod == 'seasonIsRow') { // for column
                if ($seasonRowStr != '') {
                    // define the column number
                    $seasonRow = $this->setRow($seasonRowStr);

                    $seasonStr = mb_strtolower($arr[$seasonRow], 'UTF-8');

                    if (
                        strpos($seasonStr, 'лет') !== false
                        or strpos($seasonStr, 'літ') !== false
                        or strpos($seasonStr, 'summ') !== false
                    ) {
                        $this->season = 2;
                    } elseif (
                        strpos($seasonStr, 'зим') !== false
                        or strpos($seasonStr, 'wint') !== false
                    ) {
                        $this->season = 1;
                    } elseif (
                        strpos($seasonStr, 'всесезон') !== false
                        or strpos($seasonStr, 'вс/с') !== false
                    ) {
                        $this->season = 3;
                    } else {
                        $this->season = 0;
                    }
                } else {
                    $this->season = 0;
                }
            } elseif ($seasonMethod == 'seasonIsStr') { // one for all price list
                $this->season = $post['idSeason'];
            } elseif ($seasonMethod == 'seasonIsLonely') { // if set in subtopic, determine is it subtopic
                if ($this->width == 1 and $this->radius == 0) { // It is subtopic. Check there words
                    if ($seasonRowStrLonely != '') {
                        // define the column number
                        $seasonRow = $this->setRow($seasonRowStrLonely);

                        $seasonStr = mb_strtolower($arr[$seasonRow], 'UTF-8');

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
                    } else {
                        $this->season = 0;
                    }
                } // If the width and the radius defined, we don't go to check up, the ыуфыщт must be save
            }

            // Availability
            $isItRowStr = $post['isItRow'];
            if ($isItRowStr != '') {
                // define the column number
                $isItRow = $this->setRow($isItRowStr);

                $this->isIt = $arr[$isItRow];
            } else {
                $this->isIt = '';
            }

            // Currency
            $moneyMethod = $post['moneyIs'];
            $moneyRowStr = $post['moneyRow'];

            if ($moneyMethod == 'moneyIsOne') {
                $this->money = $post['idMoney'];
            } elseif ($moneyMethod == 'moneyIsRow') { // in column
                if ($moneyRowStr != '') {
                    // define the column number
                    $moneyRow = $this->setRow($moneyRowStr);

                    $moneyStr = mb_strtolower($arr[$moneyRow], 'UTF-8');

                    if (
                        strpos($moneyStr, 'usd') !== false
                        or strpos($moneyStr, '$') !== false
                    ) {
                        $this->money = 2;
                    } elseif (
                        strpos($moneyStr, 'грн') !== false
                        or strpos($moneyStr, 'uah') !== false
                    ) {
                        $this->money = 1;
                    } else {
                        if ($post['idMoneyUnknow'] == 1) {
                            $this->money = 1;
                        } elseif ($post['idMoneyUnknow'] == 2) {
                            $this->money = 2;
                        } else {
                            $this->money = 3;
                        }
                    }
                } else {
                    $this->money = 3;
                }
            }

            // Price
            $priceRowStr = $post['priceRow'];

            if ($priceRowStr != '') {
                // define the column number
                $priceRow = $this->setRow($priceRowStr);

                if (preg_match('|,(.+)\.(.+)|u', $arr[$priceRow])) {
                    $arrPriceRow = mb_convert_encoding($arr[$priceRow], 'UTF-8');
                    $arrPriceRow = str_replace(",", '.', $arrPriceRow);

                    if (preg_match('/(.+)\.(.+)\.(.+)/u', $arrPriceRow, $matchesPriceRow)) {
                        $arrPriceRow = $matchesPriceRow[1] . $matchesPriceRow[2] . '.' . $matchesPriceRow[3];
                    }

                    $this->price = preg_replace("/[^x\d|*\.]/", "", $arrPriceRow);
                } else {
                    $arrPriceRow = mb_convert_encoding($arr[$priceRow], 'UTF-8');
                    $arrPriceRow = str_replace(",", '.', $arrPriceRow);
                    $this->price = preg_replace("/[^x\d|*\.]/", "", $arrPriceRow);
                }
            } else {
                $this->price = '';
            }

            if ($this->money == 2) {
                if ($this->course4Ascania == 0) {
                    $course = static::getCourse();
                    $this->price *= $course;
                } else {
                    $this->price *= $this->course4Ascania;
                }
            }

            $this->price = $this->priceChange($priceChange);

            $this->priceMove = 0;

            if ($flag != -1) {
                $this->checkPriceMove($this->str);
            }

            // Camera
            $cameraMethod = $post['cameraIs'];
            $cameraRowStr = $post['cameraRow'];

            if ($cameraMethod == 'cameraIsRow') { // in column
                if ($cameraRowStr != '') {
                    // define the column number
                    $cameraRow = $this->setRow($cameraRowStr);

                    $cameraStr = mb_strtolower($arr[$cameraRow], 'UTF-8');

                    if (
                        strpos($cameraStr, 'бескамер') !== false
                        or strpos($cameraStr, 'безкамер') !== false
                        or strpos($cameraStr, 'б/к') !== false
                        or strpos($cameraStr, 'без камер') !== false
                        or strpos($cameraStr, 'бес камер') !== false
                    ) {
                        $this->camera = 3;
                    } elseif (strpos($cameraStr, 'камер') !== false) {
                        $this->camera = 2;
                    } else {
                        $this->camera = 1;
                    }
                } else {
                    $this->camera = 1;
                }

            } else { // in basic string
                $cameraStr = mb_strtolower($this->str, 'UTF-8');
                if (
                    strpos($cameraStr, 'бескамер') !== false
                    or strpos($cameraStr, 'безкамер') !== false
                    or strpos($cameraStr, 'б/к') !== false
                    or strpos($cameraStr, 'без камер') !== false
                    or strpos($cameraStr, 'бес камер') !== false
                ) {
                    $this->camera = 3;
                } elseif (strpos($cameraStr, 'камер') !== false) {
                    $this->camera = 2;
                } else {
                    $this->camera = 1;
                }
            }

            // Note (other)
            $otherMethod        = $post['otherIs'];
            $otherRowStr        = $post['otherRow'];
            $otherRowPlusTxt    = $post['otherRowPlusText'];
            $otherRowStrTwo     = $post['otherRowTwo'];
            $otherRowPlusTxtTwo = $post['otherRowPlusTextTwo'];

            if ($otherMethod == 'otherIsRow') { // in column
                if ($otherRowStr != '') {
                    // define the column number
                    $otherRow = $this->setRow($otherRowStr);

                    if (! empty($arr[$otherRow])) {
                        $arr[$otherRow] = $this->translitCountry($arr[$otherRow]);
                        $this->other    = $otherRowPlusTxt . $arr[$otherRow];
                    } else {
                        $this->other = '';
                    }
                } else {
                    $this->other = '';
                }

                if ($otherRowStrTwo != '') {
                    // define the column number
                    $otherRowTwo = $this->setRow($otherRowStrTwo);

                    if ($arr[$otherRowTwo] !== '' & $arr[$otherRowTwo] != ' ' & ! is_null($arr[$otherRowTwo])) {
                        $arr[$otherRowTwo] = $this->translitCountry($arr[$otherRowTwo]);
                        $otherTwo          = $otherRowPlusTxtTwo . $arr[$otherRowTwo];
                        $this->other .= '; ' . $otherTwo;
                    }
                }
            } else { // in subtopic
                $otherRowStr     = $post['otherRowLonely'];
                $otherRowPlusTxt = $post['otherRowPlusTextLonely'];

                if ($otherRowStr != '' and $this->width == 1 and $this->radius == 0) { // it is subtopic
                    if ($otherRowStr != '') {
                        // define the column number
                        $otherRow = $this->setRow($otherRowStr);

                        if ($arr[$otherRow] != '') {
                            $arr[$otherRow] = $this->translitCountry($arr[$otherRow]);
                            $this->other    = $otherRowPlusTxt . $arr[$otherRow];
                        } else {
                            $this->other = '';
                        }
                    } else {
                        $this->other = '';
                    }
                } else {
                    $this->other = '';
                }
            }

            // Production year
            $yearRowStr = $post['yearRow'];
            if ($yearRowStr != '') {
                // define the column number
                $yearRow = $this->setRow($yearRowStr);

                $this->year = trim($arr[$yearRow]);
            } else {
                $this->year = '';
            }

            // Additional conditions
            $delRowStr = $post['delRow'];
            $delText   = $post['delText'];

            if ($delRowStr != '' & $delText != '') {
                // define the column number
                $delRow = $this->setRow($delRowStr);

                $delText   = mb_strtolower($delText, 'UTF-8');
                $arrDelRow = $arr[$delRow];
                $arrDelRow = mb_strtolower($arrDelRow, 'UTF-8');

                if (strpos($arrDelRow, $delText) !== false) {
                    continue;
                }
            }

            $delRowStrEmpty = $post['delRowEmpty'];

            if ($delRowStrEmpty != '') {
                // define the column number
                $delRowEmpty = $this->setRow($delRowStrEmpty);

                $arrDelRow = $arr[$delRowEmpty];

                if ($arrDelRow === '') {
                    continue;
                }
            }

            // If it was subtopic, will not insert any rows
            if ($this->width == 1 and $this->radius == 0) {
                continue;
            }

            // And set cap at first word in the basic string
            if ($this->strFirstWord != '') {
                if (! preg_match("|^$this->strFirstWord|u", $arr[$strRow])) {
                    continue;
                }
            }
            $this->insertRows();
        }

        return true;
    }

    /**
     * Determine column number
     *
     * @param string $rowStr [A-Z]
     * @return string Number of column
     */
    private function setRow($rowStr)
    {
        if (preg_match('|^[0-9]|', $rowStr)) {
            // if we take a number
            $row = $rowStr - 1; // -1 because user set number column countdown to 1, but in array is countdown to 0
        } else {
            // If there is a letter, translate it into number
            $strRowStr = strtoupper($rowStr);
            $row       = $this->translitRow($strRowStr);
        }

        return $row;
    }

    /**
     * Check for unique incoming name of price list
     *
     * @param string $listName Price list name
     * @return bool
     */
    private function checkInputListName($listName)
    {
        if ($listName == '') {
            $str = 'Задайте имя для нового прайса';
            $this->echoError($str);

            return false;
        }

        $listInBaseBefore = $this->getLists();

        foreach ($listInBaseBefore as $a) {
            if ($listName == $a['nameList']) {
                $str = 'Прайс с таким именем уже сущетсвует. Присвойте новое имя.';
                $this->echoError($str);

                return false;
            }
        }

        return true;
    }

    /**
     * Echo error message for user
     *
     * @param string $str Error message
     */
    private function echoError($str)
    {
        echo <<<LABEL
    <link rel="stylesheet" href="css/style.css" type="text/css" />
    <div class="errorLinksHead">
        <p>$str<p>
    </div>
LABEL;
    }

    /**
     * Insert new price list into database
     *
     * @param string $listName Price list name
     * @param string $listCity City
     * @param string $data     Data in serialize format from POST array
     * @throws GenerateException
     */
    private function insertNewListData($listName, $listCity, $data)
    {
        $query  = new Query();
        $result = $query
            ->insertInto('list', [
                'nameList' => ':nameList',
                'city'     => ':city',
                'post'     => ':post',
                'method'   => 'universal'
            ])
            ->prepareBindStatement()
            ->execute([
                'nameList' => $listName,
                'city'     => $listCity,
                'post'     => $data,
            ]);

        if ($result === false) {
            GenerateException::getException('Insert new price list impossible, check values.', __CLASS__, __LINE__);
        }
    }

    /**
     * Get price list ID
     *
     * @return int Price list ID
     * @throws GenerateException
     */
    private function setListId()
    {
        $query = new Query();

        $listInBaseAfter = $query
            ->select([
                'idList',
                'nameList'
            ])
            ->from('list')
            ->all();

        $list = $listInBaseAfter[count($listInBaseAfter) - 1]['idList'];

        if (! $list) {
            GenerateException::getException('Can not get price list ID.', __CLASS__, __LINE__);
        }

        return $list;
    }

    /**
     * Get the course for Ascania price list
     *
     * @return float|int
     */
    private function getAscaniaCourse()
    {
        $course4Ascania = 0;

        if (strstr($this->file2array[4][0], 'ascania')) {
            if (preg_match('|1 USD - (\b.+\b) ?UAH|u', $this->file2array[1][5], $matchesAscania)) {
                $course4Ascania = (float)strtr($matchesAscania[1], [',' => '.']);
            }
        }

        return $course4Ascania;
    }
}

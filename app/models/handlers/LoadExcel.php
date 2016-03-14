<?php
namespace app\models\handlers;

use config\App;

require_once $_SERVER{'DOCUMENT_ROOT'} . App::$pathToRoot . '/core/outsider/PHPExcel/Classes/PHPExcel.php';

/**
 * Class LoadExcel provide logic for export data into .xlsx file
 *
 * @package app\models\handlers
 */
class LoadExcel
{
    /**
     * Set the style and load incoming param $tires in excel file
     *
     * @param array $tires Array with all rows
     */
    public static function load(array $tires)
    {
        // Create a new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()
            ->setCreator("Alex Petrov")
            ->setLastModifiedBy("Alex Petrov");

        /* ************************************** Variables ***************************************************** */

        // General
        $ews            = $objPHPExcel->getActiveSheet();
        $nameFile       = 'Прайс - ' . date("d.m.Y"); // file name
        $rowNumberFirst = $rowNumber = 5; // Starting position for header
        $col            = 'A';
        $defaultFont    = 'Palatino Linotype';

        $ews->setTitle('Автошины'); // File list name

        // Headed
        $headerMergeFrom  = 'A';
        $headerMergeTo    = 'C';
        $contentFirstRow  = 'Прайс-лист';
        $contentSecondRow = date("d.m.Y");
        $contentThirdRow  = 'Автошины';
        $countFields      = 'S'; // The number of column, need for style
        $rowHeight        = 18; // Height header rows
        $styleHead        = [
            'font'      => [
                'bold' => true,
                'size' => 11,
                'name' => $defaultFont,
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // Headers
        $headings = [
            '№п/п',
            'Код',
            'Размер',
            'Модель',
            'Изготовитель',
            'Применяемость',
            'Радиус',
            'Ширина',
            'Высота',
            'Нагрузка',
            'Скорость',
            'Страна',
            'PCD',
            'ET',
            'DIA',
            'Цена',
            'Наличие',
            'Сезон',
            'ID'
        ];

        $style = [
            'font'      => [
                'bold'  => true,
                'size'  => 10,
                'name'  => $defaultFont,
                'color' => [
                    'argb' => '4a3297',
                ],
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
            'borders'   => [
                'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN,],
            ],
            'fill'      => [
                'type'       => \PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => [
                    'argb' => 'a6caf0'
                ],
            ]
        ];

        // Data
        $headerLight       = 'Легковые шины';
        $headerVantage     = 'Грузовые шины';
        $headerSuv         = 'Внедорожники';
        $headerWeightLight = 'Легкогрузовые шины';
        $fieldFor2Header   = 'C'; // Row for subheading
        $fieldForPrice     = 'P'; // Column with price
        $fieldForId        = 'S'; // Column with ID
        $fieldNumberPP     = 'A'; // Column for consecutive number
        $numberPP          = 1;

        // Delivery price for Petr price list
        $roadFactorPetr = [
            '13'   => 13.5,
            '13C'  => 15,
            '14'   => 14,
            '14C'  => 20,
            '15'   => 14.5,
            '15C'  => 30,
            '16'   => 14.5,
            '16C'  => 35,
            '17'   => 14.5,
            '17C'  => 35,
            '17.5' => 75,
            '18'   => 27.5,
            '19'   => 27.5,
            '19.5' => 75,
            '20'   => 33,
            '21'   => 33,
            '22'   => 33,
            '22.5' => 75
        ];

        // Delivery price for Slavic price list
        $roadFactorSlava = [
            '13'   => 13.5,
            '13C'  => 15,
            '14'   => 13.5,
            '14C'  => 20,
            '15'   => 15.5,
            '15C'  => 30,
            '16'   => 15.5,
            '16C'  => 35,
            '17'   => 19,
            '17C'  => 35,
            '17.5' => 75,
            '18'   => 19,
            '19'   => 23,
            '19.5' => 75,
            '20'   => 32,
            '21'   => 32,
            '22'   => 32,
            '22.5' => 75
        ];

        $styleForSecondHeader = [
            'font'      => [
                'bold'      => true,
                'italic'    => true,
                'underline' => true,
                'size'      => 14,
                'name'      => $defaultFont
            ],
            'alignment' => [
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
        ];
        $styleForData         = [
            'font'      => [
                'size' => 8,
                'name' => $defaultFont
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical'   => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders'   => [
                'allborders' => ['style' => \PHPExcel_Style_Border::BORDER_THIN,],
            ],
        ];

        // Column width
        $ews->getColumnDimension('A')
            ->setWidth(7);
        $ews->getColumnDimension('B')
            ->setWidth(7);
        $ews->getColumnDimension('C')
            ->setWidth(25);
        $ews->getColumnDimension('D')
            ->setWidth(27);
        $ews->getColumnDimension('E')
            ->setWidth(18);
        $ews->getColumnDimension('F')
            ->setWidth(18);
        $ews->getColumnDimension('G')
            ->setWidth(9);
        $ews->getColumnDimension('H')
            ->setWidth(9);
        $ews->getColumnDimension('I')
            ->setWidth(9);
        $ews->getColumnDimension('J')
            ->setWidth(10);
        $ews->getColumnDimension('K')
            ->setWidth(10);
        $ews->getColumnDimension('L')
            ->setWidth(14);
        $ews->getColumnDimension('M')
            ->setWidth(7);
        $ews->getColumnDimension('N')
            ->setWidth(7);
        $ews->getColumnDimension('O')
            ->setWidth(7);
        $ews->getColumnDimension('P')
            ->setWidth(17);
        $ews->getColumnDimension('Q')
            ->setWidth(9);
        $ews->getColumnDimension('R')
            ->setWidth(12);
        $ews->getColumnDimension('S')
            ->setWidth(6);

        // Hide empty column
        $ews->getColumnDimension('B')
            ->setVisible(false);
        $ews->getColumnDimension('F')
            ->setVisible(false);
        $ews->getColumnDimension('J')
            ->setVisible(false);
        $ews->getColumnDimension('K')
            ->setVisible(false);
        $ews->getColumnDimension('L')
            ->setVisible(false);
        $ews->getColumnDimension('M')
            ->setVisible(false);
        $ews->getColumnDimension('N')
            ->setVisible(false);
        $ews->getColumnDimension('O')
            ->setVisible(false);


        /* ******************************************************************************************************* */

        // Combine cells for header
        $ews
            ->mergeCells($headerMergeFrom . '1:' . $headerMergeTo . '1')
            ->mergeCells($headerMergeFrom . '2:' . $headerMergeTo . '2')
            ->mergeCells($headerMergeFrom . '3:' . $headerMergeTo . '3');
        // Insert content and apply styles
        $ews
            ->setCellValue($headerMergeFrom . '1', $contentFirstRow)
            ->setCellValue($headerMergeFrom . '2', $contentSecondRow)
            ->setCellValue($headerMergeFrom . '3', $contentThirdRow)
            ->getStyle($headerMergeFrom . '1:' . $headerMergeFrom . '3')
            ->applyFromArray($styleHead);
        $ews
            ->getStyle($headerMergeFrom . '1')
            ->getFont()
            ->setSize(18)
            ->setUnderline(\PHPExcel_Style_Font::UNDERLINE_SINGLE);

        // Insert headers
        foreach ($headings as $heading) {
            $objPHPExcel
                ->getActiveSheet()
                ->setCellValue($col . $rowNumber, $heading);
            $col++;
        }
        $headerSize = "A{$rowNumber}:{$countFields}{$rowNumber}";
        $ews
            ->getStyle($headerSize)
            ->applyFromArray($style);
        $ews
            ->getRowDimension($rowNumber)
            ->setRowHeight($rowHeight);

        // prepare data
        $i        = 0;
        $tiresOur = [];

        $listArray = Helper::getLists();

        if (! empty($tires)) {
            foreach ($tires as $t) {
                if (! empty($t)) {
                    foreach ($t as $tKey => $tValue) {
                        if (
                            $tKey == 'id'
                            or $tKey == 'idList'
                            or $tKey == 'city'
                            or $tKey == 'str'
                            or $tKey == 'Kg'
                            or $tKey == 'speed'
                            or $tKey == 'nameCamera'
                            or $tKey == 'priceMove'
                            or $tKey == 'nameMoney'
                            or $tKey == 'dateTime'
                            or $tKey == 'year'
                        ) {
                            continue;
                        }

                        // Size
                        if ($tKey == 'nameWidth') {
                            if ($tValue != 'Не указано') {
                                $tiresOur[$i]['size']  = $tValue;
                                $tiresOur[$i]['width'] = $tValue;
                            } else {
                                $tiresOur[$i]['width'] = '';
                            }
                            continue;
                        }
                        if ($tKey == 'nameHeight') {
                            if ($tValue != 'Не указано') {
                                $tiresOur[$i]['size'] .= '/' . $tValue;
                                $tiresOur[$i]['height'] = $tValue;
                            } else {
                                $tiresOur[$i]['height'] = '';
                            }
                            continue;
                        }
                        if ($tKey == 'radius') {
                            if ($tValue != 'Не указан') {
                                $tiresOur[$i]['size'] .= ' R ' . $tValue;
                                $tiresOur[$i]['radius'] = $tValue;
                            } else {
                                $tiresOur[$i]['radius'] = '';
                            }
                            continue;
                        }
                        if ($tKey == 'nameIndexPower' and $tValue != 'Не указан') {
                            $tiresOur[$i]['size'] .= ' ' . $tValue;
                            continue;
                        }
                        if ($tKey == 'nameIndexSpeed' and $tValue != 'Не указан') {
                            $tiresOur[$i]['size'] .= ' ' . $tValue;
                            continue;
                        }
                        // Model
                        if ($tKey == 'model') {
                            $tiresOur[$i]['model'] = $tValue;
                            continue;
                        }
                        // Producer
                        if ($tKey == 'nameBrand') {
                            ($tValue != 'Не указан') ? $tiresOur[$i]['brand'] = $tValue : $tiresOur[$i]['brand'] = '';
                            continue;
                        }
                        // Country
                        if ($tKey == 'other') {
                            if (preg_match('|страна( производства)?:? (\w+)|ui', $tValue, $matches)) {
                                $tiresOur[$i]['country'] = $matches[2];
                            } else {
                                $tiresOur[$i]['country'] = '';
                            }
                            continue;
                        }
                        // Season
                        if ($tKey == 'nameSeason') {
                            ($tValue != 'Не указан')
                                ? $tiresOur[$i]['season'] = $tValue
                                : $tiresOur[$i]['season'] = '';
                            continue;
                        }
                        // Price
                        $roadFactor = 0;
                        if ($tKey == 'price') {
                            if ($t['nameList'] == 'ПЕТР') {
                                if (! empty($roadFactorPetr)) {
                                    foreach ($roadFactorPetr as $rfKey => $rfVal) {
                                        if (gettype($rfKey) !== gettype($t['radius'])) {
                                            settype($rfKey, 'string');
                                            settype($t['radius'], 'string');
                                        }
                                        if ($rfKey == $t['radius']) {
                                            $roadFactor = $rfVal;
                                            break;
                                        }
                                    }
                                }
                            } elseif ($t['nameList'] == 'СЛАВИК') {
                                if (! empty($roadFactorSlava)) {
                                    foreach ($roadFactorSlava as $rfKey => $rfVal) {
                                        if (gettype($rfKey) !== gettype($t['radius'])) {
                                            settype($rfKey, 'string');
                                            settype($t['radius'], 'string');
                                        }
                                        if ($rfKey == $t['radius']) {
                                            $roadFactor = $rfVal;
                                            break;
                                        }
                                    }
                                }
                            }
                            $tiresOur[$i]['price'] = $tValue + $roadFactor;
                            continue;
                        }
                        // Availability
                        if ($tKey == 'isIt') {
                            $tiresOur[$i]['isIt'] = $tValue;
                            continue;
                        }
                        // ID
                        if ($tKey == 'nameList') {
                            if (! empty($listArray)) {
                                foreach ($listArray as $la) {
                                    if ($la['nameList'] == $tValue) {
                                        $tiresOur[$i]['idForExcel'] = $la['idForExcel'];
                                    }
                                }
                            }
                            continue;
                        }
                        // If radius is undefined, for indexes (need, when drop for group)
                        if ($tKey == 'nameGroup') {
                            $tiresOur[$i]['nameGroup'] = $tValue;
                        }
                    }
                }
                $i++;
            }
        }
        // For the right sort
        $i = 0;

        $tiresGeneral = [];
        foreach ($tiresOur as $t) {
            $tiresOurFinal[$i]['code']       = '';
            $tiresOurFinal[$i]['size']       = $t['size'];
            $tiresOurFinal[$i]['model']      = $t['model'];
            $tiresOurFinal[$i]['brand']      = $t['brand'];
            $tiresOurFinal[$i]['toWork']     = '';
            $tiresOurFinal[$i]['radius']     = $t['radius'];
            $tiresOurFinal[$i]['width']      = $t['width'];
            $tiresOurFinal[$i]['height']     = $t['height'];
            $tiresOurFinal[$i]['indexPower'] = '';
            $tiresOurFinal[$i]['indepSpeed'] = '';
            $tiresOurFinal[$i]['country']    = $t['country'];
            $tiresOurFinal[$i]['PCD']        = '';
            $tiresOurFinal[$i]['ET']         = '';
            $tiresOurFinal[$i]['DIA']        = '';
            $tiresOurFinal[$i]['price']      = $t['price'];
            $tiresOurFinal[$i]['isIt']       = $t['isIt'];
            $tiresOurFinal[$i]['season']     = $t['season'];
            $tiresOurFinal[$i]['idForExcel'] = $t['idForExcel'];

            if ($t['nameGroup'] == 'Грузовые') {
                $tiresGeneral['tiresVantage'][] = $tiresOurFinal[$i];
            } elseif ($t['nameGroup'] == 'Легковые' or $t['nameGroup'] == 'Не указано') {
                $tiresGeneral['tiresLight'][] = $tiresOurFinal[$i];
            } elseif ($t['nameGroup'] == 'Внедорожники') {
                $tiresGeneral['tiresSuv'][] = $tiresOurFinal[$i];
            } elseif ($t['nameGroup'] == 'Легкогрузовые') {
                $tiresGeneral['tiresWeightLight'][] = $tiresOurFinal[$i];
            }
            $i++;
        }

        $rowNumber++;
        if (! empty($tiresGeneral)) {
            foreach ($tiresGeneral as $tgKey => $tgVal) {
                if (empty($tgVal)) {
                    continue;
                }
                $headerName = '';
                if ($tgKey == 'tiresLight') {
                    $headerName = $headerLight;
                } elseif ($tgKey == 'tiresVantage') {
                    $headerName = $headerVantage;
                } elseif ($tgKey == 'tiresSuv') {
                    $headerName = $headerSuv;
                } elseif ($tgKey == 'tiresWeightLight') {
                    $headerName = $headerWeightLight;
                }

                $ews
                    ->setCellValue($fieldFor2Header . $rowNumber, $headerName)
                    ->getStyle($fieldFor2Header . $rowNumber)
                    ->applyFromArray($styleForSecondHeader);
                $rowNumber++;
                $rowFrom = $rowNumber;
                if (! empty($tgVal)) {
                    foreach ($tgVal as $row) {
                        $col = 'A';
                        $ews->setCellValue($col++ . $rowNumber, $numberPP++);
                        if (! empty($row)) {
                            foreach ($row as $key => $cell) {
                                $ews->setCellValue($col++ . $rowNumber, $cell);
                            }
                        }
                        $rowNumber++;
                    }
                }
                $rowTo = $rowNumber - 1;
                // Styles
                $field = $fieldNumberPP . $rowFrom . ':' . $countFields . $rowTo;
                $ews
                    ->getStyle($field)
                    ->applyFromArray($styleForData);
            }
        }

        // Format for price
        $priceField = $fieldForPrice . ($rowNumberFirst + 1) . ':' . $fieldForPrice . ($rowNumber - 1);
        $ews
            ->getStyle($priceField)
            ->getNumberFormat()
            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_HRN);
        $ews
            ->getStyle($priceField)
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Format for ID
        $idField = $fieldForId . ($rowNumberFirst + 1) . ':' . $fieldForId . ($rowNumber - 1);
        $ews
            ->getStyle($idField)
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Format for consecutive number
        $field = $fieldNumberPP . ($rowNumberFirst + 1) . ':' . $fieldNumberPP . ($rowNumber - 1);
        $ews
            ->getStyle($field)
            ->getAlignment()
            ->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        // Save as an Excel2007 file. Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $nameFile . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}

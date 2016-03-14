<?php
namespace app\models;

use config\App;
use core\helpers\GenerateException;
use core\helpers\Query;
use core\Model;

/**
 * Class ModelSearch provide logic for searching in database
 *
 * @package app\models
 */
class ModelSearch extends Model
{
    /**
     * Get array $_POST from request and put them into session variable
     * or get values from session variable
     *
     * @param array $params Params from GET request
     * @return array
     * @throws GenerateException
     */
    public function getPost(array $params)
    {
        $post = [];
        if ($params['mode'] == 1) {
            if (is_null($params['page']) or $params['page'] == -1) {
                $post             = App::post();
                $_SESSION['post'] = serialize($post);
            } else {
                $post = unserialize($_SESSION['post']);
            }
        } elseif ($params['mode'] == 2) {
            $post = unserialize($_SESSION['post']);
        } else {
            GenerateException::getException('Unknown parameter "mode" in GET request', __CLASS__, __LINE__);
        }

        return $post;
    }

    /**
     * Select from database requested information
     *
     * @param array $post Input values
     * @return array|bool
     * @throws GenerateException
     */
    public function getSearchingTires(array $post)
    {
        $arrayChoose = [];
        $bindParams  = [];
        $queryString = '';
        $flagCash    = 0;
        $flagBank    = 0;
        $flagList    = 0;
        $flagBigg    = 0;

        if (! empty($post)) {
            $queryString = 'tires.price BETWEEN :priceFrom AND :priceTo';

            $bindParams[':priceFrom'] = $post['priceFrom'];
            $bindParams[':priceTo']   = $post['priceTo'];

            if ($post['idListArr'][0] == -1) {
                $flagList = 1;
            }

            foreach ($post as $postKey => $postVal) {
                $values = '';

                $i = 1;

                if ($postKey == 'model' and $postVal != '') {
                    $identification = ':model';
                    $queryString .= ' AND tires.model LIKE ' . $identification;
                    $bindParams[$identification] = '%' . $postVal . '%';
                    continue;
                }
                if ($postKey == 'str' and $postVal != '') {
                    $identification = ':str';
                    $queryString .= ' AND tires.str LIKE ' . $identification;
                    $bindParams[$identification] = '%' . $postVal . '%';
                    continue;
                }
                if ($postKey == 'other' and $postVal != '') {
                    $identification = ':other';
                    $queryString .= ' AND tires.other LIKE ' . $identification;
                    $bindParams[$identification] = '%' . $postVal . '%';
                    continue;
                }
                if ($postVal[0] == -1) {
                    continue;
                }

                if ($postVal[0] == -2) {
                    $queryString .= ' AND tires.isIt !=""';
                    continue;
                }
                if ($flagList) {
                    if ($postKey == 'listSettingCash') {
                        $flagCash = 1;
                        continue;
                    }
                    if ($postKey == 'listSettingBank') {
                        $flagBank = 1;
                        continue;
                    }
                    if ($postKey == 'listSettingBigg') {
                        $flagBigg = 1;
                        continue;
                    }
                }

                if (is_array($postVal) and ! empty($postVal)) {
                    foreach ($postVal as $a) {
                        if ($a == -1) {
                            continue;
                        }
                        $arrayChoose[$postKey][$i] = $a;
                        $i++;
                        (! $values)
                            ? $values = $a
                            : $values .= ', ' . $a;
                    }
                }

                switch ($postKey) {
                    case 'idHeightArr':
                        $field = 'tires.height';
                        break;
                    case 'idWidthArr':
                        $field = 'tires.width';
                        break;
                    case 'idRadiusArr':
                        $field = 'tires.radius';
                        break;
                    case 'idIndexPowerArr':
                        $field = 'tires.indexPower';
                        break;
                    case 'idIndexSpeedArr':
                        $field = 'tires.indexSpeed';
                        break;
                    case 'idBrandArr':
                        $field = 'tires.brand';
                        break;
                    case 'idCameraArr':
                        $field = 'tires.camera';
                        break;
                    case 'idSeasonArr':
                        $field = 'tires.season';
                        break;
                    case 'idGroupArr':
                        $field = 'tires.gr';
                        break;
                    case 'idListArr':
                        $field = 'tires.list';
                        break;
                    case 'isItArr':
                        $field = 'tires.isIt';
                        break;
                    default:
                        $field = '';
                }

                if ($field != '' and $values != '') {
                    $valuesArr = [];

                    if (strpos($values, ',')) {
                        $valuesArr = explode(', ', $values);
                    }

                    if (! empty($valuesArr)) {
                        $identArr = '';
                        $count    = count($valuesArr);

                        $i = 1;
                        foreach ($valuesArr as $val) {
                            $identification = strtr($field, ['.' => '']) . $val;
                            if ($i == $count) {
                                $identArr .= ':' . $identification;
                            } else {
                                $identArr .= ':' . $identification . ', ';
                            }
                            $bindParams[':' . $identification] = $val;
                            $i++;
                        }

                        $queryString .= ' AND ' . $field . ' IN (' . $identArr . ')';
                    } else {
                        $identification = strtr($field, ['.' => '']) . $values;
                        $queryString .= ' AND ' . $field . ' IN (:' . $identification . ')';
                        $bindParams[':' . $identification] = $values;
                    }
                }
            }
        } else {
            GenerateException::getException('POST data does not find', __CLASS__, __LINE__);
        }

        if ($flagList) {
            if ($flagCash) {
                // is cash payment
                if ($flagBank) { // search for all price lists OR without BIGG (< 100 000)
                    if (! $flagBigg) {
                        $queryString .= ' AND tires.list < 100000';
                    }
                } else {  // without cashless payment, just cash and BIGG (<10 000 >99 999) OR only cash (<10 000)
                    ($flagBigg)
                        ? $queryString .= ' AND NOT tires.list BETWEEN "9999" AND "99999"'
                        : $queryString .= ' AND tires.list < 10000';
                }
            } else {
                // without cash payment
                if ($flagBank) { // is cashless payment and with/without BIGG
                    ($flagBigg)
                        ? $queryString .= ' AND tires.list > 9999'
                        : $queryString .= ' AND tires.list BETWEEN "9999" AND "99999"';
                } else {  // without cashless payment and with/without BIGG
                    ($flagBigg)
                        ? $queryString .= ' AND tires.list > 99999'
                        : $queryString .= ' AND tires.list = 0';
                }
            }
        }

        return $this->selectTiresFromDB($queryString, $bindParams);
    }

    /**
     * Using $queryParams get tires from database
     *
     * @param string     $queryParams Params for request
     * @param null|array $bindParams  Bind params. For example: $bindParams[':model'] = '%' . $model . '%';
     * @return array|bool
     * @throws GenerateException
     */
    public function selectTiresFromDB($queryParams, $bindParams = null)
    {
        $query = new Query();

        $querySql = $query
            ->select([
                'tires.id',
                'list.nameList',
                'list.idList',
                'list.city',
                'str',
                'width.nameWidth',
                'height.nameHeight',
                'radius.radius',
                'indexPower.nameIndexPower',
                'indexPower.Kg',
                'indexSpeed.nameIndexSpeed',
                'indexSpeed.speed',
                'brand.nameBrand',
                'model',
                'camera.nameCamera',
                'season.nameSeason',
                'isIt',
                'price',
                'priceMove',
                'money.nameMoney',
                'gr.nameGroup',
                'dateTime',
                'other',
                'year'
            ])
            ->from('tires')
            ->innerJoin('list', 'tires.list = list.idList')
            ->innerJoin('radius', 'tires.radius = radius.idRadius')
            ->innerJoin('brand', 'tires.brand = brand.idBrand')
            ->innerJoin('camera', 'tires.camera = camera.idCamera')
            ->innerJoin('money', 'tires.money = money.idMoney')
            ->innerJoin('gr', 'tires.gr = gr.idGroup')
            ->innerJoin('height', 'tires.height = height.idHeight')
            ->innerJoin('width', 'tires.width = width.idWidth')
            ->innerJoin('indexPower', 'tires.indexPower = indexPower.idIndexPower')
            ->innerJoin('indexSpeed', 'tires.indexSpeed = indexSpeed.idIndexSpeed')
            ->innerJoin('season', 'tires.season = season.idSeason');

        if (is_array($bindParams)) {
            $querySql
                ->whereCustom($queryParams)
                ->orderBy('model');

            $stmt = $query->prepareBindStatement();

            $tires = $query->executeBindStmt($stmt, $bindParams);
        } else {
            $stmt = $querySql
                ->whereBindStmt(['id' => ':id'])
                ->prepareBindStatement();

            $stmt->bindParam(':id', $queryParams);

            $tires = $query->executeBindStmtOne($stmt);
        }

        if (! is_array($tires)) {
            GenerateException::getException('Tires does not array', __CLASS__, __LINE__);
        }

        return $tires;
    }

    /**
     * Insert row in the database
     *
     * @param array $post Input values
     * @return true
     * @throws GenerateException
     */
    public function insertNewValue(array $post)
    {
        $query = new Query();

        $result = $query
            ->deleteFrom('tires')
            ->whereBindStmt([
                'id' => ':id'
            ])
            ->prepareBindStatement()
            ->execute(['id' => $post['id']]);

        if ($result === false) {
            GenerateException::getException('Deleting wrong', __CLASS__, __LINE__);
        }

        $result = $query
            ->insertInto('tires', [
                'id'         => ':id',
                'list'       => ':list',
                'str'        => ':str',
                'width'      => ':width',
                'height'     => ':height',
                'radius'     => ':radius',
                'indexPower' => ':indexPower',
                'indexSpeed' => ':indexSpeed',
                'brand'      => ':brand',
                'model'      => ':model',
                'camera'     => ':camera',
                'season'     => ':season',
                'isIt'       => ':isIt',
                'price'      => ':price',
                'money'      => ':money',
                'gr'         => ':gr',
                'other'      => ':other'
            ])
            ->prepareBindStatement()
            ->execute([
                'id'         => $post['id'],
                'list'       => $post['idList'],
                'str'        => $post['str'],
                'width'      => $post['idWidth'],
                'height'     => $post['idHeight'],
                'radius'     => $post['idRadius'],
                'indexPower' => $post['idIndexPower'],
                'indexSpeed' => $post['idIndexSpeed'],
                'brand'      => $post['idBrand'],
                'model'      => $post['model'],
                'camera'     => $post['idCamera'],
                'season'     => $post['idSeason'],
                'isIt'       => $post['isIt'],
                'price'      => $post['price'],
                'money'      => $post['idMoney'],
                'gr'         => $post['idGroup'],
                'other'      => $post['other']
            ]);

        if ($result === false) {
            GenerateException::getException('Insert new values wrong', __CLASS__, __LINE__);
        }

        return true;
    }

    /**
     * Remove row from database
     *
     * @param int $id Id row for deleting
     * @return true
     * @throws GenerateException
     */
    public function deleteRow($id)
    {
        $query = new Query();

        $result = $query
            ->deleteFrom('tires')
            ->whereBindStmt([
                'id' => ':id'
            ])
            ->prepareBindStatement()
            ->execute(['id' => $id]);

        if ($result === false) {
            GenerateException::getException('Deleting wrong', __CLASS__, __LINE__);
        }

        return true;
    }

    /**
     * Prepare array for statistic
     *
     * @param array $tires Search result
     * @return array
     */
    public function getArraysData(array $tires)
    {
        if (empty($tires)) {
            return false;
        }

        $arrayData    = [];
        $arrayPrice   = [];
        $arrayWidth   = [];
        $arrayHeight  = [];
        $arrayRadius  = [];
        $arrayBrand   = [];
        $arraySeason  = [];
        $arrayGroup   = [];
        $arrayCountry = [];

        $arrayCountValuesFirst = [];
        if (! empty($tires)) {
            foreach ($tires as $t) {
                $arrayCountValuesFirst[] = "в городе <em>{$t['city']}</em>, прайс <em>({$t['nameList']})</em>";

                $arrayPrice[]   = $t['price'];
                $arrayWidth[]   = $t['nameWidth'];
                $arrayHeight[]  = $t['nameHeight'];
                $arrayRadius[]  = $t['radius'];
                $arrayBrand[]   = $t['nameBrand'];
                $arraySeason[]  = $t['nameSeason'];
                $arrayGroup[]   = $t['nameGroup'];
                $arrayCountry[] = $t['other'];
            }
        }

        $arrayCountValues = array_count_values($arrayCountValuesFirst);
        arsort($arrayCountValues);
        $arrayData['countValues'] = $arrayCountValues;

        $arrayHeight = array_unique($arrayHeight);
        $arrayWidth  = array_unique($arrayWidth);
        $arrayRadius = array_unique($arrayRadius);

        sort($arrayPrice);
        sort($arrayHeight);
        sort($arrayWidth);
        sort($arrayRadius);
        $arrayData['price'] = $arrayPrice;

        $arrayData['height'] = $arrayHeight;
        $arrayData['width']  = $arrayWidth;
        $arrayData['radius'] = $arrayRadius;

        $arrayData['brandUnique']   = array_unique($arrayBrand);
        $arrayData['seasonUnique']  = array_unique($arraySeason);
        $arrayData['groupUnique']   = array_unique($arrayGroup);
        $arrayData['countryUnique'] = array_unique($arrayCountry);

        return $arrayData;
    }

    /**
     * If variable is null - set them = 0 or set = param $variable
     *
     * @param mixed $variable
     * @return int|mixed
     */
    public function checkForNull($variable)
    {
        $returnVariable = 0;

        if (! is_null($variable)) {
            $returnVariable = $variable;
        }

        return $returnVariable;
    }
}

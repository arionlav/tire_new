<?php
namespace app\models;

use core\helpers\GenerateException;
use core\helpers\Query;
use core\Model;

/**
 * Class ModelAdmin provide logic for modification default values in database
 *
 * @package app\models
 */
class ModelCatalog extends Model
{
    /**
     * Call $this->insertValue() for each input value
     *
     * @param array $post Input values
     * @throws GenerateException
     */
    public function insertData(array $post)
    {
        $arrayKey = [
            'width',
            'height',
            'radius',
            'brand',
            'camera',
            'season',
            'group',
            'list',
            'indexPower',
            'indexSpeed'
        ];

        if (! empty($arrayKey)) {
            foreach ($arrayKey as $aKey) {
                if ($post[$aKey . 'InsertParam'] != '') {
                    if ($aKey == 'group') {
                        $aKeyCorrect = 'gr';
                    } else {
                        $aKeyCorrect = $aKey;
                    }

                    if ($aKey == 'indexPower' or $aKey == 'indexSpeed') {
                        if ($this->insertValue($post[$aKey . 'InsertParam'],
                                $aKeyCorrect, $post[$aKey . 'InsertParamTwo']) === false
                        ) {
                            GenerateException::getException('Error at inserting ' . $aKey . ' value');
                        }
                        continue;
                    }

                    if ($this->insertValue($post[$aKey . 'InsertParam'], $aKeyCorrect) === false) {
                        GenerateException::getException('Error at inserting ' . $aKey . ' value');
                    }
                }
            }
        }
    }

    /**
     * Insert new value in database
     *
     * @param string      $val   New value
     * @param string      $table Table name
     * @param null|string $val2  Second value (for indexes)
     * @return bool
     */
    private function insertValue($val, $table, $val2 = null)
    {
        $query = new Query();

        ($table != 'radius')
            ? $row = 'name' . ucfirst($table)
            : $row = $table;

        if ($table == 'gr') {
            $row = 'nameGroup';
        }

        if (! is_null($val2)) {
            ($table == 'indexPower')
                ? $rowTwo = 'Kg'
                : $rowTwo = 'speed';

            return $query
                ->insertInto($table, [
                    $row    => ':value1',
                    $rowTwo => ':value2'
                ])
                ->prepareBindStatement()
                ->execute([
                    'value1' => $val,
                    'value2' => $val2
                ]);

        } else {
            return $query
                ->insertInto($table, [
                    $row => ':value'
                ])
                ->prepareBindStatement()
                ->execute([
                    'value' => $val
                ]);
        }
    }

    /**
     * Call $this->dropValue() for each value
     *
     * @param array $post Input values
     */
    public function deleteData(array $post)
    {
        $arrayKey = [
            'width',
            'height',
            'radius',
            'brand',
            'camera',
            'season',
            'group',
            'list',
            'indexPower',
            'indexSpeed'
        ];

        foreach ($arrayKey as $aKey) {
            $aKeyBig = ucfirst($aKey);

            if ($aKey == 'group') {
                $aKeyCorrect = 'gr';
            } else {
                $aKeyCorrect = $aKey;
            }

            if ($post['id' . $aKeyBig . 'Arr'][0]) {
                foreach ($post['id' . $aKeyBig . 'Arr'] as $a) {
                    $this->dropValue($aKeyCorrect, $a);
                }
            }
        }
    }

    /**
     * Remove selected value from database
     *
     * @param string $table Table name
     * @param string $val   Removing value
     * @return true|GenerateException
     */
    private function dropValue($table, $val)
    {
        $idInTable = 'id' . ucfirst($table);

        $query = new Query();

        // remove from 'tires' table
        // request can return 0, if don't find any values and this is normal
        $result = $query
            ->deleteFrom('tires')
            ->whereBindStmt([
                $table => ':value'
            ])
            ->prepareBindStatement()
            ->execute(['value' => $val]);

        if ($result === false) {
            GenerateException::getException('Error removing from  ' . $table . ' value ' . $val);
        }

        if ($table == 'gr') {
            $idInTable = 'idGroup';
        }

        // remove from relative table $table
        $result = $query
            ->deleteFrom($table)
            ->whereBindStmt([
                $idInTable => ':value'
            ])
            ->prepareBindStatement()
            ->execute(['value' => $val]);

        if ($result === false) {
            GenerateException::getException('Error deleting from  ' . $table . ' value ' . $val);
        }

        return true;
    }

    /**
     * Remove all values from table
     *
     * @param string $table Table name
     * @return true
     * @throws GenerateException
     */
    public function truncateCourseTable($table)
    {
        $query = new Query();

        if ($query->truncate($table) === false) {
            GenerateException::getException('Truncate table does not happen, check table name');
        }

        return true;
    }

    /**
     * Insert new course
     *
     * @param int $course New course
     * @return bool
     * @throws GenerateException
     */
    public function insertNewCourse($course)
    {
        $query = new Query();

        $result = $query
            ->insertInto('course', [
                'nameCourse' => ':course'
            ])
            ->prepareBindStatement()
            ->execute(['course' => $course]);

        if ($result === false) {
            GenerateException::getException('Insert new course impossible, check values', __CLASS__, __LINE__);
        }

        return true;
    }

    /**
     * When we change course, we must change all prices for rows in usd, let's do it
     *
     * @param string $course    New course value
     * @param string $courseOld Old course value
     * @return bool
     * @throws \PDOException
     */
    public function updatePriceInUsd($course, $courseOld)
    {
        $query = new Query();

        $priceUsd = $query
            ->select([
                'list'
            ], true)
            ->from('tires')
            ->where([
                'money' => 2
            ])
            ->all();

        if (! empty($priceUsd)) {
            foreach ($priceUsd as $pUsd) {
                try {
                    $queryDb = $query->getDb();

                    $courseQuote = $queryDb->quote($course);

                    $sql = "UPDATE tires
                            SET price = (price / {$courseOld} * {$courseQuote}),
                                priceMove = 0
                            WHERE list = {$pUsd['list']}";

                    $result = $queryDb->exec($sql);

                    if ($result === false) {
                        throw new \PDOException('Update new course wrong');
                    }
                } catch (\PDOException $e) {
                    return false;
                }
            }
        }

        return true;
    }
}

<?php
namespace app\models;

use app\models\handlers\Helper;
use config\App;
use core\helpers\GenerateException;
use core\Model;
use core\helpers\Query;

/**
 * Class ModelSite provide logic for index page and adding price list
 *
 * @package app\models
 */
class ModelSite extends Model
{
    /**
     * @var string Handler name for current price list
     */
    public $method;

    /**
     * @var int Price list id
     */
    public $idList;

    /**
     * @var null|string Handler params if it is Universal handler
     */
    public $post;

    /**
     * @var array Array with all price lists
     */
    public $lists;

    /**
     * Define method for price list
     * Take from database price lists and compare id
     *
     * @param string $postIdList Incoming price list id
     * @return bool
     */
    public function getClassName($postIdList)
    {
        $lists = Helper::getLists();

        if (! empty($lists)) {
            foreach ($lists as $list) {
                if ($postIdList == $list['idList']) {
                    $this->idList = $list['idList'];
                    $this->post   = $list['post'];
                    $this->method = '\app\models\handlers\\' . $list['method'];

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get price change factor
     *
     * @param array $post Input values
     * @return array
     * @throws GenerateException
     */
    public function getPriceChange(array $post)
    {
        $change                = [];
        $change['priceMethod'] = $post['priceChangeMethod']; // custom or oneForAll

        if ($change['priceMethod'] == 'oneForAll') {
            $change['oneForAllText'] = (real)strtr($post['oneForAllText'], [',' => '.']);
        } elseif ($change['priceMethod'] == 'custom') {
            if ($post['allOtherText']) {
                $change['allOtherText'] = (real)strtr($post['allOtherText'], [',' => '.']);
            }
            for ($i = 1; $i <= 3; $i++) {
                if ($post['forWho' . $i]) {
                    if ($post['forWho' . $i] == 'forBrand' . $i) {
                        $change[$i]['forWho']   = 'brand';
                        $change[$i]['forWhoId'] = $post['idBrand' . $i];
                    } elseif ($post['forWho' . $i] == 'forGroup' . $i) {
                        $change[$i]['forWho']   = 'group';
                        $change[$i]['forWhoId'] = $post['idGroup' . $i];
                    }
                    $change[$i]['value'] = (real)strtr($post['for' . $i . 'Text'], [',' => '.']);
                }
            }
        } else {
            GenerateException::getException('POST parameter priceMethod does is undefined', __CLASS__, __LINE__);
        }

        return $change;
    }

    /**
     * Get price list name by id
     *
     * @param int $id Price list id
     * @return string
     */
    public function getNameListById($id)
    {
        $query = new Query();

        $stmt = $query
            ->select(['nameList'])
            ->from('list')
            ->whereBindStmt(['id' => ':id'])
            ->prepareBindStatement();

        $stmt->bindParam(':id', $id);

        $nameList = $query->executeBindStmtOne($stmt);

        return $nameList['nameList'];
    }

    /**
     * Remove all files from folder App::$pathToLoadFiles
     *
     * @return true
     */
    public function dropAllFilesFromFolder()
    {
        $path  = $_SERVER{'DOCUMENT_ROOT'} . App::$pathToRoot . '/' . App::$pathToLoadFiles . '/*';
        $files = glob($path);
        if (! empty($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        return true;
    }

    /**
     * Show in search result all rows, that we add moment ago
     *
     * @param int    $id          Price list id
     * @param string $requestList Price list name
     * @return array
     */
    public function createPostArray($id, $requestList)
    {
        return $post = [
            'widthText'         => '',
            'idWidthArr'        => [0 => '-1'],
            'heightText'        => '',
            'idHeightArr'       => [0 => '-1'],
            'radiusText'        => '',
            'idRadiusArr'       => [0 => '-1'],
            'indexPowerText'    => '',
            'idIndexPowerArr'   => [0 => '-1'],
            'indexSpeedText'    => '',
            'idIndexSpeedArr'   => [0 => '-1'],
            'idBrandArr'        => [0 => '-1'],
            'idCameraArr'       => [0 => '-1'],
            'idSeasonArr'       => [0 => '-1'],
            'idGroupArr'        => [0 => '-1'],
            'isItArr'           => [0 => '-1'],
            'idListArr'         => [0 => $id],
            'str'               => '',
            'model'             => '',
            'other'             => '',
            'priceFrom'         => '0',
            'priceTo'           => '100000',
            'requestBrand'      => '',
            'requestWidth'      => '',
            'requestHeight'     => '',
            'requestRadius'     => '',
            'requestGroup'      => '',
            'requestIndexPower' => '',
            'requestIndexSpeed' => '',
            'requestCamera'     => '',
            'requestSeason'     => '',
            'requestIsIt'       => '',
            'requestList'       => $requestList,
            'listSettingCash'   => 'on',
            'listSettingBank'   => 'on',
            'listSettingBigg'   => 'on'
        ];
    }
}

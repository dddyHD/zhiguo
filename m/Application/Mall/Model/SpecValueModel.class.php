<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2017/1/9
 * Time: 13:23
 */
namespace Mall\Model;

use Think\Model;

class SpecValueModel extends Model
{

    protected $tableName = 'mall_spec_value';

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function getDetail($id = 0)
    {
        $detail = $this->where(array('spec_id'=>$id))->select();
        return $detail;
    }
}
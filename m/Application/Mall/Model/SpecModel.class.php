<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2017/1/9
 * Time: 13:21
 */
namespace Mall\Model;

use Think\Model;

class SpecModel extends Model
{

    protected $tableName = 'mall_spec';

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function getAllSpec()
    {
        $spec = $this->select();
        return $spec;
    }
}
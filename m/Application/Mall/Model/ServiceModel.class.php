<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2017/10/14
 * Time: 15:04
 */

namespace Mall\Model;


use Think\Model;

class ServiceModel extends Model
{
    protected $tableName ='mall_service';
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function getAllService()
    {
        $spec = $this->where(array('status'=>array('neq',-1)))->select();
        return $spec;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/28 0028
 * Time: 下午 2:08
 */

namespace Mall\Model;


use Think\Model;

class GoodsCarModel  extends Model
{
    protected $tableName = 'mall_goods_car';

    public function getCarCount($id){
        $res=$this->where(array('uid'=>get_uid(),'goods_id'=>$id))->count();
        return $res;
    }

    public function addCar($item){
        $old = $this->where(array('uid' => is_login(), 'goods_id' => $item))->find();
        if ($old) {
            return false;
            //$res=  $this->where(array('uid' => is_login(), 'goods_id' => $item))->save(array('create_time' => time(), 'count' => $old['count'] + 1));
        } else {
            $res=  $this->add(array('uid' => is_login(), 'goods_id' => $item, 'create_time' => time(), 'count' =>1));
        }
        return $res;
    }
    public function  clearCar(){
       $this->where(array('uid' => is_login()))->delete();
    }

    public function removeCar($good_id){
        $res = $this->where(array('uid' => is_login(), 'goods_id' => $good_id))->delete();
        return $res;
    }
}
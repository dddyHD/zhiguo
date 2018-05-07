<?php
namespace Order\Model;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/29
 * Time: 11:17
 * @author lin <lt@ourstu.com>
 */
class OrderGoodsModel extends Model
{
    protected $tableName = 'order_goods';

    protected $_auto = array(
        array('id', 'randId', self::MODEL_INSERT, 'callback'),
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    //获得订单中的商品id的数量
    public function getGoodsInfo($id){
        $goodsArray= $this->where(array('id'=>$id))->getField('goods_id');
        return $goodsArray;
    }
  //获得订单中的商品信息
  public function getGoodsById($id){
      $gId=$this->where(array('id'=>$id))->getField('goods_id');
      $goods=M('mall_goods')->where(array('id'=>$gId))->find();
      $cate=M('mall_goods_category')->where(array('id'=>$goods['cate']))->getField('title');
      $goods['cate']=$cate;
      return $goods;
  }

  //获得商品订单信息
  public function getGoodsOrder($aId){
      $gOrder=$this->where(array('id'=>$aId))->find();
      switch($gOrder['process']){
          case -1:
          {
              $gOrder['state']='交易关闭';
              break;
          }
          case 0:
          {
              $gOrder['state']='待付款';
              break;
          }
          case 1:
          {
              $gOrder['state']='已付款';
          }
      }
      return $gOrder;
  }

  //创建商品订单
  public function createOrder($data){
      $data = $this->create($data);
      if(!$data) return false;
      $res=$this->add($data);
      if(!$res) {
          return false;
      }
      return $data['id'];
  }

    protected function randId(){
        $id = time().create_rand(4,'num');
        return $id;
    }

    //获取用户已购商品id
    public function getAllGoods($uid){
        $ids=$this->where(array('uid'=>$uid,'process'=>1,'is_pay'=>1))->getField('goods_id',true);
        return $ids;
    }

    //判断商品是否已购
    public function isPayed($uid,$goods_id){
        $map['pay_time']=array('neq',0);
        $map['uid']=$uid;
        $map['goods_id']=$goods_id;
        $order=D('order_goods')->where(array('uid'=>$uid,'is_pay'=>1))->select();
        foreach ($order as $v){
            $goodsId= explode(',', $v['goods_id']);
            foreach ($goodsId as $vo){
                if($vo==$goods_id){
                    return true;
                }
            }
        }
        unset($v);
        return false;
    }

    //是否存在订单
    public function isOrder($data){
        $order=$this->where($data)->find();
        return $order;
    }
    //历史收益增加
    public function addProfit($goods_id,$amount){
        $profit=D('mall_goods')->where(array('id'=>$goods_id))->getField('profit');
        $data['profit']=$profit+$amount;
        D('mall_goods')->where(array('id'=>$goods_id))->save($data);
    }
}
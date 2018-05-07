<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/30
 * Time: 16:58
 */

namespace Ucenter\Model;


use Think\Model;

class OrderModel extends Model
{
    //获取订单
    public function getOrderByScreen($condition,$page){
        $map['uid']=is_login();
        switch($condition){
            case 1:;break;
            case 2:$map['is_pay']=0;break;
            case 3:$map['is_pay']=1;break;
            default:;break;
        }
        $order=D('order_goods')->order('create_time desc')->where($map)->page($page,10)->select();
        foreach ($order as $key=>$vo){
            $goodsId=explode(',',$vo['goods_id']);
            $countId=count($goodsId);
            $goods=D('mall_goods')->where(array('id'=>$goodsId[0]))->find();
            $order[$key]['goods_name']=$goods['name'];
            $order[$key]['price']=$goods['price'];
            $order[$key]['friend_time']=friendlyDate($vo['create_time']);
            $order[$key]['status_name']=$this->statusName($vo['is_pay']);
            $order[$key]['count']=$countId;
        }
        unset($key,$vo);
        return $order;
    }

    public function statusName($is_pay){
        $statusArray=array(
            '0'=>'待付款',
            '1'=>'已付款'
        );
        return $statusArray[$is_pay];
    }
}
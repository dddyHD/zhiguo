<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/6
 * Time: 19:48
 * @author lin <lt@ourstu.com>
 */

namespace Order\Model;


use Think\Model;

class RechargeModel extends Model
{
    protected $tableName = 'order_recharge';
    protected $_auto = array(
        array('id', 'randId', self::MODEL_INSERT, 'callback'),
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function rechargeOrder($data){
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

    public function getRechargeOrder($id){
        $order=$this->where(array('id'=>$id))->find();
        if($order['method']=='wechat'){
            $order['way']='微信支付';
        }
        $order['nickname']=M('member')->where(array('uid'=>$order['uid']))->getField('nickname');
        if($order['is_pay']==0){
            $order['pay']='未付款';
        }elseif ($order['is_pay']==1){
            $order['pay']='已付款';
        }
        return $order;
    }


    //获取充值配置积分信息
    public function get_re_field(){
        $fields_config = modC('RE_FIELD', "", 'order');
        $fields = json_decode($fields_config, true);
        foreach ($fields as &$v) {
            $v['score'] = D('Ucenter/Score')->getType(array('status' => 1, 'id' => $v['FIELD']));
            $v['have'] = M('member')->where(array('uid' => is_login()))->getField('score' . $v['FIELD']);
            $v['have'] = number_format($v['have'], 2, ".", "");
        }
        return $fields;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/8
 * Time: 14:26
 * @author lin <lt@ourstu.com>
 */

namespace Order\Model;


use Think\Model;

class WithdrawModel extends Model
{
    protected $tableName = 'order_withdraw';
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function withdrawOrder($data){
        $data = $this->create($data);
        if(!$data) return false;
        $res=$this->add($data);
        if(!$res) {
            return false;
        }
        return $res;
    }

    public function getWithdrawOrder($id){
        $order=$this->where(array('id'=>$id))->find();
        $order['nickname']=M('member')->where(array('uid'=>$order['uid']))->getField('nickname');
        $order['type']=M('ucenter_score_type')->where(array('id'=>$order['field']))->getField('title');
        return $order;
    }

    //获取提现配置积分信息
    public function get_wi_field(){
        $fields_config = modC('WI_FIELD', "", 'order');
        $fields = json_decode($fields_config, true);
        foreach ($fields as &$v) {
            $v['score'] = D('Ucenter/Score')->getType(array('status' => 1, 'id' => $v['FIELD']));
            $v['have'] = M('member')->where(array('uid' => is_login()))->getField('score' . $v['FIELD']);
            $v['have'] = number_format($v['have'], 2, ".", "");
            $v['withdraw'] = $v['have']/$v['UNIT'];
            $v['withdraw'] = number_format( $v['withdraw'],2,".","");
        }
        return $fields;
    }
}
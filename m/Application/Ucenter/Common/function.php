<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */




/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function count2str($count){
    if($count>9999){
        $count=number_format($count/10000,1).'万';
        return $count;
    }
    else{
        return $count;
    }

}

function check_verify($code, $id = 1)
{
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

function is_weixin()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        return true;
    }
    return false;
}

function wx_pay($status = 0,$out_trade_no = '',$openId = '' ,$uid = 0)
{
    M('wx_pay_log')->add(array(
        'uid'=>$uid,
        'open_id'=>$openId,
        'trade_no'=>$out_trade_no,
        'create_time' => time(),
        'status' => $status
    ));
}
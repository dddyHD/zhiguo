<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 上午10:47
 * @author 钱枪枪<8314028@qq.com>
 */

namespace Weixin\Model;

use Think\Model;

class WeixinConfigModel extends Model{

    public function getWeixinConfig(){
        $config = S('WEIXIN_CONFIG');
        if($this->debug || !$config){
            $config = array();
            $tmp = D('MConfig')->where(array('name' => array('like', '_WEIXIN_' . '%')))->limit(999)->select();
            foreach ($tmp as $k => $v) {
                $key = str_replace('_WEIXIN_', '', strtoupper($v['name']));
                $config[$key] = text($v['value']);
            }
            S('WEIXIN_CONFIG', $config);
        }
        return $config;
    }



}
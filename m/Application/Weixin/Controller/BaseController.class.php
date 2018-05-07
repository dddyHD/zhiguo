<?php
namespace Weixin\Controller;
use Weixin\Sdk\Wechat;
use Think\Controller;

class BaseController extends Controller{
    protected $debug = true;

    protected function getWeixinConfig(){
        $config = S('WEIXIN_CONFIG');
        if($this->debug || !$config){
            $config = array();
            $tmp = D('MConfig')->where(array('name' => array('like', '_WEIXIN_' . '%')))->limit(999)->select();
            foreach ($tmp as $k => $v) {
                $key = str_replace('_WEIXIN_', '', strtoupper($v['name']));
                $config[$key] = $v['value'];
            }
            S('WEIXIN_CONFIG', $config);
        }
        return $config;
    }

    protected function getAreplyModel(){
        return D('Weixin/WeixinAreply');
    }

}
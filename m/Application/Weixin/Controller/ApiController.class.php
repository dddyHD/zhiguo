<?php
namespace Weixin\Controller;
use Weixin\Sdk\Wechat;

class ApiController extends BaseController{


    /**
     * 微信消息接口入口
     * 所有发送到微信的消息都会推送到该操作
     * 所以，微信公众平台后台填写的api地址则为该操作的访问地址
     */
    public function index(){
        $config = $this->getWeixinConfig();

        $token = $config['WX_TOKEN']; //微信后台填写的TOKEN

        
        /* 加载微信SDK */
        $wechat = new Wechat($token);
        /* 获取请求信息 */
        $data = $wechat->request();

        $this->debug && S('WX_IN_DEBUG', $data);
        if($data && is_array($data)){
            $type = $data['MsgType'];
            $keywords = $data['Content'];
            $userId = $data['FromUserName'];
            switch($type){
                case Wechat::MSG_TYPE_TEXT:
                    $info = D('Weixin/WeixinAreply')->getAreply($keywords);
                    $type = $info['type'];
                    $content = $info['content'];
                    $this->debug && S('WX_RS_DEBUG', $info);
                    break;
                case Wechat::MSG_TYPE_EVENT:
                    switch($data['Event']){
                        case Wechat::MSG_EVENT_SUBSCRIBE:
                            $info = D('Weixin/WeixinAreply')->getAttention();
                            $type = $info['type'];
                            $content = $info['content'];
                            $this->debug && S('WX_RS_DEBUG', $info);
                            break;
                        case Wechat::MSG_EVENT_CLICK:
                            $this->debug && S('WX_RS_DEBUG', $info);
                            $keywords = $data['EventKey'];
                            $info = D('Weixin/WeixinAreply')->getAreply($keywords);
                            $type = $info['type'];
                            $content = $info['content'];
                            $this->debug && S('WX_RS_DEBUG', $info);
                            break;
                    }
                    break;
            }
            if(!$content){
                if(isset($config['WX_KEFU'])){
                    $type = Wechat::MSG_TYPE_KEFU;
                    $content = '';
                } else {
                    $content = '没有查询到相关信息';
                    $type = 'text';
                }
            }

            /**
             * 你可以在这里分析数据，决定要返回给用户什么样的信息
             * 接受到的信息类型有9种，分别使用下面九个常量标识
             * Wechat::MSG_TYPE_TEXT       //文本消息
             * Wechat::MSG_TYPE_IMAGE      //图片消息
             * Wechat::MSG_TYPE_VOICE      //音频消息
             * Wechat::MSG_TYPE_VIDEO      //视频消息
             * Wechat::MSG_TYPE_MUSIC      //音乐消息
             * Wechat::MSG_TYPE_NEWS       //图文消息（推送过来的应该不存在这种类型，但是可以给用户回复该类型消息）
             * Wechat::MSG_TYPE_LOCATION   //位置消息
             * Wechat::MSG_TYPE_LINK       //连接消息
             * Wechat::MSG_TYPE_EVENT      //事件消息
             *
             * 事件消息又分为下面五种
             * Wechat::MSG_EVENT_SUBSCRIBE          //订阅
             * Wechat::MSG_EVENT_SCAN               //二维码扫描
             * Wechat::MSG_EVENT_LOCATION           //报告位置
             * Wechat::MSG_EVENT_CLICK              //菜单点击
             * Wechat::MSG_EVENT_MASSSENDJOBFINISH  //群发消息成功
             */

            /*$content = ''; //回复内容，回复不同类型消息，内容的格式有所不同
            $type    = ''; //回复消息的类型*/

            /* 响应当前请求(自动回复) */
            $wechat->response($content, $type);

            /**
             * 响应当前请求还有以下方法可以只使用
             * 具体参数格式说明请参考文档
             *
             * $wechat->replyText($text); //回复文本消息
             * $wechat->replyImage($media_id); //回复图片消息
             * $wechat->replyVoice($media_id); //回复音频消息
             * $wechat->replyVideo($media_id, $title, $discription); //回复视频消息
             * $wechat->replyMusic($title, $discription, $musicurl, $hqmusicurl, $thumb_media_id); //回复音乐消息
             * $wechat->replyNews($news, $news1, $news2, $news3); //回复多条图文消息
             * $wechat->replyNewsOnce($title, $discription, $url, $picurl); //回复单条图文消息
             *
             */
        }
    }

    public function debug(){
        if(!$this->debug){
            exit('请先打开debug');
        }
        $debugIn = S('WX_IN_DEBUG');
        $debugRs = S('WX_RS_DEBUG');
        echo '<pre>in:';
            print_r($debugIn);
        echo '</pre>';
        echo '<pre>out:';
        print_r($debugRs);
        echo '</pre>';
        exit;
    }
}
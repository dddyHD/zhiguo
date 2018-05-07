<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-27
 * Time: 下午1:22
 * @author 钱枪枪<8314028@qq.com>
 */

namespace Weixin\Model;

use Think\Model;
use Weixin\Sdk\Wechat;

class WeixinAreplyModel extends Model{

    public function getAttention(){
        $info = $this->where(array('is_attention' => 1))->find();
        if(!$info){
            $info = array(
                'type' => 1,
                'content' => '欢迎您,感谢关注本微信公众号。',
            );
        }
        return $this->getReturnInfo($info);
    }

    public function getAreply($keywords){
        $where = array(
            'keywords' => $keywords
        );
        $info = $this->where($where)->find();
        return $this->getReturnInfo($info);
    }

    protected function getReturnInfo($info){
//        return $info;
        $types = array(
            1 => Wechat::MSG_TYPE_TEXT,
            2 => Wechat::MSG_TYPE_NEWS,
            3 => Wechat::MSG_TYPE_NEWS,
        );
        $content = $this->formartForReply($info);
        return array(
            'type' => $types[$info['type']],
            'content' => $content
        );
    }

    protected function formartForReply($info){
        switch($info['type']){
            case 1:
                return $info['content'];
                break;
            case 2:
                $news = $this->formartNews($info);
                return array($news);
                break;
            case 3:
                if(!$info['content']){
                    return array();
                }
                $news = array();
                $tmp = $this->order('id ASC')->where(array('id'=> array('in', $info['content'])))->select();
                foreach($tmp as $item){
                    $news[] = $this->formartNews($item);
                }
                return $news;
        }
    }

    protected function formartNews($info){
        $info['content'] = strip_tags($info['content']);
        $content = substr($info['content'], 0, 50);
        $content = str_replace(' ', '', $content);
        $content = str_replace('\r', '', $content);
        $content = str_replace('\n', '', $content);
        $host = $this->getHost();
        $picUrl = render_picture_path(pic($info['image']));
        return array(
            'Title' => $info['title'],
            'Description' => $content,
            'PicUrl' => $picUrl,
            'Url' => $info['linkurl'] ? $info['linkurl'] : $host . U('Weisite/index/detail', array('id' => $info['id']))
        );
    }

    protected $host;
    protected function getHost(){
        if($this->host){
            return $this->host;
        }
        $url = S('WX_FRONT_SITEURL');
        if(!$url){
        $tmp = D('MConfig')->where(array('name' => '_WEIXIN_WX_SITEURL'))->find();
            $url = $tmp['value'];
            S('WX_FRONT_SITEURL', $url);
        }
        return $this->host = 'http://' . $url;
    }

}
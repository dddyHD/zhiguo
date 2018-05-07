<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2016/12/7
 * Time: 9:15
 */
namespace Message\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function _initialize()
    {
        if (!is_login()) {
            $url = U('Ucenter/Member/login');
            redirect($url);
        }
        $this->setTitle("消息");
        $this->setKeywords("消息");
        $this->setDescription("消息");
        $this->assign('bottom_flag','message');
    }
    /**
     * 主页面显示
     */
    public function index()
    {
        $uid = is_login();
        $messageModel = D('Common/Message');
        $messageTypeList = $messageModel->getMyMessageSessionList($uid);
//        foreach($messageTypeList as &$value){
//            $value['detail']['logo'] = get_attach_path($value['detail']['logo']);
//        }
//        $this->ajaxReturn($messageTypeList);
//        dump($messageTypeList);exit;
        $this->assign('message',$messageTypeList);
        $this->display();

    }

    public function message()
    {
        $mid = is_login();
        $messageType = I('get.type','','text');
        $aPage = I('get.page',1,'intval');
        $aIsPull = I('get.is_pull','0','intval');
        $msgMod = M('Message');
        $map['to_uid'] = $mid;
        if($messageType){
            $messageType = ucfirst($messageType);
            $map['type'] = $messageType;
        }
        $map['status'] = 1;
        $messages = $msgMod->where($map)->order('id desc')->page($aPage,10)->select();
        foreach($messages as &$messageInfo){
            $messageInfo = $this->_dealMessage($messageInfo);
        }
        unset($messageInfo);
        //设置未读消息为已读
        D('Common/Message')->setAllReaded($mid,$messageType);
        $this->assign('first_message_num',count($messages));
        $this->assign('type',$messageType);
        $this->assign('type_title',get_message_title($messageType));
        $this->assign('message',$messages);
        if ($aIsPull) {
            $data['html'] = '';
            $data['status'] = 1;
            $data['html'] .= $this->fetch('_list');
            $this->ajaxReturn($data);
        } else {
            $this->display();
        }
    }

    /**
     * 设置已读
     */
    public function setAllReaded()
    {
        $uid = $this->requireIsLogin();
        $id = I_POST('id','intval');
        if($id){
            $messages = D('message')->where(array('to_uid='=> $uid, 'is_read'=>0 ,'id'=>$id))->setField('is_read', 1);
        }else{

            $messages = D('message')->where(array('to_uid='=> $uid, 'is_read'=>0))->setField('is_read', 1);
        }
    }

    public function _dealMessage($messageInfo){
        if (in_array($messageInfo['type'], array('1', '2', '3', '0', '4', '5'))) {
            $messageInfo['type'] = 'Common_system';
        }
        $messageInfo['content'] = D('Common/Message')->getContent($messageInfo['content_id']);
        if (is_array($messageInfo['content']['content'])) {
            $messageInfo['content']['untoastr'] = 1;
        }
        if(!$messageInfo['content']['user']){
            $messageInfo['content']['user'] = array();
        } else {
            $messageInfo['content']['user']['space_mob_url'] = U('Ucenter/Index/mine/', array('uid' => $messageInfo['content']['user']['uid']));
        }
        if($messageInfo['type'] == 'Weibo'){
            $messageInfo['content']['weibo_data'] = D('Weibo/weibo')->getWeiboDetail($messageInfo['content']['args']['id']);
        }
        return $messageInfo;
    }
}
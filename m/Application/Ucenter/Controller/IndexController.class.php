<?php
namespace Ucenter\Controller;

use Think\Controller;
require_once APP_PATH.'User/Common/common.php';
require_once(APP_PATH . '/User/Conf/config.php');
class IndexController extends Controller
{

    public function _initialize()
    {
        $this->assign('bottom_flag','mine');
    }

    public function index($uid=null)
    {
//
//        $this->setTitle('我');
//        $this->setKeywords("我");
//        $this->setDescription("我");
        $aIsShare = I('get.is_share',0,'intval');
        if(empty($uid)){
            $uid=is_login();
        }
        if($uid!=is_login()){
           redirect('404');
        }
        $model=new \Core\Model\CheckInModel();
        $check = $model->getCheck($uid);
        if($check){
            $this->assign("check", true);
        }else{
            $this->assign("check", false);
        }
        $user_info = query_user(array('avatar64', 'nickname', 'uid', 'space_url','space_mob_url', 'icons_html', 'score', 'title', 'fans', 'following', 'weibocount', 'rank_link', 'signature','con_check', 'total_check'), $uid);
        $noteCount=D('note')->where(array('uid'=>$uid))->count();
        $this->assign('noteCount',$noteCount);
        $this->assign("uid", $uid);
        $this->assign('user_info', $user_info);
        $friend_list=$this->_myfriend($uid);
        $friends=count($friend_list);
        $this->assign('friends',$friends);
        $this->assign('is_share',$aIsShare);
        $this->display();

    }
    public function edit(){
        $this->setTitle('基本资料');
        $this->setKeywords("基本资料");
        $this->setDescription("基本资料");
        $aUid = I('get.uid', 0, 'intval');
        if (!$aUid) {
            redirect(U('Mall/index/index'));
        }
        $userData=$this->_userData($aUid);
        $this->assign('user',$userData);
        $this->assign('uid',$aUid);
        $this->display();
    }
    public function mine(){
        $this->setTitle('个人中心');
        $this->setKeywords("个人中心");
        $this->setDescription("个人中心");
        $aUid = I('get.uid', 0, 'intval');
        if (!$aUid) {
            redirect(U('Mall/index/index'));
        }
        $user_info =$this->_userData($aUid);
        $friend_list=$this->_myfriend($aUid);
        //判断是否是自己的名片
        $isMy=$aUid==is_login()?true:false;
        $this->assign('isMy',$isMy);

        $this->assign('uid', $aUid);
        $this->assign('page', 1);
        $this->assign('user_info', $user_info);
        $this->display();
    }
   public function follow(){
       if(!is_login()){
           $data['status']=0;
           $data['info']='先去登录吧';
           $this->ajaxReturn($data);
       }
       $aUid=I('post.uid',0,'intval');
       $aType=I('post.type','follow','text');
       $res=D('Follow')->$aType($aUid);
       $this->ajaxReturn($res);
   }
    /**
     * @param $uid
     * 获得我的好友
    */
    private function _myfriend($uid){
        $aPage = I('get.page', 1, 'intval');
        $aCount = I('get.count', 10, 'intval');
        $uids=D('Follow')->page($aPage,$aCount)->getMyFriends($uid);
        $k=0;
        foreach ($uids as $val){
            $user_info[$k] = query_user(array('avatar64', 'nickname', 'uid', 'space_url', 'space_mob_url', 'title', 'fans', 'following', 'signature'), $val);
            $k++;
        }
        unset($val,$k);

        foreach ($user_info as &$v){
            $v['is_follow']=D('Common/Follow')->isFollow(is_login(),$v['uid']);
        }
        return $user_info;
    }
    public function addMoreFriend(){
        $aPage = I('post.page', 1, 'intval');
        $aCount = I('post.count', 10, 'intval');
        $aUid=I('post.uid',0,'intval');
        $uids=D('Follow')->page($aPage,$aCount)->getMyFriends($aUid);
        if(empty($uids)){
            $data['status']=0;
            $data['info']='没有更多了';
            $this->ajaxReturn($data);
        }
        $k=0;
        foreach ($uids as $val){
            $user_info[$k] = query_user(array('avatar64', 'nickname', 'uid', 'space_url', 'space_mob_url', 'title', 'fans', 'following', 'signature'), $val);
            $k++;
        }
        unset($val,$k);
        foreach ($user_info as &$v){
            $v['is_follow']=D('Common/Follow')->isFollow(is_login(),$v['uid']);
        }
        $this->assign('friend_list',$user_info);
        $html=$this->fetch('_friend');
        $this->ajaxReturn($html);
    }




    /**
     * @param $uid
     * @return mixed
     * 用户资料
     */
    private function _userData($uid){
        $userdata=D('Member')->where(array('uid'=>$uid))->find();
        $userdata['user']=query_user(array('nickname','email','mobile','birthday','following','avatar64','signature','space_mob_url','fans'), $uid);
        return $userdata;
    }

    public function avatar(){
        $this->setTitle('头像');
        $this->setKeywords("头像");
        $this->setDescription("头像");
        if(IS_POST){
            $aUid=I('post.uid',0,'intval');
            if($aUid!=is_login()){
                $data['status']=0;
                $data['info']='无法修改';
            }
            else{
                $data['status']=1;
            }
            $this->ajaxReturn($data);
        }

        $data=query_user('avatar512',is_login());
        $this->assign('avatar',$data['avatar512']);
        $this->assign('uid',is_login());
        $this->display();
    }


    public  function rank(){
        $this->setTitle('排行榜');
        $this->setKeywords("排行榜");
        $this->setDescription("排行榜");
        if(IS_POST){
            $aUid=I('post.uid',0,'intval');
            if(!is_login()||is_login()!=$aUid){
                $data['status']=0;
                $data['info']='非正常登录';
                $this->ajaxReturn($data);
            }
            $addon   = new \Core\Controller\CheckInController();
            $res=$addon->doCheckIn();
            if ($res) {
                $check = query_user(array('con_check', 'total_check'), $aUid);
                $this->ajaxReturn(array('status' => 1, 'info' => '签到成功!', 'con_check' => $check['con_check'], 'total_check' => $check['total_check']));
            } else {
                $this->ajaxReturn(array('status' => 0,'info' =>'已经签到了！'));
            }
        }else{
            $memberModel = D('Member');
            $uid=is_login();
            $user=query_user(array('avatar512','nickname','con_check', 'total_check'), $uid);
            $model=new \Core\Model\CheckInModel();
            $con=$model->getRank('con');
            $total=$model->getRank('total');
            $rankList=array();
            $p=1;
            foreach ($con as $co){
                if($co['uid']==is_login()){
                    $rankList['con_check_rank'] = $p;
                }else{
                    $p++;
                }

            }

            $q=1;
            foreach ($total as $to){
                if($to['uid']==is_login()){
                    $rankList['total_check_rank'] =$q;
                }else{
                    $q++;
                }

            }
             unset($p,$q,$to,$co);
            //排行榜个人排名
            $userScore = $memberModel->where(array('uid' => $uid))->field('fans')->find();
            $rankList['my_fans']=count2str($userScore['fans']);

            $tag='fans_rank';
            $user_fans_list=S($tag);
            if(empty($user_fans_list)){
                $user_fans_list = $memberModel->where(array('status' => 1,'fans'=>array('gt',0)))->field('uid,fans,nickname')->order('fans desc,uid asc')->limit(10)->select();
                foreach ($user_fans_list as &$u) {
                    $temp_user = query_user(array('avatar512'), $u['uid']);
                    $u['avatar512'] = $temp_user['avatar512'];
                }
                S($tag,$user_fans_list,60*60);
            }

            $k=1;
            foreach ($user_fans_list as $vo){
                if($vo['uid']==is_login()){
                    $rankList['fans_rank'] = count2str($k);
                }else{
                    $k++;
                }

            }
            unset($k,$vo);
            $this->assign('user',$user);
            $this->assign('con',$con);
            $this->assign('total',$total);
            $this->assign('fans_list',$user_fans_list);
            $this->assign('rank',$rankList);
            $this->display();
        }

    }
    public function handleData($data){
        foreach ($data as &$v){
            $v['user']['is_follow']=D('Common/Follow')->isFollow(is_login(),$v['user']['uid']);
        }
        unset($v);
        return $data;
    }
    public function fansList($aType,$aUid,$page=1){
        switch ($aType) {
            case 'friends':
                $list = $this->_myfriend($aUid);
                $k=0;
                foreach ($list as $v){
                    $data[$k]['user']=$v;
                    $k++;
                }
                unset($v,$k);
                if($aUid==is_login()){
                    $title='我的好友';
                }else{
                    $title='他的好友';
                }
                $this->assign('type','friends');
                break;
            case 'fans':
                if($aUid==is_login()){
                    $title='我的粉丝';
                }else{
                    $title='他的粉丝';
                }
                $data = D('Follow')->getFans($aUid, $page, array('avatar128', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title','space_mob_url','signature'));
                $data=$this->handleData($data);
                $this->assign('type','fans');
                break;
            case 'follow':
                if($aUid==is_login()){
                    $title='我的关注';
                }else{
                    $title='他的关注';
                }
                $this->assign('type','follow');
                $data = D('Follow')->getFollowing($aUid, $page, array('avatar128', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title','space_mob_url','signature'));
                $data=$this->handleData($data);
                break;
            default:
                if($aUid==is_login()){
                    $title='我的关注';
                }else{
                    $title='他的关注';
                }
                $this->assign('type','follow');
                $data = D('Follow')->getFollowing($aUid, $page, array('avatar128', 'uid', 'nickname', 'fans', 'following', 'weibocount', 'space_url', 'title','space_mob_url','signature'));
                $data=$this->handleData($data);
        }
       return array($title,$data);
    }
    //我的关注、粉丝、好友
    public function fans(){
            $aUid = I('get.uid', 0, 'intval');
            $aType = I('get.type', 'follow', 'text');
            $aPage=I('get.page',1,'intval');
            $aPull=I('get.is_pull',0,'intval');
            $result=$this->fansList($aType,$aUid,$aPage);
            $this->assign('first_num',count($result[1]));
            $this->setTitle($result[0]);
            $this->setKeywords($result[0]);
            $this->setDescription($result[0]);
            $this->assign('title',$result[0]);
            $this->assign('data',$result[1]);
            $this->assign('page',$aPage);
            $this->assign('uid',$aUid);
            if($aPull==0){
                $this->display();
            }else{
                $data['html'] = '';
                $data['status'] = 1;
                $data['html'] .= $this->fetch('_fans');
                $this->ajaxReturn($data);
            }

        }

    public function getInfo()
    {
        $aUid = I('get.uid',0,'intval');
        $user = query_user(array('uid', 'nickname', 'avatar64','avatar_html64', 'space_url', 'following', 'fans', 'weibocount', 'signature', 'rank_link','pos_province', 'pos_city', 'pos_district', 'pos_community'), $aUid);
        $cover = modC('WEB_SITE_LOGO','','Config');
        $user['logo'] = getThumbImageById($cover,80,80);
        if ($aUid == is_login()) {
            $user['is_self'] = 1;
        } else{
            $user['is_self'] = 0;
        }
        $user['fans']=$user['fans']?$user['fans']:0;
        $user['following']=$user['following']?$user['following']:0;
        $res = D('Common/Follow')->isFollow(is_login(), $aUid);
        if ($res == 1) {
            $user['follow_status'] = '已关注';
            $user['is_follow'] = 'unfollow';
        } else {
            $user['follow_status'] = '关注';
            $user['is_follow'] = 'follow';
        }
        $user['is_login'] = is_login();
        $user['is_wechat'] = is_weixin();
        if ($user) {
            $this->ajaxReturn(array('status'=>1,'data'=>$user));
        } else {
            $this->ajaxReturn(array('status'=>0));
        }
    }
    public function favorite(){
        $this->setTitle('收藏夹');
        $this->setKeywords("收藏夹");
        $this->setDescription("收藏夹");
        $collection = D('Collect')->where(array('module'=>'Mall'))->count();
        $count= D('Collect')->where(array('module'=>'News'))->count();
        $this->assign('collection',$collection);
        $this->assign('count',$count);
        $this->display();
    }

    public function addList(){

        $aPage = I('post.page',1,'intval');
        $aCount = I('post.count',10,'intval');
        $aModule=I('post.module','Mall','text');
        $collection = D('Collect')->getCollection($aModule,'',$aPage,$aCount);
        foreach ($collection as &$v) {
            switch ($v['table']) {
                case 'collect':
                    $v['detail'] = D('Mall/Goods')->getGoods($v['row']);
                    break;
                case 'news':
                    $v=D('News/news')->where(array('id'=>$v['row']))->find();
                    $v['category']=D('New/news_category')->where(array('id'=>$v['category']))->getField('title');
                    $v['uid']=query_user(array('nickname','uid','avatar512'),$v['uid']);
                    break;
            }
        }
        unset($v);
        if($collection){
            if($aModule == 'Mall'){
                $this->assign('collection',$collection);
                $collection=$this->fetch('_favorite');
                $this->ajaxReturn(array(
                    'info'=>'请求成功',
                    'status'=>1,
                    'data'=>$collection
                ));
            }else{
                $this->assign('data',$collection);
                $collection=$this->fetch('_list');
                $this->ajaxReturn(array(
                    'info'=>'请求成功',
                    'status'=>1,
                    'data'=>$collection
                ));
            }
        }else{
            $this->ajaxReturn(array(
                'info'=>'请求失败',
                'status'=>1,
                'data'=>''
            ));
        }
    }
    public function wallet(){
        $this->setTitle('我的钱包');
        $this->setKeywords("我的钱包");
        $this->setDescription("我的钱包");
        //查询总金额
        $data=D('member')->field('score4')->where(array('uid'=>is_login()))->find();
        //查询领红包情况
        $data['score4']=number_format($data['score4'],2);
        if(IS_POST){
            $page=I('post.page',1,'intval');
            $dataList=D('consumption_log')->where(array('uid'=>is_login()))->page($page,10)->order('create_time desc')->select();
                foreach ($dataList as &$vo){
                    $username=D('member')->where(array('id'=>$vo['id']))->field('nickname')->find();
                    $vo['uid']=$username['nickname'];
                    $vo['create_time']=friendlyDate($vo['create_time']);
                }
                unset($vo);
            if($dataList){
                $this->assign('dataList',$dataList);
                $html=$this->fetch('_walletlist');
                $this->ajaxReturn(array('status'=>1,'info'=>'请求成功','data'=>$html));
            }else{
                $this->ajaxReturn(array('data' => '', 'status' => 1, 'info' => '获取失败'));
            }
        }else{
            $count=D('consumption_log')->where(array('uid'=>is_login()))->count();
            //获取后台配置提现字段
            $fields=D('Order/Withdraw')->get_wi_field();
            $isBindWeixin=D('Score')->isBindWeixin();
            $this->assign(array(
                'totalMoney'=>$data['score4'],
                'count'=>$count,
                'fields'=>$fields,
                'isBindWeixin'=>$isBindWeixin
            ));
            $this->display();
        }
    }

    public function pay()
    {
        include_once(APP_PATH."Ucenter/Lib/WxPayPubHelper.php");

        $jsApi = new \JsApi_pub();
        redirect($jsApi->createOauthUrlForCode(U('chooseMoney','',true,true)));

    }

    public function chooseMoney()
    {
        include_once(APP_PATH."Ucenter/Lib/WxPayPubHelper.php");
        $code = I('get.code','','html');
        $jsApi = new \JsApi_pub();
        $jsApi->code = $code;
        $openId = $jsApi->getOpenid();
        $this->assign('open_id',$openId);
        $this->display();
    }

    public function payapi()
    {
        include_once(APP_PATH."Ucenter/Lib/WxPayPubHelper.php");
        $openId = I('post.open_id',0,'html');
        $money = I('post.money',1,'intval');

        if (!is_login()) {
            $this->ajaxReturn(array('status'=>0,'info'=>'未登录'));
        }
//        $user = M('sync_login')->where(array('type'=>'weixin','oauth_token_secret'=>$openId))->find();
//        $rs = D('Ucenter/Score')->setUserScore(is_login(), $money/100, 4, 'dec');

        $jsApi = new \JsApi_pub();
        $unifiedOrder = new \UnifiedOrder_pub();

        $unifiedOrder->setParameter("openid",$openId);
        $unifiedOrder->setParameter("body","this is a test");
        $timeStamp = time();
        $out_trade_no = \WxPayConf_pub::APPID.$timeStamp;
        $unifiedOrder->setParameter("out_trade_no",$out_trade_no);
        $unifiedOrder->setParameter("total_fee",$money);//总金额
        $unifiedOrder->setParameter("notify_url",\WxPayConf_pub::NOTIFY_URL);
        $unifiedOrder->setParameter("trade_type","JSAPI");

        $prepay_id = $unifiedOrder->getPrepayId();
        $jsApi->setPrepayId($prepay_id);

        $jsApiParameters = $jsApi->getParameters();

        wx_pay(0,$out_trade_no,$openId,is_login());

        exit($jsApiParameters);
    }
    //修改密码
    public function resetPassword(){
        $this->setTitle('修改密码');
        $this->setKeywords("修改密码");
        $this->setDescription("修改密码");
        if (!is_login()){
            $this->error('请先登录');
        }
        if (IS_POST){
            $oldPassword=I('post.oldPassword','string');
            $newPassword=trim(I('post.newPassword','','string'));
            $RenewPassword=trim(I('post.RenewPassword','','string'));
            if ($oldPassword==null){
                $this->error('旧密码不能为空');
            }else if($newPassword == null or $RenewPassword == null){
                $this->error('新密码不能为空');
            }
            if ($newPassword!=$RenewPassword||$newPassword==null||$RenewPassword==null){
                $this->error('密码不一致');
            }
            $oldPassword=think_ucenter_md5($oldPassword, UC_AUTH_KEY);
            $id=D('ucenter_member')->field('id')->where(array('password'=>$oldPassword,'id'=>is_login()))->find();
            if ($id==null){
                $this->error('旧密码不正确');
            }else{
                $newPassword=think_ucenter_md5($newPassword,UC_AUTH_KEY);
                if ($newPassword==$oldPassword){
                    $this->error('新旧密码一致，不能更改');
                }else{
                    $data['password']=$newPassword;
                    $res=D('ucenter_member')->where(array('id'=>is_login()))->save($data);
                    if ($res){
                        $data['status']=1;
                        $data['info']='修改成功';
                        $this->ajaxReturn($data);
                    }else{
                        $this->error('修改失败，未知错误！');
                    }
                }
            }


        }
        $this->display();
    }

    public function square(){
        $map['status']=1;
        $map['location']=1;
        $advertisement=S('ucenter_square_advertisement');
        if ($advertisement===false){
            $advertisement=D('advertisement')->where($map)->select();
            foreach ($advertisement as &$val){
                $val['img']=getThumbImageById($val['imgid'],5000,5000);
            }
            unset($val);
            S('ucenter_square_advertisement',$advertisement,86400);
        }
        $count=D('advertisement')->where($map)->count();
        $this->assign('count', $count);
        $this->assign('advertisement', $advertisement);
        $this->display();
    }
    
    
    //账号与安全 start
    public function setting()
    {
        $this->setTitle('设置');
        $this->setKeywords("设置");
        $this->setDescription("设置");
        $this->display();
    }

    public function safe()
    {
        $this->setTitle('帐号与安全');
        $this->setKeywords('帐号与安全');
        $this->setDescription("帐号与安全");
        $uid = is_login();
        if(IS_POST) {
            $sync = I('post.sync', '', 'intval');
            $mobile = I('post.mobile', '', 'intval');

            if($sync == 1) {
                $tel = query_user('mobile', $uid);
                if(!$tel['mobile']) {
                    $this->error('请先绑定手机号再解绑微信！');
                }
                $res = M('sync_login')->where(array('uid' => $uid))->delete();
//                $res = M('sync_login')->where(array('uid' => $uid))->setField('status', -1);
                if($res) {
                    $this->success('解绑成功~，即将刷新页面');
                } else {
                    $this->error('解绑失败！');
                }
            }

            if($mobile == 1) {
                $res = UCenterMember()->where(array('id' => $uid))->setField('mobile', null);
                clean_query_user_cache($uid, 'mobile');
                if($res) {
                    $this->success('解绑成功~，即将刷新页面');
                } else {
                    $this->error('解绑失败！');
                }
            }

        } else {
            $is_sync = M('sync_login')->where(array('uid' => $uid, 'status' => 1))->find();
            if($is_sync) {
                $user = query_user(array('nickname', 'avatar512'), $uid);
                $this->assign('user_info', $user);
            }

            $this->assign('is_sync', $is_sync);

            //手机号
            $is_mobile = query_user('mobile', $uid);
            $this->assign('is_mobile', $is_mobile['mobile']);
        }
        $this->display();
    }

    public function checkVerify()
    {
        $aAccount = I('post.account', '', 'text');
        $aVerify = I('post.verify', '', 'text');
        $aUid = is_login();
        $aType = 'mobile';

        if (!is_login()) {
            $this->error('请先登录');
        }

        $res = D('Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if (!$res) {
            $this->error(L('_FAIL_VERIFY_'));
        }
        UCenterMember()->where(array('id' => $aUid))->save(array($aType => $aAccount));
        $this->success(L('_SUCCESS_VERIFY_'), U('Ucenter/Index/safe'));
    }
    //账号与安全 end

    //笔记
    public function note(){
        $this->setTitle('笔记');
        $this->setKeywords("笔记");
        $this->setDescription("笔记");
        $aUid = I('get.uid', 0, 'intval');
        if (!$aUid) {
            redirect(U('Mall/index/index'));
        }
        $note=D('Note')->getNoteByUid($aUid);
        $goods=array();
        $noteGoodsId=D('note')->getNoteGoodsId($aUid);
        foreach ($noteGoodsId as $key=>$vo){
            $goods[$key]=D('Mall/Goods')->getGoods($vo);
            $goods[$key]['pictures'] = explode(',', $goods[$key]['pictures']);
            $goods[$key]['img'] = getThumbImageById($goods[$key]['pictures'][0],120,160);
            $goods[$key]['time_age']=D('Note')->getFriendlyData($aUid,$vo);
            $goods[$key]['count']=D('Note')->getNoteCount($aUid,$vo);
        }
        unset($key,$vo);
        $this->assign('note',$note);
        $this->assign('goods',$goods);
        $this->display();
    }
    public function notelist(){
        $this->setTitle('笔记');
        $this->setKeywords("笔记");
        $this->setDescription("笔记");
        $goodsId=I('get.id', 0, 'intval');
        $uid=is_login();
        $goods[0]=D('Mall/Goods')->getGoods($goodsId);
        $goods[0]['pictures'] = explode(',', $goods[0]['pictures']);
        $goods[0]['img'] = getThumbImageById($goods[0]['pictures'][0],120,160);
        $goods[0]['time_age']=D('Note')->getFriendlyData($uid,$goodsId);
        $goods[0]['count']=D('Note')->getNoteCount($uid,$goodsId);
        $note=D('Note')->getNoteByGoods($uid,$goodsId);
        $this->assign('goods',$goods);
        $this->assign('note',$note);
        $this->display();
    }

    //我的订单
    public function order(){
        $this->setTitle("我的订单");
        $this->setKeywords("我的订单");
        $this->setDescription("我的订单");
        $uid=is_login();
        if (!is_login()) {
            $this->error('请您先登录', U('Ucenter/member/login'), 1);
        }
        $countAll=D('order_goods')->where(array('uid'=>$uid))->count();
        $countPayment=D('order_goods')->where(array('uid'=>$uid,'is_pay'=>0))->count();
        $countComplete=D('order_goods')->where(array('uid'=>$uid,'is_pay'=>1))->count();
        $this->assign('countAll',$countAll);
        $this->assign('countPayment',$countPayment);
        $this->assign('countComplete',$countComplete);
        $this->display();
    }
    public function addListOrder(){
        $condition=I('post.data',0,'intval');
        $page=I('post.page',1,'intval');
        $order=D('Order')->getOrderByScreen($condition,$page);
        if($order){
            $this->assign('order',$order);
            $html=$this->fetch('_order');
            $arr=array('data'=>$html,'status'=>1,'info'=>'获取成功');
        }else{
            $arr=array('data'=>'','status'=>1,'info'=>'获取失败');
        }
        $this->ajaxReturn($arr);
    }
    //关于
    public function about(){
        $this->setTitle("关于");
        $this->setKeywords("关于");
        $this->setDescription("关于");
        $about=D('about')->where(array('id'=>1))->find();
        $this->assign('about',$about);
        $this->display();
    }

    public function information($uid = null)
    {
        //调用API获取基本信息
        //TODO tox 获取省市区数据
        $user = query_user(array('nickname', 'signature', 'email', 'mobile', 'rank_link', 'sex', 'pos_province', 'pos_city', 'pos_district', 'pos_community'), $uid);
        if ($user['pos_province'] != 0) {
            $user['pos_province'] = D('district')->where(array('id' => $user['pos_province']))->getField('name');
            $user['pos_city'] = D('district')->where(array('id' => $user['pos_city']))->getField('name');
            $user['pos_district'] = D('district')->where(array('id' => $user['pos_district']))->getField('name');
            $user['pos_community'] = D('district')->where(array('id' => $user['pos_community']))->getField('name');
        }
        //显示页面
        $this->assign('user', $user);
        $this->getExpandInfo($uid);
        //四处一词 seo
        $str = '{$user_info.nickname|text}';
        $this->setTitle($str . L('_INFO_TITLE_'));
        $this->setKeywords($str . L('_INFO_KEYWORDS_'));
        $this->setDescription($str . L('_INFO_DESC_'));
        //四处一词 seo end

        $this->display();
    }

    /**获取用户扩展信息
     * @param null $uid
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function getExpandInfo($uid = null, $profile_group_id = null)
    {
        $profile_group_list = $this->_profile_group_list($uid);
        foreach ($profile_group_list as &$val) {
            $val['info_list'] = $this->_info_list($val['id'], $uid);
        }
        $this->assign('profile_group_list', $profile_group_list);
    }

    /**扩展信息分组列表获取
     * @param null $uid
     * @return mixed
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function _profile_group_list($uid = null)
    {

        $profile_group_list = array();
        $fields_list = $this->getRoleFieldIds($uid);
        if ($fields_list) {
            $fields_group_ids = D('FieldSetting')->where(array('id' => array('in', $fields_list), 'status' => '1'))->field('profile_group_id')->select();
            if ($fields_group_ids) {
                $fields_group_ids = array_unique(array_column($fields_group_ids, 'profile_group_id'));
                $map['id'] = array('in', $fields_group_ids);

                if (isset($uid) && $uid != is_login()) {
                    $map['visiable'] = 1;
                }
                $map['status'] = 1;
                $profile_group_list = D('field_group')->where($map)->order('sort asc')->select();
            }
        }
        return $profile_group_list;
    }

    private function getRoleFieldIds($uid = null)
    {
        $roleid = M('member')->where('uid=' . $uid)->field('show_role')->select();
        $role_id = $roleid[0]['show_role'];
        $fields_list = S('Role_Expend_Info_' . $role_id);
        if (!$fields_list) {
            $map_role_config = getRoleConfigMap('expend_field', $role_id);
            $fields_list = D('RoleConfig')->where($map_role_config)->getField('value');
            if ($fields_list) {
                $fields_list = explode(',', $fields_list);
                S('Role_Expend_Info_' . $role_id, $fields_list, 600);
            }
        }
        return $fields_list;
    }
}
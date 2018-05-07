<?php
namespace Weixin\Controller;

use Weixin\Sdk\WechatAuth;

class IndexController extends BaseController
{


    /**
     * 微信消息接口入口
     * 所有发送到微信的消息都会推送到该操作
     * 所以，微信公众平台后台填写的api地址则为该操作的访问地址
     */
    public function index($id = '')
    {

    }

    public function news()
    {
        $id = I('get.id');
        if (!intval($id)) {
            exit('not found');
        }
        $info = $this->getAreplyModel()->find($id);
        $this->assign('info', $info);
        $this->wdisplay('news');
    }

    protected function wdisplay($tpl)
    {
        $this->display(T('Weixin@Index/' . $tpl));
    }

    /*
     * 获取微信头像
     */
    /*function getCurl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close ($ch);
        return $result;
    }*/

    public function callback()
    {

        $code = I('get.code', '', 'text');
        $config = D('Weixin/WeixinConfig')->getWeixinConfig();
        $wechat = new WechatAuth($config['APP_ID'], $config['APP_SECRET']);
        /* 获取请求信息 */
        $token = $wechat->getAccessToken('code', $code);
        $userinfo = $wechat->getUserInfo($token);

        $openid = !empty($userinfo['unionid']) ? $userinfo['unionid'] : $userinfo['openid'];
        session('weixin_token',array('access_token'=>$token['access_token'],'openid'=>$openid,'openid_public'=>$userinfo['openid']));

        $map = array('type_uid' => $openid, 'type' => 'weixin');
        $uid = D('sync_login')->where($map)->getField('uid');

        if (empty($uid)) {
            if(D('Common/Module')->checkInstalled('Weixin')) {
                redirect(U('Ucenter/member/weixin_bind'));
            }
            $user_info = $this->weixin($userinfo);
            $uid = $this->addData($user_info);
        }
        $res = D('Member')->login($uid, true); //登陆
        if ($res) {
            redirect(U('mall/index/index'));
        } else {
            $this->error('微信登录失败~');
        }
    }

    public function mCallback()
    {
        $code = I('get.code', '', 'text');
        $config = D('Weixin/WeixinConfig')->getWeixinConfig();
        $wechat = new WechatAuth($config['APP_ID'], $config['APP_SECRET']);
        /* 获取请求信息 */
        $token = $wechat->getAccessToken('code', $code);
        $userinfo = $wechat->getUserInfo($token);

        $openid = !empty($userinfo['unionid']) ? $userinfo['unionid'] : $userinfo['openid'];
        session('weixin_token',array('access_token'=>$token['access_token'],'openid'=>$openid,'openid_public'=>$userinfo['openid']));
        if(M('sync_login')->where(array('type_uid' => $openid, 'status' => 1))->count()) {
            $this->error('您的微信已经绑定了其他账号，请先解绑再来绑定~', U('Ucenter/Index/safe'), 10);
        }
        $uid = is_login();
        $res = $this->addSyncLoginData($uid);
        if($res) {
            redirect(U('Ucenter/Index/safe'));
        }
    }

    private  function weixin($data){
        if($data['ret'] == 0){
            $userInfo['type'] = 'WEIXIN';
            $userInfo['name'] = $data['nickname'];
            $userInfo['nick'] = $data['nickname'];
            $userInfo['head'] = $data['headimgurl'];
            $userInfo['sex'] = $data['sex']=='1'? 0:1;
            return $userInfo;
        } else {
            return("获取微信用户信息失败：{$data['errmsg']}");
        }
    }


    private function addData($user_info)
    {
        $ucenterModer = UCenterMember();
        $uid = $ucenterModer->addSyncData();
        D('Member')->addSyncData($uid, $user_info);
        $ucenterModer->initRoleUser(1, $uid); //初始化角色用户
        // 记录数据到sync_login表中
        $this->addSyncLoginData($uid);
        $this->saveAvatar($user_info['head'], $uid);
        return $uid;
    }

    /**
     * addSyncLoginData  增加sync_login表中数据
     * @param $uid
     * @return mixed
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    private function addSyncLoginData($uid)
    {
        $session = session('weixin_token');
        $data['uid'] = $uid;
        $data['type_uid'] =$session['openid'];
        $data['oauth_token'] = $session['access_token'];
        $data['oauth_token_secret'] =$session['openid'];
        $data['open_id'] = $session['openid_public'];
        $data['type'] = 'weixin';
        $data['open_id'] = $session['openid_public'];
        $syncModel = D('sync_login');

        if (!($syncModel->where($data)->count())) {
            $syncModel->add($data);
        }
        return true;
    }

    private function saveAvatar($url, $uid)
    {
        $driver = modC('PICTURE_UPLOAD_DRIVER', 'local', 'config');
//        $url = str_replace('http', 'https', $url);
        if ($driver == 'local') {
            mkdir('./Uploads/Avatar/' . $uid, 0777, true);
            $img = file_get_contents($url);
            $filename = './Uploads/Avatar/' . $uid . '/crop.jpg';
            file_put_contents($filename, $img);
            $data['path'] = '/' . $uid . '/crop.jpg';
        } else {
            $name = get_addon_class($driver);
            $class = new $name();
            $path = '/Uploads/Avatar/' . $uid . '/crop.jpg';
            $res = $class->uploadRemote($url, 'Uploads/Avatar/' . $uid . '/crop.jpg');
            if ($res !== false) {
                $data['path'] = $res;
            }
        }
        $data['uid'] = $uid;
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['is_temp'] = 0;
        $data['driver'] = $driver;
        D('avatar')->add($data);
    }


    public function existLogin()
    {
        $aUsername = I('post.username', '', 'text');
        $aPassword = I('post.password', '', 'text');

        $uid = UCenterMember()->login($aUsername, $aPassword, 3);
        if (0 < $uid) { //UC登陆成功
            /* 登陆用户 */
            $Member = D('Member');
            if ($Member->login($uid, true)) { //登陆用户
                $this->addSyncLoginData($uid);
                $this->ajaxReturn(array('status'=>1));
            } else {
                $this->error($Member->getError());
            }

        } else { //登陆失败
            switch ($uid) {
                case -1:
                    $error = '用户不存在或被禁用！';
                    break; //系统级别禁用
                case -2:
                    $error = '密码错误！';
                    break;
                default:
                    $error = '未知错误27！';
                    break; // 0-接口参数错误（调试阶段使用）
            }
            $this->error($error);
        }
    }


    public function newAccount()
    {
        $aUsername = I('post.username', '', 'text');
        $aNickname = I('post.nickname', '', 'text');
        $aPassword = I('post.password', '', 'text');

        if (empty($aUsername)) {
            $this->error('请输入账号');
        }

        $isRegister = M('ucenter_member')->where(array('mobile' => $aUsername))->count();
        if ($isRegister) {
            $this->error('该号码已被注册');
        }

        preg_match("/^(1[3|4|5|7|8])[0-9]{9}$/", $aUsername, $match_mobile);
        if (!$match_mobile) {
            $this->error('请输入正确的手机号码');
        } else {
            $mobile = $aUsername;
            $username = '';
            $email = '';
            $aUnType = 3;
        }

        $this->checkNickname($aNickname);

        $return = check_action_limit('reg', 'ucenter_member', 1, 1, true);
        if ($return && !$return['state']) {
            $this->error($return['info'], $return['url']);
        }

        if (empty($aPassword)) {
            $this->error('请输入密码');
        }
        if (strlen($aPassword) < 6 || strlen($aPassword) > 32) {
            $this->error('密码长度在6-32位之间');
        }

        $token = session('weixin_token');
        $config = D('Weixin/WeixinConfig')->getWeixinConfig();
        $wechat = new WechatAuth($config['APP_ID'], $config['APP_SECRET']);
        $userinfo = $wechat->getUserInfo($token);
        $user_info = $this->weixin($userinfo);

        $ucenterModel = UCenterMember();
        $uid = $ucenterModel->register($username, $aNickname, $aPassword, $email, $mobile, $aUnType);
        if (0 < $uid) { //注册成功
            $this->addSyncLoginData($uid);
            $this->saveAvatar($user_info['head'], $uid);

            $config =  D('MAddons')->where(array('name'=>'SyncLogin'))->find();
            $config   =   json_decode($config['config'], true);

            $ucenterModel->initRoleUser($config['role'], $uid); //初始化角色用户

            $res = D('Member')->login($uid, true); //登陆
            if ($res) {
                if (empty($res['info'])) {
                    $this->ajaxReturn(array('status' => 1, 'info' => '注册成功！'));
                } else {
                    $this->ajaxReturn(array('status' => 1, 'info' => $res['info']));
                }
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '注册失败'));
            }
        } else { //注册失败，显示错误信息
//            $this->error(A('Ucenter/Member')->showRegError($uid));
        }

    }



    public function unBind()
    {
        $token = session('weixin_token');
        $config = D('Weixin/WeixinConfig')->getWeixinConfig();
        $wechat = new WechatAuth($config['APP_ID'], $config['APP_SECRET']);

        $userinfo = $wechat->getUserInfo($token);

        $user_info = $this->weixin($userinfo);
        $uid = $this->addData($user_info);
        $this->saveAvatar($user_info['head'], $uid);

        $res = D('Member')->login($uid, true); //登陆
        if($res) {
            redirect(U('Weibo/Index/index'));
        }

    }

    /**
     * checkNickname  验证昵称是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkNickname($aNickname = "")
    {
        if (empty($aNickname)) {
            $this->error(L('_EMPTY_CANNOT_') . L('_EXCLAMATION_'));
        }

        $length = mb_strlen($aNickname, 'utf-8'); // 当前数据长度
        if ($length < modC('NICKNAME_MIN_LENGTH', 2, 'USERCONFIG') || $length > modC('NICKNAME_MAX_LENGTH', 32, 'USERCONFIG')) {
            $this->error(L('_ERROR_NICKNAME_LENGTH_11_') . modC('NICKNAME_MIN_LENGTH', 2, 'USERCONFIG') . '-' . modC('NICKNAME_MAX_LENGTH', 32, 'USERCONFIG') . L('_ERROR_USERNAME_LENGTH_2_'));
        }

        $memberModel = D('member');
        $uid = $memberModel->where(array('nickname' => $aNickname))->getField('uid');
        if ($uid) {
            $this->error(L('_ERROR_NICKNAME_EXIST_'));
        }
        preg_match('/^(?!_|\s\')[A-Za-z0-9_\x80-\xff\s\']+$/', $aNickname, $result);
        if (!$result) {
            $this->error(L('_ERROR_NICKNAME_ONLY_PERMISSION_'));
        }
    }
}
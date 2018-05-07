<?php
namespace Ucenter\Controller;

use Think\Controller;

require_once APP_PATH . 'User/Conf/config.php';

class MemberController extends Controller
{
    /**
     * 登录主页
     */
    public function login()
    {

        if (IS_POST) {
            $result = A('Ucenter/Login', 'Widget')->doLogin();
            if ($result['status']) {
                $data['status'] = 1;
                $data['info'] = "登录成功！";
            } else {
                $data['status'] = 0;
                $data['info'] = $result['info'];
            }
            $this->ajaxReturn($data);
        } else {
            if (is_login()) {
                redirect(U('Mall/index/index'));
            } else {
                if(D('Common/Module')->checkInstalled('Weixin')){
                    //开启同步登录
                    $this->assign('can_wechat_login',true);
                }
                $this->setTitle('登陆');
                $this->display();
            }
        }
    }


    /**
     * 微信登录
     * @author:Andy(王杰) wj@ourstu.com
     */
    public function weChatLogin()
    {
        $this->setTitle('微信登录');
        if(!is_login()){
            if(is_weixin()){
                if(D('Common/Module')->checkInstalled('Weixin')){
                    $config = D('Weixin/WeixinConfig')->getWeixinConfig();
                    $redirect =urlencode(U('Weixin/Index/callback','',true,true));
                    $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$config['APP_ID']."&redirect_uri={$redirect}&response_type=code&scope=snsapi_userinfo&state=opensns#wechat_redirect";
                    redirect($url);
                    exit;
                }
            }else{
                $this->error('请在微信中打开',U('ucenter/member/login'),3);
            }
        }
    }

    public function weChatBind()
    {
        if(is_weixin()){
            if(D('Common/Module')->checkInstalled('Weixin')){
                $config = D('Weixin/WeixinConfig')->getWeixinConfig();
                $redirect =urlencode(U('Weixin/Index/mCallback','',true,true));
                $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$config['APP_ID']."&redirect_uri={$redirect}&response_type=code&scope=snsapi_userinfo&state=opensns#wechat_redirect";
                redirect($url);
                exit;
            }
        }
    }

    /**
     * 注册页
     */
    public function register()
    {
        $this->setTitle('注册');
        $aUsername = $username = I('post.mobnumber', '', 'op_t');
        $aNickname = I('post.mnickname', '', 'op_t');
        $aPassword = I('post.mpassword', '', 'op_t');

        $aRegVerify = I('post.reg_verify', 0, 'intval');
        $this->setTopTitle('欢迎注册');
        //用户协议
        $protocol=D('protocol')->where(array('id'=>1))->find();
        $this->assign('protocol',$protocol);
        if (!modC('REG_SWITCH', '', 'USERCONFIG')) {
            $this->error('注册已关闭');
        }
        if (IS_POST) {
            if (empty($aUsername)) {
                $this->error('请输入账号');
            }

            $isRegister = M('ucenter_member')->where(array('mobile' => $aUsername))->count();
            if ($isRegister) {
                $this->error('该号码已被注册');
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
            $aUnType = 3;
            preg_match("/^(1[3|4|5|7|8])[0-9]{9}$/", $aUsername, $match_mobile);
            if (!$match_mobile) {
                $this->error('请输入正确的手机号码');
            } else {
                $mobile = $username;
                $username = '';
                $email = '';
                $type = 3;
            }
            if(modC('MOBILE_VERIFY_TYPE', 1, 'USERCONFIG')){
            if (modC('SMS_REGISTER_RADIO',1,'USERCONFIG')) {
                if (empty($aRegVerify)) {
                    $this->error('请输入短信验证码');
                }

                if (!D('Verify')->checkVerify($aUsername, 'mobile', $aRegVerify, 0)) {
                    $this->error('手机短信验证失败');
                }
            }
            }

            /* 注册用户 */
            $uid = UCenterMember()->register('', $aNickname, $aPassword, $email, $mobile, $aUnType);
            if (0 < $uid) {
                $this->_initRoleUser(1, $uid); //初始化角色用户
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
                $this->ajaxReturn(array('status' => 0, 'info' => '昵称含有敏感词汇，请更换昵称后注册'));
            }
        } else { //显示注册表单
            if (is_login()) {
                redirect(U('Weibo/index/index'));
            }
            $this->display();
        }
    }

    /**
     * 密码找回页
     */
    public function resetPassword()
    {
        $this->setTitle('密码找回');
        $aEmail = $email = I('post.email', '', 'string');
        $aMobile = $mobile = I('post.mobile', '', 'string');
        $aVerify = $verify = I('post.verify', '', 'string');
        $aMobVerify = $mobverify = I('post.reg_verify', '', 'op_t');//获取手机验证码

        $email = strval($email);
        if (IS_POST) { //登录验证
            if ($aEmail) {
                //根据用户名获取用户UID
                $user = UCenterMember()->where(array('email' => $email, 'status' => 1))->find();
                $uid = $user['id'];
                if (!$uid) {
                    $this->ajaxReturn(array('status' => 0, 'info' => '用户名或邮箱错误'));
                }
                if (!check_verify($verify)) {
                    $this->error('验证码输入错误');
                }
                //生成找回密码的验证码
                $verify = $this->getResetPasswordVerifyCode($uid);

                //发送验证邮箱
                $url = 'http://' . $_SERVER['HTTP_HOST'] . U('Ucenter/member/reset?uid=' . $uid . '&verify=' . $verify);
                $content = C('USER_RESPASS') . "<br/>" . $url . "<br/>" . modC('WEB_SITE_NAME', 'OpenSNS开源社交系统', 'Config') . "系统自动发送--请勿直接回复<br/>" . date('Y-m-d H:i:s', TIME()) . "</p>";
                send_mail($email, modC('WEB_SITE_NAME', 'OpenSNS开源社交系统', 'Config') . "密码找回", $content);

                $this->ajaxReturn(array('status' => 1, 'info' => '密码找回邮件已发送，请耐心等待！'));
            } else {
                $isVerify = D('Verify')->checkVerify($aMobile,'mobile', $aMobVerify, 0);
                if ($isVerify) {
                    $user = UCenterMember()->where(array('mobile' => $aMobile, 'status' => 1))->find();
                    if (empty($user)) {
                        $this->ajaxReturn(array('status' => 0, 'info' => '该用户不存在！'));
                    }
                    /*重置密码操作*/
                    $ucModel = UCenterMember();
                    $res = $ucModel->where(array('id' => $user['id'], 'status' => 1))->save(array('password' => think_ucenter_md5('123456', UC_AUTH_KEY)));
                    if ($res) {
                        $this->ajaxReturn(array('status' => 1, 'info' => '密码重置成功！新密码是“123456”'));
                    } else {
                        $this->ajaxReturn(array('status' => 0, 'info' => '密码重置失败！可能密码重置前就是“123456”。'));
                    }
                } else {
                    $this->ajaxReturn(array('status' => 0, 'info' => '验证码或手机号码错误！'));
                }
            }
        } else {
            $this->_top_menu_list = array(
                'left' => array(
                    array('type' => 'back'),
                ),
                'center' => array('title' => '密码找回')
            );
            $this->assign('top_menu_list', $this->_top_menu_list);
            $this->display();
        }
    }

    public function step()
    {
        $aStep = I('get.step', '', 'op_t');
        $aUid = session('temp_login_uid');
        $aRoleId = session('temp_login_role_id');
        if (empty($aUid)) {
            $this->error('参数错误');
        }
        $userRoleModel = D('UserRole');
        $map['uid'] = $aUid;
        $map['role_id'] = $aRoleId;
        $step = $userRoleModel->where($map)->getField('step');
        if (get_next_step($step) != $aStep) {
            $aStep = check_step($step);
            $_GET['step'] = $aStep;
            $userRoleModel->where($map)->setField('step', $aStep);
        }
        $userRoleModel->where($map)->setField('step', $aStep);
        if ($aStep == 'finish') {
            D('Member')->login($aUid, false, $aRoleId);
        }
        $this->assign('step', $aStep);
        $this->display('register');
    }

    /* 快捷登录登录页面 */
    public function quickLogin()
    {
        if (IS_POST) {
            $result = A('Mob/Login', 'Widget')->doLogin();
            $this->ajaxReturn($result);
        } else { //显示登录弹出框
            $this->display();
        }
    }

    /* 退出登录 */
    public function logout()
    {
        if (is_login()) {
            D('Member')->logout();
            $data['status'] = 1;

        } else {
            $data['status'] = 0;
            $data['info'] = "退出失败！";
        }
        $this->ajaxReturn($data);
    }

    /* 验证码，用于登录和注册 */
    public function verify($id = 1)
    {
        verify($id);
    }


    /**
     * 重置密码
     */
    public function reset($uid, $verify)
    {
        //检查参数
        $uid = intval($uid);
        $verify = strval($verify);
        if (!$uid || !$verify) {
            $this->error("参数错误");
        }

        //确认邮箱验证码正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error("参数错误");
        }

        //将邮箱验证码储存在SESSION
        session('reset_password_uid', $uid);
        session('reset_password_verify', $verify);

        //显示新密码页面
        $this->display();
    }

    public function doReset($password, $repassword)
    {
        //确认两次输入的密码正确
        if ($password != $repassword) {
            $this->error('两次输入的密码不一致');
        }

        //读取SESSION中的验证信息
        $uid = session('reset_password_uid');
        $verify = session('reset_password_verify');

        //确认验证信息正确
        $expectVerify = $this->getResetPasswordVerifyCode($uid);
        if ($expectVerify != $verify) {
            $this->error("验证信息无效");
        }

        //将新的密码写入数据库
        $data = array('id' => $uid, 'password' => $password);
        $model = UCenterMember();
        $data = $model->create($data);
        if (!$data) {
            $this->error('密码格式不正确');
        }
        $result = $model->where(array('id' => $uid))->save($data);
        if ($result === false) {
            $this->error('数据库写入错误');
        }

        //显示成功消息
        $this->success('密码重置成功', U('Ucenter/Member/login'));
    }

    private function getResetPasswordVerifyCode($uid)
    {
        $user = UCenterMember()->where(array('id' => $uid))->find();
        $clear = implode('|', array($user['uid'], $user['username'], $user['last_login_time'], $user['password']));
        $verify = thinkox_hash($clear, UC_AUTH_KEY);
        return $verify;
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    public function showRegError($code = 0)
    {
        switch ($code) {
            case -1:
                $error = '用户名长度必须在' . modC('USERNAME_MIN_LENGTH', 2, 'USERCONFIG') . '-' . modC('USERNAME_MAX_LENGTH', 32, 'USERCONFIG') . '个字符之间！';
                break;
            case -2:
                $error = '用户名被禁止注册！';
                break;
            case -3:
                $error = '用户名被占用！';
                break;
            case -4:
                $error = '密码长度必须在6-30个字符之间！';
                break;
            case -5:
                $error = '邮箱格式不正确！';
                break;
            case -6:
                $error = '邮箱长度必须在4-32个字符之间！';
                break;
            case -7:
                $error = '邮箱被禁止注册！';
                break;
            case -8:
                $error = '邮箱被占用！';
                break;
            case -9:
                $error = '手机格式不正确！';
                break;
            case -10:
                $error = '手机被禁止注册！';
                break;
            case -11:
                $error = '手机号被占用！';
                break;
            case -20:
                $error = '用户名只能由数字、字母和"_"组成！';
                break;
            case -30:
                $error = '昵称被占用！';
                break;
            case -31:
                $error = '昵称被禁止注册！';
                break;
            case -32:
                $error = '昵称只能由数字、字母、汉字和"_"组成！';
                break;
            case -33:
                $error = '昵称长度必须在' . modC('NICKNAME_MIN_LENGTH', 2, 'USERCONFIG') . '-' . modC('NICKNAME_MAX_LENGTH', 32, 'USERCONFIG') . '个字符之间！';
                break;
            default:
                $error = '未知错误24';
        }
        return $error;
    }


    /**
     * doSendVerify  发送验证码
     * @param $account
     * @param $verify
     * @param $type
     * @return bool|string
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doSendVerify($account, $verify, $type)
    {
        switch ($type) {
            case 'mobile':
                //$content = modC('SMS_CONTENT', '{$verify}', 'USERCONFIG');
                $content=<<<str
您的验证码为{$verify}，切勿泄露给他人，有效期为五分钟。【OpenSNS官方社群】
str;
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = sendSMS($account, $content);
                return $res;
                break;
            case 'email':
                //发送验证邮箱
                $content = modC('REG_EMAIL_VERIFY', '{$verify}', 'USERCONFIG');
                $content = str_replace('{$verify}', $verify, $content);
                $content = str_replace('{$account}', $account, $content);
                $res = send_mail($account, modC('WEB_SITE_NAME', 'OpenSNS开源社交系统', 'Config') . '邮箱验证', $content);
                return $res;
                break;
        }

    }

    /**
     * saveAvatar  保存头像
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function saveAvatar()
    {
        $aCrop = I('post.crop', '', 'op_t');
        $aUid = session('temp_login_uid') ? session('temp_login_uid') : is_login();
        $aExt = I('post.ext', '', 'op_t');
        if (empty($aCrop)) {
            $this->success('保存成功！', session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('change_avatar'))) : 'refresh');
        }
        $dir = './Uploads/Avatar/' . $aUid;
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != ".." && $file != 'original.' . $aExt) {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    deldir($fullpath);
                }
            }
        }
        closedir($dh);
        A('Ucenter/UploadAvatar', 'Widget')->cropPicture($aUid, $aCrop, $aExt);
        $res = M('avatar')->where(array('uid' => $aUid))->save(array('uid' => $aUid, 'status' => 1, 'is_temp' => 0, 'path' => "/" . $aUid . "/crop." . $aExt, 'create_time' => time()));
        if (!$res) {
            M('avatar')->add(array('uid' => $aUid, 'status' => 1, 'is_temp' => 0, 'path' => "/" . $aUid . "/crop." . $aExt, 'create_time' => time()));
        }
        clean_query_user_cache($aUid, array('avatar256', 'avatar128', 'avatar64'));
        $this->success('头像更新成功！', session('temp_login_uid') ? U('Ucenter/member/step', array('step' => get_next_step('change_avatar'))) : 'refresh');

    }

    /**
     * doActivate  激活步骤
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function doActivate()
    {
        $aAccount = I('get.account', '', 'op_t');
        $aVerify = I('get.verify', '', 'op_t');
        $aType = I('get.type', '', 'op_t');
        $aUid = I('get.uid', 0, 'intval');
        $check = D('Verify')->checkVerify($aAccount, $aType, $aVerify, $aUid);
        if ($check) {
            set_user_status($aUid, 1);
            $this->success('激活成功', U('Ucenter/member/step', array('step' => get_next_step('start'))));
        } else {
            $this->error('激活失败！');
        }
    }

    /**
     * checkAccount  ajax验证用户帐号是否符合要求
     * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
     */
    public function checkAccount()
    {
        $aAccount = I('post.account', '', 'op_t');
        $aType = I('post.type', '', 'op_t');
        if (empty($aAccount)) {
            $this->error('不能为空！');
        }
        check_username($aAccount, $email, $mobile, $aUnType);
        $mUcenter = UCenterMember();
        switch ($aType) {
            case 'username':
                empty($aAccount) && $this->error('用户名格式不正确！');
                $length = mb_strlen($aAccount, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 30) {
                    $this->error('用户名长度在4-30之间');
                }
                $id = $mUcenter->where(array('username' => $aAccount))->getField('id');
                if ($id) {
                    $this->error('该用户名已经存在！');
                }
                preg_match("/^[a-zA-Z0-9_]{1,30}$/", $aAccount, $result);
                if (!$result) {
                    $this->error('只允许字母和数字和下划线！');
                }
                break;
            case 'email':
                empty($email) && $this->error('邮箱格式不正确！');
                $length = mb_strlen($email, 'utf-8'); // 当前数据长度
                if ($length < 4 || $length > 32) {
                    $this->error('邮箱长度在4-32之间');
                }

                $id = $mUcenter->where(array('email' => $email))->getField('id');
                if ($id) {
                    $this->error('该邮箱已经存在！');
                }
                break;
            case 'mobile':
                empty($mobile) && $this->error('手机格式不正确！');
                $id = $mUcenter->where(array('mobile' => $mobile))->getField('id');
                if ($id) {
                    $this->error('该手机号已经存在！');
                }
                break;
        }
        $this->success('验证成功');
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

    /**
     * 切换登录身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function changeLoginRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                clean_query_user_cache($uid, array('avatar64', 'avatar128', 'avatar32', 'avatar256', 'avatar512', 'rank_link'));
                $result = $memberModel->login($uid, false, $aRoleId);
                if ($result) {
                    $data['info'] = "身份切换成功！";
                    $data['status'] = 1;
                }
            }
        }
        $data['info'] = "非法操作！";
        $this->ajaxReturn($data);
    }

    /**
     * 持有新身份
     * @author 郑钟良<zzl@ourstu.com>
     */
    public function registerRole()
    {
        $aRoleId = I('post.role_id', 0, 'intval');
        $uid = is_login();
        $data['status'] = 0;
        if ($uid > 0 && $aRoleId != get_login_role()) {
            $roleUser = D('UserRole')->where(array('uid' => $uid, 'role_id' => $aRoleId))->find();
            if ($roleUser) {
                $data['info'] = "已持有该身份！";
                $this->ajaxReturn($data);
            } else {
                $memberModel = D('Common/Member');
                $memberModel->logout();
                $this->initRoleUser($aRoleId, $uid);
                clean_query_user_cache($uid, array('avatar64', 'avatar128', 'avatar32', 'avatar256', 'avatar512', 'rank_link'));
                $memberModel->login($uid, false, $aRoleId); //登陆
            }
        } else {
            $data['info'] = "非法操作！";
            $this->ajaxReturn($data);
        }
    }

    private function _initRoleUser($role_id = 1, $uid)
    {
        $memberModel = D('Member');
        $user_role = array('uid' => $uid, 'role_id' => $role_id, 'step' => "finish");
        $user_role['status'] = 1;
        $result = D('UserRole')->add($user_role);
        $memberModel->initUserRoleInfo($role_id, $uid);
        return $result;
    }

    public function bind()
    {

        $session = session('SYNCLOGIN');
        if (!$session['TOKEN']) {
            $this->error('无效的token');
        }
        $tip = I('get.tip');
        $tip == '' && $tip = 'new';
        $this->assign('tip', $tip);
        $this->display();
    }


    public function weixin_bind()
    {
        $this->setTitle('微信登录');
        $session = session('weixin_token');
        if (!$session) {
            $this->error('无效的token');
        }

        //用户协议
        $protocol=D('protocol')->where(array('id'=>1))->find();
        $this->assign('protocol',$protocol);
        $tip = I('get.tip');
        $tip == '' && $tip = 'new';
        $this->assign('tip', $tip);
        $this->display();
    }

    //checkuid
    private function checkUid($aUid)
    {
        if ($aUid != is_login()) {
            $data['status'] = 0;
            $data['info'] = '无法修改';
            $this->ajaxReturn($data);
        } else {
            return true;
        }
    }

    //修改nickname sex sign birth
    public function changeUserData()
    {
        $aUid = I('post.uid', 0, 'intval');
        $aType = I('post.type', '', 'text');
        $aValue = I('post.value', '', 'text');
        $this->checkUid($aUid);
        switch ($aType) {
            case 'sex':
                switch ($aValue) {
                    case '男':
                        $value = 1;
                        break;
                    case '女':
                        $value = 2;
                        break;
                    default:
                        $value = 0;
                }
                break;
            default :
                $value = $aValue;
        }

        $rs = D('Member')->changeUserData($aType, $aUid, $value);
        if ($rs) {
            $data['status'] = 1;
            $data['info'] = '修改成功';
        } else {
            $data['status'] = 0;
            $data['info'] = '修改失败';
        }
        $this->ajaxReturn($data);

    }

    //修改地区
    public function changePos()
    {
        $aUid = I('post.uid', 0, 'intval');
        $aPos = I('post.pos', '', 'text');
        $this->checkUid($aUid);
        $return = $aPos;
        $aPos = explode(' ', $aPos);
        $rs = D('Member')->changePos($aUid, $aPos);
        if ($rs) {
            $data['status'] = 1;
            $data['info'] = '修改成功';
            $data['value'] = $return;
        } else {
            $data['status'] = 0;
            $data['info'] = '修改失败';
        }
        $this->ajaxReturn($data);
    }

    //修改手机
    public function changePhone()
    {
        $aUid = I('post.uid', 0, 'intval');
        $aValue = I('post.value', '', 'text');
        $this->checkUid($aUid);
        $rs = D('User/UcenterMember')->changePhone($aUid, $aValue);
        if ($rs) {
            $data['status'] = 1;
            $data['info'] = '修改成功';
            $data['value'] = $aValue;
        } else {
            $data['status'] = 0;
            $data['info'] = '修改失败';
        }
        $this->ajaxReturn($data);
    }

}
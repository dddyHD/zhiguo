<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PublicController extends \Think\Controller
{

    /**
     * 后台用户登录
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function login($username = null, $password = null, $verify = null)
    {
        header('Access-Control-Allow-Origin:http://pc.opensns.cn');
        if (IS_POST) {
            /* 检测验证码 TODO: */
            if (APP_DEBUG == false) {
                if (!check_verify($verify)) {
                    $this->error(L('_VERIFICATION_CODE_INPUT_ERROR_'));
                }
            }


            /* 调用UC登录接口登录 */
            $uid = UCenterMember()->login($username, $password);
            if (0 < $uid) { //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if ($Member->login($uid)) { //登录用户
                    //TODO:跳转到登录前页面
                    $this->success(L('_LOGIN_SUCCESS_'), U('Index/index'));
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch ($uid) {
                    case -1:
                        $error = L('_USERS_DO_NOT_EXIST_OR_ARE_DISABLED_');
                        break; //系统级别禁用
                    case -2:
                        $error = L('_PASSWORD_ERROR_');
                        break;
                    default:
                        $error = L('_UNKNOWN_ERROR_');
                        break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if (is_login()) {
                $this->redirect('Index/index');
            } else {
                /* 读取数据库中的配置 */
                $config = S('DB_CONFIG_DATA');
                if (!$config) {
                    $config = D('Config')->lists();
                    S('DB_CONFIG_DATA', $config);
                }
                C($config); //添加配置

                $this->display();
            }
        }
    }

    /* 退出登录 */
    public function logout()
    {
        if (is_login()) {
            D('Member')->logout();
            session('[destroy]');
            $this->success(L('_EXIT_SUCCESS_'), U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify()
    {
        verify();
        // $verify = new \Think\Verify();
        // $verify->entry(1);
    }

    /**
     *pc管理平台登陆调用方法
     */
    public function cloudAccess()
    {
        $token=I('get.token','','string');
        $timestrap=I('get.timestrap','','string');
        if ($token!=null&&$timestrap!=null) {
            $app_name = trim(modC('CLOUD_APP_NAME', '', 'CAPI'));
            $app_secret = trim(modC('CLOUD_APP_SECRET', '', 'CAPI'));
            $digest = hash_hmac('sha1', $app_name . ':' . $timestrap, $app_secret, true);
            $token_enc = md5($app_name . ':' . $digest);
            if ($token!=$token_enc){
                $this->error('非法登陆！',U('login'));
            }elseif (time()-$timestrap>10){
                $this->error('token已过期,请手动登陆！',U('login'));
            }
            else{
                $uid = 1;
                $Member = D('Admin/Member');
                if ($Member->login($uid)) { //登录用户
                    $this->redirect('index/index');
                }
            }

        } else {
            $this->redirect('login');
        }
    }
}
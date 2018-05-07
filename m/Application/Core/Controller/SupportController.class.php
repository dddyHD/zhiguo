<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2016/12/14
 * Time: 16:10
 */
namespace Core\Controller;

use Think\Controller;

class SupportController extends Controller
{
    public function doSupport()
    {
        if (!is_login()) {
            $this->error("请登陆后再点赞。");
        }
        $appname = I('POST.appname','','text');
        $table = I('POST.table','','text');
        $row = I('POST.row','','intval');
        $aJump = I('POST.jump','Weibo/Index/detail','text');

        $message_uid = intval(I('POST.uid')) ? intval(I('POST.uid')) : is_login();
        $support['appname'] = $appname;
        $support['table'] = $table;
        $support['row'] = $row;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {

            $this->error("您已经赞过，不能再赞了。");
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {

                S('weibo_count_by_'.$row,null);

                $user = query_user(array('nickname'),get_uid());
                send_message($message_uid,$title = $user['nickname'] . '赞了您', '快去看看吧^……^！',  $aJump , array('id' => $row),-1,'Ucenter');
                $this->success("感谢您的支持。");
            } else {
                $this->error("写入数据库失败。");
            }

        }
    }

    public function doSupportAll()
    {
        if (!is_login()) {
            $this->error("请登陆后再点赞。");
        }
        $appname = I('POST.appname','','text');
        $table = I('POST.table','','text');
        $row = I('POST.row','','intval');

        $message_uid = intval(I('POST.uid')) ? intval(I('POST.uid')) : is_login();
        $support['appname'] = $appname;
        $support['table'] = $table;
        $support['row'] = $row;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {

            $this->error("您已经赞过，不能再赞了。");
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {

                S('weibo_count_by_'.$row,null);

                $user = query_user(array('nickname'),get_uid());
                send_message($message_uid,$title = $user['nickname'] . '赞了您', '快去看看吧^……^！', '' , array('id' => $row),-1,'Ucenter');
                $this->success("感谢您的支持。");
            } else {
                $this->error("写入数据库失败。");
            }

        }
    }

    public function doSupportQuestion()
    {
        if (!is_login()) {
            $this->error("请登陆后再点赞。");
        }
        $appname = I('POST.appname','','text');
        $table = I('POST.table','','text');
        $row = I('POST.row','','intval');

        $message_uid = intval(I('POST.uid')) ? intval(I('POST.uid')) : is_login();
        $support['appname'] = $appname;
        $support['table'] = $table;
        $support['row'] = $row;
        $support['uid'] = is_login();

        if (D('Support')->where($support)->count()) {

            $this->error("您已经赞过，不能再赞了。");
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {

                S('weibo_count_by_'.$row,null);

                $user = query_user(array('nickname'),get_uid());
                D('question_rank')->where(array('uid'=>$message_uid))->setInc('support_count');
                send_message($message_uid,$title = $user['nickname'] . '赞了您', '快去看看吧^……^！', '' , array('id' => $row),-1,'Ucenter');
                $this->success("感谢您的支持。");
            } else {
                $this->error("写入数据库失败。");
            }

        }
    }
}


<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        redirect(U('Weibo/Index/Index'));
    }
    public function outside(){
        $this->display();
    }

    public function url()
    {
        $aShort = I('get.url', '', 'text');
        $model = D('Url');
        $url = $model->getUrl($aShort);
        $model->setCountInc($aShort);
        $this->assign('url',$url);
        $this->display();
    }



}
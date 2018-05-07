<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2016/12/7
 * Time: 9:15
 */
namespace Module\Controller;

use Think\Controller;

class IndexController extends Controller
{
    public function _initialize()
    {

        $this->assign('bottom_flag','find');
    }
    /**
     * 主页面显示
     */
    public function index()
    {
        $this->display();
        
    }
    
}
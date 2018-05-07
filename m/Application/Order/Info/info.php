<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/28
 * Time: 9:58
 * @author lin <lt@ourstu.com>
 */
return array(
    //模块名
    'name' => 'Order',
    //别名
    'alias' => '支付系统',
    //版本号
    'version' => '1.0.0',
    //是否商业模块,1是，0，否
    'is_com' => 0,
    //是否显示在导航栏内？  1是，0否
    'show_nav' => 1,
    //模块描述
    'summary' => '支付模块',
    //开发者
    'developer' => '嘉兴想天信息科技有限公司',
    //开发者网站
    'website' => 'http://www.ourstu.com',
    //前台入口，可用U函数
    'entry' => 'Order/index/index',

    'admin_entry' => 'Admin/Order/index',

    'icon' => '',

    'can_uninstall' => 0

);
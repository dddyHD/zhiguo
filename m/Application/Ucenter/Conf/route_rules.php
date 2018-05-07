<?php
/**
 * Created by PhpStorm.
 * User: zzl
 * Date: 2016/10/25
 * Time: 16:12
 * @author:zzl(郑钟良) zzl@ourstu.com
 */
return array(
    'route_rules' => array(
        'register$' => 'ucenter/member/register',
        'login$' => 'ucenter/member/login',
    ),
    'router' => array(
        'ucenter/member/index' => 'login',
        'ucenter/member/login' => 'login',
        'ucenter/member/register' => 'register',
    )
);
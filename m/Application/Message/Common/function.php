<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2016/12/20
 * Time: 18:51
 */
function get_message_title($type)
{
    switch ($type) {
        case 'Weibo':
            return '动态消息';
            break;
        case 'Ucenter':
            return '用户消息';
            break;
        case 'Weibo_Crowd':
            return '圈子动态';
            break;
        default;
    }
}
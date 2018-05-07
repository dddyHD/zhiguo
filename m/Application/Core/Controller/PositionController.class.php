<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Core\Controller;

use Think\Controller;

class PositionController extends Controller
{

    private $_key = 'TTKBZ-DMPKX-BQ54K-TPPX5-F5OL7-NFBIA';

    public function search()
    {
        $aKeyword = I('keyword', '', 'text');
        $aLocation = I('location', '', 'text');
        $key = 'position_' . $aKeyword;
        //$result = S($key);
        if (empty($result)) {
            $url = 'http://apis.map.qq.com/ws/place/v1/suggestion/?keyword=' . $aKeyword . '&key=' . $this->_key . '&orderby=distance(' . $aLocation . ')';
            $result = file_get_contents($url);
            S($key, $result, 60 * 60 * 24);
        }
        exit($result);
    }


    public function getNearby()
    {
        $aLocation = I('location', '', 'text');
        $key = 'location_nearby_' . $aLocation;
        $result = S($key);
        if (empty($result)) {
            $url = 'http://apis.map.qq.com/ws/geocoder/v1/?location=' . $aLocation . '&key=' . $this->_key . '&get_poi=1';
            $result = file_get_contents($url);
            S($key, $result, 60 * 60 * 24);
        }
        exit($result);
    }


}

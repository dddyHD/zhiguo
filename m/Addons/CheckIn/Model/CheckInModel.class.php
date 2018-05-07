<?php

namespace Addons\CheckIn\Model;

use Think\Model;

/**
 * Class CheckInModel 签到模型
 * @package Addons\CheckIn\Model
 * @author:xjw129xjt(肖骏涛) xjt@ourstu.com
 */
class CheckInModel extends Model
{
    protected $tableName = 'checkin';

    public function getCheck($uid)
    {
        $time = get_some_day(0);
        $res = S('check_in_' . $uid . '_' . $time);
        if (empty($res)) {
            $res = $this->where(array('uid' => $uid, 'create_time' => array('egt', $time)))->find();
            $check = query_user(array('con_check', 'total_check'), $uid);
            $res = array_merge($res, $check);
            S('check_in_' . $uid . '_' . $time, $res, 60 * 60 * 24);
        }
        return $res;
    }

    public function addCheck($uid)
    {
        $data['uid'] = $uid;
        $data['create_time'] = time();
        return $this->add($data);
    }

    public function resetConCheck()
    {
        $memberModel = D('Member');
        $time = get_some_day(0);
        $time_yesterday = get_some_day(1);
        $users = $memberModel->where(array('con_check' => array('gt', 0)))->field('uid')->select();
        foreach ($users as $val) {
            $check = $this->where(array('uid' => $val['uid'], 'create_time' => array('between', array($time_yesterday, $time - 1))))->find();
            if (!$check) {
                $memberModel->where(array('uid' => $val['uid']))->setField('con_check', 0);
            }
        }
    }

    public function getRank($type)
    {
        $time = get_some_day(0);
        $time_yesterday = get_some_day(1);
        $memberModel = D('Member');
        switch ($type) {
            case 'today' :
                $list = $this->where(array('create_time' => array('egt', $time)))->order('create_time asc')->limit(5)->select();
                break;
            case 'con' :
                $uids = $this->where(array('create_time' => array('egt', $time_yesterday)))->field('uid')->select();
                $uids = getSubByKey($uids, 'uid');
                $list = $memberModel->where(array('uid' => array('in', $uids)))->field('uid,con_check')->order('con_check desc,uid asc')->limit(5)->select();
                break;
            case 'total' :
                $list = $memberModel->field('uid,total_check')->order('total_check desc,uid asc')->limit(5)->select();
                break;
        }

        foreach ($list as &$v) {
            $v['user'] = query_user(array('avatar32', 'avatar64', 'space_url', 'nickname', 'uid',), $v['uid']);
        }
        unset($v);
        return $list;
    }

    /**
     * getLastWithoutCheckin   获取用户上次未签到的日期
     * @param int $uid
     * @return int|null
     * @author :  xjw129xjt（駿濤） xjt@ourstu.com
     */
    public function getLastWithoutCheckin($uid = 0)
    {
        $uid = $uid ? $uid : is_login();
        $last = 0;
        $checkinModel = M('checkin');
        $r = 10;
        $i = 1;
        $k = true;
        while ($k) {
            $need = $r * $i;
            $time = get_some_day($need);
            $count = $checkinModel->where(array('uid' => $uid, 'create_time' => array('between', array($time, get_some_day($r * ($i - 1)) - 1))))->count();
            if ($count < $r) {
                $k = false;
            } else {
                $i++;
            }
        }
        $start = get_some_day($i * $r);
        $end = get_some_day(($i - 1) * $r);
        $list = $checkinModel->where(array('uid' => $uid, 'create_time' => array('between', array($start, $end - 1))))->order('create_time desc')->select();
        if (!empty($list)) {
            foreach ($list as $key => $v) {
                $t = get_some_day(($i - 1) * $r + $key);
                $t1 = get_some_day(($i - 1) * $r + $key + 1);
                $t2 = $v['create_time'];
                $last = $t1 - 3600 * 24;
                if ($t2 >= $t || $t2 < $t1) {
                    $last = $t1;
                    break;
                }
            }
        } else {
            $last = $end - 3600 * 24;
        }
        return $last;
    }

    /**
     * getLast   使用连续签到天数获取的上次未签到时间
     * @param int $uid
     * @return int|null
     * @author :  xjw129xjt（駿濤） xjt@ourstu.com
     */
    public function getLast($uid = 0)
    {
        $uid = $uid ? $uid : is_login();
        $today = $this->getCheck($uid);
        $p = empty($today) ? 1 : 0;
        $query = query_user(array('con_check', 'total_check'), $uid);
        $time = get_some_day($query['con_check'] + $p);
        return $time;
    }

    /**
     * getConCheck  获取连续签到天数
     * @param int $uid
     * @return int
     * @author :  xjw129xjt（駿濤） xjt@ourstu.com
     */
    public function getConCheck($uid = 0)
    {
        $uid = $uid ? $uid : is_login();
        $now = time();
        $last = $this->getLastWithoutCheckin($uid);
        $today = $this->getCheck($uid);
        $p = empty($today) ? 1 : 0;
        $timediff = $now - $last;
        $days = intval($timediff / 86400);

        return $days - $p;
    }
}

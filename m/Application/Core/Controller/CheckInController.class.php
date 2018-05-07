<?php

namespace Core\Controller;

use Think\Controller;

class CheckInController extends Controller
{
    public function doCheckIn()
    {

        $time = get_some_day(0);
        $uid = is_login();

        $model = D('Core/CheckIn');
        $memberModel = D('Member');
      
        $check = $model->getCheck($uid);
        if (!$check) {
            $model->addCheck($uid);
            $memberModel->where(array('uid' => $uid))->setInc('total_check');
            $memberModel->where(array('uid' => $uid))->setInc('con_check');
            clean_query_user_cache($uid, array('con_check', 'total_check', 'score1'));
            S('check_rank_today_' . $time, null);
            S('check_rank_con_' . $time, null);
            S('check_rank_total_' . $time, null);
            S('check_in_' . $uid . '_' . $time,null);
            return true;
        } else {
            return false;
        }
    }





}
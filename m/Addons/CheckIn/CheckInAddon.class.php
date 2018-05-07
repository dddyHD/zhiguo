<?php

namespace Addons\CheckIn;

use Common\Controller\Addon;

/**
 * 签到插件
 * @author 嘉兴想天信息科技有限公司
 */
class CheckInAddon extends Addon
{

    public $info = array(
        'name' => 'CheckIn',
        'title' => '签到',
        'description' => '签到插件',
        'status' => 1,
        'author' => 'xjw129xjt(肖骏涛)',
        'version' => '3.0'
    );


    public function install()
    {
        $prefix = C("DB_PREFIX");
        D()->execute("DROP TABLE IF EXISTS `{$prefix}checkin`");
        D()->execute(<<<SQL
CREATE TABLE IF NOT EXISTS `{$prefix}checkin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `is_remedy` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否补签',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
SQL
        );

        D()->execute(<<<SQL
        ALTER TABLE  `{$prefix}member` ADD  `con_check` INT NOT NULL DEFAULT  '0',
ADD  `total_check` INT NOT NULL DEFAULT  '0';
SQL
        );


        return true;
    }

    public function uninstall()
    {

        $prefix = C("DB_PREFIX");
        D()->execute("DROP TABLE IF EXISTS `{$prefix}checkin`");

        D()->execute(<<<SQL
ALTER TABLE `{$prefix}member`
  DROP `con_check`,
  DROP `total_check`;
SQL
        );
        return true;
    }


    public function checkIn($param)
    {
        $model = $this->checkInModel();
        $uid = is_login();
        $check = $model->getCheck($uid);
        $user_info = query_user(array('con_check', 'total_check'), $uid);

        $last_day = $model->getLastWithoutCheckin($uid);

        $sign_card_count = 0;
        $moduleModel = D('Common/Module');
        $tcenter_is_installed = $moduleModel->checkInstalled('Tcenter');
        if ($tcenter_is_installed) {
            $propOwnModel = D('Tcenter/PropOwn');
            if (method_exists($propOwnModel, 'getCount')) {
                $sign_card_count = $propOwnModel->getCount('sign_card');
            }
        }

        $this->assign('tcenter_is_installed', $tcenter_is_installed);
        $this->assign('sign_card_count', $sign_card_count);
        $this->assign('last_day', date('Y-m-d', $last_day));
        $this->assign('user_info', $user_info);
        $this->assign('check', $check);
        $this->assignDate();
        $this->display('View/checkin');
    }

    private function checkInModel()
    {
        return D('Addons://CheckIn/CheckIn');
    }

    private function assignDate()
    {
        $week = date('w');
        switch ($week) {
            case '0':
                $week = '周日';
                break;
            case '1':
                $week = '周一';
                break;
            case '2':
                $week = '周二';
                break;
            case '3':
                $week = '周三';
                break;
            case '4':
                $week = '周四';
                break;
            case '5':
                $week = '周五';
                break;
            case '6':
                $week = '周六';
                break;
        }
        $this->assign('day', date('Y.m.d'));
        $this->assign('week', $week);

    }


    public function doCheckIn()
    {

        $time = get_some_day(0);
        $uid = is_login();

        $model = $this->checkInModel();
        $memberModel = D('Member');
        $scoreModel = D('Ucenter/Score');
        $check = $model->getCheck($uid);
        if (!$check) {
            $model->addCheck($uid);
            $memberModel->where(array('uid' => $uid))->setInc('total_check');
            //签到积分奖励 从addons表获得设置的类型和积分数

            $arrconf = get_addon_config('CheckIn');
            array_shift($arrconf);
            $new = array_filter($arrconf);

            foreach ($new as $k => $v) {
                $k1 = substr($k, 5, strlen($k) - 5);
                $scoreModel->setUserScore($uid, $v, $k1, 'inc', 'weibo', $uid, '签到[' . $k . ']类型积分+[' . $v . ']');
                $scoreModel->addScoreLog($uid, $k1, 'inc', $v, 'weibo', $uid, '签到[' . $k . ']类型积分+[' . $v . ']');
            }

            $memberModel->where(array('uid' => $uid))->setInc('con_check');
            clean_query_user_cache($uid, array('con_check', 'total_check', 'score1'));
            S('check_rank_today_' . $time, null);
            S('check_rank_con_' . $time, null);
            S('check_rank_total_' . $time, null);

            return true;
        } else {
            return false;
        }
    }


    public function handleAction($param)
    {
        $typedata = M('ucenter_score_type');
        $arrconf = get_addon_config('CheckIn');
        array_shift($arrconf);
        $arrkey = array_keys($arrconf);
        foreach ($arrkey as $k) {
            $k = substr($k, 5, strlen($k) - 5);
            $typename[] = $typedata->where('id=' . $k)->getfield('title');
        }
        $p = 0;
        foreach ($arrconf as $f) {
            $newarr[$typename[$p]] = $f;
            $p++;
        }
        unset($f);
        $new = array_filter($newarr);//类型名+积分数  除去空元素
        $str = '';
        foreach ($new as $t => $v) {
            $str .= $t . '+' . $v . '!';
        }
        $config = $this->getConfig();
        if (!empty($config['action'])) {
            $action_info = M('Action')->where(array('name' => array('in', $config['action'])))->field('id')->select();
            $action_info = array_column($action_info, 'id');
            if (in_array($param['action_id'], $action_info)) {
                $res = $this->doCheckIn();
                if ($res) {
                    $param['log_score'] .= '签到成功!' . $str;
                    return $res;
                }
            }
        }
        return false;

    }


    /*Pro增强*/
    public function tool()
    {

        $mid = is_login();

        $model = $this->checkInModel();
        $scoreModel = D('Ucenter/Score');


        $check = S('close_checkin_remind_' . $mid . '_' . get_some_day(0));
        if ($mid) {
            $check_is_checked = $model->getCheck($mid);
            $this->assign('check_is_checked', $check_is_checked);

            $user_info = query_user(array('nickname', 'avatar128', 'con_check', 'total_check', 'space_url'), $mid);

            $memberModel = D('Member');
            $ranking = $memberModel->field('uid')->order('con_check desc,uid asc')->select();
            $ranking = getSubByKey($ranking, 'uid');
            $user_info['ranking'] = array_search($mid, $ranking) + 1;


            $count = $model->field('uid')->where(array('create_time' => array('egt', get_some_day(0))))->order('create_time asc, uid asc')->count();
            $user_info['today_count'] = $count;
            if (D('Common/Module')->isInstalled('Shop')) {
                $score_type_id = modC('SHOP_SCORE_TYPE', '1', 'Shop');
                $user_info['score'] = $scoreModel->getUserScore($mid, $score_type_id);  //获取用户积分
                $score_type = $scoreModel->getType(array('id' => $score_type_id));  //获取积分类型信息
                $goods = M('shop')->where(array('money_need' => array('gt', $user_info['score']), 'status' => 1))->limit(1)->order('money_need asc')->select();  //获取积分商城商品
                if (empty($goods)) {
                    $goods = M('shop')->where(array('money_need' => array('elt', $user_info['score']), 'status' => 1))->limit(1)->order('money_need desc')->select();  //获取最接近的
                }
                $this->assign('goods', $goods[0]);
                $this->assign('score_type', $score_type);
            }
            $this->assign('user_info', $user_info);

        }
        $this->assign('check', $check);
        $config = $this->getConfig();
        $this->assign('config', $config);
        $this->display('View/remind');
    }


}
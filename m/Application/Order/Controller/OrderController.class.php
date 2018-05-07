<?php
namespace Admin\Controller;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/28
 * Time: 9:45
 * @author lin <lt@ourstu.com>
 */
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
class OrderController extends AdminController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    //todo 各项配置
    public function config(){
        $field = D('Ucenter/Score')->getTypeList(array('status' => 1));
        $configBuilder = new AdminConfigBuilder();
        $data = $configBuilder->handleConfig();

        $param = array();
        $param['opt'] = $field;
        $de_data = $data['PAY_FIELD'];
        $param['jsonData'] = $de_data;
        $param['data'] = json_decode($de_data, true);

        $recharge = array();
        $recharge['opt'] = $field;
        $re_data = $data['RE_FIELD'];
        $recharge['jsonData'] = $re_data;
        $recharge['data'] = json_decode($re_data, true);


        $withdraw = array();
        $withdraw['opt'] = $field;
        $wi_data = $data['WI_FIELD'];
        $withdraw['jsonData'] = $wi_data;
        $withdraw['data'] = json_decode($wi_data, true);


        $configBuilder->title('配置管理')->data($data)
            ->keyText('APPID','APPID','绑定支付的APPID')
            ->keyText('MCHID','MCHID','商户号')
            ->keyText('KEY','KEY','商户支付密钥')
            ->keyText('APPSECRET','APPSECRET','公众帐号secert')
            ->keyUserDefined('PAY_FIELD', '积分类型和积分的兑率设置','如填写100则表示1RMB=100积分', T('Order@Order/config'), $param)

            ->keyText('ALIPAY_APPID','APPID','应用ID,您的APPID')
            ->keyTextArea('MERCHANT_PRIVATE_KEY','merchant_private_key','商户私钥，您的原始格式RSA私钥')
            ->keyTextArea('ALIPAY_PUBLIC_KEY','alipay_public_key','支付宝公钥')

            ->keyText('RECHARGE_MIN_AMOUNT', '最小充值金额')
            ->keyUserDefined('RE_FIELD', '积分类型和积分的兑率设置','如填写100则表示1RMB=100积分', T('Order@Order/config'), $recharge)
            ->keyDefault('RECHARGE_MIN_AMOUNT',0)

            ->keyText('WITHDRAW_MIN_AMOUNT', '最小提现金额')
            ->keyUserDefined('WI_FIELD', '积分类型和积分的兑率设置','如填写100则表示1RMB=100积分', T('Order@Order/config'), $withdraw)
            ->keyDefault('WITHDRAW_MIN_AMOUNT',0)
        ->group('微信配置','NOTIFY_URL,APPID,MCHID,KEY,APPSECRET')
        ->group('支付宝配置','ALIPAY_APPID,MERCHANT_PRIVATE_KEY,ALIPAY_PUBLIC_KEY')
        ->group('支付设置', 'PAY_FIELD')
        ->group('充值设置','RECHARGE_MIN_AMOUNT,RE_FIELD')
        ->group('提现设置', 'WITHDRAW_MIN_AMOUNT,WI_FIELD');
        $configBuilder->buttonSubmit()
            ->buttonBack();
        $configBuilder->display();
    }

    public function index(){
        $this->meta_title='商品订单列表';
        $aPage=I('get.page',1,'intval');
        $aCate=I('cate',0,'intval');
        $method=I('method',0,'intval');
        $sTime=I('start','','string');
        $eTime=I('end','','string');
        $min=I('min',0,'floatval');
        $max=I('max',0,'floatval');
        $num=I('num',0);
        $map = $this->searchCondition($aCate,$method,$sTime,$eTime,$min,$max,$num);
        $orders=M('order_goods')->where($map)->page($aPage,20)->order('create_time desc')->select();
        $totalCount=M('order_goods')->where($map)->count();
        foreach ($orders as &$v){
            $goods=D('Order/OrderGoods')->getGoodsById($v['id']);
            $v['goods_id']=$goods['name'];
            $v['goods_type']=$goods['cate'];
            $v = $this->define($v);
        }
        unset($v);
        $this->assign('orders',$orders);
        $this->assign('count',$totalCount);
        $this->display(T('Order@Order/index'));
    }

    //充值订单
    public function rechargeList(){
        $this->meta_title='充值订单列表';
        $aPage=I('get.page',1,'intval');
        $aCate=I('cate',0,'intval');
        $method=I('method',0,'intval');
        $sTime=I('start','','string');
        $eTime=I('end','','string');
        $min=I('min',0,'floatval');
        $max=I('max',0,'floatval');
        $num=I('num',0);
        $map = $this->searchCondition($aCate,$method,$sTime,$eTime,$min,$max,$num);
        $orders=M('order_recharge')->where($map)->page($aPage,20)->order('create_time desc')->select();
        $totalCount=M('order_recharge')->where($map)->count();
        foreach ($orders as &$v){
            $v = $this->define($v);
        }
        unset($v);
        $this->assign('orders',$orders);
        $this->assign('count',$totalCount);
        $this->display(T('Order@Order/rechargelist'));
    }

    //todo 提现账户信息
    public function withdrawList(){
        $aPage=I('get.page',1,'intval');
        $orders=M('order_withdraw')->page($aPage,20)->order('create_time desc')->select();
        $totalCount=M('order_withdraw')->count();
        foreach ($orders as &$v){
            if ($v['pay_uid'] != 0) {
                $user = query_user(array('nickname'), $v['pay_uid']);
                $v['operator'] = $user['nickname'];
            } else {
                $v['operator'] = '-';
            }
            $v = $this->define($v);
        }
        $builder=new AdminListBuilder();
        $builder->title('提现记录')
            ->button('自动划款',$attr = array('href'=>U('order/doWithdraw'),'class'=>'btn ajax-post btn-danger tox-confirm','target-form'=>'ids','data-confirm'=>'您确定执行此操作吗？（此操作会自动转账给用户且不可逆）'))
            ->ajaxButton(U('order/manualWithdraw'),null,'手动提现（不转账）')
            ->ajaxButton(U('order/cancelWithdraw'), null, '取消提现')
            ->keyId()
            ->keyText('uid','用户')
            ->keyText('field','提现字段')
            ->keyText('amount','提现金额')
            ->keyText('method','提现方式')
            ->keyText('process','提现流程')
            ->keyText('operator','操作者')
            ->keyText('account_info','收款账户信息')
            ->keyText('create_time','创建时间')
            ->keyText('pay_time','提现完成时间')
            ->data($orders)
            ->pagination($totalCount,20)
            ->display();
    }

    public function doWithdraw($ids = array()){
        //todo 批量提现
        require_once(APP_PATH."Order/Lib/WxPay.Api.php");
        $pay = new \WxPayApi();
        if(empty($ids)){
            $this->error(L('请勾选要操作的选项'));
        }
        foreach ($ids as $id) {
            $withdraw = D('Order/Withdraw')->getWithdrawOrder($id);
            if($withdraw['process']==-1){
                $this->error('交易已关闭');
            }
            if($withdraw['process']==1){
                $this->error('交易已完成');
            }
            $amount = $withdraw['amount']*100;
            $desc = '提现';
            $number = time().create_rand(16,'num');
            $openid = M('sync_login')->where(array('uid'=>$withdraw['uid']))->getField('oauth_token_secret');
            $params = array(
                'partner_trade_no' => $number,
                'openid' => $openid,
                'check_name' => 'NO_CHECK',
                'amount' => $amount,
                'desc' => $desc,
            );
            $toPay = $pay->payToUser($params);
            if($toPay){
                $data['process']=1;
                $data['pay_time']=time();
                $data['is_pay']=1;
                $data['pay_uid']=is_login();
                $res=M('order_withdraw')->where(array('id'=>$id))->save($data);
                action_log('do_withdraw','Order',$id,$withdraw['uid']);
                if($res){
                    $this->success('提现成功');
                }
            }else{
                $this->error('提现失败');
            }
        }
    }

    public function manualWithdraw($ids = array()){
        if(empty($ids)){
            $this->error(L('请勾选要操作的选项'));
        }
        foreach ($ids as $id) {
            $withdraw = D('Order/Withdraw')->getWithdrawOrder($id);
            if ($withdraw['process'] == -1) {
                $this->error('交易已关闭');
            }
            if ($withdraw['process'] == 1) {
                $this->error('交易已完成');
            }
            $data['process']=1;
            $data['pay_time']=time();
            $data['is_pay']=1;
            $data['pay_uid']=is_login();
            M('order_withdraw')->where(array('id'=>$id))->save($data);
        }
        $this->success('请手动操作转账,提现成功');
    }

    public function cancelWithdraw($ids = array()){
        if(empty($ids)){
            $this->error(L('请勾选要操作的选项'));
        }
        foreach ($ids as $id) {
            $withdraw=D('Order/Withdraw')->getWithdrawOrder($id);
            if($withdraw['process']==-1){
                $this->error('交易已关闭');
            }
            if($withdraw['process']==1){
                $this->error('交易已完成');
            }
            $data['process']=-1;
            $data['pay_uid']=is_login();
            $data['pay_time']=time();
            M('order_withdraw')->where(array('id'=>$withdraw['id']))->save($data);
            M('member')->where(array('uid'=>$withdraw['uid']))->setInc('score' . $withdraw['field'],$withdraw['freeze_amount']);
            $behavior='提现交易关闭+'.$withdraw['amount'];
            $amount=$withdraw['amount'];
            $uid=$withdraw['uid'];
            expense_alendar($behavior,$amount,$uid);
            action_log('cancel_withdraw','Order',$withdraw['id'],$withdraw['uid']);
        }
        $this->success('已取消提现');
    }

    private function searchCondition($aCate,$method,$sTime,$eTime,$min,$max,$num){
        if ($method == 1){
            $map['uid'] = array('like', '%' . $num . '%');
            $this->assign('num',$num);
            $this->assign('method',$method);
        }elseif ($method == 2){
            $map['id'] = $num;
            $this->assign('num',$num);
            $this->assign('method',$method);
        }
        if($method!=2){
            if($aCate!=0){
                if($aCate == 1){
                    $map['process']=1;
                }
                if($aCate == 2){
                    $map['process']=0;
                }
                if($aCate == 3){
                    $map['process']=-1;
                }
                $this->assign('cate',$aCate);
            }
            if($min>=0&&$max>0){
                $map['amount']=array('between',array($min,$max));
                $this->assign('min',$min);
                $this->assign('max',$max);
            }
            if($sTime!=''&&$eTime!='') {
                $map['create_time'] = array('between', array(strtotime($sTime), strtotime($eTime.'23:59:59')));
                $this->assign('sTime',$sTime);
                $this->assign('eTime',$eTime);
            }
        }
        return $map;
    }

    private function define($v){
        $score=M('ucenter_score_type')->where(array('id'=>$v['field']))->getField('title');
        $v['field']=$score;
        if($score==''){
            $v['field']='无';
        }
        if($v['method']=="wechat"){
            $v['method']='微信支付';
        } elseif ($v['method']=="alipay"){
            $v['method']='支付宝支付';
        } else{
            $v['method']=M('ucenter_score_type')->where(array('id'=>$v['method']))->getField('title');;
        }
        $nickname=M('member')->where(array('uid'=>$v['uid']))->getField('nickname');
        $v['uid']='['.$v['uid'].']'.$nickname;
        switch ($v['process']){
            case -1:
            {
                $v['process']='交易关闭'; // todo
                break;
            }
            case 0:
            {
                $v['process']='等待付款';
                break;
            }
            case 1:
            {
                $v['process']='交易完成';
            }
        }
        $v['create_time'] = $v['create_time'] == 0 ? '-' : time_format($v['create_time']);
        $v['pay_time'] = $v['pay_time'] == 0 ? '-' : time_format($v['pay_time']);
        $v['is_pay'] = $v['is_pay'] == 0 ? '未付款' : '已付款';
        switch ($v['status']){
            case -1:
            {
                $v['status']='删除';
                break;
            }
            case 0:
            {
                $v['status']='禁用';
                break;
            }
            case 1:
            {
                $v['status']='启用';
            }
        }
        return $v;
    }
}
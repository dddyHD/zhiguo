<?php
namespace Order\Controller;
use Think\Controller;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/28
 * Time: 9:44
 * @author lin <lt@ourstu.com>
 */
class IndexController extends Controller
{
    public function index(){
        $notify_url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Order/Index/notify';
        $this->assign('notify_url',$notify_url);
        $aId=I('get.id',0);
        $goods=D('OrderGoods')->getGoodsById($aId);
        $amount=$goods['price']*100;
        $name=$goods['name'];
        $this->assign('id',$aId);
        $this->assign('amount',$amount);
        $this->assign('name',$name);
        $this->display();
    }

    public function notify()
    {
        require_once(APP_PATH."Order/Lib/WxPay.Data.php");
        $WxPay = new \WxPayResults();

        header('Content-type: text/xml');

        $returnResult = $GLOBALS['HTTP_RAW_POST_DATA'];

        //$res = $WxPay->FromXml($returnResult);
        $res = $WxPay::Init($returnResult);

        //支付成功
        if ($res['result_code'] == 'SUCCESS') {
            $data['process']=1;
            $data['is_pay']=1;
            $data['pay_time']=time();
            $data['method']='wechat';
            if(M('order_goods')->where(array('wechat_order'=>$res['out_trade_no']))->count()){
                M('order_goods')->where(array('wechat_order'=>$res['out_trade_no']))->save($data);
                $order=M('order_goods')->where(array('wechat_order'=>$res['out_trade_no']))->find();
                $goods=M('mall_goods')->where(array('id'=>$order['goods_id']))->getField('name');
                $behavior='购买-'.$goods;
                $amount='-'.$order['amount'];
                $uid=$order['uid'];
                D('orderGoods')->addProfit($order['goods_id'],$order['amount']);
                expense_alendar($behavior,$amount,$uid);
            }else if(M('order_recharge')->where(array('wechat_order'=>$res['out_trade_no']))->count()){
                M('order_recharge')->where(array('wechat_order'=>$res['out_trade_no']))->save($data);
                $recharge=M('order_recharge')->where(array('wechat_order'=>$res['out_trade_no']))->find();
                M('member')->where(array('uid'=>$recharge['uid']))->setInc('score4',$recharge['amount']);
                $behavior='充值+'.$recharge['amount'];
                $amount='+'.$recharge['amount'];
                expense_alendar($behavior,$amount,$recharge['uid']);
                action_log('recharge_order', 'Order', $recharge['id'], $recharge['uid']);
            }
            $success = array('return_code' => 'SUCCESS', 'return_msg' => 'OK');
            exit(ToXml($success));
        } else{
            // todo 返回错误信息记录表
        }
    }

    public function deposit(){
        $notify_url = 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Order/Index/notify';
        $this->assign('notify_url',$notify_url);
        $aId=I('get.id',0);
        $order=D('Recharge')->getRechargeOrder($aId);
        $amount=$order['amount']*100;
        $this->assign('id',$aId);
        $this->assign('amount',$amount);
        $this->display();
    }

    //支付宝
    public function pay(){
        require_once(APP_PATH."Order/alipay/wappay/service/AlipayTradeService.php");
        require_once(APP_PATH."Order/alipay/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php");
        require_once(APP_PATH."Order/alipay/config.php");
        $aId=I('post.order_id',0);
        $goods=D('OrderGoods')->getGoodsById($aId);
        //dump($goods);exit;
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = time().create_rand(10,'num');

        //订单名称，必填
        $subject = $goods['name'];

        //付款金额，必填
        $total_amount = $goods['price'];

        //商品描述，可空
        //$body = $goods['introduce'];

        //超时时间
        $timeout_express="1m";

        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        //$payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $data['wechat_order']=$out_trade_no;
        D('order_goods')->where(array('id'=>$aId))->save($data);

        $payResponse = new \AlipayTradeService($config);
        $payResponse->wapPay($payRequestBuilder,$config['return_url'],$config['notify_url']);

    }

    public function alipay_notify()
    {
        require_once(APP_PATH."Order/alipay/wappay/service/AlipayTradeService.php");
        require_once(APP_PATH."Order/alipay/config.php");
        $arr = $_POST;
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);
        if ($result) {//验证成功
            //商户订单号

            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];


            if ($_POST['trade_status'] == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                $data['process'] = 1;
                $data['is_pay'] = 1;
                $data['pay_time'] = time();
                $data['method'] = 'alipay';
                if (M('order_goods')->where(array('wechat_order' => $out_trade_no))->count()) {
                    M('order_goods')->where(array('wechat_order' => $out_trade_no))->save($data);
                    $order = M('order_goods')->where(array('wechat_order' => $out_trade_no))->find();
                    $goods = M('mall_goods')->where(array('id' => $order['goods_id']))->getField('name');
                    $behavior = '购买-' . $goods;
                    $amount = '-' . $order['amount'];
                    $uid = $order['uid'];
                    expense_alendar($behavior, $amount, $uid);
                }

                echo "success";        //请不要修改或删除

            } else {
                //验证失败
                echo "fail";    //请不要修改或删除

            }
        }
    }

    public function orderGoods(){
        $this->setTitle('商品订单');
        $aId=I('get.id',0);
        $idAmount=count(explode(',',D('OrderGoods')->getGoodsInfo($aId)));
        $this->assign('idAmount',$idAmount);
        $goods=D('OrderGoods')->getGoodsById($aId);
        if(empty($goods['banner'])){
            $goods['banner']= $goods['pictures'];
        }
        $this->assign('goods',$goods);
        $order=D('OrderGoods')->getGoodsOrder($aId);
        if($order['uid']!=is_login()||$order['id']!=$aId){
            $this->error('订单不存在','',3);
        }
        $score=get_pay_field();
        foreach ($score as &$v){
            $v['UNIT']=$order['amount']*$v['UNIT'];
        }
        unset($v);
        //判断商品是否被购买
        $is_pay=D('ordergoods')->isPayed(is_login(),$order['goods_id']);
        $this->assign('is_pay',$is_pay);
        $this->assign('score',$score);
        $this->assign('order',$order);
        $this->display();
    }

    public function addOrder()
    {
        if (IS_POST) {
            if (!is_login()) {
                $res['status']=-1;
                $res['info']='请登录后操作';
                $this->ajaxReturn($res,"JSON");
            }
            $gId = I('post.goods_id', '', 'intval');
            $goods = M('mall_goods')->where(array('id' => $gId))->find();
            $data['goods_id'] = $gId;
            $data['goods_type'] = $goods['cate'];
            $data['uid'] = get_uid();
            $data['amount'] = $goods['price'];
            $data['status']=1;
            //判断是否已存在该商品的订单
            $isOrder=D('orderGoods')->isOrder($data);
            if($isOrder){
                $this->success('存在该商品的订单，为你跳转到该订单',U('Order/Index/ordergoods',array('id' =>$isOrder['id'])),3);
            }
            $data['create_time'] = time();
            $data['method'] = 'wechat';
            $res = D('OrderGoods')->createOrder($data);
            if ($res) {
                $this->success('下单成功！',U('Order/Index/ordergoods',array('id' => $res)));
            }
        }
    }

    public function carOrder()
    {
        if (IS_POST) {
            if (!is_login()) {
                $res['status']=-1;
                $res['info']='请登录后操作';
                $this->ajaxReturn($res,"JSON");
            }
            $gId = $_POST['goods_id'];
            $map['id']=array('in',$gId);
            $goods = M('mall_goods')->where($map)->select();
            $price=0;
            foreach ($goods as $val){
                $price=$price+$val['price'];
            }
            unset($val);
            foreach ($goods as $key=>$vo){
                $data['goods_id'][$key]= $gId[$key];
                $data['goods_type'] = $goods[$key]['cate'];
                $data['uid'] = get_uid();
                $data['amount'] = $price;
                $data['create_time'] = time();
                $data['status']=1;
                $data['method'] = 'wechat';
            }
            unset($vo);
            $data['goods_id']=implode(',', $data['goods_id']);
            $res = D('OrderGoods')->createOrder($data);
            if ($res) {
                $this->success('下单成功！',U('Order/Index/ordergoods',array('id' => $res)));
            }
        }
    }

    public function getOwn(){
        $method=I('post.method','','string');
        $aId=I('post.order_id',0);
        M('order_goods')->where(array('id'=>$aId))->setField('method',$method);
        if($method=='wechat'){
            $this->ajaxReturn($method,"JSON");
        }
        if($method=='alipay'){
            $this->ajaxReturn($method,"JSON");
        }
        $result['own']=M('member')->where(array('uid' => is_login()))->getField('score' . $method);
        $result['own'] = number_format($result['own'], 2, ".", "");
        $result['str']=M('ucenter_score_type')->where(array('id'=>$method))->getField('unit');
        $result=$result['own'] . $result['str'];
        $this->ajaxReturn($result,"JSON");
    }

    public function payOrder(){
        if (!is_login()) {
            $res['status']=-1;
            $res['info']='请登录后操作';
            $this->ajaxReturn($res,"JSON");
        }
        $oId = I('post.order_id',0);
        $method=I('post.method','','intval');
        $data['field']=$method;
        $data['process']=1;
        $data['is_pay']=1;
        $data['pay_time']=time();
        $data['method']=$method;
        $order=D('OrderGoods')->getGoodsOrder($oId);
        $goods=D('OrderGoods')->getGoodsById($oId);
        if($order['method']==''){
            $res['status']=-1;
            $res['info']='请选择付款方式';
            $this->ajaxReturn($res,"JSON");
        }
        if($order['is_pay']==1){
            $res['status']=-1;
            $res['info']='该订单已付款';
            $this->ajaxReturn($res,"JSON");
        }
        $own=M('member')->where(array('uid' => is_login()))->getField('score' . $method);
        $type = get_pay_type($method);
        if(!$type){
            $res['status']=-1;
            $res['info']='该付款类型不存在';
            $this->ajaxReturn($res,"JSON");
        }
        $all=$order['amount']*$type['UNIT'];
        if($own<$all){
            $res['status']=-1;
            $res['info']='余额不足';
            $this->ajaxReturn($res,"JSON");
        }
        $res=M('member')->where(array('uid' => is_login()))->setDec('score' . $method,$all);
        if($res){
            M('order_goods')->where(array('id'=>$oId))->save($data);
            action_log('pay_order', 'Order', $oId, is_login());
            $behavior='购买-'.$goods['name'];
            $amount='-'.$order['amount'];
            expense_alendar($behavior,$amount);
            D('Mall/GoodsCar')->clearCar();
            D('orderGoods')->addProfit($order['goods_id'],$order['amount']);
            $this->success('付款成功',U('Order/index/completion'));
        }
    }

    public function completion(){
        $this->setTitle('付款成功');
        $this->display();
    }

    public function recharge(){
        if(IS_POST){
            $this->createRecharge();
        }else{
            $aId=I('get.id',0);
            $recharge_order=D('Recharge')->getRechargeOrder($aId);
            $this->assign('order',$recharge_order);
            $this->display();
        }
    }

    private function createRecharge(){
        if(!is_login()){
            $res['status']=-1;
            $res['info']='请登录后操作！';
            $this->ajaxReturn($res,"JSON");
        }
        $amount=I('post.amount',0,'floatval');
        $amount = number_format($amount, 2, ".", "");
        $minAmount=modC('RECHARGE_MIN_AMOUNT',0,'order');
        if($amount<=0){
            $res['status']=-1;
            $res['info']='请输入正确的数额！';
            $this->ajaxReturn($res,"JSON");
        }
        if($amount<$minAmount){
            $res['status']=-1;
            $res['info']='最小充值数额为' . $minAmount;
            $this->ajaxReturn($res,"JSON");
        }
        $data['amount']=$amount;
        $data['field']=4;
        $data['method']='wechat';
        $data['uid']=is_login();
        $data['create_time']=time();
        $data['status']=1;
        $res=D('Recharge')->rechargeOrder($data);
        if ($res) {
            $this->success('请查看充值订单，确认充值',U('Order/Index/recharge', array('id' => $res)));
        }

    }

    public function withdraw(){
        if(IS_POST){
            $this->createWithdraw();
        }else{
            $aId=I('get.id',0,'intval');
            $withdraw=D('Withdraw')->getWithdrawOrder($aId);
            $this->assign('draw',$withdraw);
            $this->display();
        }
    }

    private function createWithdraw(){
        if(!is_login()){
            $res['status']=-1;
            $res['info']='请登录后操作！';
            $this->ajaxReturn($res,"JSON");
        }
        $draw_amount=I('post.draw_amount',0,'floatval');
        $alipay=I('post.alipay','','text');
        $alipay_name=I('post.alipay_name','','text');
        $amount = number_format($draw_amount, 2, ".", "");
        $minAmount=modC('WITHDRAW_MIN_AMOUNT',0,'order');
        $field=I('post.field',0,'intval');
        if($field==''){
            $res['status']=-1;
            $res['info']='请选择提现类型';
            $this->ajaxReturn($res,"JSON");
        }
        if($alipay==''){
            $res['status']=-1;
            $res['info']='请填写支付宝账号';
            $this->ajaxReturn($res,"JSON");
        }
        if($amount<=0){
            $res['status']=-1;
            $res['info']='请输入正确的数额！';
            $this->ajaxReturn($res,"JSON");
        }
        if($amount<$minAmount){
            $res['status']=-1;
            $res['info']='最小提现数额为￥' . $minAmount;
            $this->ajaxReturn($res,"JSON");
        }
        $type=get_wi_type($field);
        if(!$type){
            $res['status']=-1;
            $res['info']='该付款类型不存在';
            $this->ajaxReturn($res,"JSON");
        }
        $score=M('member')->where(array('uid' => is_login()))->getField('score' . $field);
        $freeze_count = $type['UNIT'] * $amount;
        if($score<$freeze_count){
            $res['status']=-1;
            $res['info']='超出可提现数额！';
            $this->ajaxReturn($res,"JSON");
        }
        $data['field']=$field;
        $data['amount']=$amount;
        $data['method']='wechat';
        $data['uid']=is_login();
        $data['create_time']=time();
        $data['status']=1;
        $data['freeze_amount']=$freeze_count;
        $res=D('Withdraw')->withdrawOrder($data);
        if($res){
            M('member')->where(array('uid' => is_login()))->setDec('score' . $field,$freeze_count);
            $this->success('请查看提现详情',U('Order/Index/withdraw', array('id' => $res)));
        }
    }
}
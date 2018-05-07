<?php
/**
 * Created by PhpStorm.
 * User: 王杰 wj@ourstu.com
 * Date: 2017/1/4
 * Time: 9:10
 */
namespace Mall\Controller;

use Think\Controller;

require_once('./Application/Mall/Conf/jssdk.php');
class IndexController extends Controller
{

    public function _initialize()
    {
//        $this->setTitle("商城");
//        $this->setKeywords("商城");
//        $this->setDescription("商城");
        $this->assign('bottom_flag','find');
    }

    /**
     * 主页面显示
     */
    public function index()
    {
        $themeId=D('theme')->where(array('id'=>1))->getField('theme_id');
        switch ($themeId){
            case 0: $this->mfreeIndex();break;
            default:$this->mfreeIndex();break;
        }
    }
    public function mfreeIndex(){
        $aPage = I('get.page',1,'intval');
        $aCount = I('get.count',10,'intval');
        $aIsPull = I('get.is_pull',0,'intval');
        $aType = I('get.type','','intval');

        $cateList = D('Mall/GoodsCategory')->getCategory();
        $this->assign('cate',$cateList);

        $goodsModel = D('Goods');
        if (!empty($aType)) {
            $temp = array_column($cateList,'id');
            if (!in_array($aType,$temp)) {
                $this->error('参数错误~');
            }
            $this->assign('type',$aType);

            $cateGoods = array();
            $cateIds = $goodsModel->getCateGoods($aType);
            $cateId = array_column($cateIds,'id');
            foreach ($cateId as $key => $v) {
                $cateGoods[] = $goodsModel->getGoods($v);
                $cateGoods[$key]['pictures'] = explode(',', $cateGoods[$key]['pictures']);
                $cateGoods[$key]['img'] = getThumbImageById($cateGoods[$key]['pictures'][0],160,160);
            }
            $this->assign('cate_goods',$cateGoods);
        }
        $good = $goodsModel->getAllGoodsByPage($aPage,$aCount);
        $ids = array_column($good, 'id');
        $goods = array();
        foreach ($ids as $key => $v) {
            $goods[] = $goodsModel->getGoods($v);
            $goods[$key]['pictures'] = explode(',', $goods[$key]['pictures']);
            $goods[$key]['img'] = getThumbImageById($goods[$key]['pictures'][0],120,160);
        }
        $this->assign('goods',$goods);

        $new_time = modC('MALL_NEW_LEFT',3);
        $time_left = get_some_day($new_time);
        $param['update_time'] = array('gt', $time_left);
        $param['status'] = 1;
        $newGoods = $goodsModel->order('update_time desc')->where($param)->limit(10)->select();
        foreach ($newGoods as &$v) {
            $v['pictures'] = explode(',',$v['pictures']);
            $v['img'] = getThumbImageById($v['pictures'][0],120,160);
        }
        unset($v);
        $this->assign('new_goods',$newGoods);

        $hotGoods = array();
        $hotIds = $goodsModel->getHotGoods();
        $hotId = array_column($hotIds, 'id');
        foreach ($hotId as $key => $v) {
            $hotGoods[] = $goodsModel->getGoods($v);
            $hotGoods[$key]['pictures'] = explode(',', $hotGoods[$key]['pictures']);
            $hotGoods[$key]['img'] = getThumbImageById($hotGoods[$key]['pictures'][0],120,160);
        }
        $this->assign('hot_goods',$hotGoods);

        $this->assign('mall_pictures',getThumbImageById(modC('MALL_PICTURES','','Mall'),1080,720));
        $this->assign('mall_name',modC('MALL_NAME','智果内容付费系统','Mall'));
        $this->assign('mall_intro',modC('MALL_INTRO','未填写社区简介~','Mall'));
        $this->assign('first_mall_num', count($goods));
        if ($aIsPull) {
            $data['html'] = '';
            $data['status'] = 1;
            $data['html'] .= $this->fetch('_list');
            $this->ajaxReturn($data);
        }
        $this->display();
    }
    
    public function detail(){
        $aId = I('get.id',0,'intval');
        if (empty($aId)) {
            $this->error('此商品不存在');
        }
        $good = D('Goods')->getGoods($aId);
        if (empty($good)) {
            $this->error('此商品不存在');
        }
        S('goods_' . $aId, null);
        M('mall_goods')->where(array('id'=>$aId))->setInc('views',1);
        $this->setTitle($good['name']);
        $this->setKeywords($good['name']);
        $this->setDescription($good['name']);
        $collect['module'] = 'Mall';
        $collect['table'] = 'collect';
        $collect['row'] = $aId;
        $collect['uid']=is_login();
        $isCollect = D('Collect')->isCollect($collect);
        if ($isCollect) {
            $good['is_collect'] = 1;
        } else {
            $good['is_collect'] = 0;
        }
        $good['pictures'] = (int)$good['pictures'];
        $good['banner'] = $good['banner']?$good['banner']:$good['pictures'];
        $uid=is_login();
        $is_pay=D('Order/OrderGoods')->isPayed($uid,$aId);
        $commentAll=D('Goods')->getCommentByGoodsId($aId);
        $comment=D('Goods')->getCommentByGoodsId($aId,2);
        $commentCount=count($comment);
        $good['content'] = preview_image(M('mall_goods_article')->where(array('goods_id' => $aId))->getField('content'));
        $good['introduce'] = preview_image($good['introduce']);
        $good['show'] = preview_image($good['show']);
        $appid=modC('APP_ID','','weixin');
        $appsecret=modC('APP_SECRET','','weixin');
        $jssdk = new \JSSDK ($appid,$appsecret);
        $signPackage = $jssdk->GetSignPackage();
        $shareImg  = getThumbImageById($good['banner'],300,300);
        //不存在http://
        $not_http_remote = (strpos($shareImg, 'http://') === false);
        //不存在https://
        $not_https_remote = (strpos($shareImg, 'https://') === false);
        if ($not_http_remote && $not_https_remote) {
            //本地url
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $a=substr($shareImg , 0 , 2);
            $shareImg =  $http_type.$_SERVER['HTTP_HOST']. $shareImg;
        }
        //判断下架商品提示
        $shelf=0;
        if(intval($good['status'])==0){
            if( S('shelf'.$good['id'].'goods'.$uid)){
                $shelf= S('shelf'.$good['id'].'goods'.$uid);
            }else{
                $shelf=1;
            }
        }
//        dump($shelf);exit;
        //QQ客服
        $mapSer['status']=1;
        $service=D('service')->where($mapSer)->find();
        $this->assign('service',$service);

        $this->assign('shelf',$shelf);
        $this->assign("signPackage",$signPackage);
        $this->assign('good',$good);
        $this->assign('share_img',$shareImg);
        $this->assign('is_pay',$is_pay);
        $this->assign('is_login',$uid);
        $this->assign('commentAll',$commentAll);
        $this->assign('commentCount',$commentCount);
        $this->display();
    }

    public function getGoods($type='common')
    {
        $uid = is_login();
        if (!$uid) {
            $this->ajaxReturn('error');
        }
        $goodsModel = D('Goods');
        if ($type == 'hot') {
            $good = $goodsModel->getHotGoods();
        } else {
            $good = $goodsModel->getAllGoods();
        }
        $ids = array_column($good, 'id');
        $goods = array();
        foreach ($ids as $key => $v) {
            $goods[] = $goodsModel->getGoods($v);
            $goods[$key]['img'] = getThumbImageById($goods[$key]['pictures'],160,160);
        }
        $this->ajaxReturn($goods);
    }

    public function searchGoods()
    {
        $aPage = I('get.page', 1, 'intval');
        $aCount = I('get.count',10,'intval');
        $aGoodsName = I('get.keywords', '', 'text');
        $map['status']=1;
        $map['name']=array('like', '%' . $aGoodsName . '%');
        $goods = D('Mall/Goods')->where($map)->page($aPage, $aCount)->select();
        foreach ($goods as &$v) {
            $v['pictures'] = explode(',', $v['pictures']);
            $v['img'] = getThumbImageById($v['pictures'][0],160,160);
        }
        unset($v);
        $this->ajaxReturn($goods);
    }
    public function bought()
    {
        $this->setTitle('已购');
        $this->setKeywords("已购");
        $this->setDescription("已购");
        $uid=is_login();
        $map['uid']=is_login();
        $map['is_pay']=array('eq',1);
        $book_id=D('order_goods')->order('pay_time desc')->where($map)->select();
        foreach ($book_id as $key=>$vo){
            $book_id['goods_id'][$key]['goods_id']=explode(',',$vo['goods_id']);
        }
        unset($key);
        unset($vo);
        $i=0;
        foreach ($book_id['goods_id'] as $v) {
            foreach ($v['goods_id'] as $key => $vo) {
                $book_message[$i] = D('mall_goods')->where(array('id' => $vo))->find();
                $i++;
            }
        }
        unset($v);
        unset($key);
        unset($vo);
        foreach ($book_message as $key => $v) {
        $book_message[$key]['pictures'] = explode(',', $book_message[$key]['pictures']);
        $book_message[$key]['img'] = getThumbImageById($book_message[$key]['pictures'][0],160,200);
        }
        unset($key);
        unset($v);
        $this->assign('book_list',$book_message);
        $this->assign('aType',S('aType'.$uid));
        $this->display();
    }
    public function loadBought(){
        $aType=I('post.type','flow','text');
        $aPage=I('post.page',1,'intval');
        $aSort=I('post.sort',0,'intval');
        $uid=is_login();
        $map['uid']=$uid;
        $map['is_pay']=array('eq',1);
        if($aType=='0'){
            $aType=S('aType'.$uid);
        }
        if($aSort){
            $book_id=D('order_goods')->order('pay_time')->page($aPage,10)->where($map)->select();
        }else{
            $book_id=D('order_goods')->order('pay_time desc')->page($aPage,10)->where($map)->select();
        }
        foreach ($book_id as $key=>$vo){
            $goodsIds[$key]=explode(',',$vo['goods_id']);
        }
        unset($key);
        unset($vo);
        $i=0;
        foreach ($goodsIds as $key =>$v) {
            foreach ($v as $vo) {
                $book_message[$i] = D('mall_goods')->where(array('id' => $vo))->find();
                $book_message[$i]['time'] = D('mall_goods_audio')->where(array('goods_id' => $book_message[$i]['id']))->getfield("time");
                if( $book_message[$i]['status']==-1){
                    unset($book_message[$i]);
                }else{
                    $i++;
                }
            }
        }
        unset($key);
        unset($v);
        unset($vo);
        if($book_id){
            foreach ($book_message as $key=>$vo){
                $book_message[$key]=D('mall_goods')->where(array('id'=>$vo['id']))->find();
                $book_message[$key]['pictures'] = explode(',', $book_message[$key]['pictures']);
                $book_message[$key]['img'] = getThumbImageById($book_message[$key]['pictures'][0],160,200);
                $user=D('member')->where(array('uid'=>$vo['uid']))->getField('nickname');
                $book_message[$key]['writer_name']=$user;
                $book_message[$key]['buy_time']=date('Y-m-d ',D('Goods')->getPayTime($uid,$vo['id']));
            }
            unset($key);
            unset($vo);
            if($book_message){
                if($aType=='list') {
                    S('aType'.$uid,'list');
                    $this->assign('book_list', $book_message);
                    $html = $this->fetch('_boughtlist');
                    $arr = array('data' => $html, 'status' => 1, 'info' => '获取成功');
                    $this->ajaxReturn($arr);
                }else{
                    S('aType'.$uid,'flow');
                    $this->assign('book_list', $book_message);
                    $html = $this->fetch('_boughtflow');
                    $arr = array('data' => $html, 'status' => 1, 'info' => '获取成功');
                    $this->ajaxReturn($arr);
                }
            }
        }else{
            $arr = array('data' => '', 'status' => 1, 'info' => '获取失败');
            $this->ajaxReturn($arr);
        }


    }


    public function article()
    {
        $aId = I('get.id',0,'intval');
        $good = D('Goods')->getGoods($aId);
        $page=I('post.page',-1,'intval');

        $uid = is_login();
        if (!$uid) {
            $this->error("请登录",U('mall/index/detail',array('id' =>$aId)));
        }


        S('goods_' . $aId, null);
        $this->setTitle($good['name']);
        $this->setKeywords($good['name']);
        $this->setDescription($good['name']);
        $good['content'] = M('mall_goods_article')->where(array('goods_id'=>$aId))->getField('content');
        $content=$good['content'];
        $goods=D('Goods')->paging2($content);
        $goodsAll='';
        for($i=0;$i<count($goods);$i++){
            S('goods.content'.$i, $goods[$i]);
            $goodsAll=$goodsAll.$goods[$i];
        }
        $goods['length']=count($goods);
        $goods['page']=1;
        if($page!=-1){
            $res['status']=1;
            $res['content']=S('goods.content'.$page);
            $res['page']=($page);
            $this->ajaxReturn($res);
        }
        $good['pictures'] = (int)$good['pictures'];
        $appid=modC('APP_ID','','weixin');
        $appsecret=modC('APP_SECRET','','weixin');
        $jssdk = new \JSSDK ($appid,$appsecret);
        $signPackage = $jssdk->GetSignPackage();
        $shareImg  = getThumbImageById($good['pictures'],300,300);
        //不存在http://
        $not_http_remote = (strpos($shareImg, 'http://') === false);
        //不存在https://
        $not_https_remote = (strpos($shareImg, 'https://') === false);
        if ($not_http_remote && $not_https_remote) {
            //本地url
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $shareImg =  $http_type.$_SERVER['HTTP_HOST']. $shareImg;
        }
        $is_pay=D('Order/OrderGoods')->isPayed($uid,$aId);
        if(!$is_pay&&$goods['price']!=0){
            $this->error("请先购买",U('mall/index/detail',array('id' =>$aId)));
        }
        $this->assign('share_img',$shareImg);
        $this->assign("signPackage",$signPackage);
        $this->assign('good',$good);
        $this->assign('content',$content);
        $this->assign('goodsAll',$goodsAll);
        $this->assign('goods',$goods);
        $this->display();
    }
    //热门搜索
    public function search(){
        $change=D("mall_goods");  //换一批之后，搜索页面显示的热门搜索内容
        $parme=array('status'=>'1');
        if(IS_POST){
            $page=I("post.page","","intval");
            $result=$change->where($parme)->order("views desc")->page($page,8)->getfield("name",true);
            $myId=$change->where($parme)->order("views desc")->page($page,8)->getfield("id",true);
            if($result==null){
                $result=$change->where($parme)->order("views desc")->page(1,8)->getfield("name",true);
                $myId=$change->where($parme)->order("views desc")->page(1,8)->getfield("id",true);
                $res['is']=0;
            }

            $res['status']="1";
            $res['data']=$result;
            $res['id']=$myId;
            $this->ajaxReturn($res);
        }
        else{
            $history=D("mall_search"); //点击搜索之后，进入到搜索页面显示的热门搜索内容
            $result=$change->where($parme)->order("views desc")->page(1,8)->select();
            $this->assign("result",$result);
            $historyResult=$history->where(array("uid"=>get_uid()))->order("create_time desc")->limit(3)->select();
            $this->assign("historyResult",$historyResult);
            $this->display();
        }
    }

    public function allSearch(){
        $is=0;
        $title=I("post.title","","text");
        $parame['status']=1;
        $parame['name']=array("like","%".$title."%");
        $change=D("mall_goods");
        if(I("post.is","","text")=="no"){
            $result=$change->where($parame)->select();
            if(!$result){
                $this->ajaxReturn(array("status"=>"1","html"=>"none"));
            }
            foreach ($result as &$val) {
                $val['img']=getThumbImageById($val['pictures'],160,160);
            }
            unset($val);
            $this->assign("goods", $result);
            $html = $this->fetch('_list');
            $this->ajaxReturn(array("status" => "1","html" => $html));
        }
        $result=$change->where($parame)->select();
        $history=D("mall_search");
        $historyContent=$history->where(array('uid'=>get_uid()))->select();
        foreach ($historyContent as $val){
            if($val["historical"]==$title){
                $is=1;
            } ;
        }
        if($is==0){
            $goods["uid"]=get_uid();
            $goods["historical"]=$title;
            $goods["create_time"]=time();
            $history->add($goods);
        }

        $historyResult=$history->where(array("uid"=>get_uid()))->order("create_time desc")->limit(3)->select();

        $this->assign("historyResult",$historyResult);
        $myHtml=$this->fetch("_history");
        if(!$result){
            $this->ajaxReturn(array("status"=>"1","html"=>"none","goods"=>$myHtml));
        } else {
            foreach ($result as &$val) {
                $val['img']=getThumbImageById($val['pictures'],160,160);
            }
            unset($val);
            $this->assign("goods", $result);
            $html = $this->fetch('_list');
            $this->ajaxReturn(array("status" => "1", "html" => $html,"goods"=>$myHtml));
        }
    }

    /**
     * delete
     * 删除一条历史记录
     * @auth wb
     */
    public function delete(){
        $history=D("mall_search");
        $id=I("post.id","","intval");
        $history->where(array("uid"=>get_uid(),"id"=>$id))->delete();
        $historyResult=$history->where(array("uid"=>get_uid()))->order("create_time desc")->limit(3)->select();
        $this->assign("historyResult",$historyResult);
        $myHtml=$this->fetch("_history");
        $this->ajaxReturn(array("status" => "1", "html" => $myHtml));
    }

    /**
     * allDelete
     * 清空历史记录
     * @auth wb
     */
    public function allDelete(){
        $history=D("mall_search");
        $history->where(array("uid"=>get_uid()))->delete();
        $this->ajaxReturn(array("status" => "1"));
    }
    /**
     * goodslist
     * 商品列表页面
     * @auth swf
     */
    public function goodslist()
    {
        $aType = I('get.type',0,'intval');
        $cateList = D('Mall/GoodsCategory')->getCategory();
        $goodsModel = D('Goods');
        if (!empty($aType)) {
            $temp = array_column($cateList,'id');
            if (!in_array($aType,$temp)) {
                $this->error('参数错误~');
            }
        }
        if($aType==0){
            $totalCount=$goodsModel->where(array('status'=>1))->count();
        }else{
            $totalCount=$goodsModel->where(array('cate'=>$aType,'status'=>1))->count();
        }
        $carCount=D('GoodsCar')->where(array('uid'=>is_login()))->count();
        $this->assign('totalCount',$totalCount);
        $this->assign('carCount',$carCount);
        $this->assign('type',$aType);
        $this->display();
    }
   //加载更多和排序
    public function loadList(){
        $aSort=I('post.sort',"update_time desc",'text');
        $aPage=I('post.page',1,'intval');
        $aType=I('post.type',1,'intval');
        $price=I('post.price',0,'intval');
        $cateGoods = array();
        $goodsModel = D('Goods');
        $cateIds = $goodsModel->getSortGoods($aType,$aSort,$aPage,$price);
        $cateId = array_column($cateIds,'id');
        foreach ($cateId as $key => $v) {
            S('goods_'.$v ,null);
            $cateGoods[] = $goodsModel->getGoods($v);
            $cateGoods[$key]['is_pay']=D('Order/OrderGoods')->ispayed(is_login(), $cateGoods[$key]['id']);
            $cateGoods[$key]['pictures'] = explode(',', $cateGoods[$key]['pictures']);
            $cateGoods[$key]['img'] = getThumbImageById($cateGoods[$key]['pictures'][0],120,160);
            $cateGoods[$key]['is_car']=D('Mall/GoodsCar')->getCarCount($v);
        }
        if($cateGoods){
            $this->assign('cate_goods',$cateGoods);
            $html=$this->fetch('_catelist');
            $arr=array('data'=>$html,'status'=>1,'info'=>'获取成功');
            $this->ajaxReturn($arr);
        }else{
            $arr=array('data'=>'','status'=>1,'info'=>'获取失败');
            $this->ajaxReturn($arr);
        }
    }
    /**
     * @auth sun  slf02@ourstu.com
     * @return 加入购物车
     */
    public function joinCar(){
        if(!is_login()){
            $this->error('请先登录');
        }
        $id=I('post.id',0,'intval');
        $res=D('Mall/GoodsCar')->addCar($id);
        if($res){
            $this->ajaxReturn(array('status'=>1));
        }else{
            $this->ajaxReturn(array('status'=>0));
        }
    }
    public function carList(){
        $model=D('Mall/GoodsCar');
            $goodsModel = D('Goods');
            $cateGoods = array();
            $list=$model->where(array('uid'=>get_uid()))->field('goods_id')->select();
            foreach ($list as $key=>$v){
                S('goods_'.$v['goods_id'] ,null);
                $cateGoods[] = $goodsModel->getGoods($v['goods_id']);
                $cateGoods[$key]['pictures'] = explode(',', $cateGoods[$key]['pictures']);
                $cateGoods[$key]['img'] = getThumbImageById($cateGoods[$key]['pictures'][0],120,160);
            }
            $this->assign('car',$cateGoods);
            $totalCount=$model->where(array('uid'=>get_uid()))->count();
            $this->assign('totalCount',$totalCount);
            $this->display();
    }
    public function deleteGoods(){
        $goodsIds=$_POST['goods_id'];
        foreach ($goodsIds as $key=>$vo){
            $result[$key]=D('GoodsCar')->removeCar($vo);
        }
        $r=true;
        for($i=0;$i<count($result);$i++){
            if($result[$i]!=1){
                $r=false;
                break;
            }
        }
        if($r){
            $res['status']=1;
            $res['info']='删除成功';
        }else{
            $res['status']=0;
            $res['info']='删除失败';
        }
        $this->ajaxReturn($res);
    }
    public function moveCollect(){
        if (!is_login()) {
            $this->error("请登陆后再收藏。");
        }
        $row = I('post.goods_id');
        if(empty($row)){
            $this->error("没有可以收藏的商品");
        }
        $a=0;
        $b=count($row);
        $map['uid'] = is_login();
        foreach ($row as $key=>$vo){
            $map['row']=$vo;
            $collect['row']=$vo;
            if(D('Collect')->where($map)->count()){
                $a++;
            }else{
                $collect['module'] = 'mall';
                $collect['table'] = 'collect';
                $collect['uid'] = is_login();
                $collect['create_time'] = time();
                $result[$key]=D('Collect')->where($collect)->add($collect);
            }
        }
        $this->success("其中".$a."件已收藏，收藏成功".($b-$a)."件");
    }

    //商品的评价
    public function addGoodsComment()
    {
        if (!is_login()) {
            $this->error('请您先登录', U('Ucenter/member/login'), 1);
        }
        if (IS_POST) {
            $aContent = I('post.content', '', 'text');              //说点什么的内容
            $goodsId = I('post.goods_id', 0, 'intval');
            $uId=is_login();
            if (empty($aContent)) {
                $this->error('评论内容不能为空。');
            }
            $isComment=D('Goods')->isCommentSeniority($uId,$goodsId);
            if(!$isComment){
                $this->error('你没有权限评论。');
            }
            $data['create_time']=time();
            $data['uid']=intval($uId);
            $data['goods_id']=$goodsId;
            $data['comment']=text($aContent);
            $data['status']=1;
            $result=D('goods_comment')->data($data)->add();
            if($result){
                $commentAll= D('Goods')->getCommentByGoodsId($goodsId);
                $this->assign('commentAll',$commentAll);
                $res['status']=1;
                $res['html']=$this->fetch('_judge');
                $res['info']='评论成功';
            }else{
                $res['status']=0;
                $res['info']='评论失败';
            }
            $this->ajaxReturn($res);
        }
    }

    //查看该商品所有的评论
    public function seeAllComment(){
        if (!is_login()) {
            $this->error('请您先登录', U('Ucenter/member/login'), 1);
        }
        $goods_id=I('get.id',0,'intval');
        $page=I('get.id',1,'intval');
        $goods=D('mall_goods')->where(array('id'=>$goods_id))->find();
        $commentAll=D('Goods')->getCommentByGoodsId($goods_id,2);
        $count=count($commentAll);
        $this->assign("goods",$goods);
        $this->assign("commentAll",$commentAll);
        $this->assign("count",$count);
        $this->display();
    }
    public function addListComment(){
        $aPage=I('post.page',1,'intval');
        $goodsId=I('post.data',0,'intval');
        $commentAll=D('Goods')->getCommentByGoodsId($goodsId,1,$aPage);
        if($commentAll){
            $this->assign('commentAll',$commentAll);
            $html=$this->fetch('_judge');
            $arr=array('data'=>$html,'status'=>1,'info'=>'获取成功');
            $this->ajaxReturn($arr);
        }else{
            $arr=array('data'=>'','status'=>1,'info'=>'获取失败');
            $this->ajaxReturn($arr);
        }
    }

    public function shelfPrompt(){
        $uid=is_login();
        $id=I('post.id',0,'intval');
        S('shelf'.$id.'goods'.$uid,2);
    }

    //修改首页
    public function themeType(){
        $type=I("post.data",0,'intval');
        $map['theme_id']=$type;
        $res=D('theme')->where(array('id'=>1))->data($map)->save();
        if($res){
            $data['status']=1;
            $data['info']='修改成功';
        }else{
            $data['status']=0;
            $data['info']='修改失败';
        }
        $this->ajaxReturn($data);
    }
    //笔记
    public function addNote(){
        if (!is_login()) {
            $this->error('请您先登录', U('Ucenter/member/login'), 1);
        }
        if (IS_POST) {
            $aContent = I('post.content', '', 'text');
            $position = I('post.position', 0, 'intval');
            $goodsId = I('post.goods_id', 0, 'intval');
            $uId=is_login();
            if (empty($aContent)) {
                $this->error('评论内容不能为空。');
            }
            $data['note_time']=time();
            $data['uid']=intval($uId);
            $data['position']=$position;
            $data['goods_id']=$goodsId;
            $data['note']=text($aContent);
            $result=D('note')->data($data)->add();
            if($result){
                $res['status']=1;
                $res['info']='笔记保存成功';
            }else{
                $res['status']=0;
                $res['info']='笔记保存失败';
            }
            $this->ajaxReturn($res);
        }
    }
    //客服
    public function getService(){
        $map['status']=1;
        $service=D('service')->where($map)->find();
        dump($service);
        $this->assign('service',$service);
    }
}
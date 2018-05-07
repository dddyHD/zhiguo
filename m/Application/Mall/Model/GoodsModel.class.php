<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2017/1/4
 * Time: 9:30
 */
namespace Mall\Model;

use Think\Model;

class GoodsModel extends Model {

    protected $tableName = 'mall_goods';
    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function getGoods($id)
    {
        $goods = S('goods_' . $id);
        $check_empty = empty($goods);
        if ($check_empty) {
            $goods = $this->where(array('id' => $id))->find();
            if (!$goods) {
                return null;
            }
            S('goods_' . $id, $goods);
        }
        return $goods;
    }

    //获得所有商品的id
    public function getAllGoods(){
        $ids= $this->where('status!=-1')->field('id')->select();
        return $ids;
    }

    public function getAllGoodsByPage(){
        $ids= $this->order('update_time desc')->where('status=1')->field('id')->limit(5)->select();
        return $ids;
    }

    public function getHotGoods()
    {
        $ids = S('mall_goods_list');
        if (empty($ids)) {
            $ids = $this->order('update_time desc')->where(array('status'=>array('neq',-1),'is_hot'=>1))->field('id')->limit(10)->select();
            S('mall_goods_list',$ids);
        }
        return $ids;
    }

    public function getCateGoods($type = '')
    {
        $ids = $this->where(array('cate' => $type, 'status' => 1))->order('update_time desc')->field('id')->select();
        return $ids;
    }

    public function getSortGoods($type='',$sort='new',$page,$price){
        if($type){
            if($sort=='price'){
                if($price){
                    $ids = $this->where(array('cate' => $type, 'status' => 1))->order('price desc')->page($page,10)->field('id')->select();
                }else{
                    $ids = $this->where(array('cate' => $type, 'status' => 1))->order('price ')->page($page,10)->field('id')->select();
                }
            }elseif ($sort=='sales'){
                $ids = $this->where(array('cate' => $type, 'status' => 1))->order('sales ')->page($page,10)->field('id')->select();
            }else{
                $ids = $this->where(array('cate' => $type, 'status' => 1))->order('update_time desc')->page($page,10)->field('id')->select();
            }
        }else{
            $ids = $this->where(array('status' => 1))->order('update_time desc')->page($page,10)->field('id')->select();
            if($sort=='price'){
                if($price){
                    $ids = $this->where(array('status' => 1))->order('price desc')->page($page,10)->field('id')->select();
                }else{
                    $ids = $this->where(array('status' => 1))->order('price ')->page($page,10)->field('id')->select();
                }
            }elseif ($sort=='sales'){
                $ids = $this->where(array('status' => 1))->order('sales ')->page($page,10)->field('id')->select();
            }else{
                $ids = $this->where(array('status' => 1))->order('update_time desc')->page($page,10)->field('id')->select();
            }
        }
        return $ids;
    }

    public function editData($data)
    {
        //检测show是否为空
        if (!mb_strlen($data['show'], 'utf-8')) {
            //msubstr字符串截取
            $data['show'] = msubstr(text($data['content']), 0, 200);
        }
        $detail['content'] = $data['content'];
        if ($data['id']) {
            $data['update_time'] = time();
            $res = $this->save($data);
            $detail['goods_id'] = $data['id'];
        } else {
            $data['create_time'] = $data['update_time'] = time();
            $res = $this->add($data);
            //action_log行为标识,触发行为的模型名,触发行为的记录id,执行行为的用户id ; is_login检测用户是否登入
            $detail['goods_id'] = $res;
        }
        if ($res) {
            D('Mall/GoodsArticle')->editData($detail);
        }
        return $res;
    }

    //判断是否对商品有评论的资格
    public function  isCommentSeniority($uid,$goods_id){
        $map['is_pay']=1;
        $map['uid']=$uid;
        $goodsId=D('order_goods')->where($map)->getField('goods_id',true);
        $isComment=false;
        $goodsPrice=D('mall_goods')->where(array('id'=>$goods_id,'status'=>1))->getField('price');
        if($goodsPrice==0){
            $isComment=true;
            return $isComment;
        }
        foreach ($goodsId as $key=>$vo){
            $book_id[$key]=explode(',',$vo);
            foreach($book_id[$key] as $v){
                if($goods_id==$v){
                    $isComment=true;
                    break;
                }
            }
        }
        return $isComment;
    }
    //根据商品ID获取该商品评论信息和评论者的信息
    public function getCommentByGoodsId($goodsId,$type=0,$page=1){
        $map['goods_id']=$goodsId;
        $map['status']=1;
        switch ($type){
            case 0:  $comment=D('goods_comment')->order('create_time desc')->where($map)->limit(2)->select();break;
            case 1:  $comment=D('goods_comment')->order('create_time desc')->where($map)->page($page,10)->select();break;
            case 2:  $comment=D('goods_comment')->order('create_time desc')->where($map)->select();break;
            default: $comment=D('goods_comment')->order('create_time desc')->where($map)->limit(2)->select();
        }
        $commentAll=null;
        foreach ($comment as $key=>$vo){
            $user[$key]=query_user(array('avatar64', 'nickname'),$comment[$key]['uid']);
            $commentAll[$key]=array_merge($comment[$key],$user[$key]);
        }
        unset($key,$vo);
        return $commentAll;
    }
    //文章分页
    public function paging($content)
    {
        preg_match_all("/<.+?(.+?)<\/.+?>/",$content,$goods);
        foreach ($goods[0] as $key=> $vo){
            $first=strpos($vo,'</');
            if($first==0){
                $first=strpos($vo,'>');
                $close=mb_substr($vo,0,$first+1,'utf-8');
                $goods[0][$key]=mb_substr($vo,$first+1,strlen($vo)-($first+1),'utf-8');
                $goods[0][$key-1]=$goods[0][$key-1].$close;
            }
        }
        unset($key,$vo);
        $i=0;
        $paging[0]='';
        $pageCount=1200;//一页的字数
        $line=0;//判断行不能太多
        $lineNumber=21;//一行的字数
        foreach ($goods[0] as $key=>$vo){
            $vo=str_replace('&nbsp;',' ',$vo);
            $pagingLength=strlen(text($paging[$i]));
            $voLength=strlen(text($vo));
            $length=$pagingLength+$voLength;
            preg_match_all("/<.+?>/",$vo,$voFirst);
            preg_match_all("/<\/.+?>/",$vo,$voLast);
            $count=ceil(strlen(text($vo))/$lineNumber);
            $line=+$count;
           if($pagingLength<$pageCount){
               if($length>($pageCount*2)){
                   if($line>=18) {
                       $count1 = $line - 20;
                       $division1 = mb_substr(text($vo), 0, $count1 * $lineNumber, 'utf-8');
                       $division2 = mb_substr(text($vo), $count1 * $lineNumber, strlen(text($vo)), 'utf-8');
                       $paging[$i] = $paging[$i] . $voFirst[0][0] . $division1 . $voLast[0][0];
                       $i++;
                       $imgCount = 0;
                       $paging[$i] = '';
                       $paging[$i] = $paging[$i] . $voFirst[0][0] . $division2 . $voLast[0][0];
                   }
                   $vo1=mb_substr(text($vo),0,($pageCount-$pagingLength)/3,'utf-8');
                   $vo2=mb_substr(text($vo),($pageCount-$pagingLength)/3,strlen(text($vo)),'utf-8');
                   $surplus=explode('。',$vo2);
                   $vo3='';
                   foreach ($surplus as  $k=>$r) {
                       if($k==0){
                           continue;
                       }
                       $vo3=$vo3.$r;
                   }
                   $paging[$i]=$paging[$i].$voFirst[0][0].$vo1.$surplus[0].'。'.$voLast[0][0];
                   $rest=str_split($vo3,$pageCount);
                   foreach ($rest as $r) {
                       $i++;
                       $paging[$i] = '';
                       $paging[$i] = $paging[$i] . $voFirst[0][0] . text($r) . $voLast[0][0];
                   }
                   unset($r);
               }else{
                   if($line>=18){
                       $count1=$line-20;
                       $division1=mb_substr(text($vo),0,$count1*$lineNumber,'utf-8');
                       $division2=mb_substr(text($vo),$count1*$lineNumber,strlen(text($vo)),'utf-8');
                       $paging[$i]=$paging[$i].$voFirst[0][0].$division1.$voLast[0][0];
                       $i++;
                       $paging[$i]='';
                       $paging[$i]=$paging[$i].$voFirst[0][0].$division2.$voLast[0][0];
                       $line=0;
                   }else{
                       $paging[$i]=$paging[$i].$vo;
                   }
               }
           }else{
               $i++;
               $paging[$i] = '';
               $paging[$i]=$paging[$i].$vo;
           }
        }
        unset($key,$vo);
        foreach($paging as $key=>$vo){
            if(text($vo)==''){
                unset($paging[$key]);
            }
       }
      return $paging;
    }
    public function paging2($content)
    {
        preg_match_all("/<.+?(.+?)<\/.+?>/", $content, $goods);
        foreach ($goods[0] as $key => $vo) {
            $first = strpos($vo, '</');
            if ($first == 0) {
                $first = strpos($vo, '>');
                $close = mb_substr($vo, 0, $first + 1, 'utf-8');
                $goods[0][$key] = mb_substr($vo, $first + 1, strlen($vo) - ($first + 1), 'utf-8');
                $goods[0][$key - 1] = $goods[0][$key - 1] . $close;
            }
        }
        unset($key, $vo);
        $i = 0;
        $paging[0] = '';
        $j = 0;
        $line[$j] = 0;
        $lineNumber = 22;//一行的字数
        $pageLine = 18;//一页的行数
        $imgCount = 0;//用于一页最多存2张图片
        foreach ($goods[0] as $key => $vo) {
            $vo = str_replace('&nbsp;', ' ', $vo);
            preg_match_all("/<.+?>/", $vo, $voFirst);
            preg_match_all("/<\/.+?>/", $vo, $voLast);
            $count = intval(ceil(strlen(text($vo)) / $lineNumber / 3));
            $is_img = strpos($vo, 'img');
            if ($is_img) {
                $str='';//用于图片前的文字
                $img_position = strpos($vo, '<img');
                preg_match_all("/<img (.+?)>/", $vo, $img);
                foreach ($img[0] as $m => $n) {
                    $imgCount++;
                    $img_first = strpos($vo, '>');
                    if ($imgCount >= 2) {
                        $paging[$i] .=$voLast[0][0];
                        $i++;
                        $imgCount = 0;
                        $paging[$i] = '';
                    }
                    if($img_first!=0){
                        $str=mb_substr($vo,0,$img_position-12,'utf-8');
                    }
                    $paging[$i] = $voFirst[0][0] .$str. $paging[$i].$n;
                }
                $paging[$i] .=$voLast[0][0];
                unset($m,$n);
            } else {
                if ($count == 0) {
                    $count = 1;
                }
                $storage = $line[$j];
                $line[$j] = $line[$j] + $count;
                if ($line[$j] >= $pageLine) {
                    $count1 = $line[$j] - $pageLine;
                    $division1 = mb_substr(text($vo), 0, ($pageLine - $storage) * $lineNumber, 'utf-8');
                    $divisionOver = mb_substr(text($vo), ($pageLine - $storage) * $lineNumber, (strlen(text($vo)) - $count1 * $lineNumber) * 3, 'utf-8');
                    $surplus = explode('。', $divisionOver);
                    $division2 = '';
                    foreach ($surplus as $k => $r) {
                        if ($k == 0) {
                            $division1 = $division1 . $r;
                            if (strripos($division1, '。') != strlen($division1)) {
                                $division1 = $division1 . '。';
                            }
                        } else {
                            $division2 = $division2 . $r;
                        }
                    }

                    $paging[$i] = $paging[$i] . $voFirst[0][0] . $division1 . $voLast[0][0];
                    //特殊截取下来的还是存在很多的时候
                    $count2 = intval(ceil($count1 / $pageLine));
                    $count3 = $count1 - $pageLine * ($count2 - 1);
                    $startPage = 0;
                    for ($m = 0; $m < $count2; $m++) {
                        $i++;
                        $imgCount = 0;
                        $paging[$i] = '';
                        $division3[$m] = mb_substr(text($division2), $pageLine * $lineNumber * $m + $startPage, $pageLine * $lineNumber, 'utf-8');
                        $divisionOver1 = mb_substr(text($division2), $pageLine * $lineNumber * ($m + 1), $pageLine * $lineNumber, 'utf-8');
                        $surplus = explode('。', $divisionOver1);
                        $division2 = '';
                        foreach ($surplus as $k => $r) {
                            if ($k == 0 && $r != null) {
                                $division3[$m] = $division3[$m] . $r . '。';
                                $startPage = strlen($r) + 1;
                            }
                        }
                        $paging[$i] = $paging[$i] . $voFirst[0][0] . $division3[$m] . $voLast[0][0];
                    }
                    $j++;
                    $line[$j] = $line[$j] + $count3;
                } else {
                    $paging[$i] = $paging[$i] . $vo;
                }
            }
            unset($key, $vo);
            foreach ($paging as $k => &$v) {
                $is_paging_img = strpos($v, 'img');
                if (text($v) == ''&&!$is_paging_img) {
                    unset($paging[$k]);
                }else{
                    $v = preview_image($v);
                }
            }
            unset($k,$v );
        }
        return $paging;
    }
    //获取购买时间
    public function  getPayTime($uid,$goods_id){
        $order=D('order_goods')->where(array('uid'=>$uid,'is_pay'=>1))->select();
        $pay_time=0;
        $is_find=false;
        foreach ($order as $v){
            if($is_find){
                break;
            }
            $goodsId= explode(',', $v['goods_id']);
            foreach ($goodsId as $vo){
                if($vo==$goods_id){
                    $pay_time=$v['pay_time'];
                    $is_find=true;
                    break;
                }
            }
        }
        return $pay_time;
    }
}
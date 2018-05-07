<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/29
 * Time: 14:56
 */

namespace Ucenter\Model;


use Think\Model;

class NoteModel extends Model
{
    public function getNoteByUid($aUid){
        $note=D('note')->where(array('uid'=>$aUid))->select();
        return $note;
    }
    public function  getNoteGoodsId($aUid){
        $note=D('note')->order('note_time desc')->where(array('uid'=>$aUid))->getField('goods_id',true);
        $noteGoodsId=array_unique ($note);
        return $noteGoodsId;
    }
    public function getNoteByGoods($uid,$goods_id){
        $note=D('note')->order('note_time desc')->where(array('uid'=>$uid,'goods_id'=>$goods_id))->select();
        return $note;
    }
    public function getFriendlyData($uid,$goods_id){
        $noteTime=D('Note')->order('note_time desc')->where(array('uid'=>$uid,'goods_id'=>$goods_id))->limit(1)->getField('note_time');
        $time_age=friendlyDate($noteTime);
        return $time_age;
    }
    public  function  getNoteCount($uid,$goods_id){
        $count=D('Note')->where(array('uid'=>$uid,'goods_id'=>$goods_id))->count();
        return $count;
    }
}
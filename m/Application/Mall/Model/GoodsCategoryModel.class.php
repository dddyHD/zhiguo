<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2017/1/4
 * Time: 9:45
 */

namespace Mall\Model;

use Think\Model;

class GoodsCategoryModel extends Model
{
    protected $tableName = 'mall_goods_category';

    protected $_auto = array(
        array('create_time', NOW_TIME, self::MODEL_INSERT)
    );

    public function getCategory()
    {
        $map['status']=array('neq',-1);
        $map['pid'] = 0;
        $cate = $this->where($map)->select();
        return $cate;
    }

    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('egt', 0));
        $list = $this->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);


        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }
}





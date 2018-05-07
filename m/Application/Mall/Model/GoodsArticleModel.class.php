<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/3
 * Time: 19:17
 */

namespace Mall\Model;

use Think\Model;

class GoodsArticleModel extends Model
{
    protected $tableName = 'mall_goods_article';
    public function editData($data = array())
    {
        if ($this->find($data['goods_id'])) {
            $res = $this->save($data);
        } else {
            $res = $this->add($data);
        }
        return $res;
    }

}
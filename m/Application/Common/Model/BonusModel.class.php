<?php
/**
 * Created by PhpStorm.
 * User: Yixiao Chen
 * Date: 2015/4/30 0030
 * Time: 下午 3:39
 */

namespace Common\Model;


use Think\Model;

class BonusModel extends Model
{
    protected $tableName = 'bonus';

    /**
     * @param $total    生成随机红包
     * @param $num
     * @return array
     * @author:xjw129xjt(駿濤) xjt@ourstu.com
     */
    public function luck($total, $num)
    {
        $min = 0.01;//每个人最少能收到0.01元
        $rs = array();
        for ($i = 1; $i < $num; $i++) {
            $safe_total = ($total - ($num - $i) * $min) / ($num - $i);//随机安全上限
            $money = mt_rand($min * 100, $safe_total * 100) / 100;
            $total = $total - $money;
            $rs[] = $money;
        }
        $rs[] = $total;

        shuffle($rs);  //打乱红包顺序
        $rs = array_map(function ($v) {
            return number_format($v, 2, ".", "");  // 格式化金额，保留两位小数
        }, $rs);
        return $rs;
    }

    /**
     * @param $fee    生成等额红包
     * @param $num
     * @return array
     * @author:xjw129xjt(駿濤) xjt@ourstu.com
     */
    public function same($fee, $num){
        $rs=array_fill(0,$num,$fee);   //填充数组

        $rs = array_map(function ($v) {
            return number_format($v, 2, ".", "");  // 格式化金额，保留两位小数
        }, $rs);

        return $rs;
    }


}
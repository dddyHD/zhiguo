<?php

//价格区间计算
function priceRange($range)
{
    $d0 = intval($range['min'],-2);
    $d1 = intval(($range['min']+$range['avg'])/2);
    $d2 = intval($range['avg']);
    $d3 = intval(($range['max']+$range['avg'])/2);
    $d4 = intval($range['max']);

    if($d4>$d3 && $d3>$d2 && $d2>$d1 && $d1>$d0){
        $d1 = formatInt($d1);
        $d2 = formatInt($d2);
        $d3 = formatInt($d3);
        $d4 = formatInt($d4);
        $price_range[0] = '0-'.$d1;
        if($d2-$d1>2) $price_range[1] = $d1.'-'.($d2-1);
        else $price_range[1] = $d1.'-'.$d2;
        if($d3-$d2>2) $price_range[2] = $d2.'-'.($d3-1);
        else $price_range[2] = $d2.'-'.$d3;
        if($d4-$d3>2) $price_range[3] = $d3.'-'.($d4-1);
        else $price_range[3] = $d3.'-'.$d4;
        $price_range[4] = "$d4";
        return $price_range;
    }else{
        if($d2!=0){
            $d2 = formatInt($d2);
            if($d2>1)return array(0=>('0-'.($d2-1)),1=>"$d2");
            else return array(0=>('0-'.($d2)),1=>"$d2");
        }else if($range['min']!=0){
            return array(0=>('0-'.($range['min'])),1=>"$range[min]");
        }
        else return array();
    }
}

function formatInt($value)
{
    $len = strlen($value);
    switch ($len) {
        case 1:
            break;
        case 2:
            $value = round($value,-1);
            break;
        case 3:
        case 4:
            $value = round($value,-2);
            break;
        default:
            $value = round($value,2-$len);
            break;
    }
    return $value;
}
function group($m,$n){
    $num=0;
    $tem=array();
    for ($i=0;$i<count($m);$i++){
        for($j=0;$j<count($n);$j++){
            $tem[$num++]=$m[$i].'##'.$n[$j];
        }
    }
    return $tem;
}
/**
 * 商城规格笛卡尔积
 * $array 二位 数字下标 数组
*/
function store_decare($array){
    if($array==null){
        return null;
    }
    $typeCount=count($array);
    if($typeCount==1){
        return $array[0];
    }
    else{
        $tem=array();
        $tem=group($array[0],$array[1]);
        for($i=2;$i<$typeCount;$i++){
            $tem=group($tem,$array[$i]);
        }
        unset($i);
        $result=array();
        $tem_len=count($tem);
        $num=0;
        for($i=0;$i<$tem_len;$i++){
            $result[$num++]=explode('##',$tem[$i]);
        }
    }
    return $result;
}
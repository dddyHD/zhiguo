<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/28
 * Time: 9:57
 * @author lin <lt@ourstu.com>
 */
//获取支付配置积分信息
function get_pay_field(){
    $fields_config = modC('PAY_FIELD', "", 'order');
    $fields = json_decode($fields_config, true);
    foreach ($fields as &$v) {
        $v['score'] = D('Ucenter/Score')->getType(array('status' => 1, 'id' => $v['FIELD']));
        $v['have'] = M('member')->where(array('uid' => is_login()))->getField('score' . $v['FIELD']);
        $v['have'] = number_format($v['have'], 2, ".", "");
    }
    return $fields;
}

//获取支付积分类型
function get_pay_type($field){
    $fields_config = modC('PAY_FIELD', "", 'order');
    $fields = json_decode($fields_config,true);
    $res = array_search_key($fields,'FIELD',$field);
    return $res;
}

//获取充值积分类型
function get_re_type($field){
    $fields_config = modC('RE_FIELD', "", 'order');
    $fields = json_decode($fields_config,true);
    $res = array_search_key($fields,'FIELD',$field);
    return $res;
}

//获取提现积分类型
function get_wi_type($field){
    $fields_config = modC('WI_FIELD', "", 'order');
    $fields = json_decode($fields_config,true);
    $res = array_search_key($fields,'FIELD',$field);
    return $res;
}

//array转xml
function ToXml($data)
{
    $xml = "<xml>";
    foreach ($data as $key=>$val)
    {
        if (is_numeric($val)){
            $xml.="<".$key.">".$val."</".$key.">";
        }else{
            $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
    }
    $xml.="</xml>";
    return $xml;

}
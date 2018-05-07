<?php

return   array_merge( array(
    'action'=>array(
        'title'=>'签到绑定行为：',
        'type'=>'checkbox',
        'options'=>get_option(),
    ),
),
    get_option2(),
    array(
        'remind_text'=>array(
            'title'=>'签到提醒提示语：',
            'type'=>'text',
            'value'=>'头可破血可流，签到不能断~',			 //表单的默认值
            'tip'=>'当积分商城获取不到符合条件的商品时显示'
        ),
    )
);


function get_option(){
    $opt = D('Admin/Action')->getActionOpt();
    $return = array('no_action'=>'不绑定');
    foreach($opt as $v){
        $return[$v['name']] = $v['title'];
    }
    return $return;

}

function get_option2(){
    $type= M('ucenter_score_type');
    $opt=$type->select();
    foreach($opt as $v)
    {
        $arr[ 'score'.$v['id']] =
            array(
                'title'=>$v['title'],
                'type'=>'text',
                'value'=>0

            );


    }
    return $arr;
}
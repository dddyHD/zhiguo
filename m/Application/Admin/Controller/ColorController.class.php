<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
class ColorController extends AdminController {

    public function changeColor(){
//        #ec725d;/*theme*/ 更改主题色
        if (IS_POST){
            $nowColor=modC('color','#ec725d;','Admin');
            $color=I('post.color','','string');
            $file=file_get_contents('./Public/css/core.less');
            $file=str_replace($nowColor.'/*theme*/',$color.';/*theme*/',$file);
            $res=file_put_contents('./Public/css/core.less',$file);
            shell_exec();
        }
        $this->display();
    }

}

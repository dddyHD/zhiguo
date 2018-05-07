<?php

namespace Addons\QiNiu\Controller;
use Home\Controller\AddonsController;
class QiNiuController extends AddonsController
{

    public function getPrefop(){
        set_time_limit(0);
        $aId  =I('get.id');
        $content = $this->getInfo($aId);
        $this->ajaxReturn($content);
    }

    private function getInfo($id){

        $content = file_get_contents('http://api.qiniu.com/status/get/prefop?id='.$id);
        $array = json_decode($content,true);
        /*        if($array['code'] != 0){
                    ob_flush();
                    flush();
                    sleep(2);
                    $array = $this->getInfo($id);
                }*/
        return $array;
    }

}
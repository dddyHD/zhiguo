<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2017/1/4
 * Time: 10:48
 */

namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminTreeListBuilder;
use Common\Model\ContentHandlerModel;

class MallController extends AdminController
{
    protected $goodsModel;
    protected $goodsArticleModel;
    protected $goodsCategoryModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->goodsModel = D('Mall/Goods');
        $this->goodsArticleModel = D('Mall/GoodsArticle');
        $this->goodsCategoryModel = D('Mall/GoodsCategory');
    }

    public function index()
    {
        $aPage=I('get.page',1,'intval');
        $goodsModel=D('Mall/Goods');
        $builder=new AdminListBuilder();
        $ids=$goodsModel->page($aPage,10)->getAllGoods();
        $totalCount=count($goodsModel->getAllGoods());
        $cateList=D('Mall/GoodsCategory')->getCategory();
        $k=0;
        foreach ($ids as $v){
            $list[$k]=$goodsModel->getGoods($v['id']);
            S('goods_' .  $v['id'], null);
            if($list[$k]['is_hot']==1){
                $list[$k]['is_hot']='是';
            }elseif ($list[$k]['is_hot']==0){
                $list[$k]['is_hot']='否';
            }
            $k++;
        }

        unset($k,$v);
        $map['status']=array('egt',0);
        $r=10;
        $list = $goodsModel->where($map)->order('id desc')->page($aPage , $r)->select();
        $totalCount = $goodsModel->where($map)->count();
        foreach ($list as &$v){
            foreach ($cateList as $vo){
                S('goods_' . $vo,null);
                if($v['cate']==$vo['id']){
                    $v['cate']=$vo['title'];
                }
            }
            $v['show']=strip_tags($v['show']);
            $v['introduce']=strip_tags($v['introduce']);
        }
        unset($v,$vo);

        $attr['class'] = 'btn ajax-post';
         $attr['target-form'] = 'ids';
        $builder->title('商品列表')
            ->buttonNew(U('addGoods'))
            ->buttonDelete(U('setGoodsStatus'))
            ->buttonEnable(U('setGoodsStatus'),'上架')
            ->buttonDisable(U('setGoodsStatus'),'下架')
            ->button('设置热销商品', array_merge($attr, array('url' => U('setHotGoods',array('type'=>'1')))))
            ->button('取消热销商品', array_merge($attr, array('url' => U('setHotGoods',array('type'=>'0')))))
            ->keyId()
            ->keyUid('uid','操作者')
            ->keyText('name','商品名称')
            ->keygoodsStatus('status')
            ->keyText('price','价格')
            ->keyText('cate','商品类型')
            ->keyText('introduce','文章简介')
            ->keyText('show','文章展示内容')
            ->keyImage('pictures','封面')
            ->keyImage('banner','商品详情页图')
            ->keyUpdateTime('update_time')
            ->keyText('is_hot','是否为热销商品')
            ->keyText('profit','历史收入')
            ->keyDoActionEdit('addGoods?id=###')
            ->data($list)
            ->pagination($totalCount,$r)
            ->display();
    }

    public function setHotGoods( $type)
    {
        if ($this->_getHotGoodsNum() >= 10&&$type==1){
                $this->error('最多设置10条热销商品');
        }
        $type = $type == '1' ? true : false;
        $ids = I('post.ids','','intval');
        $id = array_unique((array)$ids);
        $rs = D('Mall/Goods')->where(array('id' => array('in', $id)))->save(array('is_hot' => $type));
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }
        foreach ($id as $v) {
            S('goods_'.$v,null);
        }
        S('mall_goods_list',null);
        $this->success(L('_SUCCESS_SETTING_'), $_SERVER['HTTP_REFERER']);
    }

    private function _getHotGoodsNum()
    {
        $count = D('Mall/Goods')->where(array('status'=>array('neq',-1),'is_hot'=>1))->count();
        return $count;
    }

    public function addGoods(){
        //todo 检测权限
        $aId=I('id',0,'intval');
        $name=$aId?L('_EDIT_'):L('_ADD_');
        if(IS_POST){
            $aId=I('post.id',0,'intval');
            $aName=I('post.name','','text');

            $aStatus=I('post.status','','text');
            $aPrice=I('post.price',0,'text');
            $aCate=I('post.cate','','text');
            $aIntroduce=I('post.introduce','',0);
            $aShow=I('post.show','',0);
            $aPictures=I('post.pictures','','');
            $aBanner=I('post.banner','','');
            $aContent=I('post.content','',0);
            $data['id']=$aId;
            $data['uid']=is_login();
            $data['name']=$aName;

            $data['status']=$aStatus;
            $data['price']=$aPrice;
            $data['cate']=$aCate;
            $data['introduce']=$aIntroduce;
            $data['show']=$aShow;
            $data['pictures']=$aPictures;
            $data['banner']=$aBanner;
            $data['content']=$aContent;
            $data['update_time']=time();

            $this->checkRight($data);
            $result=$this->goodsModel->editData($data);

            if($result){
                if(empty($aId)){
                    $this->success($name.L('_SUCCESS_'),U('index',array('id'=>$aId)));   //新增跳转的页面
                }else{
                    $this->success($name.L('_SUCCESS_'),U('',array('id'=>$aId)));   //编辑跳转的页面
                }
            }else{
                $this->error($name.L('_SUCCESS_'),$this->goodsModel->getError());
            }
        }else{
            $aId=I('id',0,'intval');
            $builder=new AdminConfigBuilder();
            $cateList=D('Mall/GoodsCategory')->getCategory();
            foreach ($cateList as $v){
                if($v['status']==1){
                    $cate[$v['id']]=$v['title'];
                }
            }
            if($aId){
                $data=D('Mall/Goods')->getGoods($aId);
                $good = S('goods_' . $aId,null);
                $data['content']=M('mall_goods_article')->where(array('goods_id'=>$aId))->getField('content');
                $builder->data($data)->title('编辑商品')->keyHidden('id');  //这里就是编辑的时候调用原来的数据
            }else{
                $builder->title('新增商品');
            }
            $builder->keyText('name','商品名称')
             ->keySelect('status','状态','',array(-1 => '删除', 0 => '下架', 1 => '上架'))->keyDefault('status',1)
             ->keyText('price','价格','人民币 元')
             ->keySelect('cate','商品种类','',$cate)
             ->keyEditor('show','文章展示内容')
             ->keyMultiImage('pictures','文章封面')
             ->keyMultiImage('banner','文章详情页图','建议尺寸1080px*720px')
             ->keyEditor('content','文章整体内容')
             ->keyEditor('introduce','文章简介')
             ->group(L('_BASIS_'),'id,uid,name,status,price,cate,introduce,pictures,banner')
             ->group(L('内容'),'show,content')
             ->buttonSubmit()->buttonBack()->display();
        }
    }

    public function setGoodsStatus($ids,$status){
        //todo 检测权限
        $map['id']=array('in',$ids);
        $res= D('Mall/Goods')->where($map)->setField('status',$status);
        foreach ($ids as $v){
            S('goods_' . $v,null);
        }
        if($res!==false){
            $this->success('编辑成功');
        }else{
            $this->error('编辑失败');
        }
    }
    public function goodsCategory(){
        $builder=new AdminTreeListBuilder();
        $tree =D('Mall/GoodsCategory')->getTree(0, 'id,title,sort,status,cate_picture');
        $builder->title(L('_GOODS_CATEGORY_'))->setLevel(1)
            ->buttonNew(U('Mall/add'))
            ->data($tree)
            ->display();
    }

    public function add($id = 0)
    {
        if (IS_POST) {
            if ($id != 0) {
                $goodsCategory = $this->goodsCategoryModel->create();
                $picture=$goodsCategory['cate_picture'];
                $picture=explode(",",$picture);
                $goodsCategory['cate_picture']=$picture[1];
                if ($this->goodsCategoryModel->save($goodsCategory)) {
                    $this->success(L('_SUCCESS_EDIT_'));
                } else {
                    $this->error(L('_FAIL_EDIT_'));
                }
            } else {
                $goodsCategory = $this->goodsCategoryModel->create();
                if ($this->goodsCategoryModel->add($goodsCategory)) {
                    $this->success(L('_SUCCESS_ADD_'));
                } else {
                    $this->error(L('_FAIL_ADD_'));
                }
            }


        } else {
            $builder = new AdminConfigBuilder();
            if ($id != 0) {
                $goodsCategory = $this->goodsCategoryModel->find($id);
            } else {
                $goodsCategory = array('pid'=>0,'status' => 1);
            }
            $builder->title('商品分类')->keyId()->keyText('title', L('_TITLE_'))
                ->keyStatus()->keyCreateTime()->keyUpdateTime()->keyText('sort','排序')->keyMultiImage('cate_picture','分类图片','建议尺寸1080px*720px')
                ->data($goodsCategory)
                ->buttonSubmit(U('add'))->buttonBack()->display();
        }

    }

    public function setStatus($ids,$status){
        //todo 检测权限
        $builder = new AdminListBuilder();
        $builder->doSetStatus('mall_goods_category', $ids, $status);
    }





    public function config()
    {
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();
        $builder->title('商城设置')
            ->keyText('MALL_NAME','商城名')
            ->keyText('MALL_INTRO','商城介绍')
            ->keySingleImage('MALL_PICTURES','商城封面')
            ->keyText('MALL_NEW_LEFT', '上新取多少天以内的，以那天零点之后为准')
            ->keyText('MALL_NEW_GOODS_NUM', '上新商品取多少条')
            ->data($data)
            ->keyDefault('MALL_NEW_LEFT', 3)
            ->keyDefault('MALL_NEW_GOODS_NUM', 8)
            ->keyDefault('MALL_NAME',modC('WEB_SITE_NAME','微社区','Config'))
            ->keyDefault('MALL_INTRO',modC('WEB_SITE_INTRO','未填写社区简介~','Config'))
            ->keyDefault('MALL_PICTURES',modC('WEB_SITE_LOGO','','Config'))
            ->buttonSubmit();
        $builder->display();
    }

    private function checkRight($data=array()){
        if(mb_strlen($data['name'],'utf-8')<1){
            $this->error(L('商品名称不能为空'));
        }
        if(mb_strlen($data['price'],'utf-8')<1){
            $this->error(L('商品价格不能为空'));
        }
        elseif (!is_numeric($data['price'])) {
            $this->error(L('商品价格必须为数字'));
        }
        if(($data['price']<0)){
            $this->error(L('商品价格不能小于0'));
        }
        if(mb_strlen($data['introduce'],'utf-8')<10){
            $this->error(L('商品介绍不能少于10个字！'));
        }
        if(mb_strlen($data['content'],'utf-8')<20){
            $this->error(L('商品整体内容不能少于20个字！'));
        }
        return true;
    }
    private function checkRightSort($data=array()){
        if(mb_strlen($data['title'],'utf-8')<1){
            $this->error(L('商品分类不能为空'));
        }
    }


    public function theme(){
        $this->meta_title='模版首页修改';
        $theme=$this->getThemeMessage();
        $themeId=D('theme')->where(array('id'=>1))->getField('theme_id');
        $this->assign('theme',$theme);
        $this->assign('themeId',$themeId);
        $this->display(T('Mall@Mall/theme'));
    }
    private  function getThemeMessage(){
        $theme[0]=array(
            'id'=>0,
            'title'=>'默认模版',
            'name'=>'Default template',
            'ht_name_cover'=>'./Application/Mall/Static/images/img/ht_mfree_cover.png',
            'ht_name'=>'./Application/Mall/Static/images/img/ht_mfree.png'
        );
        return $theme;
    }

    public function spec()
    {
        $list = D('Mall/Spec')->getAllSpec();
        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title('商品规格')
            ->buttonNew(U('Mall/editSpec'))
            ->setStatusUrl(U('Mall/setSpecStatus'))->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()->keyLink('name', L('_TITLE_'), 'Mall/specValue?spec_id=###')
            ->keyCreateTime()->keyStatus()->keyDoActionEdit('editSpec?id=###')
            ->data($list)
            ->display();
    }
    public function editSpec()
    {
        $aId = I('id', 0, 'intval');
        if (IS_POST) {
            if ($aId != 0) {
                $data = D('Mall/Spec')->create();
                $res = D('Mall/Spec')->save($data);
            } else {
                $data = D('Mall/Spec')->create();
                $res = D('Mall/Spec')->add($data);
            }
            if ($res) {
                $this->success(($aId == 0 ?  L('_ADD_'): L('_EDIT_')) . L('_SUCCESS_'));
            } else {
                $this->error(($aId == 0 ?  L('_ADD_'): L('_EDIT_')) . L('_FAIL_'));
            }

        } else {
            $builder = new AdminConfigBuilder();

            if ($aId != 0) {
                $spec = D('Mall/Spec')->find($aId);
            } else {
                $spec = array('status' => 1);
            }
            $builder->title($aId == 0 ?  '新增规格': '编辑规格')->keyId()->keyText('name', '标题')
                ->keyStatus()->keyCreateTime()
                ->data($spec)
                ->buttonSubmit(U('Mall/editSpec'))->buttonBack()->display();
        }
    }
    public function editSpecValue()
    {
        $aId = I('id', 0, 'intval');
        $specId = I('spec_id',0,'intval');
        if (IS_POST) {
            $data = D('Mall/SpecValue')->create();
            if ($aId != 0) {
                $res = D('Mall/SpecValue')->save($data);
            } else {
                $res = D('Mall/SpecValue')->add($data);
            }
            if ($res) {
                $this->success(($aId == 0 ?  L('_ADD_'): L('_EDIT_')) . L('_SUCCESS_'));
            } else {
                $this->error(($aId == 0 ?  L('_ADD_'): L('_EDIT_')) . L('_FAIL_'));
            }

        } else {
            $builder = new AdminConfigBuilder();

            if ($aId != 0) {
                $spec = D('Mall/SpecValue')->find($aId);
            } else {
                $spec = array('status' => 1,'spec_id' => $specId);
            }
            $builder->title($aId == 0 ?  '新增规格详情': '编辑规格详情')->keyId()->keyText('name', '标题')
                ->keyStatus()->keyCreateTime()
                ->keyHidden('spec_id','规格id')
                ->data($spec)
                ->buttonSubmit(U('Mall/editSpecValue'))->buttonBack()->display();
        }
    }
    public function setSpecValueStatus($ids, $status)
    {
        $id = array_unique((array)$ids);
        //todo  可优化
        foreach ($id as $v) {
            $spec[] =  D('Mall/SpecValue')->where(array('id'=>$v))->find();
        }
        $rs = D('Mall/SpecValue')->where(array('id' => array('in', $id)))->save(array('status' => $status));
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }
        $this->success(L('_SUCCESS_SETTING_'), $_SERVER['HTTP_REFERER']);
    }
    public function specValue()
    {
        $specId = I('spec_id',0,'intval');
        $spaceValue = D('Mall/SpecValue')->getDetail($specId);
        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title('规格详情')
            ->buttonNew(U('Mall/editSpecValue?spec_id='.$specId))
            ->setStatusUrl(U('Mall/setSpecValueStatus'))->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()->keyText('name', L('_TITLE_'))
            ->keyCreateTime()->keyStatus()->keyDoActionEdit('editSpecValue?id=###&spec_id='.$specId)
            ->data($spaceValue)
            ->display();
    }
    public function setSpecStatus($ids, $status)
    {
        $id = array_unique((array)$ids);
        foreach ($id as $v) {
            $spec[] =  D('Mall/Spec')->where(array('id'=>$v))->find();
        }
        $rs = D('Mall/Spec')->where(array('id' => array('in', $id)))->save(array('status' => $status));
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }
        $this->success(L('_SUCCESS_SETTING_'), $_SERVER['HTTP_REFERER']);
    }
    //QQ客服
    public function  Service(){
        $list = D('Mall/service')->getAllService();
        //显示页面
        $builder = new AdminListBuilder();
        $builder
            ->title('客服列表')
            ->buttonNew(U('Mall/editService'))
            ->setStatusUrl(U('Mall/setServiceStatus'))->buttonEnable()->buttonDisable()->buttonDelete()
            ->keyId()->keyLink('QQ', L('客服QQ'), 'Mall/serviceValue?service_id=###')
            ->keyCreateTime()->keyStatus()->keyDoActionEdit('editService?id=###')
            ->data($list)
            ->display();
    }
    public  function editService(){
        $aId = I('id', 0, 'intval');
        if (IS_POST) {
            if ($aId != 0) {
                $data = D('Mall/Service')->create();
                $res = D('Mall/Service')->save($data);
            } else {
                $data = D('Mall/Service')->create();
                $this->checkRightQQ($data);
                $res = D('Mall/Service')->add($data);
            }
            if ($res) {
                $this->success(($aId == 0 ?  L('_ADD_'): L('_EDIT_')) . L('_SUCCESS_'));
            } else {
                $this->error(($aId == 0 ?  L('_ADD_'): L('_EDIT_')) . L('_FAIL_'));
            }

        } else {
            $builder = new AdminConfigBuilder();

            if ($aId != 0) {
                $service = D('Mall/Service')->find($aId);
            } else {
                $service = array('status' => 1);
            }
            $builder->title($aId == 0 ?  '新增客服': '编辑客服')->keyId()->keyText('QQ', '客服QQ')
                ->keyStatus()->keyCreateTime()
                ->data($service)
                ->buttonSubmit(U('Mall/editService'))->buttonBack()->display();
        }
    }
    public function setServiceStatus($ids, $status)
    {
        $id = array_unique((array)$ids);
        foreach ($id as $v) {
            $spec[] =  D('Mall/Service')->where(array('id'=>$v))->find();
        }
        $rs = D('Mall/Service')->where(array('id' => array('in', $id)))->save(array('status' => $status));
        if ($rs === false) {
            $this->error(L('_ERROR_SETTING_') . L('_PERIOD_'));
        }
        $this->success(L('_SUCCESS_SETTING_'), $_SERVER['HTTP_REFERER']);
    }
    private function checkRightQQ($data=array()){
        if(mb_strlen($data['QQ'],'utf-8')<1){
            $this->error(L('客服QQ不能为空'));
        }
        elseif (!is_numeric($data['QQ'])) {
            $this->error(L('QQ必须为数字'));
        }
        return true;
    }
}
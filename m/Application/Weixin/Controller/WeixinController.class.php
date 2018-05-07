<?php

namespace Admin\Controller;

use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
//use Admin\Builder\AdminTreeListBuilder;
use Weixin\Builder\WeixinMenuBuilder;

use Weixin\Sdk\WechatAuth;

class WeixinController extends AdminController
{
    protected function msuccess($msg = '保存成功', $url = ''){
        header('Content-type: application/json');
        $url = $url ? $url : __SELF__;
        exit(json_encode(array('info' => $msg, 'status' => 1, 'url' => $url)));
    }
    protected function merror($msg = '保存失败', $url = ''){
        header('Content-type: application/json');
        $url = $url ? $url : __SELF__;
        exit(json_encode(array('info' => $msg, 'status' => 0, 'url' => $url)));
    }
    public function index(){
        $builder = new AdminConfigBuilder();
        $data = $builder->handleConfig();

        $bindUrl = 'http://' . $_SERVER['HTTP_HOST'] . U('Weixin/Api/index');

        if(!$data['WX_SITEURL']){
            $data['WX_SITEURL'] = $_SERVER['HTTP_HOST'];
        }

        $builder->title('公众账号管理')->suggest('<span style="font-size: 12px;">绑定地址：' . $bindUrl . '</span>');
        $builder->keyText('WX_SITEURL', '网站域名', '不带http,必填，否则微信中图片无法显示');
        $builder->keyText('WX_ID', '公众号原始id');
        $builder->keyText('WX_ACCOUNT', '微信号');
        $builder->keyText('APP_ID', 'AppID（公众号）');
        $builder->keyText('APP_SECRET', 'AppSecret');
        $builder->keyBool('WX_KEFU', '多客服', '请注意您是否有此接口权限');

        $encodes = array(
            1 => '明文模式',
            2 => '兼容模式',
            3 => '安全模式',
        );
        $builder->keySelect('ENCODE', '消息加密方式', null, $encodes);

        $wx_types = array(
            1 => '订阅号',
            2 => '服务号',
            3 => '认证服务号',
        );

        $builder->keySelect('WX_TYPE', '微信号类型', null, $wx_types);
        $builder->keyText('EMAIL', '公众号邮箱');

        $builder->keyText('WX_TOKEN', 'Token', '一般不需要加密等，只用填token就行，任意字符串，与微信公众号设置保证一直即可');
        $builder->keyText('AES_ENCODING_KEY', 'AesEncodingKey');
        $builder->keyText('FUWU_APPID', 'AppID（服务窗）:');
        $builder->keyText('FUWU_PUBLIC_KEY', '公钥（服务窗）:');

        $builder->group('公众号信息', 'WX_SITEURL,WX_ID,WX_ACCOUNT,APP_ID,APP_SECRET,ENCODE,WX_TYPE,EMAIL,WX_KEFU')
                ->group('接口信息', 'WX_TOKEN,AES_ENCODING_KEY,FUWU_APPID,FUWU_PUBLIC_KEY');

        $builder->data($data);
        $builder->keyDefault('SUCCESS_WAIT_TIME',2);
        $builder->keyDefault('ERROR_WAIT_TIME',3);

        $builder->buttonSubmit();
        $builder->display();
    }

    public function areplay($page=1,$r=10){
        $builder = new AdminListBuilder();
        $builder->title("自动回复列表");
        $where = $cats = array();
        if(I('get.type')){
            $where['type'] = I('get.type');
        }
        if(I('get.cid')){
            $where['cid'] = I('get.cid');
        }
        $where['is_news'] = 0;

        $reportCount = $this->getAreplyModel()->where($where)->count();
        $list = $this->getAreplyModel()->page($page,$r)->where($where)->order('id DESC')->select();
        $types = array(
            1 => '文本回复',
            2 => '图文回复',
            3 => '多图文回复',
        );
        foreach($list as $key => $item){
            if($item['type'] == 2){
                $image = get_cover($list[$key]['image'], 'path');
                $list[$key]['content'] = "<img src='{$image}' width='30' height='30' /> " .$list[$key]['title'];
            } else if($item['type'] == 3){
                if($item['content']){
                    $initHtml = '';
                    $news = $this->getAreplyModel()->order('id ASC')->where(array('id'=> array('in', $item['content'])))->select();
                    foreach($news as $k => $v){
                        $img = "";
                        $v['image'] = get_cover($v['image'], 'path');
                        $img = "<img src=\"{$v['image']}\" width=\"30\" height=\"30\">";
                        $initHtml .= "<p>{$img} {$v['title']}</p>";
                    }
                }
                $list[$key]['content'] = $initHtml;
            }
            $list[$key]['type'] = $types[$list[$key]['type']];
            $list[$key]['is_attention'] = $list[$key]['is_attention'] == 1 ? '是' : ' ';
        }
        $builder
            ->keyId()
            ->keyText('keywords', "关键词")
            ->keyText('type', "类型")
            ->keyText('is_attention', "关注回复")
            ->keyText('content', "回复内容")
            ->keyDoActionEdit('Weixin/edit?id=###')
            ->keyDoActionEdit('Weixin/attention?id=###', '设为关注回复')
            ->buttonDelete(U('del'));

        $builder->data($list);
        $builder->pagination($reportCount, $r);
        $builder->display();
    }

    public function del(){
        $id = I('post.ids');

        if($id){
            $rs = false;
            foreach($id as $item){
                if(intval($item)){
                    $rs = $this->getAreplyModel()->delete($item) || $rs;
                }
            }
            if($rs){
                $this->msuccess('删除成功', U('areplay'));
            }
        }
        $this->msuccess('删除失败', U('areplay'));
    }

    public function edit(){
        $id = I('get.id');
        if(!$id){
            $this->error('信息不存在');
        }
        $info = $this->getAreplyModel()->find($id);
        switch($info['type']){
            case 1:
                $this->redirect('rtext', array('id' => $id));
                break;
            case 2:
                $this->redirect('rtextimg', array('id' => $id));
                break;
            case 3:
                $this->redirect('rtextimgs', array('id' => $id));
                break;
        }
    }

    public function attention(){
        $id = I('get.id');
        if(!$id){
            $this->error('信息不存在');
        }
        $this->getAreplyModel()->where(array('is_attention' => 1))->save(array('is_attention' => 2));
        $rs = $this->getAreplyModel()->where(array('id' => $id))->save(array('is_attention' => 1));
        if($rs){
            $this->success('设置成功');
        }
        $this->error('设置失败');
    }

    protected function commonSave($dataSet = array()){
        if(IS_POST){
            $id = I('post.id');
            $data = I('post.');
            $data = array_merge($data, $dataSet);
            if($id){
                $data['ctime'] = time();
            }
            $mod = $this->getAreplyModel();
            $mod->create($data);
            if($id){
                $rs = $mod->save();
            } else {
                $rs = $mod->add();
            }
            if($rs){
                $this->msuccess();
            } else {
                $this->merror();
            }
        }
    }
    
    public function rtext(){
        $id = I('get.id');
        $dataSet = array(
            'type' => 1
        );
        $this->commonSave($dataSet);

        $builder = new AdminConfigBuilder();
        $data = $this->getAreplyModel()->find($id);
        $builder->keyId();
        $builder->title('文本回复');
        $builder->keyText('keywords', '回复关键词');
        $builder->keyTextArea('content', '回复内容');

        $builder->data($data);
        $builder->keyDefault('SUCCESS_WAIT_TIME',2);
        $builder->keyDefault('ERROR_WAIT_TIME',3);

        $builder->buttonSubmit();
        $builder->display();
    }

    public function rtextimg(){
        $id = I('get.id');
        $dataSet = array(
            'type' => 2
        );
        $this->commonSave($dataSet);

        $builder = new AdminConfigBuilder();
        $data = $this->getAreplyModel()->find($id);
        $builder->keyId();
        $builder->title('图文回复');
        $builder->keyText('keywords', '回复关键词');
        $builder->keyText('title', '标题');
        $builder->keySingleImage('image', '封面图片');
        $builder->keyText('linkurl', '链接', '可不填');
        $builder->keyEditor('content', '回复内容');

        $builder->data($data);
        $builder->keyDefault('SUCCESS_WAIT_TIME',2);
        $builder->keyDefault('ERROR_WAIT_TIME',3);

        $builder->buttonSubmit();
        $builder->display();
    }

    public function dataset($page=1,$r=5){
        $inputid = I('get.inputid');
        $totalCount =  $this->getAreplyModel()->where(array('type'=>2))->count();
        $pager = new \Think\Page($totalCount, $r);
        $pager->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $paginationHtml = $pager->show();

        $list = $this->getAreplyModel()->where(array('type'=>2))->page($page,$r)->order('id DESC')->select();

        $this->assign('inputid', $inputid);
        $this->assign('list', $list);
        $this->assign('paginationHtml', $paginationHtml);
        $this->display(T('Weixin@Admin/dataset'));
    }
    
    public function rtextimgs(){
        $id = I('get.id');
        $dataSet = array(
            'type' => 3
        );
        $this->commonSave($dataSet);

        $builder = new AdminConfigBuilder();

        $data = $this->getAreplyModel()->find($id);

        $builder->keyId();
        $builder->title('图文回复');
        $builder->keyText('keywords', '回复关键词');
        $builder->keyDataSelect('content', '选择图文', '图文回复作为多图为素材', 'dataset');

        $builder->data($data);
        $builder->keyDefault('SUCCESS_WAIT_TIME',2);
        $builder->keyDefault('ERROR_WAIT_TIME',3);

        $builder->buttonSubmit();
        $builder->display();

        $initHtml = '';
        if($data){
            $news = $this->getAreplyModel()->order('id ASC')->where(array('id'=> array('in', $data['content'])))->select();
            foreach($news as $key => $item){
                if($key == 0){
                    $item['image'] = get_cover($item['image'], 'path');
                }
                $initHtml .= "addToMultiCnt({$item['id']}, '{$item['title']}', '{$item['image']}');";
            }
        }

        $script = <<<EOF
<style>
#multi_cnt{margin-top:20px;background-color: #ffffff;
    background-image: -moz-linear-gradient(center top , #ffffff 0%, #ffffff 100%);
    border: 1px solid #cdcdcd;
    border-radius: 12px;
    box-shadow: 0 3px 6px #999999;
    width: 285px;
    padding:20px 10px;
    }
#multi_cnt img{width:255px; height:124px;}
#multi_cnt .news_cnt{border-bottom:1px solid #d3d8dc; padding:5px 0; position:relative;}
#multi_cnt .delNews{background:black;color: #fff;
    position: absolute;
    text-align: center;
    top: 0;
    right:0;
    cursor: pointer;
    width: 40px;}

</style>
<script>
function delNews(){
    $(".news_cnt").hover(function(){
        if(!$(this).children('.delNews').length){
            $(this).append('<span class="delNews">删除<span>');
            $(".delNews").click(function(){
                var newsid = $(this).parent().attr('rel');
                var inputidVaule = $('#content').val().split(',');
                var newVaule = '';
                for(var i=0; i<inputidVaule.length; i++){
                    if(inputidVaule[i] != newsid){
                        if(newVaule){
                            newVaule += ',' + inputidVaule[i];
                        } else {
                            newVaule = inputidVaule[i];
                        }
                    }
                }
                $('#content').val(newVaule);
                console.log(newVaule);
                $(this).parent().remove();

            });
        } else {
            $(this).children('.delNews').show();
        }
    }, function(){
        $(this).children('.delNews').hide();
    });
}

function addToMultiCnt(id, title, image){
    if($("#multi_cnt").html().length < 1){
        var html = '<div class="news_cnt" rel="'+ id +'"><img src="'+ image +'"><p>'+ title +'</p></div>';
    } else {
        var html = '<div class="news_cnt" rel="'+ id +'">'+title+'</div>';
    }
    $("#multi_cnt").append(html);

    var inputidVaule = $('#content').val();
    if(inputidVaule){
        inputidVaule += ',' + id;
    } else {
        inputidVaule = id;
    }
    $('#content').val(inputidVaule);
    delNews();
}
$(function(){
    $("#content").hide();
    $("#content").parent().append('<div id="multi_cnt"></div>');
    {$initHtml}
})
</script>
EOF;
        $this->show($script);
    }
    
    public function menu(){
        //显示页面
        $builder = new WeixinMenuBuilder();

        $tree = D('Weixin/WeixinMenu')->getTree(0, 'id,title,sort,linkurl,pid,status');
        $builder->title('微信菜单')
            ->suggest('菜单增加/修改后需要点击“发布到微信”按钮才能生效')
            ->buttonNew(U('menuadd'))
            ->button('发布到微信', array('href'=>U('sendMenuToWeixin')))
            ->data($tree)
            ->display();

//        $this->display('Weixin@Admin/menu');
    }

    public function menuadd($id = 0, $pid = 0)
    {

        $title=$id?"编辑":"新增";
        $menuMod = D('Weixin/WeixinMenu');
        if (IS_POST) {
            if ($menuMod->editData()) {
                $this->success($title.'成功。', U('menu'));
            } else {
                $this->error($title.'失败!'.$menuMod->getError());
            }
        } else {
            $builder = new AdminConfigBuilder();

            if ($id != 0) {
                $data = $menuMod->find($id);
            } else {
                $father_category_pid=$menuMod->where(array('id'=>$pid))->getField('pid');
                if($father_category_pid!=0){
                    $this->error('菜单不能超过二级！');
                }
            }
            if($pid!=0){
                $categorys = $menuMod->where(array('pid'=>0,'status'=>array('egt',0)))->select();
            }
            $opt = array();
            foreach ($categorys as $category) {
                $opt[$category['id']] = $category['title'];
            }
            $builder->title($title.'微信菜单')
                ->data($data)
                ->keyId()->keyText('title', '标题')
                ->keySelect('pid', '父分类', '选择父级分类', array('0' => '顶级菜单') + $opt)->keyDefault('pid',$pid)
                ->keyText('linkurl','链接地址(或关键词)')->keyDefault('sort',0)
                ->keyInteger('sort','排序')->keyDefault('sort',0)
                ->keyStatus()->keyDefault('status',1)
                ->buttonSubmit(U('menuadd'))->buttonBack()
                ->display();
        }

    }

    public function menudel(){
        $id = I('get.id');
        if($id && D('Weixin/WeixinMenu')->delete($id)){
            D('Weixin/WeixinMenu')->where(array('pid' => $id))->save(array('pid' => 0));
            $this->msuccess('删除成功', U('menu'));
        } else {
            $this->merror('删除失败', U('menu'));
        }
    }

    public function sendmenutoweixin(){
        $config = $this->getWeixinConfig();
        //api发送到微信
        $wechat = new WechatAuth($config['APP_ID'], $config['APP_SECRET']);

        $access_token = S('wx_getAccessToken');
        if(ture || !$access_token){
            $rs = $wechat->getAccessToken();
            $access_token = $rs['access_token'];
            S('wx_getAccessToken', $access_token, $rs['expires_in'] - 300);
        }

        $wechat->setAccessToken($access_token);

        $tree = D('Weixin/WeixinMenu')->getTree(0, 'id,title,linkurl,pid');
        $menu = null;
        foreach($tree as $k => $v){
            if(isset($v['_']) && is_array($v['_'])){
                $menu[$k] = array(
                    'name' => $v['title'],
                    'sub_button' => array()
                );
                foreach($v['_'] as $k2 => $v2){
                    $menu[$k]['sub_button'][$k2] = $this->formatMenu($v2);
                }
            } else {
                $menu[$k] = $this->formatMenu($v);
            }
        }

        $rs = $wechat->menuCreate($menu);

        if($rs && ($rs['errcode'] == 0)){
            $this->success('发布成功');
        }
        $this->error('发布失败');
    }

    protected function formatMenu($menu){
        if($menu['linkurl'] && ((strpos($menu['linkurl'], 'http://') !== false) || (strpos($menu['linkurl'], 'https://') !== false))){
            return array(
                'type' => 'view',
                'name' => $menu['title'],
                'url' => $menu['linkurl'],
            );
        } else {
            return array(
                'type' => 'click',
                'name' => $menu['title'],
                'key' => $menu['linkurl'] ? $menu['linkurl'] : $menu['title'],
            );
        }
    }

    protected function getAreplyModel(){
        return D('Weixin/WeixinAreply');
    }

    protected function getWeixinConfig(){
        $config = array();
        $tmp = D('MConfig')->where(array('name' => array('like', '_WEIXIN_' . '%')))->limit(999)->select();
        foreach ($tmp as $k => $v) {
            $key = str_replace('_WEIXIN_', '', strtoupper($v['name']));
            $config[$key] = $v['value'];
        }
        return $config;
    }

}
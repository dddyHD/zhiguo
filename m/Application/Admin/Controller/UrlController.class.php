<?php
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;

class UrlController extends AdminController
{
    public function urlList()
    {
        $aPage = I('page', 1, 'intval');
        $r = 20;

        $aUrl = I('url', '', 'text');
        $map = array('status' => array('egt', 0));
        if ($aUrl) {
            $map['url'] = array('like', '%' . $aUrl . '%');
        }
        $model = M('url');
        $list = $model->where($map)->page($aPage, $r)->select();
        $totalCount = $model->count();
        foreach ($list as &$v) {
            $v['id'] = $v['short'];
            $v['long'] = str_replace("//", '/', $_SERVER['HTTP_HOST'] . getRootUrl() . '/link/' . $v['short']);
        }
        unset($v);
        //显示页面
        $builder = new AdminListBuilder();

        $builder->title('链接管理')
            ->buttonNew(U('Url/editUrl'))
            ->setStatusUrl(U('setUrlStatus'))->buttonEnable()
            ->buttonDisable()->buttonDelete()
//            ->keyText('short', '短链接')
             ->keyLink('long', '短链接', str_replace("//", '/', getRootUrl() . '/link/{$short}|text'))
            ->keyLink('url', '链接', '{$url}|text')
            ->keyText('view_count', '浏览量')
            ->keyCreateTime()->keyStatus()
            ->keyDoActionEdit('editUrl?short={$short}')
            ->setSearchPostUrl(U('urlList'))->search('链接', 'url')
            ->data($list)
            ->pagination($totalCount, $r)
            ->display();

    }


    public function setUrlStatus($ids, $status)
    {
        $id = array_unique((array)$ids);
        $rs = M('url')->where(array('short' => array('in', $id)))->save(array('status' => $status));

        foreach ($id as $v) {
            S('short_url_' . $v, null);
        }

        if ($rs === false) {
            $this->error('设置失败');
        }
        $this->success('设置成功', $_SERVER['HTTP_REFERER']);
    }


    public function editUrl()
    {
        if (IS_POST) {
            $aShort = I('post.short', '', 'text');
            $aUrl = I('post.url', '', 'text');
            $aViewCount = I('post.view_count', 0, 'intval');
            $aStatus = I('post.status', '', 'text');
            $aCreateTime = I('post.create_time', 0, 'intval');

            $urlModel = D('Common/Url');


            if (!empty($aShort)) {
                $data['short'] = $aShort;
                $data['url'] = $aUrl;
                $data['view_count'] = $aViewCount;
                $data['status'] = $aStatus;
                $data['create_time'] = $aCreateTime;
                $rs = $urlModel->editUrl($data);

            } else {
                $rs = $urlModel->addUrl($aUrl);
            }
            if ($rs) {
                $this->success(($aShort ? '编辑' : ' 新增') . '成功', U('url/urlList'));
            } else {
                $this->error(($aShort ? '编辑' : ' 新增') . '失败');
            }


        } else {
            $aShort = I('get.short', '', 'text');
            $builder = new AdminConfigBuilder();
            $data = D('Common/Url')->where(array('short'=>$aShort))->find();

            $builder->title(($aShort ? '编辑' : '新增') . '链接')->keyReadOnly('short', '短链接')->keyText('url', 'url路径');


            if ($aShort) {
                $builder->keyText('view_count', '访问数')
                    ->keyReadOnly('url_md5', 'md5')
                    ->keyStatus()->keyCreateTime()
                    ->data($data);
            }

            $builder->buttonSubmit(U('Url/editUrl'))->buttonBack()->display();
        }
    }

}

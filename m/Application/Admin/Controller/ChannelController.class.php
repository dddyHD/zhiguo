<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;
use  Admin\Builder\AdminConfigBuilder;
use Admin\Builder\AdminListBuilder;
/**
 * 后台频道控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */

class ChannelController extends AdminController
{

    /**
     * 频道列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index()
    {
        $Channel = D('MChannel');
        if (IS_POST) {
            $one = $_POST['nav'][1];
            if (count($one) > 0) {
                M()->execute('TRUNCATE TABLE ' . C('DB_PREFIX') . 'm_channel');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'pid' => 0,
                        'title' => op_t($one['title'][$i]),
                        'url' => op_t($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => intval($one['target'][$i]),
                        'out_site' => intval($one['out_site'][$i]),
                        'color' => op_t($one['color'][$i]),
                        'band_text' => op_t($one['band_text'][$i]),
                        'band_color' => op_t($one['band_color'][$i]),
                        'icon' => op_t($one['icon'][$i]),
                        'status' => 1

                    );
                    $pid[$i] = $Channel->add($data[$i]);
                }
                $two = $_POST['nav'][2];

                for ($j = 0; $j < count(reset($two)); $j++) {
                    $data_two[$j] = array(
                        'pid' => $pid[$two['pid'][$j]],
                        'title' => op_t($two['title'][$j]),
                        'url' => op_t($two['url'][$j]),
                        'sort' => intval($two['sort'][$j]),
                        'target' => intval($two['target'][$j]),
                        'out_site' => intval($two['out_site'][$j]),
                        'color' => op_t($two['color'][$j]),
                        'band_text' => op_t($two['band_text'][$j]),
                        'band_color' => op_t($two['band_color'][$j]),
                        'icon' => op_t(str_replace('icon-', '', $two['icon'][$j])),
                        'image' => op_t($two['image'][$j]),
                        'remark' => op_t($two['remark'][$j]),
                        'status' => 1,
                    );

                    $res[$j] = $Channel->add($data_two[$j]);
                }
                S('common_nav',null);
                S('channel_bottom',null);
                $this->success(L('_CHANGE_'));
            }
            $this->error(L('_NAVIGATION_AT_LEAST_ONE_'));


        } else {
            /* 获取频道列表 */
            $map = array('status' => array('gt', -1), 'pid' => 0);
            $list = $Channel->where($map)->order('sort asc,id asc')->select();
            foreach ($list as $k => &$v) {
                $module = D('Module')->where(array('entry' => $v['url']))->find();
                $v['module_name'] = $module['name'];
                $child = $Channel->where(array('status' => array('gt', -1), 'pid' => $v['id']))->order('sort asc,id asc')->select();
                foreach ($child as $key => &$val) {
                    $module = D('MModule')->where(array('entry' => $val['url']))->find();
                    $val['module_name'] = $module['name'];
                }
                unset($key, $val);
                $child && $v['child'] = $child;
            }

            unset($k, $v);
            $this->assign('module', $this->getModules());
            $this->assign('list', $list);

            $this->meta_title = L('_NAVIGATION_MANAGEMENT_');
            $this->display();
        }

    }


    public function user(){
        $Channel = D('UserNav');
        if (IS_POST) {
            $one = $_POST['nav'][1];
            if (count($one) > 0) {
                M()->execute('TRUNCATE TABLE ' . C('DB_PREFIX') . 'user_nav');

                for ($i = 0; $i < count(reset($one)); $i++) {
                    $data[$i] = array(
                        'title' => op_t($one['title'][$i]),
                        'url' => op_t($one['url'][$i]),
                        'sort' => intval($one['sort'][$i]),
                        'target' => intval($one['target'][$i]),
                        'out_site' => intval($one['out_site'][$i]),
                        'color' => op_t($one['color'][$i]),
                        'band_text' => op_t($one['band_text'][$i]),
                        'band_color' => op_t($one['band_color'][$i]),
                        'icon' => op_t(str_replace('icon-', '', $one['icon'][$i])),
                        'image' => op_t($one['image'][$i]),
                        'remark' => op_t($one['remark'][$i]),
                        'status' => 1

                    );
                    $pid[$i] = $Channel->add($data[$i]);
                }
                S('common_user_nav',null);
                $this->success(L('_CHANGE_'));
            }
            $this->error(L('_NAVIGATION_AT_LEAST_ONE_'));


        } else {
            /* 获取频道列表 */
            $map = array('status' => array('gt', -1));
            $list = $Channel->where($map)->order('sort asc,id asc')->select();
            foreach ($list as $k => &$v) {
                $module = D('Module')->where(array('entry' => $v['url']))->find();
                $v['module_name'] = $module['name'];
                unset($key, $val);
            }

            unset($k, $v);
            $this->assign('module', $this->getModules());
            $this->assign('list', $list);

            $this->meta_title = L('_NAVIGATION_MANAGEMENT_');
            $this->display();
        }
    }
    public function getModule()
    {
        $this->success($this->getModules());
    }


    /**
     * 频道列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index1()
    {
        $pid = i('get.pid', 0);
        /* 获取频道列表 */
        $map = array('status' => array('gt', -1), 'pid' => $pid);
        $list = M('MChannel')->where($map)->order('sort asc,id asc')->select();

        $this->assign('list', $list);
        $this->assign('pid', $pid);
        $this->meta_title = L('_NAVIGATION_MANAGEMENT_');
        $this->display();
    }


    /**
     * 添加频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function add()
    {
        if (IS_POST) {
            $Channel = D('Channel');
            $data = $Channel->create();
            if ($data) {
                $id = $Channel->add();
                if ($id) {
                    $this->success(L('_NEW_SUCCESS_'), U('index'));
                    //记录行为
                    action_log('update_channel', 'channel', $id, UID);
                } else {
                    $this->error(L('_NEW_FAILURE_'));
                }
            } else {
                $this->error($Channel->getError());
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父导航
            if (!empty($pid)) {
                $parent = M('MChannel')->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $pnav = D('Channel')->where(array('pid' => 0))->select();
            $this->assign('pnav', $pnav);
            $this->assign('pid', $pid);
            $this->assign('info', null);
            $this->meta_title = L('_NEW_NAVIGATION_');
            $this->display('edit');
        }
    }

    /**
     * 编辑频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function edit($id = 0)
    {
        if (IS_POST) {
            $Channel = D('Channel');
            $data = $Channel->create();
            if ($data) {
                if ($Channel->save()) {
                    //记录行为
                    action_log('update_channel', 'channel', $data['id'], UID);
                    $this->success(L('_SUCCESS_EDIT_'), U('index'));
                } else {
                    $this->error(L('_EDIT_FAILED_'));
                }

            } else {
                $this->error($Channel->getError());
            }
        } else {
            $info = array();
            /* 获取数据 */
            $info = M('MChannel')->find($id);

            if (false === $info) {
                $this->error(L('_GET_CONFIGURATION_INFORMATION_ERROR_'));
            }

            $pid = i('get.pid', 0);

            //获取父导航
            if (!empty($pid)) {
                $parent = M('MChannel')->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $pnav = D('Channel')->where(array('pid' => 0))->select();
            $this->assign('pnav', $pnav);
            $this->assign('pid', $pid);
            $this->assign('info', $info);
            $this->meta_title = L('_EDIT_NAVIGATION_');
            $this->display();
        }
    }

    /**
     * 删除频道
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function del()
    {
        $id = array_unique((array)I('id', 0));

        if (empty($id)) {
            $this->error(L('_PLEASE_CHOOSE_TO_OPERATE_THE_DATA_'));
        }

        $map = array('id' => array('in', $id));
        if (M('MChannel')->where($map)->delete()) {
            //记录行为
            action_log('update_channel', 'channel', $id, UID);
            $this->success(L('_DELETE_SUCCESS_'));
        } else {
            $this->error(L('_DELETE_FAILED_'));
        }
    }

    /**
     * 导航排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort()
    {
        if (IS_GET) {
            $ids = I('get.ids');
            $pid = I('get.pid');

            //获取排序的数据
            $map = array('status' => array('gt', -1));
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } else {
                if ($pid !== '') {
                    $map['pid'] = $pid;
                }
            }
            $list = M('MChannel')->where($map)->field('id,title')->order('sort asc,id asc')->select();

            $this->assign('list', $list);
            $this->meta_title = L('_NAVIGATION_SORT_');
            $this->display();
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key => $value) {
                $res = M('MChannel')->where(array('id' => $value))->setField('sort', $key + 1);
            }
            if ($res !== false) {
                $this->success(L('_SORT_OF_SUCCESS_'));
            } else {
                $this->eorror(L('_SORT_OF_FAILURE_'));
            }
        } else {
            $this->error(L('_ILLEGAL_REQUEST_'));
        }
    }
    public function customEdit($pid,$id){
        // $builder = new AdminListBuilder();

        $map['id'] = $id;
        $map['pid'] = $pid;
        $res = M('MChannel')->where($map)->select();
        //$this->assign('res', $res[0]);
        // $this->display();
        $builder = new AdminConfigBuilder();
        $builder->title('频道编辑')
            ->keyReadOnly('id','频道ID')
            ->keyReadOnly('pid','父频道ID')
            ->keyReadOnly('title','频道标题')
            ->keyText('target','是否新页面打开(1为是，0为否)')
            ->data($res[0])
            ->buttonSubmit(U('Admin/Channel/editData'))
            ->buttonBack()
            ->display();

    }

    public function delete()
    {
        $id = I('get.id','','intval');
        $model = D('Channel');
        $where['id'] = $id;
        $channel = $model->where($where)->find();
        if (strtolower($channel['url']) == 'ucenter/index/index' || strtolower($channel['url']) == 'message/index/index') {
            $this->error('-我-  -消息-  不能删除');
        }
        $res = $model->where($where)->delete();
        if ($res) {
            $this->success('操作成功');
            S('channel_bottom',null);
        } else {
            $this->error('操作失败');
        }
    }

    public function editData(){
        S('common_nav',null);
        S('channel_bottom',null);
        $aRemark=I('post.remark','','op_t');
        $aImg=I('post.image','','op_t');
        $aId=I('post.id','','op_t');
        $data['remark']=$aRemark;
        $data['image']=$aImg;
        $res=M('Channel')->where('id=' . $aId)->save($data);
        if($res){
            $this->success('编辑成功');

        }else{
            $this->error('编辑失败');
        }



    }

}

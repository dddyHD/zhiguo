<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 扩展后台管理页面
 * @author yangweijie <yangweijiester@gmail.com>
 */
class AddonsController extends AdminController
{

    public function _initialize()
    {
        parent::_initialize();
        $this->assign('_extra_menu', array(
            L('_ALREADY_INSTALLED_IN_THE_BACKGROUND_') => D('Addons')->getAdminList(),
        ));
    }




    /**
     * 插件列表
     */
    public function index()
    {
        $this->meta_title = L('_PLUGIN_LIST_');
        $type = I('get.type', 'no', 'text');
        $list = D('Addons')->getList('');
        $request = (array)I('request.');

        $listRows = 12;
        if ($type == 'yes') {//已安装的
            foreach ($list as $key => $value) {
                if ($value['uninstall'] != 1) {
                    unset($list[$key]);
                }
            }
        } else if ($type == 'no') {
            foreach ($list as $key => $value) {
                if ($value['uninstall'] == 1) {
                    unset($list[$key]);
                }
            }
        } else {
            $type = 'all';
        }
        $total = $list ? count($list) : 1;
        $this->assign('type', $type);
        $page = new \Think\PageBack($total, $listRows, $request);
        $voList = array_slice($list, $page->firstRow, $page->listRows);
        $p = $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p ? $p : '');
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->display();
    }

    /**
     * 插件后台显示页面
     * @param string $name 插件名
     */
    public function adminList($name)
    {

        if (method_exists(A('Addons://' . $name . '/Admin'), 'buildList')) {
            A('Addons://' . $name . '/Admin')->buildList();
            exit;
        }


        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $class = get_addon_class($name);
        if (!class_exists($class))
            $this->error(L('_PLUGIN_DOES_NOT_EXIST_'));
        $addon = new $class();
        $this->assign('addon', $addon);
        $param = $addon->admin_list;
        if (!$param)
            $this->error(L('_THE_PLUGIN_LIST_INFORMATION_IS_NOT_CORRECT_'));
        $this->meta_title = $addon->info['title'];
        extract($param);
        $this->assign('title', $addon->info['title']);
        $this->assign($param);
        if (!isset($fields))
            $fields = '*';
        if (!isset($map))
            $map = array();
        if (isset($model))
            $list = $this->lists(D("Addons://{$model}/{$model}")->field($fields), $map, $order);
        $this->assign('_list', $list);
        if ($addon->custom_adminlist)
            $this->assign('custom_adminlist', $this->fetch($addon->addon_path . $addon->custom_adminlist));
        $this->display();
    }

    /**
     * 启用插件
     */
    public function enable()
    {
        $id = I('id');
        $msg = array('success' => L('_ENABLE_SUCCESS_'), 'error' => L('_ENABLE_FAILED_'));
        S('hooks', null);
        $this->resume('MAddons', "id={$id}", $msg);
    }

    /**
     * 禁用插件
     */
    public function disable()
    {
        $id = I('id');
        $msg = array('success' => L('_DISABLE_SUCCESS_'), 'error' => L('_DISABLE_'));
        S('hooks', null);
        $this->forbid('MAddons', "id={$id}", $msg);
    }

    /**
     * 设置插件页面
     */
    public function config()
    {
        $id = (int)I('id');
        $addon = M('MAddons')->find($id);
        if (!$addon)
            $this->error(L('_PLUGIN_NOT_INSTALLED_'));
        $addon_class = get_addon_class($addon['name']);
        if (!class_exists($addon_class))
            trace(L('_FAIL_ADDON_PARAM_',array('model'=>$addon['name'])), 'ADDONS', 'ERR');
        $data = new $addon_class;
        $addon['addon_path'] = $data->addon_path;
        $addon['custom_config'] = $data->custom_config;
        $this->meta_title = L('_ADDONS_SET_') . $data->info['title'];
        $db_config = $addon['config'];
        $addon['config'] = include $data->config_file;
        if ($db_config) {
            $db_config = json_decode($db_config, true);
            foreach ($addon['config'] as $key => $value) {
                if ($value['type'] != 'group') {
                    $addon['config'][$key]['value'] = $db_config[$key];
                } else {
                    foreach ($value['options'] as $gourp => $options) {
                        foreach ($options['options'] as $gkey => $value) {
                            $addon['config'][$key]['options'][$gourp]['options'][$gkey]['value'] = $db_config[$gkey];
                        }
                    }
                }
            }
        }
        $this->assign('data', $addon);
        if ($addon['custom_config'])
            $this->assign('custom_config', $this->fetch($addon['addon_path'] . $addon['custom_config']));
        $this->display();
    }

    /**
     * 保存插件设置
     */
    public function saveConfig()
    {
        $id = (int)I('id');
        $config = I('config');
        $flag = M('MAddons')->where("id={$id}")->setField('config', json_encode($config));
        if (isset($config['addons_cache'])) {//清除缓存
            S($config['addons_cache'], null);
        }
        if ($flag !== false) {
            $this->success(L('_SAVE_'), Cookie('__forward__'));
        } else {
            $this->error(L('_SAVE_FAILED_'));
        }

    }

    /**
     * 安装插件
     */
    public function install()
    {
        $addon_name = trim(I('addon_name'));
        $addonsModel = D('Addons');
        $rs = $addonsModel->install($addon_name);
        if ($rs === true) {
            $this->success(L('_INSTALL_PLUG-IN_SUCCESS_'));
        } else {
            $this->error($addonsModel->getError());
        }
    }

    /**
     * 卸载插件
     */
    public function uninstall()
    {
        $addonsModel = M('MAddons');
        $id = trim(I('id'));
        $db_addons = $addonsModel->find($id);
        $class = get_addon_class($db_addons['name']);
        $this->assign('jumpUrl', U('index'));
        if (!$db_addons || !class_exists($class))
            $this->error(L('_PLUGIN_DOES_NOT_EXIST_'));
        session('addons_uninstall_error', null);
        $addons = new $class;
        $uninstall_flag = $addons->uninstall();
        if (!$uninstall_flag)
            $this->error(L('_EXECUTE_THE_PLUG-IN_TO_THE_PRE_UNLOAD_OPERATION_FAILED_') . session('addons_uninstall_error'));
        $hooks_update = D('Hooks')->removeHooks($db_addons['name']);
        if ($hooks_update === false) {
            $this->error(L('_FAILED_HOOK_MOUNTED_DATA_UNINSTALL_PLUG-INS_'));
        }
        S('hooks', null);
        $delete = $addonsModel->where("name='{$db_addons['name']}'")->delete();
        if ($delete === false) {
            $this->error(L('_UNINSTALL_PLUG-IN_FAILED_'));
        } else {
            $this->success(L('_SUCCESS_UNINSTALL_'));
        }
    }

    /**
     * 钩子列表
     */
    public function hooks()
    {
        $this->meta_title = L('_HOOK_LIST_');
        $map = $fields = array();
        $list = $this->lists(D("Hooks")->field($fields), $map);
        int_to_string($list, array('type' => C('HOOKS_TYPE')));
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->assign('list', $list);
        $this->display();
    }

    public function addhook()
    {
        $this->assign('data', null);
        $this->meta_title = L('_NEW_HOOK_');
        $this->display('edithook');
    }

    //钩子出编辑挂载插件页面
    public function edithook($id)
    {
        $hook = M('Hooks')->field(true)->find($id);
        $this->assign('data', $hook);
        $this->meta_title = L('_EDIT_HOOK_');
        $this->display('edithook');
    }

    //超级管理员删除钩子
    public function delhook($id)
    {
        if (M('Hooks')->delete($id) !== false) {
            $this->success(L('_DELETE_SUCCESS_'));
        } else {
            $this->error(L('_DELETE_FAILED_'));
        }
    }

    public function updateHook()
    {
        $hookModel = D('Hooks');
        $data = $hookModel->create();
        if ($data) {
            if ($data['id']) {
                $flag = $hookModel->save($data);
                if ($flag !== false)
                    $this->success(L('_UPDATE_'), Cookie('__SELF__'));
                else
                    $this->error(L('_UPDATE_FAILED_'));
            } else {
                $flag = $hookModel->add($data);
                if ($flag)
                    $this->success(L('_NEW_SUCCESS_'), Cookie('__forward__'));
                else
                    $this->error(L('_NEW_FAILURE_'));
            }
        } else {
            $this->error($hookModel->getError());
        }
    }

    public function execute($_addons = null, $_controller = null, $_action = null)
    {
        if (C('URL_CASE_INSENSITIVE')) {
            $_addons = ucfirst(parse_name($_addons, 1));
            $_controller = parse_name($_controller, 1);
        }

        $TMPL_PARSE_STRING = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__ADDONROOT__'] = __ROOT__ . "/Addons/{$_addons}";
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);


        if (!empty($_addons) && !empty($_controller) && !empty($_action)) {

            $Addons = A("Addons://{$_addons}/{$_controller}")->$_action();
        } else {
            $this->error(L('_NO_SPECIFIED_PLUG-IN_NAME,_CONTROLLER_OR_OPERATION_'));
        }
    }

    public function edit($name, $id = 0)
    {
        $this->assign('name', $name);
        $class = get_addon_class($name);
        if (!class_exists($class))
            $this->error(L('_PLUGIN_DOES_NOT_EXIST_'));
        $addon = new $class();
        $this->assign('addon', $addon);
        $param = $addon->admin_list;
        if (!$param)
            $this->error(L('_THE_PLUGIN_LIST_INFORMATION_IS_NOT_CORRECT_'));
        extract($param);
        $this->assign('title', $addon->info['title']);
        if (isset($model)) {
            $addonModel = D("Addons://{$name}/{$model}");
            if (!$addonModel)
                $this->error(L('_MODEL_CANNOT_BE_REAL_'));
            $model = $addonModel->model;
            $this->assign('model', $model);
        }
        if ($id) {
            $data = $addonModel->find($id);
            $data || $this->error(L('_DATA_DOES_NOT_EXIST_'));
            $this->assign('data', $data);
        }

        if (IS_POST) {
            // 获取模型的字段信息
            if (!$addonModel->create())
                $this->error($addonModel->getError());

            if ($id) {
                $flag = $addonModel->save();
                if ($flag !== false)
                    $this->success(L('_SUCCESS_ADD_PARAM_',array('model'=>$model['title'])), Cookie('__forward__'));
                else
                    $this->error($addonModel->getError());
            } else {
                $flag = $addonModel->add();
                if ($flag)
                    $this->success(L('_FAIL_ADD_PARAM_',array('model'=>$model['title'])), Cookie('__forward__'));
            }
            $this->error($addonModel->getError());
        } else {
            $fields = $addonModel->_fields;
            $this->assign('fields', $fields);
            $this->meta_title = $id ? L('_EDIT_') . $model['title'] : L('_NEW_') . $model['title'];
            if ($id)
                $template = $model['template_edit'] ? $model['template_edit'] : '';
            else
                $template = $model['template_add'] ? $model['template_add'] : '';
            $this->display($addon->addon_path . $template);
        }
    }

    public function del($id = '', $name)
    {
        $ids = array_unique((array)I('ids', 0));

        if (empty($ids)) {
            $this->error(L('_ERROR_DATA_SELECT_'));
        }

        $class = get_addon_class($name);
        if (!class_exists($class))
            $this->error(L('_PLUGIN_DOES_NOT_EXIST_'));
        $addon = new $class();
        $param = $addon->admin_list;
        if (!$param)
            $this->error(L('_THE_PLUGIN_LIST_INFORMATION_IS_NOT_CORRECT_'));
        extract($param);
        if (isset($model)) {
            $addonModel = D("Addons://{$name}/{$model}");
            if (!$addonModel)
                $this->error(L('_MODEL_CANNOT_BE_REAL_'));
        }

        $map = array('id' => array('in', $ids));
        if ($addonModel->where($map)->delete()) {
            $this->success(L('_DELETE_SUCCESS_'));
        } else {
            $this->error(L('_DELETE_FAILED_'));
        }
    }

}

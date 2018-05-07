<?php
/**
 * Created by PhpStorm.
 * User: 王杰
 * Date: 2016/12/14
 * Time: 16:10
 */
namespace Core\Controller;

use Think\Controller;

class CollectController extends Controller
{
	public function doCollect()
	{
		if (!is_login()) {
			$this->error("请登陆后再收藏。");
		}
		$appname = I('POST.module','','text');
		$table = I('POST.table','','text');
		$row = I('POST.row','','intval');

		$collect['module'] = $appname;
		$collect['table'] = 'collect';
		$collect['row'] = $row;
		$collect['uid'] = is_login();

		if (D('Collect')->where($collect)->count()) {
			D('Collect')->where($collect)->delete();
			$this->error('取消收藏');
		} else {
			$collect['create_time'] = time();
			if (D('Collect')->where($collect)->add($collect)) {
				$this->success("收藏成功");
			} else {
				$this->error("收藏失败");
			}
		}
	}
}


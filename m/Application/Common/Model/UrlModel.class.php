<?php

namespace Common\Model;

use Think\Model;

class UrlModel extends Model
{
    protected $tableName = 'url';

    public function addUrl($url = '')
    {
        $data['short'] = create_rand(6);
        $data['url'] = $url;
        $data['view_count'] = 0;
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['url_md5'] = md5($url);
        if ($this->checkUrlExist($url)) {
            return $this->getShort($url);
        }
        $rs = $this->add($data);

        return $rs ? $data['short'] : false;
    }


    public function editUrl($data = array())
    {
        $data['url_md5'] = md5($data['url']);
        S('short_url_' . $data['short'], null);
        return $this->where(array('short' => $data['short']))->save($data);
    }


    public function checkUrlExist($url = '')
    {
        $md5 = md5($url);
        return $this->where(array('url_md5' => $md5, 'status' => array('egt', 0)))->count();
    }


    public function getShort($url = '')
    {
        $md5 = md5($url);
        return $this->where(array('url_md5' => $md5, 'status' => 1))->cache('url_' . $md5)->getField('short');
    }

    public function getUrl($short = '')
    {
        $tag = 'short_url_' . $short;
        $url = S($tag);
        if (empty($url)) {
            $url = $this->where(array('short' => $short, 'status' => 1))->find();
            S($tag, $url);
        }
        return $url;
    }

    public function setCountInc($short = '')
    {
        $rs = $this->where(array('short' => $short))->setInc('view_count');
        return $rs;
    }

}

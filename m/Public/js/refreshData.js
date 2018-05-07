// <div class="content infinite-scroll mt" data-distance="100" data-ptr-distance="55">
//     <ul id="tab" class="tab active list" data-title="common">
//
//     </ul>
//     <div class="infinite-scroll-preloader">
//     <div class="preloader"></div>
//     </div>
//     </div>

// demo
// var detailID = $('.cmtWrap').attr('data-id');
// var total = $('.cmtWrap').attr('data-total');
// refreshData('Forum/index/detail',total,'.cmtWrap',{id:detailID});


//基本方法形式
// public function hotNews()
// {
//     $page=I('post.page',1,'intval');
//     $data=D('news')->where(array('status'=>1))->page($page,10)->select();
//     if ($data){
//         $this->assign('data',$data);
//         $data=$this->fetch('_list');
//         $this->ajaxReturn(array(
//                 'info'=>'请求成功',
//             'status'=>1,
//             'data'=>$data
//     ));
//     }else{
//         $this->ajaxReturn(array(
//                 'info'=>'请求失败',
//             'status'=>1,
//             'data'=>''
//     ));
//     }
// }
//AJAX返回数据的格式array('info'=>'请求信息提示','status'=>'1 or 2',data=>'html代码')
/**
 * 每页显示十条，上述是简单结构 注意必须有"content属性" content infinite-scroll mt" data-distance="100" data-ptr-distance="55"
 * @param url 请求URL
 * @param total 最大评论数
 * @param container li列表上层容器
 * @author nkx nkx@ourstu.com
 */
var refreshData=function (url,total,container,data,callback) {
    //加载更多刷新操作

    var loading = false;
    var maxItems = 0;
    var page=0;
    maxItems=total;
    //首次加载数据
    function addItems(lastIndex) {
        $('.infinite-scroll-preloader').css('display','');
        var html = '';
        data.page=++page;
        $.ajax( {
            url:U(url),
            data:data,
            type:'post',
            cache:false,
            dataType:'json',
            async:false,
            success:function(res) {
                if(res.status ==true){
                    $(container).append(res.data);
                    lastIndex = $(container+' li').length;
                    if (lastIndex<10){
                        $('.infinite-scroll-preloader').css('display','none');
                    }
                    if (res.data==''){
                        $('.infinite-scroll-preloader').css('display','none');
                    }
                }
                else{
                }
            },
            error : function() {
                $.toast('数据加载异常！')
            }
        });
    }
    addItems(0);

    if (typeof callback === 'function') {
        callback();
    }
    //分页条数每页十条 对应里面10条
    var lastIndex = 10;
    $(document).on('infinite', '.infinite-scroll',function() {
        // 如果正在加载，则退出
        if (loading) return;

        // 设置flag
        loading = true;

        setTimeout(function() {
            loading = false;

            if (lastIndex >= maxItems) {
                $.detachInfiniteScroll($('.infinite-scroll'));
                $('.infinite-scroll-preloader').remove();
                return;
            }
            addItems(lastIndex);
            lastIndex = $(container+' li').length;
        }, 1000);
    });


};
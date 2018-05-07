$(function(){
    var loading = false;
    var page = 1;
    // 添加'refresh'监听器
    $(document).on('refresh', '.pull-to-refresh-content',function(e) {
        var url = U('Message/Index/message');
        var type = $('[data-role="message"]').attr('data-type');
        $.get(url,{page:1,is_pull:1,type:type},function(res){
            if (res.status) {
                if (res.html == '') {
                    $('[data-role="message"]').html('<div class="noMessage">还没有消息~</div>');
                } else {
                    $('[data-role="message"]').html(res.html);
                    page = 1;
                }
                $.pullToRefreshDone('.pull-to-refresh-content');
                $.attachInfiniteScroll($('.infinite-scroll'));
                $('.infinite-scroll-preloader').css('display','');
            }
        });
    });

    // 注册'infinite'事件处理函数
    $(document).on('infinite', '.infinite-scroll-bottom',function() {
        if (loading) return;
        loading = true;
        var url = U('Message/Index/message');
        var type = $('[data-role="message"]').attr('data-type');
        $.get(url,{page:++page,is_pull:1,type:type},function(res){
            if (res.html != '') {
                $('[data-role="message"]').append(res.html);
            } else {
                $.detachInfiniteScroll($('.infinite-scroll'));
                $('.infinite-scroll-preloader').css('display','none');
            }
            loading = false;
        });
        //容器发生改变,如果是js滚动，需要刷新滚动
        $.refreshScroller();
    });
})
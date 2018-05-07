/**
 * Created by Administrator on 2016/12/20 0020.
 */
$(function () {
    var loading = false;
    $(document).on('infinite', '.infinite-scroll-bottom',function() {
        if (loading) return;
        loading = true;
        var page=$('[data-role="page_info"]').attr('data-value');
        var uid=$('[data-role="page_info"]').attr('data-uid');
        var type=$('[data-role="page_info"]').attr('data-type');
        var url = U('Ucenter/Index/fans');
        $.get(url,{page:++page,uid:uid,type:type,is_pull:1},function (res) {
            if(res.status==1){
                if(res.html==''){
                    $.detachInfiniteScroll($('.infinite-scroll'));
                }
                else{
                    $('.list').append(res.html);
                    $('[data-role="page_info"]').attr('data-value',page);
                    loading = false;
                }
            }
           $('.infinite-scroll-preloader').css('display','none');

        });
        
    });
    // 添加'refresh'监听器
    $(document).on('refresh', '.pull-to-refresh-content',function(e) {
        var url = U('Ucenter/Index/fans');
        var type=$('[data-role="page_info"]').attr('data-type');
        var uid=$('[data-role="page_info"]').attr('data-uid');
        $.get(url,{page:1,type:type,uid:uid,is_pull:1},function(res){
          if(res.status==1){
              $('.list').html(res.html);
              $.pullToRefreshDone('.pull-to-refresh-content');
          }
        });
    });
})

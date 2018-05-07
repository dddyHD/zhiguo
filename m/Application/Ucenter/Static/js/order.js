/**
 * Created by Administrator on 2017/8/31.
 */
$(function () {
    // var url="Ucenter/index/addListOrder";
    var total=$('.content1').attr('data-total');
    // var container='.content1';
    var data=1;
    var loading = false;
    var page=0;
    var maxItems=$('[ data-role="all"]').attr('data-total');
   $('[ data-role="all"]').click(function () {
       data=1;
       page=0;
       maxItems=$(this).attr('data-total');
       $('.content1').empty();
       addItems(data, lastIndex);
   });
    $('[ data-role="payment"]').click(function () {
        data=2;
        page=0;
        maxItems=$(this).attr('data-total');
        $('.content1').empty();
        addItems(data, lastIndex);
    });
    $('[ data-role="complete"]').click(function () {
        data=3;
        page=0;
        maxItems=$(this).attr('data-total');
        $('.content1').empty();
        addItems(data, lastIndex);
    });
    $('.screen span').click(function () {
        $(this).addClass('active').siblings().removeClass('active');
    });


    function addItems(data, lastIndex) {
        $('.infinite-scroll-preloader').css('display','');
        $.ajax( {
            url:U('Ucenter/index/addListOrder'),
            data:{
                page:++page,
                data:data
            },
            type:'post',
            cache:false,
            dataType:'json',
            async:true,
            success:function(res) {
                if(res.status ==true){
                    $('.content1').append(res.data);
                    lastIndex = $('.content1 li').length;
                    if (lastIndex<10){
                        console.log(111);
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
    addItems(data, 0);
//分页条数每页十条 对应U('Forum/index/commonForumData') 里面10条
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
            addItems(data, lastIndex);
            lastIndex = $('#tab2 a').length;
        }, 1000);
    });

});
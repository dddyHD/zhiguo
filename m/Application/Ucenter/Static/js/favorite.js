/**
 * Created by Administrator on 2017/05/24.
 */

//加载更多刷新操作
var loading = false;
var maxItems = $('[data-role="Mall"]').attr('data-total');
var page=0;
var module='Mall';

$('[data-role="Mall"]').click(function () {
    var total=$(this).attr('data-total');
    maxItems=total;
    module="Mall";
    $(this).addClass('active')
    $('[data-role="News"]').removeClass('active');
    $('#tab .fBox').remove();
    $('#tab li').remove();
    page=0;
    addItems(module,lastIndex);
});

$('[data-role="News"]').click(function () {
    var total=$(this).attr('data-total');
    maxItems=total;
    module="News";
    $(this).addClass('active')
    $('[data-role="Mall"]').removeClass('active');
    $('#tab .fBox').remove();
    page=0;
    addItems(module,lastIndex);
});


//首次加载数据
function addItems(module, lastIndex) {
    $('.infinite-scroll-preloader').css('display','');
    var html = '';
    $.ajax( {
        url:U('Ucenter/Index/addList'),
        data:{
            page:++page,
            module:module,
        },
        type:'post',
        cache:false,
        dataType:'json',
        async:true,
        success:function(res) {
            if(res.status ==true){
                $('#tab').append(res.data);
                lastIndex = $('#tab li').length;
                if (lastIndex<10){
                    console.log(111)
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
addItems(module, 0);
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
        addItems(module, lastIndex);
        lastIndex = $('#tab li').length;
    }, 1000);
});
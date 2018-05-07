/**
 * Created by Administrator on 2017/7/20 0020.
 */

$(function () {
    //加载更多刷新操作
    var loading = false;
    var page=0;
    var sort='new';
    var priceflag=0;
    var salesflag=0;
//默认加载获取总条数
    var total=$('[data-role="new"]').attr('data-total');
    var type=$('[data-role="new"]').attr('data-type');
    var maxItems=total;
//最新排序
    $('[data-role="new"]').click(function () {
        var total=$(this).attr('data-total');
        maxItems=total;
        sort="news";
        $(this).addClass('h_active');
        $('[data-role="price"]').removeClass('h_active');
        $('[data-role="sales"]').removeClass('h_active');
        $('#tab2 a').remove();
        $('#tab2 div').remove();
        page=0;
        addItems(sort,lastIndex);
    });
//价格排序
    $('[data-role="price"]').click(function () {
        var total=$(this).attr('data-total');
        maxItems=total;
        if(priceflag==0){
            sort="price";
            priceflag=1;
            $('.price i').addClass('icon-xia');
            $('.price i').removeClass('icon-shang');
        }else{
            sort="price";
            priceflag=0;
            $('.price i').addClass('icon-shang');
            $('.price i').removeClass('icon-xia');
        }
        $(this).addClass('h_active');
        $('[data-role="new"]').removeClass('h_active');
        $('[data-role="sales"]').removeClass('h_active');
        $('#tab2 a').remove();
        $('#tab2 div').remove();
        page=0;
        addItems(sort,lastIndex);
    });
//销量排序
    $('[data-role="sales"]').click(function () {
        var total=$(this).attr('data-total');
        maxItems=total;
        sort="sales";
        $(this).addClass('h_active');
        $('[data-role="new"]').removeClass('h_active');
        $('[data-role="price"]').removeClass('h_active');
        $('#tab2 a').remove();
        $('#tab2 div').remove();
        page=0;
        addItems(sort,lastIndex);
    });
//首次加载数据
    function addItems(sort, lastIndex) {
        $('.infinite-scroll-preloader').css('display','');
        $.ajax( {
            url:U('Mall/index/loadList'),
            data:{
                page:++page,
                sort:sort,
                type:type,
                sales:salesflag,
                price:priceflag
            },
            type:'post',
            cache:false,
            dataType:'json',
            async:true,
            success:function(res) {
                if(res.status ==true){
                    $('#tab2').append(res.data);
                    lastIndex = $('#tab2 a').length;
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
                addGoodsInCar();
            },
            error : function() {
                $.toast('数据加载异常！')
            }
        });
    }
    addItems(sort, 0);
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
            addItems(sort, lastIndex);
            lastIndex = $('#tab2 a').length;
        }, 1000);
    });
});

function addGoodsInCar(){
    $('[data-role="join"]').unbind('click');
    $('[data-role="join"]').click(function () {
        var id=$(this).attr('data-id');
        var carCount=$('[data-role="carCount"]').html();
        var is_car=$(this).attr('data-car');
        var free=$('[data-id="'+id+'"]').html();
        if(!is_login()){
            $.toast('请登录后操作');
            return false;
        }
        if(free=='免费'){
            $.toast('免费商品，无需添加到购物车购买');
        }else{
            if(is_car){
                $.toast('已经添加到购物车');
            }else{
                $.post(U('Mall/index/joinCar'),{id:id},function (res) {
                    if (res.status) {
                        carCount++;
                        $('[data-role="carCount"]').html(carCount);
                        $('[data-id='+id+']').css('color','#ec725d');
                        $('[data-id='+id+']').attr('data-car',1);
                        $.toast('添加到购物车成功。');
                    } else {
                        $.toast('添加到购物车失败。');
                    }
                });
            }
        }
    })
}


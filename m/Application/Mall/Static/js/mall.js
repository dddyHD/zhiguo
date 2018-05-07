/**
 * Created by 王杰 on 2017/1/20.
 */
$(function(){
    $('[data-role="loadMore"]').loadMore();

    $('[data-role="cate_goods"]').click(function(){
        var $this = $(this);
        $('.aboveBox').toggle();
        $('.ori').css('display','block');
        $('.cate').css('display','none');
    });
    $('[data-role="goods"]').click(function(){
        var $this = $(this);
        $('.aboveBox').hide();
    });
    $('.aboveBox').click(function () {
        $('.ori').css('display','none');
        $('.cate').css('display','block');
    });
});

$.fn.loadMore = function(options){
    var defaults = {
        page:1,
        container:$('[data-role="all_goods"]')
    };
    option = $.extend(defaults,options);
    var $container = $(this);
    $container.click(function(){
        var $this = $(this);
        var url = U('Mall/Index/index');
        $.showIndicator();
        $.get(url,{page:++option.page,is_pull:1},function(res){
            if (res.html != '') {
                option.container.append(res.html);
            } else {
                $this.remove();
                $.toast('没有更多了~');
            }
            $.hideIndicator();
        })
    });
};


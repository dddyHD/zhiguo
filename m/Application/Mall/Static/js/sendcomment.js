/**
 * Created by Administrator on 2017/8/17.
 */
$(function () {
    $('[data-role="sendComment"]').click(function(){
        var goods_id = $(this).attr('data-id');
        var text = $('.sendArea').val();
        var url = U('Mall/Index/addGoodsComment');
        $.post(url,{content:text,goods_id:goods_id},function(res){
            if (res.status) {
                $('.commentList').empty();
                $('.commentList').prepend(res.html);
                $('.sendArea').val('');
                $.toast(res.info);
            } else {
                $.toast(res.info);
            }
        })
    });
});
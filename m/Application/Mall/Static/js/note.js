/**
 * Created by Administrator on 2017/8/29.
 */
$(function () {
    $('[data-role="write_notes"]').click(function(){
        var text = $('.notes').val();
        var position=$('.pag_number .page_number').text();
        var goods_id = $(this).attr('data-id');
        var url = U('Mall/Index/addNote');
        $.post(url,{content:text,position:position,goods_id:goods_id},function(res){
            if (res.status) {
                $('.notes').val('');
                $.toast(res.info);
            } else {
                $.toast(res.info);
            }
        })
    });
});
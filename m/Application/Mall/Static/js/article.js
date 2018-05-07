/**
 * Created by Administrator on 2017/7/20.
 */
$(function () {
    var i=0;
    var page_num=parseInt($('.page_num').html());
    $('[data-role="pre"]').click(function () {
        i--;
        toggle_pages();
    });
    $('[data-role="next"]').click(function () {
        i++;
        toggle_pages();
    });
    function toggle_pages() {
        url=U('Mall/Index/article');
        $.post(url,{page:i},function (res) {
            if(res.status==1){
                if(res.page<0){
                    i=0;
                }else if(res.page>page_num-1) {
                    i=page_num-1;
                }else{
                    $('.essay').html(res.content);
                    $('.page_number').html(res.page+1);
                }
            }
        })
    }

});
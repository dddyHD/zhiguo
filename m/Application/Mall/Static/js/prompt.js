/**
 * Created by Administrator on 2017/8/23.
 */
 //下架通知
function notice() {
    var modal = $.modal({
        zdywrap:'signWrap',
        afterText:  '<h3>商品下架通知</h3>'+
        '<p class="myScore">该商品已下架，但是您已经购买，仍可以继续阅读</p>',
        buttons: [
            {
                text: '已了解',
                bold: sure()
            }
        ]
    });
}
var shelf=$('.dName').attr('data-status');
var id=$('.do-collect').attr('data-id');
if(shelf==1){
    notice();
}
function sure() {
    var url = U('Mall/index/shelfPrompt');
    $.post(url,{id:id});
}
/**
 * Created by 王杰 on 2017/2/3.
 */
$(document).on('click','.do-collect',function(){
    var $this = $(this);
    var id = $this.attr('data-id');
    var url = U('Core/Collect/doCollect');
    $.post(url,{module:'Mall',table:'collect',row:id},function(res){
        if (res.status) {
            $this.find('.iconfont').css("color","red");
        } else {
            $this.find('.iconfont').css("color","black");
        }
        $.toast(res.info);
    })
});
$(document).on('click','.jump',function(){
    document.getElementById("jump").scrollIntoView();
});
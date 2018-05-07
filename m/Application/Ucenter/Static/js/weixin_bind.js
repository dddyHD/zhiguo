/**
 * Created by Administrator on 2017/7/11.
 */
$(function(){
    var $form = $('[data-role="form"]');
    $('[data-role="weixin_reg"]').click(function(){
        var url = $form.attr('data-url');
        var data = $form.serialize();
        $.post(url,data,function(res){
            if (res.status == 1) {
                $.toast('登录成功');
                window.location.href = U('mall');
            } else {
                $.toast(res.info);
            }
        })
    });
    $('[data-role="weixin_login"]').click(function(){
        var url = $form.attr('data-url');
        var username=$('#text').val();
        var password=$('#password').val();
        $.post(url,{username:username,password:password},function(res){
            if (res.status == 1) {
                $.toast('登录成功');
                window.location.href = U('mall');
            } else {
                $.toast(res.info);
            }
        })
    });
});
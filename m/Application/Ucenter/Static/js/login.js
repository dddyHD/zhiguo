/**
 * Created by Administrator on 2016/12/8.
 */
$(function(){
    var $form = $('[data-role="form"]');
    $('[data-role="login"]').click(function(){
        var username = $('#text').val();
        if(username=='') {
            $.toast("请输入手机号或邮箱");
            return false;
        }
        var url = $form.attr('data-url');
        var data = $form.serialize();
        $.post(url,data,function(res){
            if (res.status == 1) {
                $.toast('登录成功');
                setTimeout(function () {
                    window.location.href = U('Mall');
                },1000)
             
            } else {
                $.toast(res.info);
            }
        })
    });
    $('[data-role="logout"]').click(function () {
        var url=U('Ucenter/Member/logout');
        $.post(url,{},function (msg) {
            if (msg.status) {
                $.toast('退出成功');
                window.location.href = U('Ucenter/Index/index');
            } else {
                $.toast(msg.info);
            }
        })
    });
    $(document).on('click', '.signCircle',function() {
        var url=U('Ucenter/Index/rank');
        var uid=is_login();
        $.post(url,{uid:uid},function (res) {
            if(res.status==1){
                $('.con_check').html(res.con_check+'天');
                $('.total_check').html(res.total_check+'天');
                $('.signCircle').html('已签');
            }
          handleAjax(res);
        })
    })


    $('[name="password"]').keypress(function (e) {
        if (e.which == 13 || e.which == 10) {
            $('[data-role="login"]').click();
        }
    });

});
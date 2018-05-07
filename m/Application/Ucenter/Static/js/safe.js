/**
 * Created by Administrator on 2017/7/31.
 */
$(function(){

    $('[data-role="cancel_bind"]').click(function () {
        $.confirm('您确定取消绑定吗？', function () {
            var url=U('Ucenter/Index/safe');
            $.post(url,{sync:1},function (msg) {
                if (msg.status) {
                    $.toast(msg.info);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                } else {
                    $.toast(msg.info);
                }
            })
        });
    });

    $(".reloadverify").click(function () {
        var $this = $(this);
        var verifyimg = $this.attr("src");
        if (verifyimg.indexOf('?') > 0) {
            $this.attr("src", verifyimg + '&random=' + Math.random());
        } else {
            $this.attr("src", verifyimg.replace(/\?.*$/, '') + '?' + Math.random());
        }
    });

    var $form = $('[data-role="form"]');
    $('[data-role="bind_mobile"]').click(function(){
        var url = $form.attr('data-url');
        var data = $form.serialize();
        $.post(url,data,function(res){
            if (res.status == 1) {
                $.toast('手机绑定成功');
                window.location.href = res.url;
            } else {
                $.toast(res.info);
            }
        })
    });

    $('[data-role="cancel_mobile_bind"]').click(function () {
        $.confirm('您确定取消绑定吗？', function () {
            var url=U('Ucenter/Index/safe');
            $.post(url,{mobile:1},function (msg) {
                if (msg.status) {
                    $.toast(msg.info);
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                } else {
                    $.toast(msg.info);
                }
            })
        });
    });
    
    $('[data-role="sendVerify"]').click(function(){
        var $this = $(this);
        console.log($this.hasClass('disabled'));
        if ($this.hasClass('disabled')) {
            return false;
        }
        //获取短信验证码
        var phone = $('#tel').val();
        var verify = $('#verifyCode').val();
        var url=U('Ucenter/Verify/sendVerify');
        var type='mobile';
        if(phone==''){
            $.toast('请输入手机号');
            return false;
        } else if (!((/^1[34578]\d{9}/.test(phone)))) {
            $.toast("手机号码有误");
            return false;
        }
        if(verify==''){
            $.toast('请输入验证码');
            return false;
        }

        $.post(url, {account: phone, type: type, action: 'member',verify:verify}, function (res) {
            if (res.status) {
                $this.addClass('disabled');
                var time = 60;
                var start=setInterval(function () {
                    $this.html('等待 '+time+' s');
                    time--;
                    if(time==-1){
                        $this.removeClass('disabled');
                        $this.html('发送');
                        clearInterval(start);
                    }
                },1000);
                $.closeModal('.popup-about');
            } else {
                $(".reloadverify").click();
            }
            $.toast(res.info);
        });
        return false;
    });

    /*$('[data-role="bind_weixin"]').click(function () {
        var url=U('Ucenter/Index/safe');
        $.post(url,{sync:1},function (msg) {
            if (msg.status) {
                $.toast(msg.info);
                setTimeout(function () {
                    window.location.href = U('Ucenter/Index/safe');
                }, 2000);
            } else {
                $.toast(msg.info);
            }
        })
    });*/

});
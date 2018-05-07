/**
 * Created by 王杰 on 2016/12/8.
 */
$(function(){
    $(".reloadverify").click(function () {
        var $this = $(this);
        var verifyimg = $this.attr("src");
        if (verifyimg.indexOf('?') > 0) {
            $this.attr("src", verifyimg + '&random=' + Math.random());
        } else {
            $this.attr("src", verifyimg.replace(/\?.*$/, '') + '?' + Math.random());
        }
    });

    // 注册页面打开输入验证码的弹窗
    $(document).on('click','.open-about', function () {
        var tel = $('#tel').val();
        if(tel==''){
            $.toast("请输入手机号码");
        }else if(!((/^1[34578]\d{9}/.test(tel)))){
            $.toast("手机号码有误");
        } else {
            $('[data-role="tel"]').val(tel);
            $.popup('.popup-about');
        }
    });
    // 打开协议
    $(document).on('click','.open-protocol', function () {
            $.popup('.protocol_content');
    });

    $('[data-role="register"]').click(function(){
        if($('.checkbox').get(0).checked){
            var $form = $('[data-role="form"]');
            var url = $form.attr('data-url');
            var data = $form.serialize();
            $.post(url,data,function(res){
                console.log(res);
                if (res.status == 0) {
                    $.toast(res.info);
                } else {
                    $.toast('注册成功,正在跳转...');
                    setTimeout(function(){
                        window.location.href = U('Ucenter/Index/index');
                    },1500);
                }
            })
        }else {
            $.toast('请同意《用户注册协议》');
        }

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
            }
            $.toast(res.info);
        });
        return false;
    });
    $('[data-role="sendRestVerify"]').click(function () {
        var $this = $(this);
        console.log($this.hasClass('disabled'));
        if ($this.hasClass('disabled')) {
            return false;
        }
        //获取短信验证码
        var phone = $('#tel').val();
        var verify = $('#verifyCode').val();
        var url=U('Ucenter/Verify/sendVerifyFindPsw');
        var type='mobile';
        if(phone==''){
            $.toast('请输入手机号');
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
            }
            $.toast(res.info);
        });
        return false;
    });
    $('[data-role="resetPassword"]').click(function () {
        var $this = $(this);
        // var password=$("#password").val();
        // var rpassword=$("#rpassword").val();
        // // console.log(password)
        // // console.log(rpassword)
        // if (password != rpassword){
        //     $.toast('两次密码不一致');
        //     return false;
        // }
        if ($("#tel").val()==null){
            $.toast('手机号不能为空');
            return false;
        }
        if ($("#verify_code").val()==null){
            $.toast('验证码不能为空');
            return false;
        }
        if ($this.hasClass('disabled')) {
            return false;
        }
        var $form = $('[data-role="form"]');
        var url = $form.attr('data-url');
        var data = $form.serialize();
        $.post(url,data,function(res){
            console.log(res);
            if (res.status == 0) {
                $.toast(res.info);
            } else {
                $.toast('修改成功,新密码为123456');
                setTimeout(function(){
                    window.location.href = U('Ucenter/member/login');
                },1500);
            }
        })

    })
});
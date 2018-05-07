/**
 *
 */
$(function(){

    $('[data-role="resetPasswordByPassword"]').click(function () {
        var $this = $(this);
        if ($("#oldPassword").val()==null){
            $.toast('密码不能为空');
            return false;
        }
        if ($("#newPassword").val()==null||$("#RenewPassword").val()==null){
            $.toast('新密码不能为空');
            return false;
        }
        if ($this.hasClass('disabled')) {
            return false;
        }

        var url = 'Ucenter/index/resetPassword';

        $.post(url,{oldPassword:$("#oldPassword").val(),newPassword:$("#newPassword").val(),RenewPassword:$('#RenewPassword').val()},function(res){
            console.log(res);
            if (res.status == 0) {
                $.toast(res.info);
            } else {
                $.toast(res.info);
                 window.location.href = U('mall/index/index');
            }
        })

    })
});
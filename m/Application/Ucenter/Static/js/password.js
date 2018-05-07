/**
 * Created by Administrator on 2017/7/25.
 */
$(function () {
    // 点击密码眼睛时候，眼睛变红，密码可见
    $('[data-role="visible"]').click(function () {
      if($('#password').attr("type")=='password'){
          $('#password').attr("type",'text');
          $('[data-role="visible"]').css('color','#ec725f');
      }else {
          $('#password').attr("type",'password');
          $('[data-role="visible"]').css('color','#333333');
      }
    });
    // 点击绑定按钮
    $('[data-role="binding_accounts"]').click(function () {
        $('[data-role="binding_accounts"]').addClass('active');
        $('[data-role="register_accounts"]').removeClass('active');
        $('.binding_accounts').show();
        $('.register_accounts').hide();
    });
    //点击注册新帐号
    $('[data-role="register_accounts"]').click(function () {
        $('[data-role="register_accounts"]').addClass('active');
        $('[data-role="binding_accounts"]').removeClass('active');
        $('.binding_accounts').hide();
        $('.register_accounts').show();
    });
});

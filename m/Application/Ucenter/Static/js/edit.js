$(function () {
    // 修改头像
    $('[data-role="change_avatar"]').click(function () {
        var uid = $('[name="uid"]').attr('data-uid');
        var url = U('Ucenter/Index/avatar');
        $.post(url, {uid: uid}, function (msg) {
            if (msg.status) {
                window.location.href = url;
            } else {
                $.toast(msg.info);
            }

        })


    });
    // 修改昵称
    $('[data-role="change_name"]').click(function () {
        $.prompt('编辑昵称',
            function (value) {
                if (value.length <= 0) {
                    $.alert('修改失败');
                    return false;
                }
                var type = $('[data-role="change_name"]').attr('data-value');
                post_function(type, value);
                $('.nickname').html(value);
            }
        );
    });
    // 选择性别
    $('[data-role="chose_sex"]').picker({
        toolbarTemplate: '<header class="bar bar-nav">\
        <button class="button button-link pull-right close-picker">确定</button>\
        <p class="title">性别修改</p>\
        </header>',
        cols: [
            {
                textAlign: 'center',
                values: ['男', '女', '保密']
            }
        ],
        onClose: function () {
            var type = 'sex';
            post_function(type, $('[data-role="chose_sex"]').val());
        }

    });


    // 选择生日
    $('[data-role="chose_birth"]').datePicker({
        toolbarTemplate: '<header class="bar bar-nav">\
        <button class="button button-link pull-right close-picker">确定</button>\
        <p class="title">选择生日</p>\
        </header>',

        onClose: function () {
            var type = 'birthday';
            post_function(type, $('[data-role="chose_birth"]').val());
        }
    });
    //修改签名
    $('[data-role="change_sign"]').click(function () {
        $.prompt('编辑签名',
            function (value) {
                if (value.length <= 0) {
                    $.alert('修改失败');
                    return false;
                }
                var type = $('[data-role="change_sign"]').attr('data-value');
                post_function(type, value);
                $('.signature').html(value);
            }
        );
    });
    //选择地址
    $('[data-role="chose_city"]').cityPicker({
        toolbarTemplate: '<header class="bar bar-nav">\
        <button class="button button-link pull-right close-picker">确定</button>\
        <p class="title">选择地区</p>\
        </header>',
        onClose: function () {
            var url = U('Ucenter/Member/changePos');
            var uid = $('[name="uid"]').attr('data-uid');
            $.post(url, {uid: uid, pos: $('[data-role="chose_city"]').val()}, function (res) {
                if (res.status) {
                    $('input[name="city"]').val(res.value);
                }
            })

        }

    });


    //修改手机
    $('[data-role="chose_phone"]').click(function () {
        $.prompt('编辑',
            function (value) {
                if (value.length <= 0) {
                    $.alert('修改失败');
                    return false;
                }
                var url = U('Ucenter/Member/changePhone');
                var uid = $('[name="uid"]').attr('data-uid');
                $.post(url, {uid: uid, value: value}, function (res) {
                    if (res.status) {
                        $('.mobile').html(res.value);
                    }
                })
            }
        );
    })

    var post_function = function (type, value) {
        var url = U('Ucenter/Member/changeUserData');
        var uid = $('[name="uid"]').attr('data-uid');
        $.post(url, {type: type, uid: uid, value: value}, function (res) {
            //$.toast(res.info);
        })
    };


});

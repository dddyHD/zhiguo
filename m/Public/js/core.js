
$(document).on('click', '[data-role="follow"]', function () {
    var $this = $(this);
    var uid = $this.attr('data-follow-who');
    $.post(U('Core/Public/follow'), {uid: uid}, function (msg) {
        if (msg.status) {
            $this.css('color', '#ec725d');
            $this.css('border-color', '#ec725d');
            $this.attr('data-role', 'unfollow');
            $this.html('已关注');
            $.toast(msg.info);
        } else {
            $.toast(msg.info);
        }
    }, 'json');
});

$(document).on('click', '[data-role="unfollow"]', function () {
    var $this = $(this);
    var uid = $this.attr('data-follow-who');
    $.confirm('你确定要取消关注吗？',
        function () {
            $.post(U('Core/Public/unfollow'), {uid: uid}, function (msg) {
                if (msg.status) {
                    $this.css('color', '#333');
                    $this.css('border-color', '#333');
                    $this.attr('data-role', 'follow');
                    $this.html('关注');
                    $.toast(msg.info);
                } else {
                    $.toast(msg.info);
                }
            }, 'json');
        },
        function () {
            return false;
        }
    );
});

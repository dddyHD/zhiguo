/**
 * Created by Administrator on 2016/12/14 0014.
 */
$(function () {
    $('[data-role="loadMore"]').click(function () {
        var $this=$(this);
        var page=$this.attr('data-value');
        var tab=$this.attr('data-type');
        var uid=$this.attr('data-uid');
        var inx;
        switch (tab){
            case 'moreFriend':
                inx='addMoreFriend';
                break;
            case 'moreWeibo':
                inx='addMoreWeibo';
                break;
            case 'moreMyCrowd':
                inx='addMoreMyCrowd';
                break;
            case 'moreCrowd':
                inx='addMoreCrowd';
                break;
            default:
                inx='addMoreFriend';
        }
        var url=U('Ucenter/Index/'+inx);

        $.post(url,{page:++page,uid:uid},function (res) {
            if(res.status==0){
               $.toast(res.info);
                $this.remove();
            }else{
                $this.before(res);
                $this.attr('data-value',page);
            }

        })
    })
    // 修改头像
    $('[data-role="change_avatar"]').click(function () {
        var uid=$(this).attr('data-uid');
        var url=U('Ucenter/Index/avatar');
        $.post(url,{uid:uid},function (msg) {
            if(msg.status){
                window.location.href=url;
            }else{
                $.toast(msg.info);
            }

        })


    });
});

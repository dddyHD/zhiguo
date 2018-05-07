/**
 * Created by 王杰 on 2016/12/15.
 */
$(function(){
    $(document).on('click','.do-support',function(){
        var $this = $(this);
        var id = $this.closest('.operate').attr('data-id');
        var uid = $this.closest('.operate').attr('data-uid');
        var url = U('Core/Support/doSupport');
        $.post(url,{appname:'Weibo',table:'weibo',row:id,uid:uid},function(res){
            if (res.status) {
                var count = parseInt($this.children('.support-count').text());
                $this.children('.icon-dianzan').css('color','#ec725d');
                if (!isNaN(count)) {
                    $this.children('.support-count').text(++count);
                }
            }
            $.toast(res.info);
        })
    });

    $(document).on('click','.do-support-forum',function(){
        var $this = $(this);
        var id = $this.closest('.operate').attr('data-id');
        var uid = $this.closest('.operate').attr('data-uid');
        var url = U('Core/Support/doSupportAll');
        $.post(url,{appname:'Forum',table:'forum',row:id,uid:uid},function(res){
            if (res.status) {
                var count = parseInt($this.children('.support-count').text());
                $this.children('.icon-dianzan').css('color','#ec725d');
                if (!isNaN(count)) {
                    $this.children('.support-count').text(++count);
                }
            }
            $.toast(res.info);
        })
        event.stopPropagation();
    });

    $(document).on('click','.do-support-lzl',function(){
        var $this = $(this);
        var id = $this.closest('.operate1').attr('data-id');
        var uid = $this.closest('.operate1').attr('data-uid');
        var url = U('Core/Support/doSupportAll');
        $.post(url,{appname:'Forum-lzl',table:'forum',row:id,uid:uid},function(res){
            if (res.status) {
                var count = parseInt($this.children('.support-count').text());
                $this.children('.icon-dianzan').css('color','#ec725d');
                if (!isNaN(count)) {
                    $this.children('.support-count').text(++count);
                }
            }
            $.toast(res.info);
        })
        event.stopPropagation();
    });

    $(document).on('click','.do-support-news',function(){
        var $this = $(this);
        var id = $this.closest('.operate').attr('data-id');
        var uid = $this.closest('.operate').attr('data-uid');
        var appname = $this.closest('.operate').attr('data-support');
        var url = U('Core/Support/doSupportAll');
        $.post(url,{appname:appname,table:'news',row:id,uid:uid},function(res){
            if (res.status) {
                var count = parseInt($this.children('.support-count').text());
                $this.children('.icon-dianzan').css('color','#ec725d');
                if (!isNaN(count)) {
                    $this.children('.support-count').text(++count);
                }
            }
            $.toast(res.info);
        })
        event.stopPropagation();
    });

    $(document).on('click','.do-support-news-a',function(){
        var $this = $(this);
        var id = $this.closest('.operate').attr('data-id');
        var uid = $this.closest('.operate').attr('data-uid');
        var appname = $this.closest('.operate').attr('data-support');
        var url = U('Core/Support/doSupportAll');
        $.post(url,{appname:appname,table:'news',row:id,uid:uid},function(res){
            if (res.status) {
                var count = parseInt($this.children('.support-count').text());
                $this.children('.icon-xin').css('color','#ec725d');
                var comment=$('[data-role="support-count"]').attr('data-total');
                comment=parseInt(comment);
                comment++;
                $('[data-role="support-count"]').text(comment+'喜欢');
                $('[data-role="support-count"]').attr('data-total',comment);
                if (!isNaN(count)) {
                    $this.children('.support-count').text(++count);
                }
            }
            $.toast(res.info);
        })
        event.stopPropagation();
    });

    $(document).on('click','.do-support-question',function(){
        var $this = $(this);
        var id = $this.closest('.operate1').attr('data-id');
        var uid = $this.closest('.operate1').attr('data-uid');
        var url = U('Core/Support/doSupportQuestion');
        $.post(url,{appname:'question-answer',table:'question',row:id,uid:uid},function(res){
            if (res.status) {
                var count = parseInt($this.children('.support-count1').text());
                $this.children('.icon-unie60b').css('color','#ec725d');
                if (!isNaN(count)) {
                    $this.children('.support-count1').text(++count);
                }
            }
            $.toast(res.info);
        })
        event.stopPropagation();
    });

    $(document).on('click','.preview-image',function(){
        var $this = $(this);
        var imgArr = [];
        var id = $this.attr('data-id');
        var index = $this.attr('data-index');
        $this.closest('.preview').children('.preview-image').each(function(){
            if ($(this).attr('data-id') == id) {
                imgArr.push($(this).attr('data-img'));
            }
        });

        var myPhotoBrowserStandalone = $.photoBrowser({
            photos : imgArr,
            swipeToClose : false,
            ofText : '/'
        });
        myPhotoBrowserStandalone.open(index);
        return false;
    });

    $(document).on('click','.photo-browser-close-link',function(){
        $('.photo-browser-light').remove();
    });

    $(document).on('click','.popup-overlay',function(){
        $.closeModal();
    });

    // 关注
    $(document).on('click','.do-active',function () {
        var $this=$(this);
        var type=$this.attr('data-value');
        var uid=$this.attr('data-uid');
        var url=U('Ucenter/Index/follow');
        if(type=='unfollow'){
            $.confirm('取消关注?', function () {
                    $.post(url,{uid:uid,type:type},function (res) {
                        if(res.status==0){
                            $.toast(res.info);
                        }else{
                            $.toast("取消了关注");
                            $this.html('关注');
                            $this.attr('data-value','follow');
                        }
                    })
                }
            );
        }else{
            $.post(url,{uid:uid,type:type},function (res) {
                if(res.status==0){
                    $.toast(res.info);
                }else{
                    $.toast("关注了TA");
                    $this.html('已关注');
                    $this.attr('data-value','unfollow');
                }
            })
        }

    });

    // 拆红包
    $(document).on('click',"[data-role='open-bag']",function(){
        var redbagId = $(this).attr('data-id');
        var token = $(this).attr('data-token');
        var url = U('Weibo/Index/redbagDetail');
        var redbag = {};

        $.ajax({
            type : "get",
            url : url,
            data : {id:redbagId},
            async : false,
            success : function(res){
                redbag = res;
            }
        });

        if (redbag.info.is_get != 0 || redbag.info.is_done == 1 || redbag.info.is_overtime == 1) {
            var url = U('Weibo/Index/result',['id',redbagId,'token',token]);
            location.href = url;
        } else {
            var modal = $.modal({
                zdywrap:'openBag',
                afterText:  '<i class="iconfont icon-tongqian oneIcon"></i><div class="redTop">' +
                '<div class="owner"><img src="'+ redbag.info.user.avatar128 +'"></div><p class="redName">'+ redbag.info.user.nickname+'</p><p class="redText">发布了一个红包</p>' +
                '<p class="redInfo">'+ redbag.info.content +'</p>' +
                '<div class="redOpen" data-role="show-result" data-id="'+redbagId+'" data-token="'+ token +'"><div class="redSmall flexWrap"><i class="iconfont icon-kai-01"></i></div></div>' +
                '<span class="redClose icon-iconfontcha iconfont" data-role="close-bag"></span></div>'
            });
            $("[data-role='close-bag']").click(function () {
                $.closeModal(modal)
            });
            $("[data-role='show-result']").click(function () {
                var url = U('Weibo/Index/doOpenRedBag');
                var id = $(this).attr('data-id');
                var token = $(this).attr('data-token');
                $.post(url,{redBagId:id,token:token},function(res){
                    $.toast(res.info);
                    if (res.status == -1) {
                        $.toast(res.info);
                    } else {
                        setTimeout(function () {
                            var url = U('Weibo/Index/result',['id',id,'token',token]);
                            window.location.href = url;
                        },2000);
                    }
                });
            });
        }
        return false;
    });
});

//论坛帖子评论的表情
var $swiper = null;
var $textarea = $('.sendArea');
var get_emoji = function () {
    if ($swiper != null) {
        return false;
    }
    $('.send-bottom.emoji .emoji-list').html('');
    $.get(U('core/expression/getEmoji'), {}, function (res) {
        for (var i in res) {
            $('.send-bottom.emoji .emoji-list').append('<div class="swiper-slide"></div>');
        }
        $swiper = $(".swiper-container").swiper({
            slidesPerView: 1,
            paginationClickable: true,
            spaceBetween: 30,
            pagination: '.swiper-pagination',
            loop: true,
            onSlideChangeStart: function (swiper) {
                var index = 0;
                if (swiper.snapIndex > 0 && swiper.snapIndex <= res.length) {
                    index = swiper.snapIndex - 1
                }
                if (swiper.snapIndex < 1) {
                    index = res.length - 1;
                }
                var t1 = res[index];
                var html = '';
                for (var j in t1) {
                    var t2 = t1[j];
                    html += emojione.toImage(':' + t2 + ':');
                }
                $('[data-swiper-slide-index="' + index + '"]').html(html);
                bind_emoji();
            }
        });
    });
};
$textarea.focus(function () {
    $('.send-bottom').hide();
});
$('[data-role="open-emoji"]').click(function () {
    $('.send-bottom').show();
    get_emoji();
    var h = screen.width / 10 * 4 + 5;
    $('.send-bottom').css('height', h);
});
var bind_emoji = function () {
    var $emojione = $('.emojione');
    $emojione.unbind('click');
    $emojione.click(function () {
        var $this = $(this);
        var img = '<img data-title="' + $this.attr('title') + '" src="' + $this.attr('src') + '" style="width: 24px;height: 24px" class="img-disable"/>';
        $textarea.append(img);
    })
};
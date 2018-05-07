/**
 * Created by 74176 on 2017/7/3.
 */
$(function () {
    var change=$("[data-role='hotChange']");
    var page=2;
    $("[data-role='change']").click(function () {
        $('.icon-shuaxin').addClass('rotate');
        var url=U('Mall/Index/search');
        $.post(url,{page:page},function (res) {
            if(res.status==1){
                change.empty();
                for(var i=0;i<res.data.length;i++){
                    change.append("<a class='myId' '>"+"<span class='hotContent'>"+res.data[i]+"</span>"+"</a>");
                    $(".hotContent").eq(i).text(res.data[i].name);
                     $(".myId").eq(i).attr("href","index.php?s=mall/index/detail/id/"+res.id[i]);
                }
                page++;
                //如果是最后一组了就从第一组开始
                if(res.is==0){
                    page=2;
                }
            }
            setTimeout(function () {
                $('.icon-shuaxin').removeClass('rotate');
            },500)

        })
    });

    //搜索功能
    $("#search").keydown(function (event) {
        if(event.keyCode==13){
            var content=$("#search").val();
            if(content==""){
                $.toast("搜索内容不能为空");
                return;
            }
            var url=U('Mall/Index/allSearch');
            $.post(url,{title:content},function (res) {
                if(res.status=="1"){
                    $("[data-role='myList']").empty();
                    if(res.html=="none"){
                        $("[data-role='myList']").append(" <div class='noWrap'>"+"<p class='emojiText'>╭(╯^╰)╮</p>"+
                            "<p class='noState open-about'>暂无搜索结果</p>"+" </div>");
                    }
                    else{
                        $("[data-role='myList']").append(res.html);
                    }
                    $("[data-role='hotChange']").css("display","none");
                    $("[data-role='change']").css("display","none");
                    $("[data-role='hotSearch']").text("相关搜索");
                    $(".history").eq(0).css("display","none");
                    $("#myHistory").empty();
                    $("#myHistory").append(res.goods);
                }
            })
        }
        if(event.keyCode==8){
            if($("#search").val().length==1){
                $("[data-role='myList']").empty();
                $("[data-role='hotChange']").css("display","block");
                $("[data-role='change']").css("display","block");
                $("[data-role='hotSearch']").text("热门搜索");
                $(".history").eq(0).css("display","block");
            }
        }
    });

})
//删除一条搜索记录
function deleteOneSearchHistory (id){
    var url=U('Mall/Index/delete');
    var id=id;
    $.post(url,{id:id},function (res) {
        if(res.status=="1"){
            $("#myHistory").empty();
            $("#myHistory").append(res.html);
            if($("#myHistory li").length==1){
                $("#myHistory").empty();
            }
        }
    })
};

//清空历史记录
function allclear() {
    var url=U('Mall/Index/allDelete');
    $.post(url,{},function (res) {
        if(res.status=="1"){
            $("#myHistory").empty();

        }
    })
}

function historyClick(content) {
    $("#search").val(content);
    var url=U('Mall/Index/allSearch');
    $.post(url,{title:content,is:"no"},function (res) {
        if(res.status=="1"){
            $("[data-role='myList']").empty();
            if(res.html=="none"){
                $("[data-role='myList']").append(" <div class='noWrap'>"+"<p class='emojiText'>╭(╯^╰)╮</p>"+
                    "<p class='noState open-about'>暂无搜索结果</p>"+" </div>");
            }
            else{
                $("[data-role='myList']").append(res.html);
            }
            $("[data-role='hotChange']").css("display","none");
            $("[data-role='change']").css("display","none");
            $("[data-role='hotSearch']").text("相关搜索");
            $(".history").eq(0).css("display","none");

        }
    })
}

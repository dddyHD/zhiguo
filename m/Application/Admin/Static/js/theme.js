/**
 * Created by Administrator on 2017/8/28.
 */
$(function () {
    var length=$('.choose input').length;
    var url=U('mall/index/themeType');
    $('[data-role="Preservation"]').click(function () {
            var val=$('input:radio[name="theme"]:checked').val();
            $(this).unbind();
            $.post(url,{data:parseInt(val)},function(res){
                if(res.status==1){
                    toast.success(res.info);
                }else{
                    toast.error(res.info);
                }

            });
    });
    $('[data-role="choose"]').click(function () {
        $(this).children('input').attr("checked",true);
    });
});
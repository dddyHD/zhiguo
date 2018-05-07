/**
 * Created by Administrator on 16-7-8.
 * @author 郑钟良<zzl@ourstu.com>
 */
(function($){

    var os_icon_list=[
        "icon-luntan2","icon-luntan1","icon-luntan","icon-zixun11","icon-zixun2","icon-zixun1","icon-zixun","icon-wenda3","icon-wenda2","icon-wenda11","icon-wenda","icon-aiguifan","icon-fuwu","icon-quanzi5","icon-xe66d","icon-quanzi4","icon-quanzi3","icon-quanzi1","icon-quanzi","icon-mall","icon-goods_light","icon-goods_new_light","icon-we_light","icon-dianpu","icon-xiaoxi1","icon-xiaoxi","icon-tongzhi","icon-geren1","icon-wode","icon-shouye","icon-fujinjingjiren","icon-geren","icon-pinglun","icon-zhuye","icon-dongtai3","icon-dongtai2","icon-dongtai11","icon-iconfontdongtai","icon-dongtai","icon-comment","icon-shouye-shouye","icon-fenlei","icon-remind","icon-store_icon","icon-me_line","icon-home_line","icon-news","icon-wodechanpin","icon-atregular","icon-store"
    ];

    var OS_ICON=function(element,options){
        this.select=element;
        this.options=$.extend({},$.fn.select_os_icon.defaults,options);
        this.init();
    }

    OS_ICON.prototype={
        init:function(){
            var $tag=this.select;
            $tag.find('option').remove();
            $tag.parent().find('.select-os-icon-block').remove();
            this._append_options_html($tag)._append_select_html($tag.parent());
            $tag.hide();
            return this;
        },
        _append_options_html:function($tag){
            var html='<option value="-">无</option>';
            for(var key in os_icon_list){
                html+='<option value="'+os_icon_list[key]+'">icon iconfont icon-'+os_icon_list[key]+'</option>';
            }
            $tag.append(html);
            return this;
        },
        _append_select_html:function($tag){
            var html='<div class="select-os-icon-block"><a class="select-single" data-role="select-single" tabindex="-1"><span title="[没有图标]">[没有图标]</span><div><b></b></div></a><div class="option-list"><ul class="select-results">';
            html+='<li class="active-result" title="" data-option-array-index="0"><em></em>[没有图标]</li>';
            for(var key in os_icon_list){
                html+='<li class="active-result icon" title="" data-option-array-index="'+(parseInt(key)+1)+'"><i class="icon iconfont '+os_icon_list[key]+'" title="'+os_icon_list[key]+'"></i></li>';
            }
            html+='</ul></div></div>';
            $tag.append(html);
            $tag.each(function(){
                var icon_name=$(this).find('.select-os-icon').attr('data-value');
                if(icon_name!='-'){
                    $(this).find('.select-single span').attr('title',icon_name).html('<i class="icon iconfont '+icon_name+'"></i>'+'icon-'+icon_name);
                }
            });
            return this;
        },
        bind_select:function(){
            $('[data-role="select-single"]').unbind();
            $('[data-role="select-single"]').click(function(){
                var $tag=$(this).parents('.select-os-icon-block');
                if($tag.hasClass('active')){
                    $tag.removeClass('active');
                }else{
                    $('.select-os-icon-block').removeClass('active');
                    $tag.addClass('active');
                }
                return true;
            });
            $('.active-result').unbind();
            $('.active-result').click(function(){
                var $tag=$(this).parents('.select-os-icon-block');
                var num=parseInt($(this).attr('data-option-array-index'));
                $tag.removeClass('active');
                if(num===0){
                    $tag.find('.select-single span').attr('title','[没有图标]').html('[没有图标]');
                    $tag.siblings('.select-os-icon').val('-').attr('data-value','-');
                }else{
                    num--;
                    $tag.find('.select-single span').attr('title',os_icon_list[num]).html('<i class="icon iconfont '+os_icon_list[num]+'"></i>'+os_icon_list[num]);
                    $tag.siblings('.select-os-icon').val(os_icon_list[num]).attr('data-value',os_icon_list[num]);
                }
            });
            return this;
        }
    }


    $.fn.select_os_icon=function(opts){
        var os_icon=new OS_ICON(this,opts);
        os_icon.bind_select();
        var icon='-';
        $(this).each(function(){
            icon=$(this).attr('data-value');
            $(this).val(icon);
        })
        return this;
    }
    $.fn.select_os_icon.defaults={

    }
})(jQuery);
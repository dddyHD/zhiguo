$("#method").change(function() {
    var pick = $(this).val();
    switch (pick) {
        case '0':
            $(".search-input").attr("placeholder", "请选择搜索方式");
            break;
        case '1':
            $(".search-input").attr("placeholder", "请输入用户UID");
            break;
        case '2':
            $(".search-input").attr("placeholder", "请输入订单编号");
            break;
    }
});
$("#search").click(function () {
    var url = $(this).attr('url');
    var number = $('#num').val();
    var min_amount = $('#min').val();
    var max_amount = $('#max').val();
    var method = $('#method').serialize();
    var num = $('#num').serialize();
    var cate = $('#cate').serialize();
    var min = $('#min').serialize();
    var max = $('#max').serialize();
    var start = $('#start-time').serialize();
    var end = $('#end-time').serialize();
    if(isNaN(number)||isNaN(min_amount)||isNaN(max_amount)){
        toast.error('请输入的数值');
        return false;
    }
    if(min_amount>max_amount){
        toast.error('请输入正确的数额范围');
        return false;
    }
    method = method.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    method = method.replace(/^&/g, '');

    num = num.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    num = num.replace(/^&/g, '');

    cate = cate.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    cate = cate.replace(/^&/g, '');

    min = min.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    min = min.replace(/^&/g, '');

    max = max.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    max = max.replace(/^&/g, '');

    start = start.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    start = start.replace(/^&/g, '');

    end = end.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    end = end.replace(/^&/g, '');
    if (url.indexOf('?') > 0) {
        url += '&' + method + '&' + num + '&' + cate + '&' + min + '&' + max + '&' + start + '&' + end;
    } else {
        url += '?' + method + '&' + num + '&' + cate + '&' + min + '&' + max + '&' + start + '&' + end;
    }

    window.location.href = url;
});
$(".search-input").keyup(function (e) {
    if (e.keyCode === 13) {
        $("#search").click();
        return false;
    }
});
$(function () {
    $('#time-inputs').dateRangePicker({
        separator : ' to ',
        showShortcuts:false,
        getValue: function()
        {
            if ($('#start-time').val() && $('#end-time').val() )
                return $('#start-time').val() + ' to ' + $('#end-time').val();
            else
                return '';
        },
        setValue: function(s,s1,s2)
        {
            $('#start-time').val(s1);
            $('#end-time').val(s2);
        }
    });
});
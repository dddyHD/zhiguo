function is_login() {
    return parseInt(MID);
}

/**
 * 模拟U函数
 * @param url
 * @param params
 * @returns {string}
 * @constructor
 */
function U(url, params, rewrite) {


    if (window.Think.MODEL[0] == 2) {

        var website = _ROOT_ + '/';
        url = url.split('/');

        if (url[0] == '' || url[0] == '@')
            url[0] = APPNAME;
        if (!url[1])
            url[1] = 'Index';
        if (!url[2])
            url[2] = 'index';
        website = website + '' + url[0] + '/' + url[1] + '/' + url[2];

        if (params) {
            params = params.join('/');
            website = website + '/' + params;
        }
        if (!rewrite) {
            website = website + '.html';
        }

    } else {
        var website = _ROOT_ + '/index.php';
        url = url.split('/');
        if (url[0] == '' || url[0] == '@')
            url[0] = APPNAME;
        if (!url[1])
            url[1] = 'Index';
        if (!url[2])
            url[2] = 'index';
        website = website + '?s=/' + url[0] + '/' + url[1] + '/' + url[2];
        if (params) {
            params = params.join('/');
            website = website + '/' + params;
        }
        if (!rewrite) {
            website = website + '.html';
        }
    }

    if (typeof (window.Think.MODEL[1]) != 'undefined') {
        website = website.toLowerCase();
    }
    return website;
}
/**播放背景音乐
 *
 * @param file 文件路径
 */
function playsound(file) {
    if (window.Think.ROOT == '') {
        file = '/' + file;
    } else {
        file = window.Think.ROOT + '/' + file;
    }
    $('embed').remove();
    $('body').append('<embed src="' + file + '" autostart="true" hidden="true" loop="false">');
    var div = document.getElementById('music');
    div.src = file;
}

/**
 * 友好时间
 * @param sTime
 * @param cTime
 * @returns {string}
 */
function friendlyDate(sTime, cTime) {
    var formatTime = function (num) {
        return (num < 10) ? '0' + num : num;
    };

    if (!sTime) {
        return '';
    }

    var cDate = new Date(cTime * 1000);
    var sDate = new Date(sTime * 1000);
    var dTime = cTime - sTime;
    var dDay = parseInt(cDate.getDate()) - parseInt(sDate.getDate());
    var dMonth = parseInt(cDate.getMonth() + 1) - parseInt(sDate.getMonth() + 1);
    var dYear = parseInt(cDate.getFullYear()) - parseInt(sDate.getFullYear());

    if (dTime < 60) {
        if (dTime < 10) {
            return '刚刚';
        } else {
            return parseInt(Math.floor(dTime / 10) * 10) + '秒前';
        }
    } else if (dTime < 3600) {
        return parseInt(Math.floor(dTime / 60)) + '分钟前';
    } else if (dYear === 0 && dMonth === 0 && dDay === 0) {
        return '今天' + formatTime(sDate.getHours()) + ':' + formatTime(sDate.getMinutes());
    } else if (dYear === 0) {
        return formatTime(sDate.getMonth() + 1) + '月' + formatTime(sDate.getDate()) + '日 ' + formatTime(sDate.getHours()) + ':' + formatTime(sDate.getMinutes());
    } else {
        return sDate.getFullYear() + '-' + formatTime(sDate.getMonth() + 1) + '-' + formatTime(sDate.getDate()) + ' ' + formatTime(sDate.getHours()) + ':' + formatTime(sDate.getMinutes());
    }
}
/**
 * Ajax系列
 */

/**
 * 处理ajax返回结果
 */
function handleAjax(a) {
    //如果需要跳转的话，消息的末尾附上即将跳转字样
    if (a.url) {
        a.info += '，页面即将跳转～';
    }

    //弹出提示消息
    if (a.status) {
        $.toast(a.info);
    } else {
        $.toast(a.info);
    }

    //需要跳转的话就跳转
    var interval = 1500;
    if (a.url == "refresh") {
        setTimeout(function () {
            location.href = location.href;
        }, interval);
    } else if (a.url) {
        setTimeout(function () {
            location.href = a.url;
        }, interval);
    }
}

var load_css = function (src) {
    var $head = $('head');
    var link = document.createElement('link');
    link.setAttribute('rel', 'stylesheet');
    link.setAttribute('charset', 'UTF-8');
    link.setAttribute('href', src);
    $head.append(link);
};


String.prototype.textMore = function (limit) {
    var str = this;
    if (str.length > limit) {
        str = str.substring(0, limit) + '...';
    }
    return str;
};


/*
 radialIndicator.js v 1.0.0
 Author: Sudhanshu Yadav
 Copyright (c) 2015 Sudhanshu Yadav - ignitersworld.com , released under the MIT license.
 Demo on: ignitersworld.com/lab/radialIndicator.html
 */

;(function ($, window, document) {
    "use strict";
    //circumfence and quart value to start bar from top
    var circ = Math.PI * 2,
        quart = Math.PI / 2;

    //function to convert hex to rgb

    function hexToRgb(hex) {
        // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
        var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
        hex = hex.replace(shorthandRegex, function (m, r, g, b) {
            return r + r + g + g + b + b;
        });

        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)] : null;
    }

    function getPropVal(curShift, perShift, bottomRange, topRange) {
        return Math.round(bottomRange + ((topRange - bottomRange) * curShift / perShift));
    }


    //function to get current color in case of
    function getCurrentColor(curPer, bottomVal, topVal, bottomColor, topColor) {
        var rgbAryTop = topColor.indexOf('#') != -1 ? hexToRgb(topColor) : topColor.match(/\d+/g),
            rgbAryBottom = bottomColor.indexOf('#') != -1 ? hexToRgb(bottomColor) : bottomColor.match(/\d+/g),
            perShift = topVal - bottomVal,
            curShift = curPer - bottomVal;

        if (!rgbAryTop || !rgbAryBottom) return null;

        return 'rgb(' + getPropVal(curShift, perShift, rgbAryBottom[0], rgbAryTop[0]) + ',' + getPropVal(curShift, perShift, rgbAryBottom[1], rgbAryTop[1]) + ',' + getPropVal(curShift, perShift, rgbAryBottom[2], rgbAryTop[2]) + ')';
    }

    //to merge object
    function merge() {
        var arg = arguments,
            target = arg[0];
        for (var i = 1, ln = arg.length; i < ln; i++) {
            var obj = arg[i];
            for (var k in obj) {
                if (obj.hasOwnProperty(k)) {
                    target[k] = obj[k];
                }
            }
        }
        return target;
    }

    //function to apply formatting on number depending on parameter
    function formatter(pattern) {
        return function (num) {
            if (!pattern) return num.toString();
            num = num || 0
            var numRev = num.toString().split('').reverse(),
                output = pattern.split("").reverse(),
                i = 0,
                lastHashReplaced = 0;

            //changes hash with numbers
            for (var ln = output.length; i < ln; i++) {
                if (!numRev.length) break;
                if (output[i] == "#") {
                    lastHashReplaced = i;
                    output[i] = numRev.shift();
                }
            }

            //add overflowing numbers before prefix
            output.splice(lastHashReplaced + 1, output.lastIndexOf('#') - lastHashReplaced, numRev.reverse().join(""));

            return output.reverse().join('');
        }
    }


    //circle bar class
    function Indicator(container, indOption) {
        indOption = indOption || {};
        indOption = merge({}, radialIndicator.defaults, indOption);

        this.indOption = indOption;

        //create a queryselector if a selector string is passed in container
        if (typeof container == "string")
            container = document.querySelector(container);

        //get the first element if container is a node list
        if (container.length)
            container = container[0];

        this.container = container;

        //create a canvas element
        var canElm = document.createElement("canvas");
        container.appendChild(canElm);

        this.canElm = canElm; // dom object where drawing will happen

        this.ctx = canElm.getContext('2d'); //get 2d canvas context

        //add intial value
        this.current_value = indOption.initValue || indOption.minValue || 0;

    }


    Indicator.prototype = {
        constructor: radialIndicator,
        init: function () {
            var indOption = this.indOption,
                canElm = this.canElm,
                ctx = this.ctx,
                dim = (indOption.radius + indOption.barWidth) * 2, //elm width and height
                center = dim / 2; //center point in both x and y axis


            //create a formatter function
            this.formatter = typeof indOption.format == "function" ? indOption.format : formatter(indOption.format);

            //maximum text length;
            this.maxLength = indOption.percentage ? 4 : this.formatter(indOption.maxValue).length;

            canElm.width = dim;
            canElm.height = dim;

            //draw a grey circle
            ctx.strokeStyle = indOption.barBgColor; //background circle color
            ctx.lineWidth = indOption.barWidth;
            ctx.beginPath();
            ctx.arc(center, center, indOption.radius, 0, 2 * Math.PI);
            ctx.stroke();

            //store the image data after grey circle draw
            this.imgData = ctx.getImageData(0, 0, dim, dim);

            //put the initial value if defined
            this.value(this.current_value);

            return this;
        },
        //update the value of indicator without animation
        value: function (val) {
            //return the val if val is not provided
            if (val === undefined || isNaN(val)) {
                return this.current_value;
            }

            val = parseInt(val);

            var ctx = this.ctx,
                indOption = this.indOption,
                curColor = indOption.barColor,
                dim = (indOption.radius + indOption.barWidth) * 2,
                minVal = indOption.minValue,
                maxVal = indOption.maxValue,
                center = dim / 2;

            //limit the val in range of 0 to 100
            val = val < minVal ? minVal : val > maxVal ? maxVal : val;

            var perVal = Math.round(((val - minVal) * 100 / (maxVal - minVal)) * 100) / 100, //percentage value tp two decimal precision
                dispVal = indOption.percentage ? perVal + '%' : this.formatter(val); //formatted value

            //save val on object
            this.current_value = val;


            //draw the bg circle
            ctx.putImageData(this.imgData, 0, 0);

            //get current color if color range is set
            if (typeof curColor == "object") {
                var range = Object.keys(curColor);

                for (var i = 1, ln = range.length; i < ln; i++) {
                    var bottomVal = range[i - 1],
                        topVal = range[i],
                        bottomColor = curColor[bottomVal],
                        topColor = curColor[topVal],
                        newColor = val == bottomVal ? bottomColor : val == topVal ? topColor : val > bottomVal && val < topVal ? indOption.interpolate ? getCurrentColor(val, bottomVal, topVal, bottomColor, topColor) : topColor : false;

                    if (newColor != false) {
                        curColor = newColor;
                        break;
                    }
                }
            }

            //draw th circle value
            ctx.strokeStyle = curColor;

            //add linecap if value setted on options
            if (indOption.roundCorner) ctx.lineCap = "round";

            ctx.beginPath();
            ctx.arc(center, center, indOption.radius, -(quart), ((circ) * perVal / 100) - quart, false);
            ctx.stroke();

            //add percentage text
            if (indOption.displayNumber) {
                var cFont = ctx.font.split(' '),
                    weight = indOption.fontWeight,
                    fontSize = indOption.fontSize || (dim / (this.maxLength - (Math.floor(this.maxLength * 1.4 / 4) - 1)));

                cFont = indOption.fontFamily || cFont[cFont.length - 1];


                ctx.fillStyle = indOption.fontColor || curColor;
                ctx.font = weight + " " + fontSize + "px " + cFont;
                ctx.textAlign = "center";
                ctx.textBaseline = 'middle';
                ctx.fillText(dispVal, center, center);
            }

            return this;
        },
        //animate progressbar to the value
        animate: function (val) {

            var indOption = this.indOption,
                counter = this.current_value || indOption.minValue,
                self = this,
                incBy = Math.ceil((indOption.maxValue - indOption.minValue) / (indOption.frameNum || (indOption.percentage ? 100 : 500))), //increment by .2% on every tick and 1% if showing as percentage
                back = val < counter;

            //clear interval function if already started
            if (this.intvFunc) clearInterval(this.intvFunc);

            this.intvFunc = setInterval(function () {

                if ((!back && counter >= val) || (back && counter <= val)) {
                    if (self.current_value == counter) {
                        clearInterval(self.intvFunc);
                        return;
                    } else {
                        counter = val;
                    }
                }

                self.value(counter); //dispaly the value

                if (counter != val) {
                    counter = counter + (back ? -incBy : incBy)
                }
                ; //increment or decrement till counter does not reach  to value
            }, indOption.frameTime);

            return this;
        },
        //method to update options
        option: function (key, val) {
            if (val === undefined) return this.option[key];

            if (['radius', 'barWidth', 'barBgColor', 'format', 'maxValue', 'percentage'].indexOf(key) != -1) {
                this.indOption[key] = val;
                this.init().value(this.current_value);
            }
            this.indOption[key] = val;
        }

    };

    /** Initializer function **/
    function radialIndicator(container, options) {
        var progObj = new Indicator(container, options);
        progObj.init();
        return progObj;
    }

    //radial indicator defaults
    radialIndicator.defaults = {
        radius: 40, //inner radius of indicator
        barWidth: 5, //bar width
        barBgColor: '#eeeeee', //unfilled bar color
        barColor: '#99CC33', //filled bar color , can be a range also having different colors on different value like {0 : "#ccc", 50 : '#333', 100: '#000'}
        format: null, //format indicator numbers, can be a # formator ex (##,###.##) or a function
        frameTime: 10, //miliseconds to move from one frame to another
        frameNum: null, //Defines numbers of frame in indicator, defaults to 100 when showing percentage and 500 for other values
        fontColor: null, //font color
        fontFamily: null, //defines font family
        fontWeight: 'bold', //defines font weight
        fontSize: null, //define the font size of indicator number
        interpolate: true, //interpolate color between ranges
        percentage: false, //show percentage of value
        displayNumber: true, //display indicator number
        roundCorner: false, //have round corner in filled bar
        minValue: 0, //minimum value
        maxValue: 100, //maximum value
        initValue: 0 //define initial value of indicator
    };

    window.radialIndicator = radialIndicator;

    //add as a jquery plugin
    if ($) {
        $.fn.radialIndicator = function (options) {
            return this.each(function () {
                var newPCObj = radialIndicator(this, options);
                $.data(this, 'radialIndicator', newPCObj);
            });
        };
    }

}(window.jQuery, window, document, void 0));


$.fn.uploadImage = function (option, callback) {
    var $this = $(this);
    var _default = {
        name: 'image',
        limit: 9,
    };
    option = $.extend({}, _default, option);
    if ($this.hasClass('image_uploader')) {
        return false;
    }
    load_css(_ROOT_ + '/Public/ext/webuploader/webuploader.css');
    $.getScript(_ROOT_ + "/Public/ext/webuploader/webuploader.html5only.js", function () {
        $this.addClass('image_uploader');
        var $uploader = null;
        var add_btn = $('<div class="add-img-btn"></div>')
        add_btn.html('<div class="iconfont icon-fabu1 text-center"></div>');
        var temp = add_btn.prop("outerHTML");
        var $list = $('<div class="img-list"></div>');
        $list.html(temp);
        $this.html($list.prop("outerHTML"));
        var $add_btn = $this.find('.add-img-btn');
        var input = $('<input type="hidden"/>');
        input.attr('name', option.name);
        $this.append(input.prop("outerHTML"));

        var update_attach_value = function (attachId, type) {
            type = type || 'add';
            var $input = $('[name="' + option.name + '"]');
            var attachVal = $input.val(),
                newArr = [];
            if (attachVal == 'undefined' || attachVal == '') {
                if (type === 'add') {
                    $input.val(attachId);
                } else {
                    $input.val('');
                }
            } else {
                var attachArr = attachVal.split(',');
                for (var i in attachArr) {
                    if (attachArr[i] !== '' && attachArr[i] !== attachId.toString()) {
                        newArr.push(attachArr[i]);
                    }
                }
                type === 'add' && newArr.push(attachId);
                $input.val(newArr.join(','));
            }
        };
        $uploader = WebUploader.create({
            // 文件接收服务端。
            server: U('Core/File/uploadPicture'),
            // 选择文件的按钮。可选。
            pick: $add_btn[0],
            compress: {
                quality: 70,
                allowMagnify: true,
            },
            fileNumLimit: option.limit,
            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/!*'
            }
        });
        var bind_delete = function () {
            var $del_btn = $('[data-role="del_image"]');
            $del_btn.unbind('click');
            $del_btn.click(function () {
                var $this = $(this);
                var $img = $this.closest('.img');
                $img.fadeOut();
                update_attach_value($this.attr('data-id'), 'del');
                var fid = $img.attr('data-fid');
                $uploader.removeFile(fid, true);
            })
        };
        $uploader.on('fileQueued', function (file) {

            $add_btn.before('<div data-fid="' + file.id + '" class="img"></div>');

           $('[data-fid="' + file.id + '"]').radialIndicator({
                barColor: '#87CEEB',
                initValue: 0,
                roundCorner: true,
                percentage: true
            });
            $uploader.upload();
        });

        $uploader.on('uploadProgress', function (file, percentage) {
            var radialObj =  $.data( $('[data-fid="' + file.id + '"]')[0], "radialIndicator" )
            radialObj.animate(percentage * 100);
        });


        $uploader.on('uploadSuccess', function (file, ret) {
            $uploader.makeThumb(file, function (error, base64) {
                $('[data-fid="' + file.id + '"]').html('<img alt="" src="' + base64 + '" /><a class="del-img-btn" data-role="del_image" data-id="' + ret.data.file.id + '"><i class="iconfont icon-guanbi2"></i></a>');
                bind_delete();
            }, 300, 300);
            update_attach_value(ret.data.file.id);
        });
        if (typeof callback == 'function') {
            callback($uploader)
        }

    });


};


+function ($) {
    "use strict";


    $.fn.datePicker = function (params) {
        return this.each(function () {

            if (!this) return;

            var today = new Date();

            var getDays = function (max) {
                var days = [];
                for (var i = 1; i <= (max || 31); i++) {
                    days.push(i < 10 ? "0" + i : i);
                }
                return days;
            };

            var getDaysByMonthAndYear = function (month, year) {
                var int_d = new Date(year, parseInt(month) + 1 - 1, 1);
                var d = new Date(int_d - 1);
                return getDays(d.getDate());
            };

            var formatNumber = function (n) {
                return n < 10 ? "0" + n : n;
            };

            var initMonthes = ('01 02 03 04 05 06 07 08 09 10 11 12').split(' ');

            var initYears = (function () {
                var arr = [];
                for (var i = 1950; i <= 2030; i++) {
                    arr.push(i);
                }
                return arr;
            })();


            var defaults = {

                rotateEffect: false,  //为了性能

                value: [today.getFullYear(), formatNumber(today.getMonth() + 1), today.getDate()],

                onChange: function (picker, values, displayValues) {
                    var days = getDaysByMonthAndYear(picker.cols[1].value, picker.cols[0].value);
                    var currentValue = picker.cols[2].value;
                    if (currentValue > days.length) currentValue = days.length;
                    picker.cols[2].setValue(currentValue);
                },

                formatValue: function (p, values, displayValues) {
                    return displayValues[0] + '-' + values[1] + '-' + values[2];
                },

                cols: [
                    // Years
                    {
                        values: initYears
                    },
                    // Months
                    {
                        values: initMonthes
                    },
                    // Days
                    {
                        values: getDays()
                    }
                ]
            };

            params = params || {};
            var inputValue = $(this).val();
            if (params.value === undefined && inputValue !== "") {
                params.value = [].concat(inputValue.split(" ")[0].split("-"));
            }

            var p = $.extend(defaults, params);
            $(this).picker(p);
        });
    };

}($);

$.fn.ucard = function (option, callback) {
    var $this = $(this);
    var _default = {is_mine:0,is_share:0};
    option = $.extend({}, _default, option);
    if (option.is_share == 0) {
        $this.click(function () {
            _getInfo(option,$(this));
        });
    } else {
        _getInfo(option);
    }

    function _getInfo(option,that){
        var uid = that.attr('data-uid');
        var url = U('Ucenter/Index/getInfo');
        $.showIndicator();
        $.get(url,{uid:uid},function(res){
            if (res.status == 1) {
                console.log(option.is_mine);
                var html = '';
                if (option.is_mine && res.data.is_wechat == 0) {
                    html = '';
                }
                else {
                    if (res.data.is_login != 0  && res.data.is_self != 1) {
                        html = '<p class="do-active doFocus" data-uid="'+ res.data.uid +'" data-value="'+ res.data.is_follow +'"><i class="iconfont icon-fabu1"></i>'+ res.data.follow_status +'</p>';
                    } else {
                        html = '';
                    }
                }
                var ownSign = res.data.signature;
                var address = res.data.pos_province + res.data.pos_city + res.data.pos_district;
                console.log(address);
                console.log(ownSign);
                if(address == ''){
                    address = "大概是来自火星吧？！"
                }else{
                    address = address;
                }
                if(ownSign == ''){
                    ownSign = '这个人很懒，什么都没留下!';
                }else {
                    ownSign = ownSign;
                }
                var modal = $.modal({
                    afterText:'<div class="amyHead flexDiv"><span class="flexWrap"><i class="iconfont icon-yonghuxinxi1"></i><b>用户信息</b></span>' +
                    ''+html+'</div>' +
                    '<div class="amyBg"><div class="main"><a href="'+U('ucenter/index/mine',['uid',uid])+'" class="myAvatar"><img src="'+ res.data.avatar128 +'" alt="会员头像"></a>' +
                    '<div class="myInfo flexCol"><p class="myName textMore">'+ res.data.nickname +'</p><p class="myIntro textMore">'+ ownSign +'</p><p class="myAddress textMore"><i class="iconfont icon-dingwei1"></i>'+ address +'</p></div></div>' +
                    '<div class="main extra"><span><i class="iconfont icon-pinglun"></i> '+ res.data.weibocount +'</span><span><i class="iconfont icon-haoyou"></i> '+ res.data.fans +'</span><span><i class="iconfont icon-fav"></i> '+ res.data.following +'</span></div></div>' +
                    '</div>',
                    zdywrap:'myOwn',
                    myfather:'outer'
                });
            } else {
                console.error('未获取用户信息~');
            }
            $.hideIndicator();
        });
    }

    if (typeof callback == 'function') {
        callback(res);
    }
};
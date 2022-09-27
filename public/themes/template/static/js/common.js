/**
 * 最后访问地址
 */
if (location.href.indexOf('login') == -1) {
    localStorage.lastUrl = location.href;
}

/**
 * 用户信息
 */
var userInfo = typeof localStorage.userInfo == 'undefined' || localStorage.userInfo == 'null' || localStorage.userInfo == null  ? {} : JSON.parse(localStorage.userInfo);

/**
 * 发送post请求
 * @param  {String} link   链接
 * @param  {Object} data  数据集
 */
function post(link, data, callback = ""){
    // 带上token
    data.token = localStorage.token;
    $.ajax({
        url: index_url(link),
        type: 'post',
        dataTyle: 'json',
        data: data,
        success:function(res) {
            var res = typeof res == 'string' ? JSON.parse(res) : res;
            if (res.status === 'login') {
                location.href = index_url("login/index");
            } else {
                if (callback != "") callback(res);
            }
        },
        error:function(res) {
            res.status  = 'error';
            res.message = res.statusText;
            if (callback != "") callback(res);
        }
    })
}

/**
 * 链接
 */
var locationUrl = {
    /**
     * 获取链接参数
     * @param  {String} name 参数名
     */
    get(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
        var u = decodeURI(window.location.search.substr(1));
        var r = u.match(reg); 
        if (r != null) return unescape(r[2]); 
        return "";
    },
    /**
     * 设置链接参数
     * @param  {String} name  参数名
     * @param  {String} value 值
     */
    set(name, value){
        var loadUrl = location.href;
        var arrUrl  = loadUrl.split('#');
        var url     = arrUrl[0];
        var pattern = name + '=([^&]*)';
        var replaceText = name+'='+value; 
        var newUrl = "";
        if (url.match(pattern)) {
            var tmp = '/(' + name + '=)([^&]*)/gi';
            newUrl = url.replace(eval(tmp),replaceText);
        } else { 
            if (url.match('[\?]')) { 
                newUrl = url + '&' + replaceText; 
            } else {
                newUrl =  url +'?' + replaceText; 
            } 
        }
        location.href = newUrl+window.location.hash
    }
}


/**
 * 手机菜单
 */
$(function(){
    var menu = $('nav#menu').mmenu({
        extensions: {all: [ 'effect-slide-menu', 'pageshadow' , "listview-50", "theme-white", "pagedim-black"],"(max-width: 549px)": ["fx-menu-slide"]},
        searchfield: true,
        counters: false,//子栏目数目
        navbar: {title: '栏目'},
        navbars: [{
            position: 'top',
            content: [ 'searchfield' ]
        }, {
            position: 'top',
            content: ['prev','title','close']
        }],
    }).data("mmenu"),
    n = $("#header .mm_btn");
    menu.bind("close:finish",function() {
        $("#header").css("top","0px");
        $(".customer-service").css("bottom","90px")
        $("#gotoTop").css("bottom","50px")
        setTimeout(t, 100)
    }),
    menu.bind("open:finish",function() {
        setTimeout(e, 100)
    }),
    menu.bind("open:start",function() {
        $("#header").removeClass("on");
        $("#header").css("top",$(window).scrollTop());
        $(".customer-service").css("bottom",$(document).height() - $(window).scrollTop() - $(window).height() + $(".customer-service").height())
        $("#gotoTop").css("bottom",$(document).height() - $(window).scrollTop() - $(window).height() + $("#gotoTop").height())
    })
    function e() {$("#header .mm_btn").attr("href", "#page").addClass("is_active");}
    function t() {$("#header .mm_btn").attr("href", "#menu").removeClass("is_active")}
})

/**
 * 自适应
 */
$(function(){
    $(window).resize(function(){
        autosize();
    })
    function autosize(){
        if ((navigator.userAgent.match(/(iPhone|iPod|Android|ios|iOS|iPad|Backerry|WebOS|Symbian|Windows Phone|Phone)/i))) {
        var a = $(window).width()>640?350:200;
        $('.col-index-banner,.col-index-banner .swiper-slide .img').height(a);
        $('.col-marketing').height($('.col-mobile').height());
      }else{
        var a=$(window).height()>878?878:$(window).height();
        $('.col-index-banner,.col-index-banner .swiper-slide .img').height(a);
        $('.col-marketing').height($('.col-mobile').height());
      }
    }autosize();
    $(window).on("scroll", function(){ 
        var top = $(this).scrollTop(); // 当前窗口的滚动距离    
        $('header').removeClass('fixed')
        if (top>=0) {
            $('header').addClass('fixed')
        }else {
            $('header').removeClass('fixed')
        }
    })
    $(function(){ $(window).trigger('scroll') }) 
})   

/**
 * 返回顶部
 */
$(function () {
    $("#gotoTop").click(function () {
        $('body,html').stop(true).animate({
            'scrollTop': 0
        }, Number(500));
        return false;
    });
})
$(window).on('scroll', function () {
    goToTopScroll();
})
function goToTopScroll() {
    var $goToTopEl = $("#gotoTop");
    var elementOffset = 450;
    if ($(window).scrollTop() > Number(elementOffset)) {
        $goToTopEl.stop().fadeIn();
    } else {
        $goToTopEl.stop().fadeOut();
    }
}goToTopScroll();

/**
 * 幻灯片
 */
$(function(){
    if($(".col-banner").length > 0){
        var interleaveOffset = 0.5;
        var swiper = new Swiper('.col-banner .swiper-container', {
            speed:1000,
            loop : true,            
            lazy: {
                loadPrevNext: true,
                loadPrevNextAmount: 2
            },
            grabCursor : true,
            pagination: {
                el: '.col-banner .swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.col-banner .slide-button-next',
                prevEl: '.col-banner .slide-button-prev',
            },
            watchSlidesProgress: true,
            mousewheelControl: true,
            keyboardControl: true, 
            on: {
                progress: function() {
                    var swiper = this;
                    for (var i = 0; i < swiper.slides.length; i++) {
                        var slideProgress = swiper.slides[i].progress;
                        var innerOffset = swiper.width * interleaveOffset;
                        var innerTranslate = slideProgress * innerOffset;
                        swiper.slides[i].querySelector(".slide-img").style.transform = "translate3d(" + innerTranslate + "px, 0, 0)";
                    }      
                },
                touchStart: function() {
                    var swiper = this;
                    for (var i = 0; i < swiper.slides.length; i++) {
                        swiper.slides[i].style.transition = "";
                    }
                },
                setTransition: function(speed) {
                    var swiper = this;
                    for (var i = 0; i < swiper.slides.length; i++) {
                        swiper.slides[i].style.transition = speed + "ms";
                        swiper.slides[i].querySelector(".slide-img").style.transition = speed + "ms";
                    }
                }
            }
        });
    }
});
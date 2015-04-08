/**
 * 
 */

$(function(){
    //导航初始化
    var init_nav=function(){
        var nav_is_show=getCookie('nav_is_show');
        if (nav_is_show!=null && nav_is_show=='0'){
            //折叠
            $('.plat_menu_width_1').removeClass('plat_menu_width_1').addClass('plat_menu_width_2');
            $('.page_content').css('margin-left', '65px');
            $('.plat_menu_erji').hide();
            bind2();
            return;
        }
        bind1();   //默认展开
    }

    //展开后事件绑定
    var bind1=function(){
        $('.plat_menu_li_a ,.stop_bind').unbind('mouseover'); 
        $('.page_content_wrapper').unbind('mouseover'); 

        $('.plat_menu_li_a').on('click','', function() {
            if($(this).siblings('.plat_menu_erji').is(':hidden') && $(this).siblings('.plat_menu_erji').find('li').length>0)
            {
                $('.plat_menu_width_1').find('.plat_menu_erji').hide();
                $(this).siblings('.plat_menu_erji').show();
                $('.plat_menu_jian1').removeClass('plat_menu_jian1').addClass('plat_menu_jian');
                $(this).find('.plat_menu_jian').removeClass('plat_menu_jian').addClass('plat_menu_jian1');
                $('.plat_menu_li_a_visit1').removeClass('plat_menu_li_a_visit1');
                $(this).addClass('plat_menu_li_a_visit1')
            }else{
                $(this).siblings('.plat_menu_erji').hide();
                $('.plat_menu_jian1').removeClass('plat_menu_jian1').addClass('plat_menu_jian');
                $('.plat_menu_li_a_visit1').removeClass('plat_menu_li_a_visit1');
            }
        });
    }

    //向左收起 后事件绑定
    var bind2=function(){
        $('.plat_menu_li_a').unbind('click');

        $('.plat_menu_li_a').mouseover(function(event) {
            $('.plat_menu_li_a_visit1').removeClass('plat_menu_li_a_visit1');
            $(this).addClass('plat_menu_li_a_visit1');
            $('.plat_menu_erji').hide();
            $(this).siblings('.plat_menu_erji').show();
        });
        $('.page_content_wrapper').mouseover(function(event) {
            $('.plat_menu_erji').hide();
        });
        $('.stop_bind').mouseover(function(){
            $('.plat_menu_erji').hide();
        })
    }

    //左右伸缩
    $('.plat_menu_ico').click(function(event) {
        if($('.plat_menu_width_1').length>0){
            //折叠
            $('.plat_menu_width_1').removeClass('plat_menu_width_1').addClass('plat_menu_width_2');
            $('.page_content').css('margin-left', '65px');
            $('.plat_menu_erji').hide();
            bind2();
            setCookie('nav_is_show',0,1);
        }else{
            //展开
            $('.plat_menu_width_2').removeClass('plat_menu_width_2').addClass('plat_menu_width_1');
            $('.page_content').css('margin-left','180px');
            $('.plat_menu_jian1').parent().next('.plat_menu_erji').show();
            bind1();
            setCookie('nav_is_show',1,1);
        }
    });

    

    //导航开始
	init_nav();
});

/**
 * cookie 简单操作
 * @param {[type]} c_name     [description]
 * @param {[type]} value      [description]
 * @param {[type]} expiredays [description]
 */
function setCookie(c_name,value,expiredays)
{
    var exdate=new Date()
    exdate.setDate(exdate.getDate()+expiredays)
    document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())
}

function getCookie(c_name)
{
    if (document.cookie.length>0){
      c_start=document.cookie.indexOf(c_name + "=")
      if (c_start!=-1){ 
        c_start=c_start + c_name.length+1 
        c_end=document.cookie.indexOf(";",c_start)
        if (c_end==-1) c_end=document.cookie.length
        return unescape(document.cookie.substring(c_start,c_end))
      } 
    }
    return ""
}
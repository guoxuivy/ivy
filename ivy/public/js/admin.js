/**
 * 
 */
(function($){
	//Attach this new method to jQuery
    $.fn.extend({ 

        //This is where you write your plugin's name
        form_ajax_submit: function(ajax_url) {

            //Iterate over the current set of matched elements
            return this.each(function() {
            	//code to be inserted here
            	if(isEmpty(ajax_url)) {
            		ajax_url = $(this).attr('action');
            	}
            	var json_data = $(this).serialize();
            	ajax_submit(ajax_url, json_data);
            });
        }
    }); 
})(jQuery);

$(function() {
	var _opacity_body = '<div id="opacity_body" style="display:none;z-index: 1000; position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; overflow: hidden; -webkit-user-select: none; opacity: 0.5; background: rgb(0, 0, 0);"></div>';
	$('body').append(_opacity_body);
});

$(document).on('click','.plat_tan_tit span', function() {
	var _self = $(this);
	$('#opacity_body').hide();
	_self.parents('.plat_tan').hide();
});

$(document).on('click','.plat_tan_footer .cance2_button', function() {
	var _self = $(this);
	$('#opacity_body').hide();
	_self.parents('.plat_tan').hide();
});

/**
 * ajax提交
 * @param ajax_url 请求地址
 * @param json_data 请求的json数据
 */
function ajax_submit(ajax_url, json_data) {
	var _html = "<div id='loading' style='z-index:2000;position:fixed;left:0;width:100%;height:100%;top:0;background:rgb(0, 0, 0);opacity:0.5;filter:alpha(opacity=50);'><div style='position:absolute;  cursor1:wait;left:50%;top:50%;width:auto;height:16px;padding:13px 22px 28px 30px;background: url(themes/default/static/images/loading.gif) no-repeat scroll 5px 10px;text-indent: -99999px;color:#000;'>正在加载，请等待...</div></div>";
    $('body').append(_html); 
	$.ajax({
		url : ajax_url,
		async : true,
		cache : false,
		type : 'post',
		data : json_data,
		dataType : 'json',
		complete : function() {
			$('#loading').remove();
		},
		success : function(json) {
			if(json.stat == 'success') {
				$('.plat_tan_tit span').trigger('click');
				if(typeof(json.back_url) !== 'undefined' && json.back_url !== '') {
					window.location.href = json.back_url;
				}
				else {
					history.go(0);
				}
			}
			if(json.stat == 'failed') {
				if($('form .error_msg').length > 0) {
					$('.error_msg').html(json.msg);
				}
				else {
					$.my_alert_dialog(json.msg);
				}
			}
		},
		error : function(XMLHttpRequest, textStatus, errorThrown) {
			if(textStatus == 'error') {
				$.my_alert_dialog('请求错误');
			}
			return false;
		},
		
	});
}

/**
 * 弹出对话框
 * @param self 对话框的div对象
 */
function popDialog(self) {
	//获得窗口的高度
	var windowHeight = $(window).height();
	//获得窗口的宽度
	var windowWidth = $(window).width();
	//获得弹窗的高度
	var popHeight;
	//获得弹窗的宽度
	var popWidth;
	if(self.hasClass('plat_tan')) {
		popHeight = self.height();
		popWidth = self.width();
	}
	else {
		popHeight = self.find('.plat_tan').height();
		popWidth = self.find('.plat_tan').width();
	}
	//获得滚动条的高度
	var scrollTop = $(window).scrollTop();
	//获得滚动条的宽度
	var scrollLeft = $(window).scrollLeft();
	//右边显示主内容区域的宽度
	var rightWidth = $('.page_content').width();
	//页面头部div高度
	var topHeight = $('.plat_top').height();
	
	var top_val = (windowHeight - popHeight)/2 + scrollTop;
	var popY = topHeight + 'px';
	if(top_val > topHeight) {
		popY = top_val + 'px';
	}
	var popX = (windowWidth - popWidth + windowWidth - rightWidth)/2 + scrollLeft + 'px';
	$('#opacity_body').show();
	if(self.hasClass('plat_tan')) {
		self.css({top:popY,left:popX}).show();
	}
	else {
		self.find('.plat_tan').css({top:popY,left:popX});
		self.show();
	}

	$(".plat_tan_tit").unbind("mousedown").mousedown(function(e){
		
		var offset = $(this).offset();//DIV在页面的位置
		var x = e.pageX - offset.left;//获得鼠标指针离DIV元素左边界的距离
		var y = e.pageY - offset.top;//获得鼠标指针离DIV元素上边界的距离
		$(document).bind("mousemove",function(ev)//绑定鼠标的移动事件，因为光标在DIV元素外面也要有效果，所以要用doucment的事件，而不用DIV元素的事件
		{
			$(".plat_tan").stop();//加上这个之后
			var _x = ev.pageX - x;//获得X轴方向移动的值
			var _y = ev.pageY - y;//获得Y轴方向移动的值
			
			$(".plat_tan").animate({left:_x+"px",top:_y+"px"},0);
		});
	});
			
	$(document).mouseup(function(){
		$(this).unbind("mousemove");
	});
}

/**
 * 判断是否为空
 * @param obj
 * @returns
 */
function isEmpty(obj) {
	if(obj == '' || typeof(obj) == 'undefined') {
		return true;
	}
	else {
		return false;
	}
}
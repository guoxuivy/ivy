<?php
namespace top;
use Ivy\core\Widget;
use Ivy\core\CException;
class NavWidget extends Widget
{
    
	public function run() {
		$res = $this->_show($this->getAdminNav());
		return $res;
	}

	/**
     * 总经理 BOSS导航数据
     *@return array 
     */
    public function getAdminNav(){
        return $_nav=array(
            '首页'=>'/',
            '部门管理'=>$this->url('admin/admin/dept'),
            '门店管理'=>$this->url('admin/admin/dept'),
            '房间管理'=>$this->url('admin/admin/dept'),
            '会员卡管理'=>$this->url('admin/admin/dept'),
            '供应商管理'=>$this->url('admin/admin/dept'),
            '员工管理'=>$this->url('admin/admin/dept'),


            '商品管理'=>array(
                '商品列表'=>$this->url('admin/product/index'),
                '商品分类'=>$this->url('admin/product/cate'),
            ),
            '项目管理'=>array(
                '项目列表'=>$this->url('/customer/order/admin'),
                '项目分类'=>$this->url('/customer/recharge/admin'),
            ),
			'活动卡管理'=>$this->url('/sys/knowledge/index'),
            '报表定制'=>$this->url('/praise/default/index'),
        );
    }


	/**
     * 导航icon
     *@return string 
     */
    public static function icon($name){
        $list=array(
            '首页'=>'p_home.png',
            '员工管理'=>'p_kehu.png',
            '门店管理'=>'p_yaoyueben.png',
            '部门管理'=>'p_zonghe.png',
            '报表定制'=>'p_shuju.png',
            '商品管理'=>'p_yuangong.png',
            '供应商管理'=>'p_jiaoyi.png',
            '项目管理'=>'p_meiri.png',
            '活动卡管理'=>'p_gongsi.png',
            '交易管理'=>'p_jiaoyi.png',
        );
        if(in_array($name,array_keys($list))){
            return $list[$name];
        }
        return 'p_jiaoyi.png';
    }
    
    /**
     * 导航渲染
     *@param array $_nav 导航数组，中文索引，支持2级
     *@return string 
     * 
     */ 
    public function _show($_nav){
        if(!is_array($_nav)) throw new CException(500,'导航无效参数！');
        
        $str='<div class="plat_menu plat_menu_width_1"><ul>';
        $str.= '<li>
                	<div class="plat_menu_ico"><img src="'.SITE_URL.'/public/images/menu_ico.png" width="30" height="27" /></div>
                    <div class="clear"></div>
                </li>
                <li class="stop_bind"></li>';
        foreach($_nav as $_name=>$_url){
            $lx = $child= '';//是否有二级
            $f_url=$_url;
            $f_is_focus=false;
            if(is_array($_url)){
                $f_url="javascript:;";
                $lx='<span class="plat_menu_jian"></span>';
                foreach($_url as $name=>$url){
                    if($url==$this->getCUrl()){
                        $f_is_focus=true;
                        $lx='<span class="plat_menu_jian1"></span>';
                        $child.='<li><a href="'.$url.'" class="plat_menu_erji_li_a plat_menu_erji_li_a_visit">'.$name.'</a></li>';
                        continue;
                    }
                    $child.='<li><a href="'.$url.'" class="plat_menu_erji_li_a">'.$name.'</a></li>';
                     
                }
            }else{
                if($_url==$this->getCUrl()){
                    $f_is_focus=true;
                }
            }
            $icon = self::icon($_name);
            $str.='<li>
            	<a href="'.$f_url.'" class="plat_menu_li_a '.($f_is_focus?"plat_menu_li_a_visit":"").'">
                	<i class="plat_menu_tubiao"><img src="'.SITE_URL.'/public/images/'.$icon.'" width="20" height="20" /></i>
                	<span class="plat_menu_text">'.$_name.'</span>
                    '.$lx.'
                	<div class="clear"></div>
                </a>
                <ul class="plat_menu_erji" '.(($f_is_focus&&is_array($_url))?"style='display: block;'":"").'>
                	<p class="'.($f_is_focus?"p_visit":"p_hover").'"><a href="'.$f_url.'">'.$_name.'</a></p>
                	'.$child.'
                </ul>
            </li>';
            
        }
        
        $str.='<li class="stop_bind"></li>';
        $str.='</ul></div>';
        return $str;
    }

    /**
     * 获取当前url
     * @return [type] [description]
     */
    public function getCUrl(){
    	return $this->url(implode('/',\Ivy::app()->_route->getRouter()));
    }

	
}
<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @since 1.0
 */
namespace Ivy\core;
class LoaderClass{
    
    /**
     * @author ivy <guoxuivy@gmail.com>
     * @comment 框架自动类加载 依次寻找路径列表 次路径只能在 framework范围内
     * @since 1.0
     */
    static $load_dir = array(
        "0"=>"db",
        "1"=>"db/pdo"
    );
    
	/**
	 * 自动加载函数
	 */
	static public function loadFile($className){
        //自动加载框架文件
        //Ivy 命名空间自动截取
        if("Ivy\\"===substr($className,0,4)){
            $className=substr($className,4);
            //加载框架文件
            $file_path=IVY_PATH.DIRECTORY_SEPARATOR.$className.'.php';
            if(is_file($file_path)){
    			return include_once $file_path;
    		}else{
                throw new CException('找不到框架'.$className.'类文件');
    		}
        }
        
        //自动加载components 文件
        $file_path=__PROTECTED__.DIRECTORY_SEPARATOR."components";
        $files=array();
        self::allScandir($file_path,$files);
        if(!empty($files)){
            foreach($files as $f){
                if( (strtolower($className))==strtolower(basename($f,".php")) ){
                    return include_once $f;
                }
            }
        }
        
        //加载其它文件 MVC文件  路由分发专用
        if("Controller"===substr($className,-10)) $dispatch="controllers";
        if("Model"===substr($className,-5)) $dispatch="models";
        if(isset($dispatch)){
            if(3==count(\Ivy::app()->temp_route)){
                $className=explode("\\",$className);
                $className=$className[1];
                $file_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.\Ivy::app()->temp_route['module'].DIRECTORY_SEPARATOR.$dispatch.DIRECTORY_SEPARATOR.$className.'.php';
                if(is_file($file_path)){
        			return include_once $file_path;
        		}
            }
            //控制器路由 自动载入
            if(2==count(\Ivy::app()->temp_route)){
                $file_path=__PROTECTED__.DIRECTORY_SEPARATOR.$dispatch.DIRECTORY_SEPARATOR.$className.'.php';
                if(is_file($file_path)){
        			return include_once $file_path;
        		}
            }
        }
        throw new CException('找不到'.$className.'类文件');
	}
    
    
    /**
	 * 文件夹遍历
     * 返回所有文件名
	 */
	static public function allScandir($file_path='',&$arr) {
	   if(!empty($file_path)){
	       foreach(scandir($file_path) as $dir){
	           if($dir!="."&&$dir!=".."){
	               $f_name=$file_path.DIRECTORY_SEPARATOR.$dir;
	               if(is_dir($f_name)){
	                   self::allScandir($f_name,$arr);
	               }else{
	                   if(self::is_php($dir)){
	                       array_push($arr,$f_name);
	                   }
	               }
	           }
	       }
	   }
	}
    
    
    /**
	 * php文件判断
	 */
    static public function is_php($file_name=""){
        $extend =explode("." , $file_name);
        $va=count($extend)-1;
        if("php"===strtolower($extend[$va])){
            return true;
        }else{
            return false;
        }
    }
	
	/**
	 * 注册自动装载机
	 */
	static public function autoLoad(){
		spl_autoload_register(array(__CLASS__,'loadFile'));
        //spl_autoload_register(array(__CLASS__,'loadMVCFile'));
	}
}
LoaderClass::autoLoad();

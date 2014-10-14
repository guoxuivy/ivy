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
        if( strtolower($className)=='basecontroller'){
            $className="Ivy\core\BaseController";
        }
        
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
        

        
        //加载其它文件 MVC文件  路由分发专用
        if("Controller"===substr($className,-10)) $dispatch="controllers";
        if("Model"===substr($className,-5)) $dispatch="models";
        
        if(isset($dispatch)){
            
            if(3==count(\Ivy::app()->router)){
                $className=explode("\\",$className);
                $className=$className[1];
                $file_path=__PROTECTED__.DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR.\Ivy::app()->router['module'].DIRECTORY_SEPARATOR.$dispatch.DIRECTORY_SEPARATOR.$className.'.php';
                if(is_file($file_path)){
        			return include_once $file_path;
        		}
            }
            //控制器路由 自动载入
            if(2==count(\Ivy::app()->router)){
                $file_path=__PROTECTED__.DIRECTORY_SEPARATOR.$dispatch.DIRECTORY_SEPARATOR.$className.'.php';
                if(is_file($file_path)){
        			return include_once $file_path;
        		}
            }
        }
        throw new CException('找不到'.$className.'类文件');
        
        
	}
	
	/**
	 * 注册自动装载机
	 */
	static public function autoLoad(){
		spl_autoload_register(array(__CLASS__,'loadFile'));
	}
}
LoaderClass::autoLoad();

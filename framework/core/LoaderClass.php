<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 */
namespace Ivy\core;
class LoaderClass{
	/**
	 * @author ivy <guoxuivy@gmail.com>
	 * @comment 框架自动类加载 依次寻找路径列表 次路径只能在 framework范围内
	 * @link https://github.com/guoxuivy/ivy * @since 1.0 
	 */

	/**
	 * 自动加载函数
	 */
	static public function loadFile($objName){
		$className = $objName;
		//Ivy命名空间自动截取  自动加载框架文件 命名空间必须和文件路径一致
		if("Ivy\\"===substr($className,0,4)){
			$className=substr($className,4);
			//加载框架文件
			$file_path=IVY_PATH.DS.$className.'.php';
			$file_path = str_replace('\\', DS, $file_path);
			if(is_file($file_path)){
				return include_once $file_path;
			}else{
				throw new CException('找不到框架'.$objName.'类文件');
			}
		}
		
		//自动加载 应用下的 components 文件
		$file_path=__PROTECTED__.DS."components".DS.$className.'.php';
		$file_path = str_replace('\\', DS, $file_path);
		if(is_file($file_path))
			return include_once $file_path;
		//自动加载 分组下的 components 文件
		$className_tmp=array_filter(explode("\\",$className));
		if(count($className_tmp)>1){
			$groupName=array_shift($className_tmp);
			$cName=implode(DS,$className_tmp);
			$file_path=__PROTECTED__.DS."modules".DS.$groupName.DS."components".DS.$cName.'.php';
			if(is_file($file_path))
				return include_once $file_path;
		}
		
		//加载应用  控制器、模型文件  路由分发专用  //业务层 model controllers文件载入
		if("Controller"===substr($className,-10)){
			$dispatch="controllers";
		}elseif("Logic"===substr($className,-5)){
			$dispatch="logics";
		}else{
			$dispatch="models";
		}
		$className=array_filter(explode("\\",$className));
		if(count($className)==1){
			//根命名空间
			$file_path=__PROTECTED__.DS.$dispatch.DS.$className[0].'.php';
			if(is_file($file_path)){
				return include_once $file_path;
			}
		}else{
			//分组命名空间
			$module=array_shift($className);
			$dir = implode(DS ,$className);
			$file_path=__PROTECTED__.DS."modules".DS.$module.DS.$dispatch.DS.$dir.'.php';
			if(is_file($file_path)){
				return include_once $file_path;
			}
		}
		
		throw new CException('找不到'.$objName.'类文件');
	}


	/**
	 * 文件夹遍历
	 * 返回所有文件名
	 */
	static public function allScandir($file_path='',&$arr) {
		if(!empty($file_path)){
			foreach(scandir($file_path) as $dir){
				if($dir!="."&&$dir!=".."){
					$f_name=$file_path.DS.$dir;
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
	}
}
LoaderClass::autoLoad();

// 引入 composer 加载器
if(file_exists(__ROOT__.'/vendor/autoload.php')){
	require __ROOT__.'/vendor/autoload.php';
}
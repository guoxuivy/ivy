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

class Route {

    // 路由规则
    private static $rules = [
        
    ];

	//默认路由
	static $_route= array(
		'module' =>'',
		'controller' =>'index',
		'action'	=> 'index'
	);

	//路由参数数组
	protected $route = NULL;
	//其它需要传递的参数
	public $param = array();

	// url传递的当前的路由参数 比如 index/detail
    public $pathinfo = '';

	public function __construct(){
        require_once(__PROTECTED__.DS.'route.php');
	}

	/**
	 * 路由 处理  
	 * 标准路由数组
	 * 可扩展 url seo
	 */
	public function start($routerStr='',$param=array()){
		if(empty($routerStr)){
            $routerStr=isset($_GET['r'])?$_GET['r']:"";
            if(!empty($routerStr)){
                $_SERVER['PATH_INFO'] = $routerStr;
            }
            // 分析PATHINFO信息
            if (!isset($_SERVER['PATH_INFO'])) {
                foreach (['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'] as $type) {
                    if (!empty($_SERVER[$type])) {
                        $_SERVER['PATH_INFO'] = (0 === strpos($_SERVER[$type], $_SERVER['SCRIPT_NAME'])) ?
                            substr($_SERVER[$type], strlen($_SERVER['SCRIPT_NAME'])) : $_SERVER[$type];
                        break;
                    }
                }
            }
            $routerStr = empty($_SERVER['PATH_INFO']) ? '' : trim($_SERVER['PATH_INFO'], '/');
            $router_arr = parse_url($routerStr);
            $routerStr = $router_arr['path'];
            $param = $this->convertUrlQuery($router_arr['query']);
            $param = array_merge($_GET,$param);
			unset($param['r']);
		}
		//参数移除
        // if(false!=strrpos($routerStr,'?')){
         //    $routerStr = substr($routerStr,0,strrpos($routerStr,'?'));
        // }
        $this->pathinfo = $routerStr;
        $this->param = $_GET= $param;
		$this->analyzeRoute($routerStr);
	}

    public function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param)
        {
            $item = explode('=', $param);
            !is_null($item[1]) && $params[$item[0]] = $item[1];
        }
        return $params;
    }

	/**
	 * 格式化为url
     * @param string $uri admin/order/index
     * @param array $param rray("id"=>1)
     * @return bool|string
     */
	public function url($uri="",$param=array()){
        $p_uri = parse_url($uri);
        $uri = $p_uri['path'];
        // 参数标准化
        foreach (explode('&',$p_uri['query']) as $q) {
            list($k,$v) = explode('=',$q);
            $param[$k] = $v;
        }

        $type = 0; //url友好模式
        if(0 === strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])){
            // 有index.php
            $type = 1;
        };
		if($uri==='/') return '/';
		if(strpos($uri,'/')===false){
			// 如 'list' 不包含分隔符 只指定方法名称
			$r=$this->getRouter();
			if(!empty($uri)) $r['action']=$uri;
			$uri=implode('/', array_filter($r));
		}
        if($type){
            $param['r'] = rtrim($uri);
            $uri = $_SERVER['SCRIPT_NAME'];
        }else{
            $uri = '/'.rtrim($uri);
        }
        list($uri,$param) = self::checkRule($uri,$param);

        // 定制路由规则检测转换
		$param_arr = array_filter($param);
		if(!empty($param_arr)){
            $uri .= '?';
			foreach($param_arr as $k=>$v){
				$uri .= "{$k}={$v}&";
			}
            $uri = substr($uri,0,-1);
		}
		return $uri;
	}

    /**
     * 将框架路由转为自定义路由参数
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     */
    protected static function checkRule($uri,$param){

        $find_route = $uri;
        '/'==$find_route[0] && $find_route = substr($find_route,1);
        foreach (self::$rules as $rule => $route) {
            if($find_route == $route){
                $params = self::parseVar($rule);
                foreach ($params as $k => $v) {
                    $rule = str_replace(':'.$k,$param[$k],$rule);
                    unset($param[$k]);
                }
                $uri =  str_replace($find_route,$rule,$uri);
                return array_values(compact("uri", "param"));
            }
        }
        return array_values( compact('uri','param') );
    }

    /**
     * 设置路由规则
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     */
    public static function rule($rule, $route){
        self::$rules[$rule] = $route;
    }

	/**
	 * 获取当前路由数组
	 */
	public function getRouter(){
		return $this->route;
	}

	/**
	 * 设置当前路由
	 */
	public function setRouter($route){
		$c_route=$this->getConfigRouter();
		if(is_array($route)){
			$r = array_merge($c_route,$route);
			$this->route=$r;
		}
		if(is_string($route)){
			$this->analyzeRoute($route);
		}
	}

	/**
	 * 获取路由配置
	 * array(
		'module' =>'',
		'controller' =>'index',
		'action'	=> 'index'
	)
	 */
	private function getConfigRouter(){
		$res = self::$_route;
		$_global_route=\Ivy::app()->C('route');
		if($_global_route){
			self::$_route = array_merge($res,$_global_route);
		}
		return self::$_route;
	}

    
    // 返回目标路由和变量参数
    private function doRules($route_arr){ 
        // $res = self::parseVar("back/[:abc]/:id/[:is]");
        $param = [];
        $find_routeStr = '';
        // var_dump($route_arr);
        // var_dump(self::$rules);
        $find_routeStr = '';
        foreach (self::$rules as $rule => $routeStr) {
            $items = explode('/',$rule);
            if(count($items)===count($route_arr)){
                $find = true;
                foreach ($items as $k => $item) {
                    if (0 === strpos($item, ':')) {
                        $name       = substr($item, 1);
                        $param[$name] = $route_arr[$k];
                        continue;
                    }
                    if($item != $route_arr[$k]){
                        $find = false; //不匹配
                        break;
                    }
                }

            }else{
                $find = false;
            }
            
            if($find){
                $find_routeStr = $routeStr;
                break;
            }
        }

        if(!empty($find_routeStr)){
            $this->param = array_merge($this->param,$param);
            return explode('/',$find_routeStr);
            // halt($find_routeStr);
        }
        return $route_arr;
        // halt($find_routeStr);
    }

    private static function parseVar($rule)
    {
        // 提取路由规则中的变量
        $var = [];
        foreach (explode('/', $rule) as $val) {
            $optional = false;
            if (0 === strpos($val, '[:')) {
                // 可选参数
                $optional = true;
                $val      = substr($val, 1, -1);
            }
            if (0 === strpos($val, ':')) {
                // URL变量
                $name       = substr($val, 1);
                $var[$name] = $optional ? 2 : 1; //2表示可选参数
            }
        }
        return $var;
    }
    

	/**
	 * 解析路由信息
	 * @routerStr  string 路由参数 m/c/a
	 */
	private function analyzeRoute($routerStr=''){
		if($routerStr===''){
			$route_tmp=array_values($this->getConfigRouter());
		}else{
			$route_str=rtrim($routerStr);
			$route_tmp = explode('/', $route_str);
		}
		$route_tmp=array_filter($route_tmp);
        //自定义路由规则检测并替换
        $route_tmp = $this->doRules($route_tmp);
		$route_info = array();
		if(count($route_tmp) == 3){ //分组模式
			$route_info['module'] = array_shift($route_tmp);
			$route_info['controller'] = array_shift($route_tmp);
			$route_info['action'] = array_shift($route_tmp);
		}
		if(count($route_tmp) == 2){ //普通模式
			$route_info['controller'] = array_shift($route_tmp);
			$route_info['action'] = array_shift($route_tmp);
		}
		if(count($route_tmp) == 1){ //普通模式 默认action
			$controller=array_shift($route_tmp);
			if($controller != ''){
				$route_info['controller'] = $controller;
				$c_r=$this->getConfigRouter();//采用配置action
				$route_info['action'] = $c_r['action'];
			}else{
				throw new CException('路由参数为空');
			}
		}
		if(!empty($route_info)){
			$this->setRouter($route_info);
		}else{
			throw new CException('路由错误');
		}
	}

}
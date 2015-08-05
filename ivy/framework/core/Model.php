<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 *
 * 普通数据库操作对象 提供复杂的自定义数据库操作
 */
namespace Ivy\core;
class Model extends CComponent{
	//静态对象保存 节省性能开销
	private static $_models=array();
	//错误搜集
	protected      $_error = array();
	//数据源个性配置，可覆盖默认配置
	protected      $_config = null;

	// 查询表达式参数
    protected $options          =   array();
    // 查询表达式参数
    protected $lastSql          =   null;
    // 链操作方法列表 table distinct field join where group having union order limit
    protected $methods          =   array('table','distinct','field','join','where','group','having','union','order','limit','page');


	public function __construct($config=null){
		$this->_config=$config;
		$this->init();
	}
	/**
	 * 返回模型对象实例  同样的类名称，不同的配置， 则生产不同的实例
	 * 支持数据库配置文件自定义
	 * @return obj 
	 */
	public static function model($config=null)
	{
		$config=is_null($config)?\Ivy::app()->C('db_pdo'):$config;
		$className = get_called_class();
		$key=md5(serialize($config));
		$classKey=$className.'_'.$key;
		if(isset(self::$_models[$classKey])){
			return self::$_models[$classKey]; 
		}else{
			$model = self::$_models[$classKey] = new $className($config);
			return $model;
		}
	}
	/**
	 * 初始化后调用
	 */
	public function init(){
	}
	/**
	 * 错误信息出栈
	 */
	public function popErr(){
		if(!empty($this->_error))
			return array_pop($this->_error);
		return '';
	}

	/**
	 * 返回数据库对象句柄
	 * @return [type] [description]
	 */
	public function getDb(){
		return \Ivy::app()->getDb($this->_config);
	}

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method,$args) {
        if(in_array(strtolower($method),$this->methods,true)) {
            // 连贯操作的实现
            $this->options[strtolower($method)] = $args[0];
            return $this;
        }
        parent::__call($method,$args);
    }

    /**
     * where 可重复使用
     * $obj->where($map)->join($string)->where($map1)->find()
     * @param  [type] $map [description]
     * @return [type]      [description]
     */
    public function where($map){
    	if(empty($map)) return $this;
    	$opt=array();
    	if(is_string($map)){
    		if(@isset($this->options['where']['_string'])){
    			$opt['_string']=$this->options['where']['_string']." and ".$map;
    		}else{
    			$opt['_string']=$map;
    		}
    	}
    	if(is_array($map))
    		$opt=$map;

    	if(isset($this->options['where']))
    		$this->options['where'] = array_merge($this->options['where'],$opt);
    	else
    		$this->options['where'] = $opt;

    	return $this;
    }

     /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     * join('user as u on t.uid = u.id')
     */
    public function join($str,$t='LEFT JOIN'){
    	$joinStr = " $t " .$str;
    	if(isset($this->options['join'])){
    		$this->options['join'].=$joinStr;
    	}else{
    		$this->options['join']=$joinStr;
    	}

        return $this;
    }
    
    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return Model
     */
    public function limit($offset,$length=null){
        $this->options['limit'] =   is_null($length)?$offset:$offset.','.$length;
        return $this;
    }

	/**
	 * 指定分页
	 * @access public
	 * @param mixed $page 页数
	 * @param mixed $listRows 每页数量
	 * @return Model
	 */
	public function page($page=null,$listRows=null){
		if ($page===null && isset($_GET['page'])) $page=(int)$_GET['page'];
		if ($page===null)$page=1;
		if ($listRows===null && isset($_GET['row'])) $listRows=(int)$_GET['row'];
	    $this->options['page'] = is_null($listRows)?$page:$page.','.$listRows;
	    return $this;
	}

	public function table($table=null){
		if(is_null($table))
			throw new CException("表名为空！");
	    $this->options['table']= $table;
	    return $this;
	}
    /**
     * 构建查询sql
     **/
    public function buildSelectSql() {
        if(empty($this->options['table']))
        	$this->table();
        $sql = $this->db->buildSelectSql($this->options);
        $this->options=array();
        $this->lastSql=$sql;
        return $sql;
    }
    /**
     * 获取当前model的最后查询sql
     * @return [type] [description]
     */
    public function getLastSql() {
        return $this->lastSql;
    }

	/**
	 * 单条记录查询
	 * @param  [type] $sql [description]
	 * @return array      二维数组
	 */
	public function findBySql($sql){
		return $this->db->findBySql($sql);
	}

	/**
	 * 多条记录查询
	 * @param  [type] $sql [description]
	 * @return array   三维数组
	 */
	public function findAllBySql($sql){
		return $this->db->findAllBySql($sql);
	}

	/**
	 * 执行sql
	 * @param  [type] $sql [description]
	 * @return array   三维数组
	 */
	public function exec($sql){
		return $this->db->_exec($sql);
	}

	/**
	 * 数据插入
	 * @param [string] $tableName  [description]
	 * @param [array] $data [description]
	 * @return  lastInsertId 
	 */
	public function insertData($tableName=null,$data=null){
		if(empty($tableName)||empty($data))
			throw new CException("插入参数不全！");
		return $this->db->insertData($tableName,$data);
	}

	/**
	 * 更新数据
	 * @param  [type] $tableName [description]
	 * @param  [type] $condition 同连贯操作 where 参数
	 * @param  [type] $data      [description]
	 * @return [boolen]          是否执行成功
	 */
	public function updateData($tableName=null,$condition=null,$data=null){
		if(empty($tableName)||empty($data))
			throw new CException("更新参数不全！");
		return $this->db->updateDataByCondition($tableName,$condition,$data);
	}

	/**
	 * 删除数据
	 * @param  [type] $tableName [description]
	 * @param  [type] $condition 同连贯操作 where 参数
	 * @return [boolen]          是否执行成功
	 */
	public function deleteData($tableName=null,$condition=null){
		if(empty($tableName)||empty($condition))
			throw new CException("删除参数不全！");
		return $this->db->deleteDataByCondition($tableName,$condition);
	}


	/**
	 * 获取翻页信息  配合 page(),方法使用
	 * @param string $tableName	表名
	 * @param array $order 排序
	 * @param int $limit 每页显示条数
	 * @param int $page 页码
	 * @return array
	 */
	public function getPagener(){
		$data = array();
		if(empty($this->options['table']))
			$this->table();
		if(!isset($this->options['page'])) $this->page();
		//统计总数
		$opt_count=$this->options;
		//删除影响统计的option
		unset($opt_count['page'],$opt_count['limit'],$opt_count['order']);
		$opt_count['field']='count(1) as `count`';
		$sql_count = $this->db->buildSelectSql($opt_count);
		//按分组需求来统计总记录数
		if($opt_count['group']){
			$count_str=' COUNT(*) AS `count` ';
		}else{
			$count_str=' SUM(ivy_count.`count`) as `count` ';
		}
		$count = $this->findBySql("select ".$count_str." from (".$sql_count.") AS ivy_count" );
		//\Ivy::log("select sum(ivy_count.`count`) as `count` from (".$sql_count.") AS ivy_count");
		$pagener['recordsTotal'] = (int)$count['count'];

		if(!isset($this->options['page'])) $this->options['page']=1;
		$options = $this->options;
        if(isset($options['page'])) {
            // 根据页数计算limit
            if(strpos($options['page'],',')) {

                list($page,$listRows) =  explode(',',$options['page']);
            }else{
                $page = $options['page'];
            }
            $page    =  $page?$page:1;//当前页
            $listRows=  isset($listRows)?$listRows:(isset($options['limit'])&&is_numeric($options['limit'])?$options['limit']:20);//每页记录数
            $offset  =  $listRows*((int)$page-1);
            $options['limit'] =  $offset.','.$listRows;
        }
		$pagener['pageSize'] = (int)$listRows;
		$pagener['pageNums'] = (int)ceil($count['count']/$listRows);
		$pagener['currentPage'] = (int)$page;
		$data['pagener']=$this->db->generatePagener($pagener);
		$data['list'] = $this->findAllBySql($this->buildSelectSql());
		$data['page_code']=\Ivy::app()->widget('page/page',array('data'=>$data));//核心无此widget 自行扩展
		return $data;
	}

}
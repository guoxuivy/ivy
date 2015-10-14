<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * 仿照TP中 DB 的连贯操作 简化实现
 * 使用示例：
 * 
* $map['t.id'] = array(array('gt',1),array('lt',10));
* $map['t.myname'] = array('like','%水%')
* $map['_logic'] = 'OR';
* $map['info.company_name'] = array('neq','CCTV');
* $sql = \CompanyAccount::model()
* ->field(array('info.`company_name`'=>'c_name'))
* ->join('`company_info` info on t.`company_id` = info.`id`')
* ->where($map)
* ->limit('2')
* ->order('id desc')
* ->buildSelectSql();
* 
 */
namespace Ivy\db;
use Ivy\core\CException;
abstract class AbsoluteDB {
	//配置参数
	protected $config = NULL;
	// 数据库表达式
	protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
	// 查询表达式 option 处理
	protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';

	//约定为非查询入口
	abstract protected function _exec($sql);
	//约定为查询入口 方便读写分离
	abstract protected function _query($sql);


	/**
	 * 取得数据库类实例
	 * @static
	 * @access public
	 * @return mixed 返回数据库驱动类
	 */
	public static function getInstance() {
		$args = func_get_args();
		return call_user_func_array(array(__CLASS__, "factory"),$args);
	}

	/**
	 * 加载缓存 支持配置文件
	 * @access public
	 * @param mixed $cache_config 配置信息
	 * @return string
	 */
	protected static function factory($db_pdo_config='') {
		// 读取数据库配置
		if(empty($db_pdo_config))
			throw new CException ('数据库配置错误！');
		$class_arr=explode(":",$db_pdo_config['dsn']);
		$class="\\Ivy\\db\\pdo\\".$class_arr[0];
		if(class_exists($class)) {
			$db = new $class ($db_pdo_config);
		} else {
			throw new CException ('数据库驱动错误：'. $class);
		}
		return $db;
	}

	/**
	 * 生成查询SQL
	 * @access public
	 * @param array $options 表达式
	 * @return string
	 */
	public function buildSelectSql($options=array()) {
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
		if(true) { // SQL创建缓存
			$key    =  md5(serialize($options));
			$value  =  \Ivy::app()->cache->get($key);
			if(false !== $value) {
				return $value;
			}
		}
		$sql = $this->parseSql($this->selectSql,$options);
		if(isset($key)) { // 写入SQL创建缓存
			\Ivy::app()->cache->set($key,$sql,0);
		}
		return $sql;
	}
	/**
	 * 替换SQL语句中表达式
	 * @access public
	 * @param array $options 表达式
	 * @return string
	 */
	public function parseSql($sql,$options=array()){
		$sql   = str_replace(
			array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
			array(
				$this->parseTable($options['table']),
				$this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
				$this->parseField(!empty($options['field'])?$options['field']:'*'),
				$this->parseJoin(!empty($options['join'])?$options['join']:''),
				$this->parseWhere(!empty($options['where'])?$options['where']:''),
				$this->parseGroup(!empty($options['group'])?$options['group']:''),
				$this->parseHaving(!empty($options['having'])?$options['having']:''),
				$this->parseUnion(!empty($options['union'])?$options['union']:''),
				$this->parseOrder(!empty($options['order'])?$options['order']:''),
				$this->parseLimit(!empty($options['limit'])?$options['limit']:''),
				$this->parseComment(!empty($options['comment'])?$options['comment']:'')
			),$sql);
		return $sql;
	}

	/**
	 * table分析
	 * @access protected
	 * @param mixed $table
	 * @return string
	 */
	public function parseTable($tables) {
		if(is_array($tables)) {// 支持别名定义
			$array   =  array();
			foreach ($tables as $table=>$alias){
				if(!is_numeric($table))
					$array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
				else
					$array[] =  $this->parseKey($table);
			}
			$tables  =  $array;
		}elseif(is_string($tables)){
			$tables  =  explode(',',$tables);
			array_walk($tables, array(&$this, 'parseKey'));
		}
		return implode(',',$tables);
	}

	/**
	 * distinct分析
	 * @access protected
	 * @param mixed $distinct
	 * @return string
	 */
	protected function parseDistinct($distinct) {
		return !empty($distinct)?   ' DISTINCT ' :'';
	}

	/**
	 * field分析
	 * @access protected
	 * @param mixed $fields
	 * @return string
	 */
	protected function parseField($fields) {
		if(is_string($fields) && strpos($fields,',')) {
			$fields    = explode(',',$fields);
		}
		if(is_array($fields)) {
			// 完善数组方式传字段名的支持
			// 支持 'field1'=>'field2' 这样的字段别名定义
			$array   =  array();
			foreach ($fields as $key=>$field){
				if(!is_numeric($key))
					$array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
				else
					$array[] =  $this->parseKey($field);
			}
			$fieldsStr = implode(',', $array);
		}elseif(is_string($fields) && !empty($fields)) {
			$fieldsStr = $this->parseKey($fields);
		}else{
			$fieldsStr = '*';
		}
		//TODO 如果是查询全部字段，并且是join的方式，那么就把要查的表加个别名，以免字段被覆盖
		return $fieldsStr;
	}

	/**
	 * join分析 
	 * @access protected
	 * @param mixed $join
	 * @return string
	 */
	protected function parseJoin($join) {
		$joinStr = '';
		if(!empty($join)) {
			if(is_string($join)) {
				$joinStr .= $join;
			}
		}
		return $joinStr;
	}


	/**
	 * where分析
	 * @access protected
	 * @param mixed $where
	 * @return string
	 */
	public function parseWhere($where) {
		$whereStr = '';
		if(is_string($where)) {
			// 直接使用字符串条件
			$whereStr = $where;
		}else{ // 使用数组表达式
			$operate  = isset($where['_logic'])?strtoupper($where['_logic']):'';
			if(in_array($operate,array('AND','OR','XOR'))){
				// 定义逻辑运算规则 例如 OR XOR AND NOT
				$operate    =   ' '.$operate.' ';
				unset($where['_logic']);
			}else{
				// 默认进行 AND 运算
				$operate    =   ' AND ';
			}
			foreach ($where as $key=>$val){
				$whereStr .= '( ';
				if(is_numeric($key)){
					$key  = '_complex';
				}                    
				if(0===strpos($key,'_')) {
					// 解析特殊条件表达式
					$whereStr   .= $this->parseThinkWhere($key,$val);
				}else{
					// 查询字段的安全过滤
					if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
						throw new CException ('_EXPRESS_ERROR_'. $key);
					}
					// 多条件支持
					$multi  = is_array($val) &&  isset($val['_multi']);
					$key    = trim($key);
					if(strpos($key,'|')) { // 支持 name|title|nickname 方式定义查询字段
						$array =  explode('|',$key);
						$str   =  array();
						foreach ($array as $m=>$k){
							$v =  $multi?$val[$m]:$val;
							$str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
						}
						$whereStr .= implode(' OR ',$str);
					}elseif(strpos($key,'&')){
						$array =  explode('&',$key);
						$str   =  array();
						foreach ($array as $m=>$k){
							$v =  $multi?$val[$m]:$val;
							$str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
						}
						$whereStr .= implode(' AND ',$str);
					}else{
						$whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
					}
				}
				$whereStr .= ' )'.$operate;
			}
			$whereStr = substr($whereStr,0,-strlen($operate));
		}
		return empty($whereStr)?'':' WHERE '.$whereStr;
	}

	// where子单元分析
	protected function parseWhereItem($key,$val) {
		$whereStr = '';
		if(is_array($val)) {
			if(is_string($val[0])) {
				if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // 比较运算
					$whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
				}elseif(preg_match('/^(NOTLIKE|LIKE)$/i',$val[0])){// 模糊查找
					if(is_array($val[1])) {
						$likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
						if(in_array($likeLogic,array('AND','OR','XOR'))){
							$likeStr    =   $this->comparison[strtolower($val[0])];
							$like       =   array();
							foreach ($val[1] as $item){
								$like[] = $key.' '.$likeStr.' '.$this->parseValue($item);
							}
							$whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';                          
						}
					}else{
						$whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
					}
				}elseif('exp'==strtolower($val[0])){ // 使用表达式
					$whereStr .= ' ('.$key.' '.$val[1].') ';
				}elseif(preg_match('/IN/i',$val[0])){ // IN 运算
					if(isset($val[2]) && 'exp'==$val[2]) {
						$whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
					}else{
						if(is_string($val[1])) {
							 $val[1] =  explode(',',$val[1]);
						}
						$zone      =   implode(',',$this->parseValue($val[1]));
						$whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
					}
				}elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN运算
					$data = is_string($val[1])? explode(',',$val[1]):$val[1];
					$whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->parseValue($data[0]).' AND '.$this->parseValue($data[1]).' )';
				}else{
					throw new CException ('_EXPRESS_ERROR_'. $val[0]);
				}
			}else {
				$count = count($val);
				$rule  = isset($val[$count-1])?strtoupper($val[$count-1]):'';
				if(in_array($rule,array('AND','OR','XOR'))) {
					$count  = $count -1;
				}else{
					$rule   = 'AND';
				}
				for($i=0;$i<$count;$i++) {
					$data = is_array($val[$i])?$val[$i][1]:$val[$i];
					if('exp'==strtolower($val[$i][0])) {
						$whereStr .= '('.$key.' '.$data.') '.$rule.' ';
					}else{
						$op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
						$whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
					}
				}
				$whereStr = substr($whereStr,0,-4);
			}
		}else {
			$whereStr .= $key.' = '.$this->parseValue($val);
		}
		return $whereStr;
	}

	/**
	 * 特殊条件分析
	 * @access protected
	 * @param string $key
	 * @param mixed $val
	 * @return string
	 */
	protected function parseThinkWhere($key,$val) {
		$whereStr   = '';
		switch($key) {
			case '_string':
				// 字符串模式查询条件
				$whereStr = $val;
				break;
			case '_complex':
				// 复合查询条件
				$whereStr   =   is_string($val)? $val : substr($this->parseWhere($val),6);
				break;
			case '_query':
				// 字符串模式查询条件
				parse_str($val,$where);
				if(isset($where['_logic'])) {
					$op   =  ' '.strtoupper($where['_logic']).' ';
					unset($where['_logic']);
				}else{
					$op   =  ' AND ';
				}
				$array   =  array();
				foreach ($where as $field=>$data)
					$array[] = $this->parseKey($field).' = '.$this->parseValue($data);
				$whereStr   = implode($op,$array);
				break;
		}
		return $whereStr;
	}

	/**
	 * group分析
	 * @access protected
	 * @param mixed $group
	 * @return string
	 */
	protected function parseGroup($group) {
		return !empty($group)? ' GROUP BY '.$group:'';
	}

	/**
	 * having分析
	 * @access protected
	 * @param string $having
	 * @return string
	 */
	protected function parseHaving($having) {
		return  !empty($having)?   ' HAVING '.$having:'';
	}

	/**
	 * limit分析
	 * @access protected
	 * @param mixed $lmit
	 * @return string
	 */
	protected function parseLimit($limit) {
		return !empty($limit)?   ' LIMIT '.$limit.' ':'';
	}

	/**
	 * order分析
	 * @access protected
	 * @param mixed $order
	 * @return string
	 */
	protected function parseOrder($order) {
		if(is_array($order)) {
			$array   =  array();
			foreach ($order as $key=>$val){
				if(is_numeric($key)) {
					$array[] =  $this->parseKey($val);
				}else{
					$array[] =  $this->parseKey($key).' '.$val;
				}
			}
			$order   =  implode(',',$array);
		}
		return !empty($order)?  ' ORDER BY '.$order:'';
	}

	/**
	 * union分析
	 * @access protected
	 * @param mixed $union
	 * @return string
	 */
	protected function parseUnion($union) {
		if(empty($union)) return '';
		if(isset($union['_all'])) {
			$str  =   'UNION ALL ';
			unset($union['_all']);
		}else{
			$str  =   'UNION ';
		}
		foreach ($union as $u){
			$sql[] = $str.(is_array($u)?$this->buildSelectSql($u):$u);
		}
		return implode(' ',$sql);
	}

	/**
	 * comment分析
	 * @access protected
	 * @param string $comment
	 * @return string
	 */
	protected function parseComment($comment) {
		return  !empty($comment)?   ' /* '.$comment.' */':'';
	}

	/**
	 * 字段名分析
	 * @access protected
	 * @param string $key
	 * @return string
	 */
	protected function parseKey(&$key) {
		return $key;
	}
	/**
	 * value分析
	 * @access protected
	 * @param mixed $value
	 * @return string
	 */
	protected function parseValue($value) {
		if(is_string($value)) {
			$value =  '\''.$this->escapeString($value).'\'';
		}elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
			$value =  $this->escapeString($value[1]);
		}elseif(is_array($value)) {
			$value =  array_map(array($this, 'parseValue'),$value);
		}elseif(is_bool($value)){
			$value =  $value ? '1' : '0';
		}elseif(is_null($value)){
			$value =  'null';
		}
		return $value;
	}
	/**
	 * SQL指令安全过滤
	 * @access public
	 * @param string $str  SQL字符串
	 * @return string
	 */
	public function escapeString($str) {
		return addslashes($str);
	}


	/**
	 * 生成插入数据sql语句
	 */
	protected function getInsertSql($tableName,$data){
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		$sql = 'insert into `'.$tableName.'` (';

		$kStr = null;
		$vStr = null;
		if(!empty($data)){
			$count = 0;
			foreach ($data as $k => $v){
				if($count == 0){
					$kStr .= '`'.$k.'`';
					$vStr .= "'".$v."'";
				}else{
					$kStr .= ',`'.$k.'`';
					$vStr .= ",'".$v."'";
				}
				$count++;
			}
		}
		$sql .= $kStr.') values('.$vStr.')';
		return $sql;
	}

	/**
	 * 生成更新sql语句
	 */
	protected function getUpdataSql($tableName,$where='',$data){
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		
		$sql = 'update `'.$tableName.'` set ';
		if(empty($data)){
			throw new CException('无效的更新语句');
		}
		$count = 0;
		foreach ($data as $k=>$v){
			if($count == 0)
				$sql .= '`'.$k."` = '".$v."'";
			else 
				$sql .= ' , `'.$k."` = '".$v."' ";
			$count++;
		}
		$sql .= $this->parseWhere($where);
		return $sql;
	}

	/**
	 * 生成删除sql语句
	 */
	protected function getDeltetSql($tableName,$where=''){
		if (! isset ( $tableName )) {
			throw new CException ( '无效的表' );
		}
		$sql = 'delete from `'.$tableName.'` ';
		$sql .= $this->parseWhere($where);
		return $sql;
	}





	/**
	 * 生产翻页器
	 * pageNums   总页数
	 * currentPage 当前页数 从1开始
	 * @param  [type] $data [description]
	 * @param  [type] $nun [list 显示个数]
	 * @return [type]       [description]
	 */
	public function generatePagener(&$data,$nun=5){
		$ban = ceil($nun/2);
		$pagener = array();		
		$page_num_arr = array();
		
		for($i=1;$i<=$data['pageNums'];$i++){
			$page_num_arr[] = $i;
		}
		
		$nowPage = $data['currentPage'];
		if(count($page_num_arr) <= $nun){
			$pagener = $page_num_arr;
		}else if($nowPage <= $ban){
			for($i = 0;$i<=($nun-1);$i++){
				$pagener[] = $page_num_arr[$i];
			}
		}else if(($nowPage-1) >= $ban && isset($page_num_arr[$nowPage+1])){
			for($i = ($nowPage-$ban); $i <= ($nowPage + 1); $i++){
				$pagener[] = $page_num_arr[$i];
			}
		}else{
			for($i=($data['pageNums']-$nun);$i <= ($data['pageNums']-1); $i++){
				$pagener[] = $page_num_arr[$i];
			}
		}
		$data['linkList'] = $pagener;
		return $data;
	}
}
<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license http://www.ivyframework.com/license/
 * @package framework
 * @link https://github.com/guoxuivy/ivy * @since 1.0 
 */
namespace Ivy\core\lib;
/**
 * 哈希一致性算法 分布式
 * 使用二分查找法查找虚拟节点环
 * demo：
 * 
 *    $config = array(
 *   	"127.0.0.1:11211",
 *     	"127.0.0.1:11212",
 *     	"127.0.0.1:11213",
 *     	"127.0.0.1:11214",
 *     	"127.0.0.1:11215"
 *     );
 *     $FlexiHash = new FlexiHash($config);
 *     echo $FlexiHash->get('ivy'); //putout 127.0.0.1:11215
 * 
 **/
class FlexiHash{
	//所有虚拟节点 与 真实节点的映射
	private $_node = array();
	//所有虚拟节点key值
	private $_nodeData = array();
	//需要查找的哈希值
	private $_keyNode = 0;
	//真实节点
	private $_realNode = array();
	// 每个真实节点生成虚拟节点的个数 值越大 速度越慢
	private $_virtualNodeNum = 200;

	public function __construct($nodes){
		if (!$nodes){
			throw new \Exception("real node null！");
		}
		$this->_realNode=$nodes;
		// 设置虚拟节点
		foreach($nodes as $key=>$value){
			for ($i = 0; $i < $this->_virtualNodeNum; $i++){
				$this->_node[sprintf("%u", crc32($value."#".$i))] = $value."#".$i;
			}
		}
		// 排序
		ksort($this->_node);
	   
	}

	/**
	 * 采用二分法从虚拟节点中查找最近的节点
	 * @param int $low 开始位置
	 * @param int $high 结束位置
	 * 
	 */
	private function _findServerNode($low, $high){
		// 开始下标小于结束下标
		if ($low < $high){
			
			$avg = intval(($low+$high)/2);
			
			if ($this->_nodeData[$avg] == $this->_keyNode){
				return $this->_nodeData[$avg];
			}elseif ($this->_keyNode < $this->_nodeData[$avg]){
				return $this->_findServerNode($low, $avg-1);
			}else{
				return $this->_findServerNode($avg+1, $high);
			}
		}else if(($low == $high)){
			// 大于平均值
			if ($low ==0 || $low == count($this->_nodeData)-1){
				return $this->_nodeData[$low];
			}
			if ($this->_nodeData[$low] < $this->_keyNode){
				
				if (abs($this->_nodeData[$low] - $this->_keyNode) < abs($this->_nodeData[$low+1]-$this->_keyNode)){
					return $this->_nodeData[$low];
				}else{
					return $this->_nodeData[$low+1];
				}
		
			}else {
				if (abs($this->_nodeData[$low] - $this->_keyNode) < abs($this->_nodeData[$low-1]-$this->_keyNode)){
					return $this->_nodeData[$low];
				}else{
					return $this->_nodeData[$low-1];
				}
			}
		}else{
			if ( ($low == 0)&&($high < 0) ){
				return $this->_nodeData[$low];
			}
		
			if (abs($this->_nodeData[$low] - $this->_keyNode) < abs($this->_nodeData[$high]-$this->_keyNode)){
				return $this->_nodeData[$low];
			}else{
				return $this->_nodeData[$high];
			}
		}
	}

	/**
	 * 查找对应的真实节点
	 * @param  $key 要查找的key
	 * 
	 */
	 private function _findNode($key){
		$this->_nodeData = array_keys($this->_node);

		$this->_keyNode = sprintf("%u", crc32($key));
		
		// 获取key值对应的最近的节点的hash值
		$nodeKey = $this->_findServerNode(0, count($this->_nodeData)-1);
		
		//获取对应的真实节点
		list($realNode, $num) = explode("#", $this->_node[$nodeKey]);
		
		if (empty($realNode)){
			throw new \Exception("serach realNode config error！");
		}
		return $realNode;
	}

	public function get($key){
		return $this->_findNode($key);
	}
}
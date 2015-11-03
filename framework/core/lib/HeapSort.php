<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy * @since 1.0 
 *
 * php堆排序实现原理
 * $l=array(11,88,7,2,3,24,6,5,9);
 * $h=new HeapSort($l);
 * $r = $h->getSortedResult();
 * var_dump($r);
 */
namespace Ivy\core\lib;
/**
 * php堆排序实现原理
 * php程序中关于堆的一些概念:
 * 假设n为当前数组的key则
 * n的父节点为 n>>1 或者 n/2(整除);
 * n的左子节点l= n<<1 或 l=n*2,n的右子节点r=(n<<1)+1 或 r=l+1
*/


/**
 * 堆排序
 * 添加、删除、修改待扩展
 */
class HeapSort {

    protected $listSize;

    protected $tree;

    /**
     * 有原始数据复制 可优化为引用传递，节约内存
     * @param [type] $list [description]
     */
    public function __construct($list) {
        $this->listSize = count($list);
        $i = 1;
        foreach ($list as $li) {
            $this->tree[$i++] = $li;
        }
        unset($list);
        $this->initHeap();
    }

 

    /**
     * 获取排序后的结果数组
     * @return [type] [description]
     */
    public function getSortedResult() {

        $this->sortHeap();

        return $this->tree;

    }

    /**
     * 数组首位置换 排序
     * @return [type] [description]
     */
    private function sortHeap() {

        for ($end = $this->listSize; $end > 1; $end--) {

            $this->swap($this->tree[1], $this->tree[$end]);

            $this->adjustHeap(1, $end - 1);

        }

    }

 
    /**
     * 初始化为 堆结构（大顶或小顶）
     * @return [type] [description]
     */
    private function initHeap() {
        $start = $this->listSize>>1; //最后一个非子叶节点的索引  右移一位
        for ($start; $start >= 1; $start--) {
            $this->adjustHeap($start, $this->listSize);
        }
    }

 
    /**
     * 子树递归处理 保持堆结构
     * @param  [type] $start [description]
     * @param  [type] $len   [description]
     * @return [type]        [description]
     */
    private function adjustHeap($start, $last) {

        $l=$start<<1;        //$n的左孩子位
         
        if(!isset($this->tree[$l])||$l>$last) return ;
        $r=$l+1;        //$n的右孩子位
        //如果右孩子比左孩子大,则让父节点的右孩子比
        if($r<=$last&&$this->tree[$r]<$this->tree[$l]) $l=$r;  //排序  取小（小顶）
        //如果其中子节点$l比父节点$n大,则与父节点$n交换
        if($this->tree[$l]<$this->tree[$start])         // 排序  取小（小顶）   
        {
            //子节点($l)的值与父节点($n)的值交换
            $this->swap($this->tree[$l],$this->tree[$start]);
            //交换后父节点($n)的值($arr[$n])可能还小于原子节点($l)的子节点的值,所以还需对原子节点($l)的子节点进行调整,用递归实现
            $this->adjustHeap($l,$last);
        }

    }

 
    /**
     * 安位与 数据交换 节约内存
     * @param  [type] &$a [description]
     * @param  [type] &$b [description]
     * @return [type]     [description]
     */
    private function swap(&$a, &$b) {
        $a=$a ^ $b;        $b=$a ^ $b;        $a=$a ^ $b;
    }

}
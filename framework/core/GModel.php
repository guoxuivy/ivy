<?php
/**
 * @author ivy <guoxuivy@gmail.com>
 * @copyright Copyright &copy; 2013-2017 Ivy Software LLC
 * @license https://github.com/guoxuivy/ivy/
 * @package framework
 * @link https://github.com/guoxuivy/ivy 
 * @since 1.0 
 * 表的横向切分扩展
 * 按时间分表 自动化(可分页)查询
 * Date: 2016/7/28
 * Time: 9:47
    //分表结构示例
    order_list     //全部数据
    order_list_1   //近10天数据
    order_list_2   //近10~20天数据
    order_list_3   //近20~30天数据
    order_list_4   //近30~40天数据

    与主表的数据同步由底层完成，此只为提高大表查询能力
 */
namespace Ivy\core;    
class GModel extends Model   
{
    //分表时间间隔 （天） //时间间隔10天，组容量4（即最近40天数据）
    protected $_period = 10;
    //分表数量
    protected $_group_num = 4;
    //分表时间字段名称
    protected $_period_col = "create_time";
    //分表名称
    protected $_period_table = "order_list_";//后接数组  1、2、3、4（存储近40天数据，1为近10天的）
 
    /**
     * 根据综合条件查询订单列表（带分页参数反回）
     * 横向时间轴分库自动查询
     * 根据综合条件查询订单列表（带分页参数反回）
     * @param array $w 查询条件
     * @return
     */
    public function listByWhere($w, $firstRow = '1', $listRows = '20') {
 
        if(empty($w[$this->_period_col])){ 
            //不具备分表查询必要条件
			throw new CException("分表查询参数错误！");
        }

        $b_time = strtotime($w[$this->_period_col][0][1]);
        $e_time = strtotime($w[$this->_period_col][1][1]);

        //生成分组时间段
        $group_time=[];
        for ($i=1; $i <= $this->_group_num; $i++) { 
            $t1 = time() - ($i-1)*$this->_period*3600*24;
            $t2 = time() -     $i*$this->_period*3600*24;
            $group_time[$i]=[$t1,$t2]; //大-》小
        }
        //本次查询涉及的分组表 安时间确定
        $group_tables = [];
        foreach ($group_time as $i => $v) {
            if($this->getMix([$b_time,$e_time],[$v[1],$v[0]])){
                $group_tables[$i]=array("name"=>$this->_period_table.$i );
            }
        }
        //数据统计游标
        $all_count = 0;
        //查询分库count
        foreach ($group_tables as &$table) {
            $count = $this->table($table["name"])->where($w)->count();
            $count = intval($count) > 0 ? $count : 0;
            $table["count"]=$count;
            //数据域
            $table["data_are"]=[$all_count,$all_count+$count]; 
            //混合域
            $table["mix_are"] = $this->getMix([$firstRow , $firstRow+$listRows],$table["data_are"],"array");
            //对应分页参数
            $a = $table["mix_are"][0]-$table["data_are"][0];
            $b = $table["mix_are"][1]-$table["mix_are"][0];
            $table["limit"] = $table["mix_are"]?[$a,$b]:[];
            $all_count += $count;
        }
        if($all_count==0) 
            return array();
        $data['c'] = $all_count;
        
        //确定每个分组需要出多少个数据
        $list=array();
        foreach ($group_tables as $t_b) {
            if( empty($t_b["mix_are"]) )
                continue;
            $result = $this->table($t_b["name"])->where($w)->limit($t_b["limit"][0] ,$t_b["limit"][1])->order($this->_period_col.' desc')->_findAll();
            $list = array_merge($list,$result);
        }
        $data['list'] = $list;
        return $data;
    }

    /**
     * 私有查询封装
     */
    private function _findAll(){
    	return $this->findAllBySql($this->buildSelectSql());
    }

    /**
     * 获取交集
     * @param array $a,$b 数组 a[0],a[1] 小，大规则
     * @return array
     */
    function getMix($a,$b,$r="bool"){//判断是否有交集
        $res=[];
        if($a[0]>$b[1] || $a[1]<$v[0]){
            //无交集
        }else{
            //有交集 共4种情况
            if($b[0]>=$a[0] && $b[1]<=$a[1]){
                $res=$b;
            }elseif($b[0]<=$a[0] && $b[1]>=$a[1]){
                $res=$a;
            }elseif($b[1]>$a[1] && $b[0]<=$a[1]) {
                $res=[$b[0],$a[1]];
            }elseif($b[0]<$a[0] && $b[1]>=$a[0]) {
                $res=[$a[0],$b[1]];
            }
        }
        if($r=="bool"){
            if(empty($res))
                return false;
            return true;
        }else{
            return $res;
        }
    }

}

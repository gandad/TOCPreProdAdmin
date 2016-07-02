<?php
namespace Home\Model;
use Think\Model;
/**
 * 这是仓库管理的对象：查询门店列表、区域列表、门店指标
 */
class PartyObjectModel extends Model {
     protected $trueTableName = 'bparty';
	// 获得区域列表
	public function getRegionList($condition,$pagestr,$fieldstr) {
		
//		$dbt = M("bparty","",getMyCon());
//		$rs = $dbt
//		->field($fieldstr)
//		->where($condition)
//		->page($pagestr)
//		->select();
//		setTag('sql123', $dbt->_sql());
//		return $rs;
	}


	// 获得门店列表
	public function getStoreList($condition,$pagestr,$fieldstr) {
		$dbt = M('vwp2partyrel','',getMyCon());
		
		$rs = $dbt
		->field($fieldstr)
		->where($condition)
		->page($pagestr)
		->select();
//		setTag('sql123', $dbt->_sql());
		return $rs;
	}
	
	//获得门店运作指标
	public function getStoreIndicator($condition,$pagestr,$fieldstr) {
		$dbt = M('vwp2partyrel','',getMyCon());
		
		$rs = $dbt
		->field($fieldstr)
		->where($condition)
		->page($pagestr)
		->select();
//		setTag('sql123', $dbt->_sql());
		return $rs;
	}
	
}
?>
	
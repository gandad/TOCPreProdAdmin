<?php
namespace Home\Model;
use Think\Model;

/**
 * 这是库存管理的对象
 */
class StockObjectModel extends Model {
     protected $trueTableName = 'dstok';
	  
	// 获得门店的目标库存及库存
 	public function getStoreTSInfo($storecode) {

		$Model = new \Think\Model("","",getMyCon());
		
		$sqlstr = "SELECT PartyCode,dstock.SKUCode,productcolorcode,colorname,SizeName,brandname,yearname,seasonname,seasonstagename,";
		$sqlstr = $sqlstr . " maintypename,subtypename,TargetQty,ifnull(OnHandQty,0)+ifnull(OnRoadQty,0) as StockQty,";
		$sqlstr = $sqlstr . " (ifnull(TargetQty,0)-ifnull(OnHandQty,0)-ifnull(OnRoadQty,0)) as RepRet";
		$sqlstr = $sqlstr . " FROM dstock left join bsku on dstock.SKUCode = bsku.skucode";
		$sqlstr = $sqlstr . " where PartyCode='" . $storecode . "'";
		p($sqlstr);
		$rs=$Model->execute($sqlstr);

		return $rs;	
		}


	// 获得中央仓的目标库存及库存
 	public function getCWHTSInfo($storecode) {

		$Model = new \Think\Model("","",getMyCon());
		
		$sqlstr = "SELECT PartyCode,dstock.SKUCode,productcolorcode,colorname,SizeName,brandname,yearname,seasonname,seasonstagename,";
		$sqlstr = $sqlstr . " maintypename,subtypename,TargetQty,ifnull(OnHandQty,0)+ifnull(OnRoadQty,0) as StockQty,";
		$sqlstr = $sqlstr . " (ifnull(TargetQty,0)-ifnull(OnHandQty,0)-ifnull(OnRoadQty,0)) as RepRet";
		$sqlstr = $sqlstr . " FROM dstock left join bsku on dstock.SKUCode = bsku.skucode";
		$sqlstr = $sqlstr . " where PartyCode='" . $storecode . "'";
		p($sqlstr);
		$rs=$Model->execute($sqlstr);

		return $rs;	
		}

	//获得目标库存调整记录
	public function getPartyAdjRec($PartyCode) {
		

		return $rs;
	}
	
	//获得目标库存调整过程
	public function getPartyAdjRec($PartyCode,$AdjType='门店') {
		

		return $rs;
	}
		
	//获得补货单
	public function getReplenishBill($PartyCode) {
		

		return $rs;
	}
	
	//获得退货单
	public function getReturnBill($PartyCode) {
		

		return $rs;
	}
	
	//获得产品的历史库存
	public function getProdHSStock($StoreCode) {
		

		return $rs;
	}
}
?>
	
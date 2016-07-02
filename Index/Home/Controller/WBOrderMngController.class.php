<?php
namespace Home\Controller;

class WBOrderMngController extends \Think\Controller {
// 获得产品列表
	public function getProProdOrder() {
		if(hasInput("SKCCode")) $condition["a.SKCCode"] = getInputValue("SKCCode");
		if(hasInput("BrandName")) $condition["BrandName"] = getInputValue("BrandName");
		if(hasInput("YearName")) $condition["YearName"] = getInputValue("YearName");
		if(hasInput("SeriesName"))  $condition["SeriesName"] = getInputValue("SeriesName");
		if(hasInput("SeasonName"))  $condition["SeasonName"] =getInputValue("SeasonName");
		if(hasInput("SeasonStageName"))  $condition["SeasonStageName"] = getInputValue("SeasonStageName");
		if(hasInput("MainTypeName")) $condition["MainTypeName"] = getInputValue("MainTypeName");

		$fieldstr = "@x:=@x+1 as rownum,a._Identify,OrderCode,a.SKCCode,b.StyleCode,BrandName,YearName,SeasonName,SeasonStageName,SeriesName,MainTypeName,";
		$fieldstr = $fieldstr . "ColorName,OtherSKUCompName,OrderType,DueDate,OrderRemark,OrderQty,";
		$fieldstr = $fieldstr . "SG1Qty,SG2Qty,SG3Qty,SG4Qty,SG5Qty,SG6Qty,SG7Qty,";
		$fieldstr = $fieldstr . "SG8Qty,SG9Qty,SG10Qty,SG11Qty,SG12Qty,SG13Qty,SG14Qty,SG15Qty";
	
//		$pagestr = getInputValue("Page","1,1000");
		$fieldstr = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
		$rs = M('preprodorder as a','',getMyCon())
		->join("inner join bskc as b on a.SKCCode=b.SKCCode")
		->field($fieldstr)
		->where($condition)
//		->page($pagestr)
		->select();

//		echo M('preprodorder as a','',getMyCon())->_sql();
//		die();
		return $this -> ajaxReturn($rs);
	}
	
		public function getOrderTree(){
			ini_set('memory_limit', '-1');
			set_time_limit(1200);
			
	 	$dbt =  M("preprodorder","",getMyCon());
		

		$treeData = $dbt
		->field("distinct OrderType as id,OrderType as value, true as Open")
		->where("IsOrderFinished=0")
		->select();
		
//		dump($treeData);
//		die();
		
		for ($x=0; $x<count($treeData); $x++) {
  		   $parentid = $treeData[$x]['id'];
			$levelTwo = $dbt
			->field("OrderCode as id ,concat(SKCCode,' | ',DueDate,' | ' ,ifnull(OrderQty,0)) as value,SKCCode,OrderType,DueDate,OrderQty")
			->where("OrderType='" . $parentid . "' and IsOrderFinished=0")
			->select();
			$treeData[$x]['data'] = $levelTwo;
		} 
		
		return $this -> ajaxReturn($treeData);
	 }
}
?>
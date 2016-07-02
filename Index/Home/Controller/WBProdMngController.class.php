<?php
namespace Home\Controller;

class WBProdMngController extends \Think\Controller {
// 获得产品列表
	public function getProductList() {
		if(hasInput("SKCCode")) $condition["SKCCode"] = getInputValue("SKCCode");
		if(hasInput("BrandName")) $condition["BrandName"] = getInputValue("BrandName");
		if(hasInput("YearName")) $condition["YearName"] = getInputValue("YearName");
		if(hasInput("SeriesName"))  $condition["SeriesName"] = getInputValue("SeriesName");
		if(hasInput("SeasonName"))  $condition["SeasonName"] =getInputValue("SeasonName");
		if(hasInput("SeasonStageName"))  $condition["SeasonStageName"] = getInputValue("SeasonStageName");
		if(hasInput("MainTypeName")) $condition["MainTypeName"] = getInputValue("MainTypeName");

		$fieldstr = "@x:=@x+1 as rownum,_Identify,IsStopProduce,IsStopReplenish,IsStopAnalyze,SKCCode,SKCName,StyleCode,";
		$fieldstr = $fieldstr . "StyleName,ColorCode,ColorName,OtherSKUCompCode,OtherSKUCompName,TicketPrice,VCRatio,BrandCode,BrandName,";
		$fieldstr = $fieldstr . "SeriesCode,SeriesName,LifeStage,YearCode,YearName,SeasonCode,SeasonName,SeasonStageCode,";
		$fieldstr = $fieldstr . "SeasonStageName,MainTypeCode,MainTypeName,SubTypeCode,seriesname,";
		$fieldstr = $fieldstr . "SubType1Code,SubType2Code,SubType3Code,OnShelfDate";
	
//		$pagestr = getInputValue("Page","1,1000");
		$fieldstr = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
		$rs = M('bskc','',getMyCon())
		->field($fieldstr)
		->where($condition)
//		->page($pagestr)
		->select();

//		dump($rs);
		return $this -> ajaxReturn($rs);
	}
	
	public function getBrandList() {
		$rs = M('bskc','',getMyCon())
		->field("brandcode as id ,brandname as value")
		->where($condition)
		->distinct(true)
		->select();
		
		array_push($rs,array('id'=>'all','value'=>'所有'));
		$rs = array_reverse($rs);
		return $this -> ajaxReturn($rs);
	}

	public function getYearList() {
		$rs = M('bskc','',getMyCon())
		->field("yearcode as id ,yearname as value")
		->where($condition)
		->distinct(true)
		->order("yearcode asc")
		->select();
	
		array_push($rs,array('id'=>'all','value'=>'所有'));
		$rs = array_reverse($rs);
		return $this -> ajaxReturn($rs);
	}	
	
	public function getSeasonList() {
		$rs = M('bskc','',getMyCon())
		->field("seasoncode as id ,seasonname as value")
		->where($condition)
		->distinct(true)
		->select();
		
		array_push($rs,array('id'=>'all','value'=>'所有'));
		$rs = array_reverse($rs);
		
		return $this -> ajaxReturn($rs);
	}	
	
	public function getMainTypeList() {
		$rs = M('bskc','',getMyCon())
		->field("maintypecode as id ,maintypename as value")
		->where($condition)
		->distinct(true)
		->select();
		
		array_push($rs,array('id'=>'all','value'=>'所有'));
		$rs = array_reverse($rs);
		
		return $this -> ajaxReturn($rs);
	}		
	
	
	public function getSizeList(){
		
		$rs = M('bsize','',getMyCon())
		->select();
		
//		dump($rs);
		return $this -> ajaxReturn($rs);
	}
	
	public function getSizeGroupName()
	{
		$rs = M('bsyspara','',getMyCon())
		->field("Name as SizeGroup,VString as SizeName")
		->where(array('Desc'=>'SizeGroupName','VBool'=>1))
		->order('_Identify asc')
		->select();
	
	
	   return $this -> ajaxReturn($rs);	
	}
}
?>
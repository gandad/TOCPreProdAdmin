<?php
namespace Home\Controller;

class IndexController extends \Think\Controller {

	// 获得最新版本号
	public function getImportStatistics() {
		$sqlstr = "select  (select count(distinct 客户号) from importstock) as stockwhnum,";
		$sqlstr = $sqlstr . " (select count(distinct 客户号) from importsale) as saleswhnum";
		$Model = new \Think\Model("","",getMyCon());
		$rs = $Model->query($sqlstr);
		
		return $this -> ajaxReturn($rs);
	}
	
	//执行补货：导入数据，执行补货\n
	public function execReplenish()
	{
		$Model = new \Think\Model("","",getMyCon());
		$Model->execute("call ReplenishOnly");
		return "OK";
	}
	// 获得参数
	public function getSysPara() {
		$dbm = M('bsyspara','',getMyCon());
//		$rs = $dbm -> where("[desc]='timespan' or name='usexscore'") -> getField('name,vinteger');
		//bdump($rs);
		return $this -> ajaxReturn($rs);
	}

	public function getRawUrl() {
		$DSSuffix = $_POST['DSSuffix'];
//		setTag('DSSuffix', $DSSuffix);
		
		$dbm = M('sysparameters','',getMyCon());
		$rs = $dbm -> where("[name]='" . $DSSuffix . "'") -> getField('vtext');
			
		return $this -> ajaxReturn($rs);
	}

	public function getPMixData() {
		
		$sqlstr = "SELECT ";
		
		if(getInputValue("ProdType")=='MainType')
		$sqlstr .= "  MainTypeName as ClassName,PeriodTput,PeriodSaleQty,PeriodCCRLoad,";
		else
		$sqlstr .= " concat(MainTypeName,'-',SubTypeName) as ClassName,PeriodTput,PeriodSaleQty,PeriodCCRLoad,";
		
		$sqlstr .= " sum(PeriodTput)/sum(PeriodSaleQty) as TputPQty,";
		$sqlstr .= " sum(PeriodSaleQty)/sum(PeriodCCRLoad) as SalePCCR,";
		$sqlstr .= " sum(PeriodTput)/sum(PeriodCCRLoad) as TputPCCR";
		$sqlstr .= " FROM vwpmix";
		
		if(hasInput("LifeStage"))
		$sqlstr .= " where LifeStage='". getInputValue("LifeStage") ."'";
		
		if(getInputValue("ProdType")=='MainType')
		$sqlstr .= " group by MainTypeName";
		else
		$sqlstr .= " group by MainTypeName,SubTypeName";
		
		$sqlstr .= " order by sum(PeriodTput)/sum(PeriodCCRLoad) desc";

//echo $sqlstr;

	    $Model = new \Think\Model("","",getMyCon());
		$rs= $Model->query($sqlstr);
		return $this -> ajaxReturn($rs);
	}	
}
?>
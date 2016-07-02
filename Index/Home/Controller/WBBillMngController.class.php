<?php
namespace Home\Controller;

class WBBillMngController extends \Think\Controller {
		//获得目标库存调整记录
	public function getExportRepBill() {
		resetCounter();
				
		$sqlstr = "select  @x:=@x+1 as rownum,MakeDate as 补货日期, SrcPartyCode as 老供应商,'UB' as 类型,'PMC',TrgPartyCode as 老工厂,'0001' as '0001', 'SDS01' as 单据类型,";
		$sqlstr = $sqlstr . " dmovskuplan.SKUCode as 商品号,bsku.SKCCode as 款杯色, CWHOnHandQty as 总仓在手, dstock.TargetQty as 目标库存,dstock.OnHandQty as 门店在手,dstock.OnRoadQty as 门店在途, Sale28Qty as 门店四周销售,MovQty as 补货件数";
		$sqlstr = $sqlstr . " from dmovskuplan inner join ";
		$sqlstr = $sqlstr . " (select SKUCode,OnHandQty as CWHOnHandQty from dstock where ifnull(OnHandQty,0)>0 ";
		$sqlstr = $sqlstr . "  and PartyCode in (select PartyCode from bparty where PartyType='总仓')  ) as z ";
		$sqlstr = $sqlstr . " on dmovskuplan.SKUCode=z.SKUCode ";
		$sqlstr = $sqlstr . " inner join dstock on  dmovskuplan.TrgPartyCode=dstock.PartyCode and dmovskuplan.SKUCode=dstock.SKUCode ";
		$sqlstr = $sqlstr . " left join zdimskusale on  dmovskuplan.TrgPartyCode=zdimskusale.PartyCode and dmovskuplan.SKUCode=zdimskusale.SKUCode ";
		$sqlstr = $sqlstr . " left join bsku on dmovskuplan.SKUCode=bsku.SKUCode  ";
		$sqlstr = $sqlstr . "  where PlanType='自动补货' and MakeDate>='" . getInputValue("StartDate",'2016-01-01') . "' and MakeDate<='" . getInputValue("EndDate",'2016-05-11') . "'";
	
//	echo $sqlstr;
	
		$Model = new \Think\Model("","",getMyCon());
		$rs=$Model->query($sqlstr);
			
				$headArr = array(
				"rownum"=>"序号",
				"老供应商"=>"老供应商",
				"类型"=>"类型",
				"pmc"=>"PMC",
				"老工厂"=>"老工厂",
				"0001"=>"0001",
				"单据类型"=>"单据类型",
				"商品号"=>"商品号",
				"款杯色"=>"款杯色",
				"总仓在手"=>"总仓在手",
				"目标库存"=>"目标库存",
				"门店在手"=>"门店在手",
				"门店在途"=>"门店在途",
				"门店四周销售"=>"门店四周销售",
				"补货件数"=>"补货件数");
		
		if(hasInput("Excel"))
		{
			getExcel('补货计划.xlsx',$rs,$headArr,1);
			exit;
		}

		if(hasInput("CSV"))
		{
			getCSV('补货计划.csv',$rs,$headArr);
			exit;
		}
		
//		dump($rs);
		return $this -> ajaxReturn($rs);
	}
	
	//获得目标库存调整记录
	public function getPartyBMRecord() {
		if(hasInput('WHCode')) $condition['a.PartyCode'] = getInputValue("WHCode","ZZ27097");		
		if(hasInput('EndDate')) $condition['RecordDate'] = array("elt",getInputValue("EndDate","2014-05-02"));			
		if(hasInput('StartDate')) $condition['a.RecordDate'] = array("egt",getInputValue("StartDate","2014-04-02"));			
		
		$pagestr = getInputValue("Page","1,100");
		
		$fieldstr = "@x:=@x+1 as rownum,a._identify,a.partycode,PartyName,SKUCode,RecordDate,OldTargetQty,SugTargetQty,BMReason,operator";
		$fieldstr  = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
        $rs = M("dbmrecord as a","",getMyCon())
        ->join("left join bparty as p1 on a.partycode = p1.partycode")
//      ->join("left join bsku as p2 on dbmrecord.skucode = p2.skucode")
        ->field($fieldstr)
        ->page($pagestr)
		->order("a._Identify desc")
        ->where($condition)
        ->select();
		
//		echo M("dbmrecord as a","",getMyCon())->_sql();
		return $this -> ajaxReturn($rs);
	}
	
	//获得SKU调拨计划
    public function getMovSKUPlan(){
    		if(hasInput('PlanType'))  $condition['a.PlanType'] = array("like","%" . getInputValue("PlanType") . "%");			
    		if(hasInput('DealState'))  $condition['a.DealState'] = getInputValue("DealState");			
		
		if(hasInput('MakeDate'))  $condition['a.MakeDate'] = getInputValue("MakeDate");				
    		if(hasInput('StartDate'))  $condition['MakeDate'] = array( 'egt',getInputValue("StartDate"));			
    		if(hasInput('EndDate'))  $condition['a.MakeDate'] = array( 'elt',getInputValue("EndDate"));	
						
		if(hasInput('SrcPartyCode'))  $condition['a.SrcPartyCode'] = getInputValue('SrcPartyCode');			
		if(hasInput('TrgPartyCode'))  $condition['a.TrgPartyCode'] = getInputValue('TrgPartyCode');		
		
		$pagestr = "1,10000";
		
		$subsql = "select PartyCode from broleorg where RoleCode in (". getUserRoles(getInputValue("UserCode")) .") ";
		if(hasInput('BranchCode') && strpos(getInputValue("BranchCode"),'all')===false)  	
		{
			$subsql .= " and BranchCode in ('" . getInputValueArray("BranchCode") . "')";
		}
		$condition['_string'] = "a.SrcPartyCode in (" . $subsql . ") or a.TrgPartyCode in (" . $subsql . ")";

		
		$fieldstr = "@x:=@x+1 as rownum,a.SrcPartyCode,p1.PartyName as SrcPartyName,a.TrgPartyCode,p2.PartyName as TrgPartyName,MakeDate,Sum(MovQty) as MovQty";
		$fieldstr  = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
        $rs = M("dmovskuplan as a","",getMyCon())
        ->join("inner join bparty as p1 on a.SrcPartyCode = p1.PartyCode")
        ->join("inner join bparty as p2 on a.TrgPartyCode = p2.PartyCode")
        ->field($fieldstr)
        ->where($condition)
		->page($pagestr)
		->group("a.SrcPartyCode,p1.PartyName,a.TrgPartyCode,p2.PartyName,MakeDate")
        ->select();
		

//		p(M("dmovskuplan as a","",getMyCon())->_sql());
		return $this -> ajaxReturn($rs);
    }  	

//获得SKU调拨计划明细
    public function getMovSKUPlanItem(){
    		if(hasInput('PlanType'))  $condition['a.PlanType'] = array("like","%" . getInputValue("PlanType") . "%");					
    		if(hasInput('DealState'))  $condition['a.DealState'] = getInputValue("DealState");	
    		
    		if(hasInput('MakeDate'))  $condition['a.MakeDate'] = getInputValue("MakeDate");				
    		if(hasInput('StartDate'))  $condition['MakeDate'] = array( 'egt',getInputValue("StartDate"));			
    		if(hasInput('EndDate'))  $condition['a.MakeDate'] = array( 'elt',getInputValue("EndDate"));			
		if(hasInput('SrcPartyCode'))  $condition['a.SrcPartyCode'] = getInputValue("SrcPartyCode");			
		if(hasInput('TrgPartyCode'))  $condition['a.TrgPartyCode'] = getInputValue("TrgPartyCode");			
		if(hasInput('SKUCode'))  $condition['a.SKUCode'] = getInputValue("SKUCode");	
		
		$subsql = "select PartyCode from broleorg where RoleCode in (". getUserRoles(getInputValue("UserCode")) .") ";	
		if(hasInput('BranchCode') && strpos(getInputValue("BranchCode"),'all')===false)  		
		{
			$subsql .= " and BranchCode in ('" . getInputValueArray("BranchCode") . "')";
		}	
		$condition['_string'] = "a.SrcPartyCode in (" . $subsql . ") or a.TrgPartyCode in (" . $subsql .")";
		
//		echo $subsql;
		
		$pagestr = "1,1000000";
		
		$fieldstr = "@x:=@x+1 as rownum,a._Identify,a.SrcPartyCode,p1.PartyName as SrcPartyName,a.TrgPartyCode,p2.PartyName as TrgPartyName,MakeDate,";
		$fieldstr = $fieldstr . "a.SKUCode,SKCCode,StyleName,ColorName,SizeName,BrandName,YearName,SeasonName,SeasonStageName,";
		$fieldstr = $fieldstr . "MainTypeName,SeriesName,PlanType,MovQty,DealState";
		$fieldstr  = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
        $rs = M("dmovskuplan as a","",getMyCon())
        ->join("inner join bparty as p1 on a.SrcPartyCode = p1.PartyCode")
        ->join("inner join bparty as p2 on a.TrgPartyCode = p2.PartyCode")
        ->join("left join bsku as p3 on a.SKUCode = p3.SKUCode")
        ->field($fieldstr)
        ->where($condition)
		->page($pagestr)
        ->select();
		
//		dump($condition);
//		p(M("dmovskuplan as a","",getMyCon())->_sql());
		return $this -> ajaxReturn($rs);
    }  	
	
  //获得SKC调拨计划
    public function getMovSKCPlanItem(){
    		if(hasInput('PlanType'))  $condition['d1.PlanType'] = getInputValue("PlanType");			
    		if(hasInput('DealState'))  $condition['d1.DealState'] = getInputValue("DealState");			
    		if(hasInput('SKCCode'))  $condition['d1.SKCCode'] = getInputValue("SKCCode");			
		if(hasInput('SrcPartyCode'))  $condition['d1.SrcPartyCode'] = getInputValue("SrcPartyCode");			
		if(hasInput('TrgPartyCode'))  $condition['d1.TrgPartyCode'] = getInputValue("TrgPartyCode");			
	
		$subsql = "select PartyCode from broleorg where RoleCode in (". getUserRoles(getInputValue("UserCode")) .") ";
		if(hasInput('BranchCode') && strpos(getInputValue("BranchCode"),'all')===false)  
		{
			$subsql .= " and BranchCode in ('" . getInputValueArray("BranchCode") . "')";
		}
		$condition['_string'] = "d1.SrcPartyCode in (" . $subsql . ") or d1.TrgPartyCode in (" . $subsql . ")";
		
		$pagestr = "1,1000";
		
		$fieldstr = "@x:=@x+1 as rownum,d1._Identify,d1.SrcPartyCode,p1.PartyName as SrcPartyName,d1.TrgPartyCode,p2.PartyName as TrgPartyName,d1.SKCCode,StyleName,ColorName,";
		$fieldstr = $fieldstr . "BrandName,YearName,SeasonName,SeasonStageName,MainTypeName,SeriesName,MovQty,MakeDate,DealState";
		$fieldstr  = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
        $rs = M("dmovskcplan as d1","",getMyCon())
        ->join("left join bparty as p1 on d1.srcpartycode = p1.partycode")
        ->join("left join bparty as p2 on d1.trgpartycode = p2.partycode")
		->join("left join bskc as p3 on d1.SKCCode = p3.SKCCode")
        ->field($fieldstr)
        ->where($condition)
		->page($pagestr)
        ->select();
		
//		$rs= M("dmovskcplan as d1","",getMyCon())->_sql();
//		p($rs);
		return $this -> ajaxReturn($rs);
    }  		
    
    
	//拉式换款计划
    public function getRefrSKCPlan(){
    		if(hasInput('SKCCode'))  $condition['d1.SKCCode'] = getInputValue("SKCCode");		
		$condition['_string'] = "(d1.SrcPartyCode='". getInputValue("WHCode") . "' or d1.TrgPartyCode='". getInputValue("WHCode") ."') ";
		
		$pagestr = "1,1000";
		
		$fieldstr = "@x:=@x+1 as rownum,d1._Identify,d1.SrcPartyCode,p1.PartyName as SrcPartyName,d1.TrgPartyCode,p2.PartyName as TrgPartyName,d1.SKCCode,StyleName,ColorName,";
		$fieldstr = $fieldstr . "BrandName,YearName,SeasonName,SeasonStageName,MainTypeName,SeriesName,MovQty,MakeDate,DealState";
		$fieldstr  = getInputValue("FieldStr",$fieldstr);
		
		resetCounter();
        $rs = M("dmovskcplan as d1","",getMyCon())
        ->join("left join bparty as p1 on d1.srcpartycode = p1.partycode")
        ->join("left join bparty as p2 on d1.trgpartycode = p2.partycode")
		->join("left join bskc as p3 on d1.SKCCode = p3.SKCCode")
        ->field($fieldstr)
        ->where($condition)
		->page($pagestr)
        ->select();
		
//		$rs= M("dmovskcplan as d1","",getMyCon())->_sql();
//		p($rs);
		return $this -> ajaxReturn($rs);
    } 
    
	public function getSaleOrder(){

		resetCounter();
	
		$sqlstr = "select @x:=@x+1 as rownum, a1.PartyCode,bparty.PartyName,salebillcode,makedate,a1.SKUCode,SKCCode,SizeName,SaleQty,SaleMoney";
		$sqlstr = $sqlstr . " from bsale as a1 left join bparty on a1.PartyCode=bparty.PartyCode ";
		$sqlstr = $sqlstr . " left join bsku on a1.SKUCode=bsku.SKUCode ";
		$sqlstr = $sqlstr . " where MakeDate>='". getInputValue("StartDate") ."' and MakeDate<='". getInputValue("EndDate") ."' ";
		
		$existsqlstr = " and exists( select 1 from broleorg as r where r.PartyCode=a1.PartyCode and r.RoleCode in ( ". getUserRoles(getInputValue("UserCode")) .")";
		
		if(hasInput('StoreCode') && strpos(getInputValue("StoreCode"),'all')===false)  
		$existsqlstr .= " and a1.PartyCode ='". getInputValue("StoreCode") ."'";
		
		if(hasInput('BranchCode') && strpos(getInputValue("BranchCode"),'all')===false)  
		$existsqlstr .= " and r.BranchCode in ('" . getInputValueArray("BranchCode") . "')";
		
		$sqlstr .= $existsqlstr . ")";
//		echo $sqlstr;
//		die();
		
	  	$Model = new \Think\Model("","",getMyCon());
		$rs=$Model->query($sqlstr);
		return $this -> ajaxReturn($rs);
	}
}
?>
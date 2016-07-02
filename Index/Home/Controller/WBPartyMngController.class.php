<?php
namespace Home\Controller;

class WBPartyMngController extends \Think\Controller {

	
   /**
    * 获得仓库列表，用于修改其值
    */
	public function getPartyList(){
		$rs = M("bparty","",getMyCon())
		->page("1,10000")
		->select();
		
		return $this -> ajaxReturn($rs);
	}
 
    /**
    * 获得仓库列表，用于在下拉框中显示，也许不用这个了
    */
	public function getCWHList() {
		if(isset($_POST['MaintainerCode']))	$condition['MaintainerCode'] = $_POST['MaintainerCode'];	
		$condition['PartyType'] = '总仓';			
		$condition['PartyEnabled'] = 1;			

		$pagestr = getInputValue("Page","1,1000");
		$fieldstr  = getInputValue("FieldStr","PartyCode as id,PartyName as value");
		
		$rs = M("bparty","",getMyCon())
		->field($fieldstr)
		->page($pagestr)
		->where($condition)
		->select();

		return $this -> ajaxReturn($rs);
	}
	
	/**
    * 获得事业部列表，用于在下拉框中显示
    */
	public function getBizUnitList() {
	
		$pagestr = getInputValue("Page","1,1000");
		$fieldstr  = getInputValue("FieldStr","distinct BizUnitCode as id,BizUnitName as value");
		$condition['_string'] = " RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
		
		$rs = M("broleorg","",getMyCon())
		->field($fieldstr)
		->where($condition)
		->page($pagestr)
		->select();

		array_push($rs,array('id'=>'all','value'=>'所有'));
		$rs = array_reverse($rs);
		return $this -> ajaxReturn($rs);
	}

	/**
    * 获得办事处列表，用于在下拉框中显示
    */
	public function getBranchList() {

		if(hasInput("BizUnitCode") && getInputValue("BizUnitCode","all")!='all')	
     	$condition['BizUnitCode'] = array("exp"," in ('" .  str_replace(",","','",getInputValue("BizUnitCode")) ."')");
		
		$condition['_string'] = " RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
	
		$pagestr = getInputValue("Page","1,1000");
		$fieldstr  = getInputValue("FieldStr","distinct BranchCode as id,BranchName as value");
		
		$rs = M("broleorg","",getMyCon())
		->field($fieldstr)
		->page($pagestr)
		->where($condition)
		->select();

		array_push($rs,array('id'=>'all','value'=>'所有'));
		$rs = array_reverse($rs);
		
//		echo M("vwp2partyblgrel","",getMyCon())->_sql();
		return $this -> ajaxReturn($rs);
	}
	
	/**
    * 获得指定事业部或办事处下属的专柜列表，用于在下拉框中显示
    */
	public function getBlgRelPartyList() {
		$condition['_string'] = " RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
		
		if(hasInput("BizUnitCode") && getInputValue("BizUnitCode","all")!='all')	
     	$condition['BizUnitCode'] = array("exp"," in ('" .  str_replace(",","','",getInputValue("BizUnitCode")) ."')");
		
		if(hasInput("BranchCode") && getInputValue("BranchCode","all")!='all')	
     	$condition['BranchCode'] = array("exp"," in ('" .  str_replace(",","','",getInputValue("BranchCode")) ."')");
			
			$dbt = M('broleorg','',getMyCon());
			
			$rs = $dbt
			->field("distinct partycode as id, partyname as value")
			->where($condition)
			->page("1,5000")
			->select();
		
			array_push($rs,array('id'=>'all','value'=>'所有'));
			$rs = array_reverse($rs);
		
//		echo M("vwp2partyblgrel","",getMyCon())->_sql();
		
			return $this -> ajaxReturn($rs);
					
		}

	
   /**
    * 获得组织树结构
    */
	public function getOrgTree(){

		//获得事业部列表
		$orgTree = M("vwp2partyblgrel","",getMyCon())
		->field("distinct BizUnitCode as id,BizUnitName as value,1 as Open,'OrgL0' as Level")
		->page("1,1000")
		->select();
		
		array_push($orgTree,array('id'=>'all','value'=>'(所有专柜)',"level"=>"OrgL0"));
		$orgTree = array_reverse($orgTree);
		
		//对每一个非“all”的事业部，获得其办事处列表
		for($i=1;$i<count($orgTree);$i++)
		{
//			$orgTree[$i]['Branch'] = array();
			$bizUnitCode = $orgTree[$i]["id"];
			
				$branches = M("vwp2partyblgrel","",getMyCon())
				->field("distinct BranchCode as id,BranchName as value,0 as Open,'OrgL1' as Level")
				->page("1,1000")
				->where(array("BizUnitCode"=>$bizUnitCode))
				->select();
		
				array_push($branches,array('id'=>'all-'. $bizUnitCode,'value'=>'(所有专柜)',"level"=>"OrgL1"));
				$branches = array_reverse($branches);
				$orgTree[$i]['data'] = $branches;
				
				//对每一个非“all”的办事处，获得其门店列表
				for($j=1;$j<count($branches);$j++)
				{
					$branchCode = $branches[$j]['id'];
					
					$stores = M("vwp2partyblgrel","",getMyCon())
					->field("distinct PartyCode as id,PartyName as value,'OrgL2' as Level")
					->page("1,1000")
					->where(array("BranchCode"=>$branchCode))
					->select();
				
					array_push($stores,array('id'=>'all-'. $branchCode,'value'=>'(所有专柜)',"level"=>"OrgL2"));
					$stores = array_reverse($stores);
					$orgTree[$i]['data'][$j]['data'] = $stores;
				}
			
		}
		
//		dump($orgTree);
//		die();
		return $this -> ajaxReturn($orgTree);
	}
	
		public function getPartyRelation(){
			$condition['a.PartyCode'] = getInputValue("PartyCode","D03A");
			$rs = M('bparty2partyrelation as a','',getMyCon())
			->join(" left join bparty as c on a.parentcode=c.partycode")
			->field("a._identify,a.parentcode,c.partyname as parentname,relationtype,relationorder")
			->where($condition)
			->select();

		
		return $this -> ajaxReturn($rs);
		}
		
		
		
		public function getRelPartyList() {

			if(getInputValue("regioncode","D03A")!='all') $condition['ParentCode'] = getInputValue("RegionCode","D03A");
			$condition['RelationType'] = getInputValue("RelationType","归属关系");
			$condition['IsReplenish']=1;
			
			$pagestr = getInputValue("Page","1,1000");

			$fieldstr = "@x:=@x+1 as rownum,_Identify,ParentCode,ParentName,RelationType,PartyCode,PartyName,";
			$fieldstr = $fieldstr . "PartyType,PartyLevel,PartyEnabled,RepBatchSize,RepNextDate,RepOrderCycle,";
			$fieldstr = $fieldstr . "RepSupplyTime,RepRollSpan,IsReturnStock,RetBatchSize,IsRetOverStock,";
			$fieldstr = $fieldstr . "RetOverStockNextDate,RetOverStockCycle,IsRetDeadStock,RetDeadStockNextDate,";
			$fieldstr = $fieldstr . "RetDeadStockCycle,IsBM,IsUseSKUBMPara,BMUpChkPeriod,BMUpFreezePeriod,";
			$fieldstr = $fieldstr . "BMUpErodeLmt,BMDnChkPeriod,BMDnFreezePeriod,BMDnErodeLmt";
		
			$fieldstr = getInputValue("FieldStr",$fieldstr);
			
			resetCounter();
			$dbt = M('vwp2partyallrel','',getMyCon());
			
			$rs = $dbt
			->field($fieldstr)
			->where($condition)
			->page($pagestr)
			->select();
		
		return $this -> ajaxReturn($rs);
	}

  
	 
}
?>
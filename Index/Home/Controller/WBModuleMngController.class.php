<?php
namespace Home\Controller;

class WBModuleMngController extends \Think\Controller {
	
	
	public function getModuleList(){
		$rs = M("bwbmodule","",getMyCon())
		->order("modulelevel asc")
		->select();
		
		return $this -> ajaxReturn($rs);
	}
	
	
	public function getSubModuleList(){
		$condition["ParentModuleID"] = getInputValue("ParentModuleID");
		
		$rs = M("bwbmodule","",getMyCon())
		->where($condition)
		->order("modulelevel asc")
		->select();
		return $this -> ajaxReturn($rs);
	}

	 public function getModuleTree(){
	 	$dbt =  M("bwbmodule","",getMyCon());
		
	 	$treeData =$dbt
		->field("moduleid as id,modulename as value,true as open,moduledesc as details")
		->where("ParentModuleID is null or trim(ParentModuleID)=''")
		->select();
		
		for ($x=0; $x<count($treeData); $x++) {
  		   $parentid = $treeData[$x]['id'];
			$levelTwo = $dbt
			->field("moduleid as id,modulename as value,moduleicon as icon, moduledesc as details")
			->where("ParentModuleID='" . $parentid . "'")
			->select();
			$treeData[$x]['data'] = $levelTwo;
		} 
	
		
		return $this -> ajaxReturn($treeData);
	 }
	 
	 public function getMyMenuTree(){
	 	 $usercode = getInputValue("UserCode","Admin");
		 
		$sqlstr = "select distinct a.UserCode,c.ParentModuleID,c.ParentModuleName,";
	    $sqlstr = $sqlstr . " b.ResourceCode as ModuleID,c.ModuleName,c.ModuleICON,c.ModuleDesc,c.ModuleLevel,";
	    $sqlstr = $sqlstr . " max(b.Operation) as Operation,max(b.Open) as Open";
	    $sqlstr = $sqlstr . " from broleuser as a inner join bprevilege as b on a.RoleCode = b.RoleCode";
	    $sqlstr = $sqlstr . " inner join vwmodule as c on b.ResourceCode = c.ModuleID and ResourceType='Module'";
	    $sqlstr = $sqlstr . " where UserCode='" . $usercode . "'";
		$sqlstr = $sqlstr . " group by a.UserCode,c.ParentModuleID,c.ParentModuleName,";
		$sqlstr = $sqlstr . " b.ResourceCode,c.ModuleName,c.ModuleICON,c.ModuleDesc,c.ModuleLevel";
		$sqlstr = $sqlstr . " order by c.ModuleLevel";

	    	$Model = new \Think\Model("","",getMyCon());
		$MyPrevilege=$Model->query($sqlstr);

//		dump($sqlstr);
//		die();

		$treeData=array();
		for ($x=0; $x<count($MyPrevilege); $x++) {
  		   $parentid = $MyPrevilege[$x]['parentmoduleid'];
			if(is_null($parentid) || trim($parentid)=='')
			{
				$levelOneNode = array(
				"id"=>$MyPrevilege[$x]["moduleid"],
				"value"=>$MyPrevilege[$x]["modulename"],
				"icon"=>$MyPrevilege[$x]["moduleicon"],
				"details"=>$MyPrevilege[$x]["moduledesc"],
				"open"=>($MyPrevilege[$x]["open"]==1? true:false));
				$levelOneNode['data'] = array();
				for ($y=0; $y<count($MyPrevilege); $y++) 
				{
					if($levelOneNode['id']==$MyPrevilege[$y]['parentmoduleid'])
					{
						$levelTwoNode = array(
						"id"=>$MyPrevilege[$y]["moduleid"],
						"value"=>$MyPrevilege[$y]["modulename"],
						"icon"=>$MyPrevilege[$y]["moduleicon"],
						"details"=>$MyPrevilege[$y]["moduledesc"]);
						array_push($levelOneNode['data'],$levelTwoNode);
					}
				}
				array_push($treeData,$levelOneNode);
			}
		} 
		
//		dump($treeData);
		return $this -> ajaxReturn($treeData);
	 }
	 
}
?>
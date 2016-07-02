<?php
namespace Home\Controller;

class WBRoleMngController extends \Think\Controller {
	 /**
	 * 获得所有角色
	 */
	 public function getRoleList()
	 {
			$rs = M('brole',"",getMyCon())->select();
		    return $this -> ajaxReturn($rs);
	 }


	/**
	 * 获得角色成员
	 */
	 public function getRoleUserList()
	 {
				$condition['a.RoleCode'] = getInputValue("RoleCode");
				$rs = M('broleuser as a',"",getMyCon())
				->join("left join buser on a.UserCode = buser.UserCode")
			 	->field("a._Identify,a.RoleCode,a.UserCode,UserTrueName,UserType")
				->where($condition)
			 	->select();
		return $this -> ajaxReturn($rs);
	 }
	 
	/**
	 * 获得角色的权限
	 */
	 public function getRolePrevilege()
	 {
			$condition['RoleCode'] = getInputValue("RoleCode");
			
			$fieldStr = "bprevilege._Identify,bprevilege.RoleCode,bprevilege.ResourceCode,";
			$fieldStr .= "ModuleName as ResourceName,ResourceType,ModuleLevel as ResourceLevel,ModuleDesc as ResourceDesc,";
			$fieldStr .= "ModuleIcon as ResourceIcon,Open,ParentModuleID as ParentResourceCode,";
			$fieldStr .= "ParentModuleName as ParentResourceName,Operation";
			
			$rs = M('bprevilege',"",getMyCon())	
			->join("left join vwmodule  on bprevilege.ResourceCode = vwmodule.ModuleID ")
			->field($fieldStr)
			->where($condition)	
			->order("ModuleLevel asc")
			->select();
			
//			dump($rs);
//			die();
			
		return $this -> ajaxReturn($rs);
	 }
	 
	 public function savePrevilege(){
		
		$Model = new \Think\Model("","",getMyCon());
		$sqlstring = " call _40_procUpdateRoleOrg;";
		$rs = $Model->execute($sqlstring);
		return $this -> ajaxReturn("OK");
	 }


}
?>
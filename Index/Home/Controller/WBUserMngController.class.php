<?php
namespace Home\Controller;

class WBUserMngController extends \Think\Controller {
	
	public function getUserList(){
		$rs = M("buser","",getMyCon())
		->page("1,10000")
		->select();
		
		return $this -> ajaxReturn($rs);
	}
	
	public function getUserTree(){
		$dbt =  M("buser","",getMyCon());
		$treeData=$dbt
		->field("usercode as id,usertruename as value,true as open")
		->select();	
		return $this -> ajaxReturn($treeData);
	}
	
	public function getUserRole(){
		$condition["UserCode"] = getInputValue("UserCode");
		
		$rs = M("broleuser","",getMyCon())
		->field("broleuser.RoleCode,RoleName,RoleType,RoleDesc")
		->join("left join brole on broleuser.RoleCode=brole.RoleCode")
		->page("1,10000")
		->where($condition)
		->select();
		
		return $this -> ajaxReturn($rs);
	}
	
		public function getRawUrl() {
	
		$dbm = M('bsyspara',"",getMyCon());
		$rs = $dbm -> where(array("Name"=> 'DSSuffix')) -> getField('vtext');
		
//		dump($dbm->_sql());
//		setTag('sql1', $dbm->_sql());
				
		return $this -> ajaxReturn($rs);
	}
		
		
	//获得user信息：基本资料，角色，相关人，事件
	public function getUserInfo() {
		$condition["UserCode"] = getInputValue("UserCode","Admin");
		$userObject['MyBasic'] = M('buser',"",getMyCon())->where($condition)->select();
		$userObject['MyRole'] = M('broleuser',"",getMyCon())->where($condition)->select();

		$sqlstr = "select distinct a.UserCode,b.RoleCode,c.ParentModuleID,c.ParentModuleName,";
	    $sqlstr = $sqlstr . " b.ResourceCode as ModuleID,c.ModuleName,c.ModuleICON,c.ModuleDesc,c.ModuleLevel,";
	    $sqlstr = $sqlstr . " max(b.Operation) as Operation,max(b.Open) as Open";
	    $sqlstr = $sqlstr . " from broleuser as a inner join bprevilege as b on a.RoleCode = b.RoleCode";
	    $sqlstr = $sqlstr . " inner join vwmodule as c on b.ResourceCode = c.ModuleID  and ResourceType='Module'";
	    $sqlstr = $sqlstr . " where UserCode='" . getInputValue('UserCode','Admin') . "'";
		$sqlstr = $sqlstr . " group by a.UserCode,b.RoleCode,c.ParentModuleID,c.ParentModuleName,";
		$sqlstr = $sqlstr . " b.ResourceCode,c.ModuleName,c.ModuleICON,c.ModuleDesc,c.ModuleLevel";

	    	$Model = new \Think\Model("","",getMyCon());
		$userObject['MyPrevilege']=$Model->query($sqlstr);
//		dump($userObject);
		return $this -> ajaxReturn($userObject);
	}
	
		public function checkUserPWD() {
//			return $this -> ajaxReturn('OK');
			
		$UserCode = getInputValue('UserCode');
		$PWD = getInputValue('PWD');
		
		$Model = new \Think\Model("","",getMyCon());
		$sqlstr = "select SHA('" . $PWD . "')=UserPassword as  cmprs from buser where LOWER(UserCode) ='" . $UserCode . "'";
		
		$rs = $Model -> query($sqlstr);

		$response = null;
		if (count($rs) > 0) {
			if ($rs[0]['cmprs'] == 1)
				$response = "OK";
		}
		return $this -> ajaxReturn($response);
	}

	public function reviseUserPWD() {
		$UserCode = $_POST['UserCode'];
		$OldPWD = $_POST['OldPWD'];
		$NewPWD = $_POST['NewPWD'];

		$Model = new \Think\Model("","",getMyCon());
		$sqlstr = "select SHA('" . $OldPWD . "')=UserPassword  cmprs from buser where UserCode ='" . $UserCode . "'";
//		echo $sqlstr;
		$rs = $Model -> query($sqlstr);
		
		$response = null;
		if (count($rs) > 0) {
			if ($rs[0]['cmprs'] == 1) {
				$sqlstr = "update buser Set UserPassword= SHA('" . $NewPWD . "')  where UserCode ='" . $UserCode . "'";
				$rs = $Model -> execute($sqlstr);
				$response = "OK";
			}
		}
		return $this -> ajaxReturn($response);
	}
	
	
		
	public function checkAuth(){
		$ModuleId = getInputValue("ModuleId");
		$UserCode = getInputValue("UserCode");
		$Model = new \Think\Model("","",getMyCon());
		$sqlstr = "select count(1) as RowCounter from bprevilege inner join broleuser on bprevilege.RoleCode = broleuser.RoleCode";
		$sqlstr = $sqlstr . " where UserCode='" . $UserCode . "' and ResourceCode='" . $ModuleId . "'  and ResourceType='Module' ";
		
		$rs = $Model -> query($sqlstr);
		if (count($rs) > 0) {
			return $this -> ajaxReturn(true);	
		}else{
			return $this -> ajaxReturn(false);	
		};
	}
	
	public function getMyCWH()
	{
		$UserCode = getInputValue("UserCode");
		$dbm = M('bparty',"",getMyCon());
		$rs = $dbm 
			-> where(array("PartyType"=> '总仓')) 
			-> field("PartyCode,PartyName")
			->select();
		return $this -> ajaxReturn($rs);
	}
	
}
?>
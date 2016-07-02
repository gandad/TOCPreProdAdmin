<?php
namespace Home\Controller;

class WBParaMngController extends \Think\Controller {

	/**
	 * 获得参数
	 */
	 public function getSysPara()
	 {
	 	//p(getMyCon());

	 	$rs = M('bsyspara',"",getMyCon())->select();
		return $this -> ajaxReturn($rs);
	 }
	 
	 public function getWBMenu()
	 {
//	 	$UserCode = I("UserCode");
//		$isAdmin = M("staffs","",getMyCon())->where(array("StaffCode"=>$UserCode))->getField("IsAdmin");
//		if($isAdmin)
//		{
			$WBMenuType = "wbmenu_admin";
//		}
//		else 
//		{
//
//				$WBMenuType = "wbmenu_staffs";
//		}

	 	$rs = M('bsyspara',"",getMyCon())->where(array("Name"=>$WBMenuType))->getField("VText");
		echo $rs;
	 }
	 

	 
	 	/**
	 * 获得参数
	 */
	 public function getSQLList()
	 {
	 	$rs = M('sqllist',"",getMyCon())->order("_identify")->select();
		return $this -> ajaxReturn($rs);
	 }
	
	
		 /**
	 * 获得参数
	 */
	 public function getDebugRecord()
	 {
	 	$rs = M('boperationrecord',"",getMyCon())->order("_identify desc")->limit(3000)->select();
		return $this -> ajaxReturn($rs);
	 }
	 
}
?>
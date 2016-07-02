<?php
namespace Home\Model;
use Think\Model\RelationModel;

class StaffObjectModel extends RelationModel{
			
	/*
	 * 查询获得一个或多个员工的信息
	 * */
		public function getStaffBasicInfo($condition,$fieldstr=null)
		{

			return $rs;
		}
	
		/*
	 * 查询获得一个的角色
	 * */
		public function getStaffRoles($StaffCode)
		{	
			$rs=M('deptstaffs',"",getMyCon())
			->distinct(true)
			->where(array('StaffCode'=>$StaffCode))
			->field('reltype as rolename')
			->select();

			return $rs;
		}
			
	/*
	 * 查询获得一个员工的相关部门
	 * */
//		$fieldstr=['staffcode','reltype','deptcode']; //必须选择关联的外键，否则关联项不显示
		public function getStaffRelDepts($condition,$fieldstr=null)
		{
			$dbtmodel = M('vw_deptrelpersons',"",getMyCon());			
			$rs = $dbtmodel 
			->field($fieldstr)
			-> where($condition) 
			-> select();
			//echo $this->_sql();
			//dump($rs);
			return $rs;
		}
	
	/*	
		 * 查询获得一个员工的模块
	 * */
//		$fieldstr=['staffcode','reltype','deptcode']; //必须选择关联的外键，否则关联项不显示
		public function getStaffOwnModules($condition,$fieldstr=null)
		{
			$dbtmodel = M('vw_deptrelpersons',"",getMyCon());			
			$rs = $dbtmodel 
			->field($fieldstr)
			-> where($condition) 
			-> select();
			//echo $this->_sql();
			//dump($rs);
			return $rs;
		}
}

?>

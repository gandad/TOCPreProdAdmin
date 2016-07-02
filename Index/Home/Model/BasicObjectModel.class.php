<?php
namespace Home\Model;
use Think\Model;

/**
 * 这是一个纯包装类,没有基表
 */
class BasicObjectModel extends Model {
	// 获得参数
	public function getSysPara() {
		$dbtmodel = M('bsyspara',"",getMyCon());
		$rs = $dbtmodel -> getField('name');
//		dump($rs);
		return $rs;
	}
	
	// 获得所有事件
	public function getEvents($condition) {
		$dbtmodel = M('events',"",getMyCon());
		$condition['Enabled'] = 1;
		$rs = $dbtmodel -> where($condition) -> select();
//		dump($rs);
		return $rs;
	}

	// 获得所有角色
	public function getRoles() {
		$dbtmodel = M('roles',"",getMyCon());
		$rs = $dbtmodel -> where('roleenabled=1') -> select();
		$rs = $dbtmodel -> select();
//		dump($rs);
		return $rs;
	}

	// 获得所有角色
	//$modeldb->getRoleStaffs('店长');
	public function getRoleStaffs($RoleName) {
		
		$condition['RelType'] = $RoleName;
		$condition['IsOnJob'] = 1;
		$condition['IsLocked'] = 0;
		
		$dbtmodel = M('deptstaffs',"",getMyCon());		
		$rs = $dbtmodel -> join('Staffs on deptstaffs.staffCode = staffs.StaffCode','LEFT')
		->field('deptstaffs.staffcode,staffname')
		->where($condition) -> select();
		
//		dump($rs);
		return $rs;

	}


	// 获得所有部门
	public function getDepts() {
		$dbtmodel = M('depts',"",getMyCon());
		$rs = $dbtmodel -> where('enabled=1') -> select();

		return $rs;

	}
	
		// 获得店铺所有成员[员工,店长,店助,经理等,人员有重复]
		//$dbtmodel->getDeptStaffs("店铺１")
	public function getDeptStaffs($deptcode) {
		$condition['DeptCode'] = $deptcode;
		$condition['IsOnJob'] = 1;
		$condition['IsLocked'] = 0;
		
		$dbtmodel = M('deptstaffs',"",getMyCon());
		$rs=$dbtmodel->join('Staffs on deptstaffs.staffCode = staffs.StaffCode','LEFT')
		->field('deptstaffs.staffcode,staffname,RelType')
		->where($condition) -> select();


		return $rs;
	}
	
	public function getDeptMembers($deptcode) {
		$condition['BelongDeptCode'] = $deptcode;
		$condition['IsOnJob'] = 1;
		$condition['IsLocked'] = 0;
		
		$dbtmodel = M('staffs',"",getMyCon());
		$rs=$dbtmodel
		->field('staffcode,staffname')
		->where($condition) 
		-> select();


		return $rs;
	}
		
			// 获得店铺所有成员[员工,店长,店助,经理等,人员有重复]
		//$dbtmodel->getDeptStaffs("店铺１")
	public function getDeptGoals($deptcode) {
		$condition['DeptCode'] = $deptcode;
		
		$dbtmodel = M('deptgoals',"",getMyCon());
		$rs=$dbtmodel
		->field('deptcode,yearmonth,totaltvaluetarget')
		->where($condition) 
		->order('yearmonth desc')
		->limit(6)
		-> select();
        
//		p($dbtmodel->_sql());

		return $rs;
	}
}

?>
	
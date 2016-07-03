<?php
namespace Home\Controller;

class WBProjectMngController extends \Think\Controller {

		/**
	    * 获得项目人员
	    */
		public function getProjectTypeList(){
			$rs = M("bproject","",getMyCon())
			->field("distinct ProjectType as id,ProjectType as value")
			->where("ProjectEnabled=1")
			->select();
			
			$rs = array_reverse($rs);
			array_push($rs,array('id'=>'all','value'=>'所有'));
			$rs = array_reverse($rs);
			
			return $this -> ajaxReturn($rs);
		}
		
	   /**
	    * 获得项目列表
	    */
		public function getProjectList(){
			if(hasInput("ProjectType")) $condition['ProjectType'] = getInputValue("ProjectType");
			if(hasInput("PathCode")) $condition['CreatePathCode'] = getInputValue("PathCode");
			
			$fieldstr = "a._Identify,ProjectEnabled,ProjectType,ProjectCode,ProjectName,";
			$fieldstr .= "createpathcode, pathname as createpathname,taskobjectcode,skccode,stylecode,orderqty,";
			$fieldstr .= "InitStartDate,InitDueDate,TOCStartDate,TOCDueDate";

			$rs = M("bproject as a","",getMyCon())
			->join("inner join bpath as b on createpathcode=pathcode")
			->join("inner join preprodorder as c on taskobjectcode=ordercode")
			->field($fieldstr)
			->where($condition)
			->select();
			
			return $this -> ajaxReturn($rs);
		}
		
		
	   /**
	    * 删除项目节点
	    */
		public function deleteProjectNode(){
			$condition['ProjectCode'] = getInputValue("ProjectCode");

			$rs = M("bprojectnode","",getMyCon())
			->where($condition)
			->delete();
			
			return $this -> ajaxReturn("OK");
		}
 
 	   /**
	    * 获得项目节点
	    */
		public function getProjectObj(){
			$condition['ProjectCode'] = getInputValue("ProjectCode","Prj1205S004");
			
			$projobj = array();
			
			$fieldstr = "ProjectCode,ProjectName,ProjectType,InitDueDate,TOCDueDate,TaskObjectCode,";
			$fieldstr .= "HeadNodeCode,TailNodeCode,SKCCode,OrderType";
			$projectinfo = M("bproject","",getMyCon())
			->join("left join preprodorder on ordercode=taskobjectcode")
			->field($fieldstr)
			->where($condition)
			->select();
			$projobj['projinfo'] = $projectinfo[0];
			
			$fieldstr = "projectcode,a.nodecode,nodename,nodename,nodetype,isnodeccr,isnodestatcs,";
			$fieldstr .= "statcsway,startcondition,belongdeptcode,";
			$fieldstr .= "prevnodecode,nextnodecode,netproctime,buffertime,";
			$fieldstr .= "accnetproctime,accbuffertime,stateupdatefreq,nodestate,nodeleadercode,usertruename as nodeleadername,";
			$fieldstr .= "nodetenseplanfinishdate,nodeuserplanfinishdate,nodeuseracturalfinishdate,";
			$fieldstr .= "nodetenseplanstartdate,nodeuseracturalstartdate";
			
			$nodelist = M("bprojectnode as a","",getMyCon())
			->join("left join bnode on a.nodecode=bnode.nodecode")
			->join("left join buser on a.nodeleadercode=buser.usercode")
			->field($fieldstr)
			->where($condition)
			->select();	
			
			$projobj['nodelist'] = $nodelist;
			
//			dump($projobj);
//			die();
				
			return $this -> ajaxReturn($projobj);
		}
  	 
	 
	  	/**
	    * 获得项目节点
	    */
		public function getProjectNode(){
			if(hasInput('DeptCode')) $condition['BelongDeptCode'] = getInputValue('DeptCode');
			if(hasInput('ProjectStaff')) $condition['NodeLeaderCode'] = getInputValue('ProjectStaff');
			if(hasInput('ProjectType')) $condition['ProjectType'] = getInputValue('ProjectType');
			if(hasInput('NodeState')) $condition['NodeState'] = getInputValue('NodeState');

			$fieldstr = "_Identify,BufferState,BelongDeptCode,BelongDeptName,NodeLeaderCode,";
			$fieldstr .= "NodeCode,NodeName,NodeLeaderName,ProjectCode,ProjectType,TaskObjectCode, ";
			$fieldstr .= "SKCCode,StyleCode,OrderType,OrderQty,NodeTensePlanFinishDate,NodeUserPlanFinishDate,NodeState";
			
			$rs = M("vwprojectnode","",getMyCon())
			->field($fieldstr)
			->where($condition)
			->select();	
		
//		    echo M("vwprojectnode","",getMyCon())->_sql();
			return $this -> ajaxReturn($rs);					
		}


	  	/**
	    * 获得部门节点统计数据
	    */
		public function getProjectNodeStatcs(){

			$fieldstr = "BelongDeptCode,BelongDeptName,count(1) as OrderCount,sum(OrderQty) as OrderQty,";
			$fieldstr .= "sum(if(NodeState='NotStart',1,0)) as NotStartCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState<0,1,0)) as BlackCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0 and BufferState<0.34,1,0)) as RedCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0.34 and BufferState<0.67  ,1,0)) as YellowCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0.67 and BufferState<1,1,0)) as GreenCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=1 ,1,0)) as BlueCount";
						
			$rs = M("vwprojectnode","",getMyCon())
			->field($fieldstr)
			->where("NodeState!='Finished'")
			->group("BelongDeptCode,BelongDeptName")
			->select();	
		
//		    echo M("vwprojectnode","",getMyCon())->_sql();
			return $this -> ajaxReturn($rs);					
		}		
		
	  	/**
	    * 获得员工节点统计数据
	    */
		public function getStaffNodeStatcs(){

			$fieldstr = "NodeLeaderCode,NodeLeaderName,count(1) as OrderCount,sum(OrderQty) as OrderQty,";
			$fieldstr .= "sum(if(NodeState='NotStart',1,0)) as NotStartCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState<0,1,0)) as BlackCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0 and BufferState<0.34,1,0)) as RedCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0.34 and BufferState<0.67  ,1,0)) as YellowCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0.67 and BufferState<1,1,0)) as GreenCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=1 ,1,0)) as BlueCount";
			
			$rs = M("vwprojectnode","",getMyCon())
			->field($fieldstr)
			->where("NodeState!='Finished'")
			->group("NodeLeaderCode,NodeLeaderName")
			->select();	
		
//		    echo M("vwprojectnode","",getMyCon())->_sql();
			return $this -> ajaxReturn($rs);					
		}		
		
	  	/**
	    * 获得红黑单统计数据
	    */
		public function getNodeRGBStatcs(){

			if(hasInput("DeptCode")) $condition['BelongDeptCode'] = getInputValue("DeptCode");
			if(hasInput("StaffCode")) $condition['NodeLeaderCode'] = getInputValue("StaffCode");
			
			$fieldstr = "NodeState,count(1) as OrderCount,sum(OrderQty) as OrderQty,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState<0,1,0)) as BlackCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0 and BufferState<0.34,1,0)) as RedCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0.34 and BufferState<0.67  ,1,0)) as YellowCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=0.67 and BufferState<1,1,0)) as GreenCount,";
			$fieldstr .= "sum(if(NodeState='InProcess' and BufferState>=1 ,1,0)) as BlueCount";
			
			$rs = M("vwprojectnode","",getMyCon())
			->field($fieldstr)
			->where($condition)
			->group("NodeState")
			->select();	
		
//		    echo M("vwprojectnode","",getMyCon())->_sql();
			return $this -> ajaxReturn($rs);					
		}	
}
?>
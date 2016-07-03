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
			if(hasInput("IsProjectFinished")) $condition['IsProjectFinished'] = getInputValue("IsProjectFinished");
			
			$fieldstr = "a._Identify,ProjectEnabled,ProjectType,ProjectCode,ProjectName,a.BufferState,a.BufferType,IsProjectFinished,";
			$fieldstr .= "createpathcode, pathname as createpathname,taskobjectcode,skccode,stylecode,orderqty,";
			$fieldstr .= "InitStartDate,InitDueDate,TOCStartDate,TOCDueDate,SKCCode,OrderQty";

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
			
			$fieldstr = "a._identify,projectcode,a.nodecode,nodename,nodename,nodetype,isnodeccr,isnodestatcs,";
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
			
			for($i=0;$i<count($nodelist);$i++)
			{
				$nodelist[$i]['nodeusedbuffer']	=null;
				$nodelist[$i]['accusedbuffer']	=null;

				if($nodelist[$i]['nodestate']=='Finished' && $nodelist[$i]['nodeuseracturalstartdate'] && $nodelist[$i]['nodeuseracturalfinishdate'])
				{
					$nodelist[$i]['nodeusedbuffer'] = $nodelist[$i]['nodeuseracturalfinishdate']-$nodelist[$i]['nodeuseracturalstartdate'];
					$nodelist[$i]['nodeusedbuffer'] -= $nodelist[$i]['netproctime'];
				}

				if($nodelist[$i]['nodestate']=='InProcess' && $nodelist[$i]['nodeuseracturalstartdate'] && $nodelist[$i]['nodeuseracturalfinishdate'])
				{
					$nodelist[$i]['nodeusedbuffer'] = $nodelist[$i]['nodeuserplanfinishdate']-$nodelist[$i]['nodeuseracturalstartdate'];
					$nodelist[$i]['nodeusedbuffer'] -= $nodelist[$i]['netproctime'];
				}				

								
				if($nodelist[$i]['nodestate']=='Finished' && $nodelist[$i]['nodeuseracturalfinishdate'])
				{
					$nodelist[$i]['accusedbuffer'] = $nodelist[$i]['nodeuseracturalfinishdate']-$nodelist[$i]['nodetenseplanfinishdate'];
				}				
					
				if($nodelist[$i]['nodestate']=='InProcess' && $nodelist[$i]['nodeuserplanfinishdate'])
				{
					$nodelist[$i]['accusedbuffer'] = $nodelist[$i]['nodeuserplanfinishdate']-$nodelist[$i]['nodetenseplanfinishdate'];
				}	
							
			}
			
			$projobj['nodelist'] = $nodelist;
					
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


	  	/**
	    * 更新项目的路径
	    */
		public function updateProjectPath(){
			$condition['ProjectCode'] = getInputValue("ProjectCode","Prj1205S044");
			
			$projectinfo = M("bproject","",getMyCon())
			->field("HeadNodeCode,InitDueDate")
			->where($condition)
			->select();	

			if(!$projectinfo || count($projectinfo)<1) return;
			
			$projectinfo = $projectinfo[0];
			
			$dbm = M("bprojectnode","",getMyCon());
			$nodelist = $dbm
			->where($condition)
			->select();	
			
			$curnode = null;
			for($i=0;$i<count($nodelist);$i++)
			{
				if($nodelist[$i]['nodecode']==$projectinfo['headnodecode'])  $curnode=$nodelist[$i];	
			}

			$accnetproctime=0;
			$accbuffertime=0;
			$nodeorder=0;
			$prevnodecode = null;
			
			$sortednodelist =[];
			while($curnode)
			{
				#-----------------------
				 $accnetproctime += (int)$curnode['netproctime'];
				 $accbuffertime += (int)$curnode['buffertime'];
				 
				 #-----------------------
				 $curnode['accnetproctime'] = $accnetproctime;
				 $curnode['accbuffertime'] = $accbuffertime;
				 $curnode['prevnodecode'] = $prevnodecode;
				 $curnode['nodeorder'] = $nodeorder++;
				 #-----------------------
				 
				$prevnodecode = $curnode['nodecode'];
				$sortednodelist[] = $curnode;
				
				//find the next node
				$nextnodecode = $curnode['nextnodecode'];
				$curnode = null;
				for($i=0;$i<count($nodelist);$i++)
				{
					if($nodelist[$i]['nodecode']==$nextnodecode)  $curnode=$nodelist[$i];	
				}
			}
				
				$Model = new \Think\Model("","",getMyCon());
				
				for($i=0;$i<count($sortednodelist);$i++)
				{
					$sortednodelist[$i]['nodetenseplanfinishdate']
					 = date("Y-m-d",strtotime($projectinfo['initduedate'])+24*3600*(1-$accbuffertime-($accnetproctime-(int)$sortednodelist[$i]['accnetproctime'])));

					 $sortednodelist[$i]['nodetenseplanstartdate'] 
					 =date("Y-m-d",strtotime($sortednodelist[$i]['nodetenseplanfinishdate'])+24*3600*(1-$sortednodelist[$i]['netproctime']));

					 $sortednodelist[$i]['nodeuserplanfinishdate'] = $sortednodelist[$i]['nodetenseplanfinishdate'];					 
           			 $sortednodelist[$i]['nodeuseracturalfinishdate'] = null;
           			 $sortednodelist[$i]['nodeuseracturalstartdate'] = null;
					 
					 $s = $sortednodelist[$i];
					 
					$sql = " Update bprojectnode set ";
					$sql .= " AccNetProcTime =" . $s['accnetproctime'] . ",";
					$sql .= " AccBufferTime =" . $s['accbuffertime'] . ",";
					$sql .= " PrevNodeCode ='" . $s['prevnodecode'] . "',";
					$sql .= " NodeOrder =" . $s['nodeorder'] . ",";
					$sql .= " NodeTensePlanFinishDate ='" . $s['nodetenseplanfinishdate'] . "',";
					$sql .= " NodeTensePlanStartDate ='" . $s['nodetenseplanstartdate'] . "',";
					$sql .= " NodeUserPlanFinishDate ='" . $s['nodeuserplanfinishdate'] . "',";
					$sql .= " NodeUserActuralFinishDate ='" . $s['nodeuseracturalfinishdate'] . "',";
					$sql .= " NodeUserActuralStartDate ='" . $s['nodeuseracturalstartdate'] . "'";
					$sql .= " where _Identify=" . $s['_identify'];
					
					$Model->execute($sql);
				}

				return $this -> ajaxReturn('OK');	
		}
	  	/**
	    * 更新项目缓冲状态
	    */
		public function updateProjBufferState(){

			$condition['ProjectCode'] = getInputValue("ProjectCode","Prj1205S004");
			$condition['NodeState'] = 'InProcess';

			$targetNode = M("bprojectnode","",getMyCon())
			->where($condition)
			->order("NodeOrder asc")
			->select();	
			
			
			if($targetNode && count($targetNode)>0)
			{
				$targetNode = $targetNode[0];
				$accbuffertime = (int)$targetNode['accbuffertime'];
				if($accbuffertime<1)
				{
					$bufferstate = 0;
				}
				else
				{
					$UserPlanFinishDate = date("Y-m-d",strtotime($targetNode['nodeuserplanfinishdate']));
					$TensePlanFinishDate = date("Y-m-d",strtotime($targetNode['nodetenseplanfinishdate']));
	
					if($UserPlanFinishDate<date("Y-m-d",time())) 
					{
						$UserPlanFinishDate = date('Y-m-d', time()+1*24*3600);
//						M("bprojectnode","",getMyCon())
//					    ->where(array('ProjectCode'=>$targetNode['projectcode'],"NodeCode"=>$targetNode['nodecode']))
//					    ->setField('NodeUserPlanFinishDate',$UserPlanFinishDate);	
					}
					
					$d1 = strtotime($TensePlanFinishDate);
					$d2 = strtotime($UserPlanFinishDate);
					$days = round(($d2-$d1)/3600/24);

					$bufferstate = 1.0*$days/$targetNode['accbuffertime'];
					
				}

			    M("bproject","",getMyCon())
			    ->where(array('ProjectCode'=>$targetNode['projectcode']))
			    ->setField('BufferState',$bufferstate);			
			}
				
			return $this -> ajaxReturn('OK');					
		}	
}
?>
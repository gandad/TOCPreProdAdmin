<?php
namespace Home\Controller;

class WBResourceMngController extends \Think\Controller {

	
	   /**
	    * 获得部门列表
	    */
		public function getDeptList(){
			$rs = M("bdept as a","",getMyCon())
			->join("left join buser on DeptMngerCode=UserCode")
			->field("a._Identify,DeptEnabled,DeptCode,DeptName,DeptType,DeptDesc,DeptMngerCode,UserTrueName as DeptMngerName")
			->select();
			
			return $this -> ajaxReturn($rs);
		}
 
  	   /**
	    * 获得部门列表
	    */
		public function getPopupDeptList(){
			$rs = M('bdept','',getMyCon())
			->field("DeptCode as id,DeptName")
			->where($condition)
			->select();
			
			$rs = array_reverse($rs);
			array_push($rs,array('id'=>'null','nodename'=>'清空'));
			$rs = array_reverse($rs);
		
			return $this -> ajaxReturn($rs);
		}	
		  
		/**
	    * 获得部门资源
	    */
		public function getDeptResource(){
			if(hasInput("DeptCode")) $condition['DeptCode'] = getInputValue("DeptCode","裁剪部");
			
			$rs = M('vwdeptresource','',getMyCon())
			->where($condition)
			->select();

			return $this -> ajaxReturn($rs);
		}

		/**
	    * 获得节点资源
	    */
		public function getNodeResource(){
			if(hasInput("NodeCode")) $condition['NodeCode'] = getInputValue("NodeCode");
			
			$rs = M('bnode','',getMyCon())
			->join("left join bdeptresource on BelongDeptCode=DeptCode")
			->join("left join buser on ResLeaderCode=UserCode")
			->field("NodeCode,NodeName,ResLeaderCode,UserTrueName as ResLeaderName")
			->where($condition)
			->select();

//echo M('bnode','',getMyCon())->_sql();
			return $this -> ajaxReturn($rs);
		}
				
	   /**
	    * 获得节点列表
	    */
		public function getNodeList(){
			if(hasInput("NodeType")) $condition['NodeType'] = getInputValue("NodeType","内部产前节点");
			$fieldstr = "bnode._Identify,NodeEnabled,NodeCode,NodeName,NodeType,NodeDesc,NodeOrder,IsNodeCCR,IsNodeStatcs,StatcsCondition,StatcsWay,";
			$fieldstr .= "NNNetProcTime,NNBufferTime,NNStateUpdateFreq,BelongDeptCode,DeptName as BelongDeptName";
			
			$rs = M('bnode','',getMyCon())
			->join("left join bdept on BelongDeptCode=DeptCode")
			->field($fieldstr)
			->where($condition)
			->select();

			return $this -> ajaxReturn($rs);
		}		


	   /**
	    * 获得节点列表
	    */
		public function getPopupNodeList(){
			if(hasInput("NodeType")) $condition['NodeType'] = getInputValue("NodeType","内部产前节点");
			
			$rs = M('bnode','',getMyCon())
			->field("NodeCode as id,NodeName,NodeType,NodeDesc,NNNetProcTime,NNBufferTime,NNStateUpdateFreq")
			->where($condition)
			->select();
			
			$rs = array_reverse($rs);
			array_push($rs,array('id'=>'null','nodename'=>'清空'));
			$rs = array_reverse($rs);
		
			return $this -> ajaxReturn($rs);
		}		
	   /**
	    * 获得节点列表
	    */
		public function getPathList(){
			if(hasInput("PathCode")) $condition['PathCode'] = getInputValue("PathCode");
			$fieldstr = "a._Identify,PathCode,PathName,PathEnabled,PathDesc,a.Remark,LeadTime,BufferType,RedLineLength,CCRCode,";
			$fieldstr .= "HeadNodeCode,b.NodeName as HeadNodeName,TailNodeCode, c.NodeName as TailNodeName";
			
			$rs = M('bpath as a','',getMyCon())
			->join("left join bnode as b on b.NodeCode=HeadNodeCode")
			->join("left join bnode as c on c.NodeCode=TailNodeCode")
			->field($fieldstr)
			->where($condition)
			->select();

			return $this -> ajaxReturn($rs);
		}				
		
		   /**
	    * 获得节点列表
	    */
		public function getPathNode(){
			if(hasInput("PathCode")) $condition['a.PathCode'] = getInputValue("PathCode","仅绣花");
			$fieldstr = "a._Identify,a.PathCode,PathName,PathEnabled,PathDesc,HeadNodeCode,TailNodeCode,";
			$fieldstr .= "Remark,LeadTime,BufferType,RedLineLength,CCRCode,";
			$fieldstr .= "a.NodeCode,NodeName,NodeType,NodeDesc,NodeOrder,IsNodeCCR,IsNodeStatcs,StatcsCondition,";
			$fieldstr .= "PNPrevNodeCode,PNNextNodeCode,PNNetProcTime,PNBufferTime,PNNodeLeaderCode,UserTrueName as PNNodeLeaderName,";
			$fieldstr .= "PNAccNetProcTime,PNAccBufferTime,PNStateUpdateFreq,NodeOrder";
			
			$rs = M('bpathnode as a','',getMyCon())
			->join("left join bpath on a.PathCode=bpath.PathCode")
			->join("left join bnode on a.NodeCode=bnode.NodeCode")
			->join("left join buser on a.PNNodeLeaderCode=buser.UserCode")
			->field($fieldstr)
			->where($condition)
			->order("NodeOrder asc")
			->select();

			return $this -> ajaxReturn($rs);
		}		
		
	public function getNodeTree(){
	 	$dbt =  M("bnode","",getMyCon());
		
//	 	$treeData =$dbt
//		->field("distinct NodeType as Id,NodeType as Value, true as Open,'' as Details")
//		->where("NodeEnabled=1")
//		->select();
		$treeData = M('bsyspara','',getMyCon())
		->field("Name as id,VString as value, true as Open")
		->where(array('Desc'=>'NodeType','VBool'=>1))
		->order('_Identify asc')
		->select();
		
		
		for ($x=0; $x<count($treeData); $x++) {
  		   $parentid = $treeData[$x]['id'];
			$levelTwo = $dbt
			->field("NodeCode as id,NodeName as value,NodeDesc as details,NNNetProcTime,NNBufferTime,NNStateUpdateFreq")
			->where("NodeType='" . $parentid . "' and NodeEnabled=1")
			->select();
			$treeData[$x]['data'] = $levelTwo;
		} 
		
		return $this -> ajaxReturn($treeData);
	 }
	 
	 public function getNodeType(){
	 	$rs = M('bsyspara','',getMyCon())
		->field("Name as id,VString as value")
		->where(array('Desc'=>'NodeType','VBool'=>1))
		->order('_Identify asc')
		->select();
	 	
	 	return $this -> ajaxReturn($rs);
	 }
}
?>
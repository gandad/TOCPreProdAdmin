<?php
namespace Home\Controller;

class WBCURDMngController extends \Think\Controller {

	/**
	 * 获得操作表
	 */
	 private function _CURDOperation($tableName,$POSTData,$attArray,$uniqArray)
	 {

	 	$dbm = M(strtolower($tableName),"",getMyCon()); 
		
	 	switch ($POSTData['webix_operation']) {
			
				case 'insert':				
				
				if($uniqArray && (count($uniqArray)>0))	
				{
					foreach($uniqArray as $uniqatt)  $condition[$uniqatt] = $POSTData[strtolower($uniqatt)];	
					$rs=$dbm->where($condition)->select();
//					setTag('sssql', $dbm->_sql());
					if(count($rs)>0) return "duplicate record";
				}
				
				foreach($attArray as $att) 
				{
					if(isset($POSTData[strtolower($att)])) $data[$att] = $POSTData[strtolower($att)];
				}	

				$recordid = $dbm -> add($data);
				
//				dump($dbm ->_sql());			
				if($recordid<1) return 'fail';
				
				return $recordid;
				break;
				
			case 'update':
				
				foreach($attArray as $att) 
				{
					if(isset($POSTData[strtolower($att)])) $data[$att] = $POSTData[strtolower($att)];
				}	
//				dump($data);
//				die();		
				$saveresult = $dbm->where('_Identify=' . $POSTData['_identify']) ->save($data);
				
				if($saveresult===false) return "fail";//$saveresult为更新的记录条数，$saveresult=0表示更新数据与原数据没变化
				
				return "success";
				break;
				
			case 'delete':
				
				$saveresult = $dbm->where('_Identify=' . $POSTData['_identify']) ->delete();
				if($saveresult===false) return "fail";//$saveresult为更新的记录条数，$saveresult=0表示更新数据与原数据没变化
				
				return "success";
			break;
				
			default:
				
				break;
		}
	 }

 	  /**
	  * ***************操作debug***************************
	  */
	  public function saveDebugRecord()
	  {
	  	if(stripos("delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
//	  	if(stripos("insert|update",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
		
	  	$tableName = "boperationrecord";
		$attArray = array('ModuleName','RecordLabel');
		$uniqArray = nulll;
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	 /**
	  * ***************操作参数表***************************
	  */
	  public function saveParameter()
	  {
	  	if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
		
	  	$tableName = "bsyspara";
		$attArray = array('Name','Type','VInteger','VFloat','VDate','VBool','VString','VText','Desc');
		$uniqArray = array("Name");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	  
	  
	/**
	  * ***************操作角色表***************************
	  */  
	  	public function saveRole()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "brole";
		$attArray = array('RoleCode','RoleName','RoleEnabled','RoleType','RoleDesc');
		$uniqArray = array("RoleCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqAtt);
				
		return $this -> ajaxReturn($status);
	  }
	  
	  	
	  /**
	  * ***************操作角色用户***************************
	  */ 
	  	 public function saveRoleUser()
	  {
	    if(stripos("insert|delete|update",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "broleuser";
		$attArray = array('RoleCode','UserCode');
		$uniqArray = array('RoleCode','UserCode');
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	
	  /**
	  * ***************操作权限表***************************
	  */ 	  
	   public function savePrevilege()
	  {
	    if(stripos("insert|delete|update",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bprevilege";
		$attArray = array("RoleCode","ResourceCode","Open","Operation","ResourceType");
		$uniqArray = array("RoleCode","ResourceCode");
		
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	
	  /**
	  * ***************操作用户表***************************
	  */ 
	  public function saveUser()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "buser";
		$attArray = array("UserCode","UserTrueName","UserType","UserEnabled");
		$uniqArray = array("UserCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
		
		if($_POST['webix_operation']=='insert' || I("isresetpwd"))
		{
			$initPWD = 'embry123';
			$Model = new \Think\Model('','',getMyCon());
			$sqlstr = "update buser Set UserPassword= SHA('" . $initPWD . "')  where UserCode ='" . I('usercode') . "'";
			$rs = $Model -> execute($sqlstr);
			$status = "OK";
		}
				
		return $this -> ajaxReturn($status);
	  }
	  
	  
	   /**
	  * ***************操作仓库表***************************
	  */ 
	  public function saveParty()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bparty";
		$attArray = array("PartyCode","PartyName","PartyType","PartyLevel","PartyEnabled","IsReplenish","RepPriority",
						"RepBatchSize","RepSchedule","RepNextDate","RepOrderCycle","RepSupplyTime","RepRollSpan","IsReturnStock",
						"RetBatchSize","IsRetOverStock","RetOverStockNextDate","RetOverStockCycle","IsRetDeadStock",
						"RetDeadStockNextDate","RetDeadStockCycle","IsBM","IsUseSKUBMPara","BMUpChkPeriod",
						"BMUpFreezePeriod","BMUpErodeLmt","BMDnChkPeriod","BMDnFreezePeriod","BMDnErodeLmt");
		$uniqArray = array("PartyCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	 
	 
	   /**
	  * ***************操作部门表***************************
	  */ 
	  public function saveDept()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bdept";
		$attArray = array("DeptEnabled","DeptCode","DeptName","DeptType","DeptDesc","DeptMngerCode");
		$uniqArray = array("DeptCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	   /**
	  * ***************操作部门资源表***************************
	  */ 
	  public function saveDeptResource()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bdeptresource";
		$attArray = array("ResEnabled","ResCode","ResName","ResCapacity","ResLeaderCode","DeptCode");
		$uniqArray = array("DeptCode","ResCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }	  
	      /**
	  * ***************操作模块表***************************
	  */ 
	  public function saveModule()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bwbmodule";
		$attArray = array("ParentModuleID","ModuleID","ModuleName","ModuleICON","ModuleDesc","ModuleLevel");
		$uniqArray = array("ModuleID");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	    /**
	  * ***************操作路径 :Path***************************
	  */ 
	  public function savePath()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bpath";
		$attArray = array("PathCode","PathName","PathEnabled","PathDesc","Remark",
		"LeadTime","BufferType","RedLineLength","CCRCode","HeadNodeCode","TailNodeCode");
		
		$uniqArray = array("PathCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	  /**
	  * ***************操作Node***************************
	  */ 
	  public function saveNode()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bnode";
		$attArray = array("NodeCode","NodeEnabled","NodeName","NodeDesc","NodeType","NodeOrder",
		"IsNodeCCR","IsNodeStatcs","StartCondtition","NNNetProcTime","NNBufferTime","NNStateUpdateFreq","StatcsWay","BelongDeptCode");
		$uniqArray = array("NodeCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  
	  /**
	  * ***************操作PathNode***************************
	  */ 
	  public function savePathNode()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bpathnode";
		$attArray = array("PathCode","NodeCode","PNPrevNodeCode","PNNextNodeCode",
		"PNNodeLeaderCode","PNNetProcTime","PNBufferTime","PNStateUpdateFreq");
		$uniqArray = array("PathCode","NodeCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }	  
	  
	   /* ***************操作PreProdOrder ***************************
	  */ 
	    public function savePreProdOrder()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "preprodorder";
		$attArray = array("OrderCode","SKCCode","OrderType","OrderQty","BufferState","DueDate","OrderRemark",
						"SG1Qty","SG2Qty","SG3Qty","SG4Qty","SG5Qty","SG6Qty","SG7Qty","SG8Qty",
						"SG9Qty","SG10Qty","SG11Qty","SG12Qty","SG13Qty","SG14Qty","SG15Qty");
		$uniqArray = array("OrderCode");
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }
	  

	  
	   /* ***************操作SKC***************************
	  */ 
	    public function saveSKC()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bskc";
		$attArray = array("IsStopProduce","IsStopReplenish","IsStopAnalyze","SKCCode","SKCName","StyleCode",
		"StyleName","ColorCode","ColorName","OtherSKUCompCode","OtherSKUCompName","BrandCode","BrandName",
		"SeriesCode","SeriesName","YearCode","YearName","SeasonCode","SeasonName","SeasonStageCode",
		"SeasonStageName","MainTypeCode","MainTypeName","SubTypeCode","SubTypeName"
		);
		$uniqArray = array("SKUCode");
		
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  }  	 
	  
	  /* ***************操作Project***************************
	  */ 
	    public function saveProject()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bproject";
		$attArray = array("ProjectCode","ProjectName","ProjectType","CreatePathCode","TaskObjectCode","BufferType",
		"InitDueDate","TOCDueDate","InitStartDate","TOCEndDate","ProjectEnabled","HeadNodeCode","TailNodeCode");
		$uniqArray = array("ProjectCode");
		
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  } 
	  
	  /* ***************操作ProjectNode***************************
	  */ 
	    public function saveProjectNode()
	  {
	    if(stripos("insert|update|delete",$_POST['webix_operation'])===false)  return $this -> ajaxReturn("not permit");
	  		  	
	  	$tableName = "bprojectnode";
		$attArray = array("ProjectCode","NodeCode","PrevNodeCode","NextNodeCode","NetProcTime","BufferTime",
								"AccNetProcTime","AccBufferTime","StateUpdateFreq","NodeState",
								"NodeLeaderCode","NodeTensePlanFinishDate","NodeUserPlanFinishDate",
								"NodeUserActuralFinishDate","NodeTensePlanStartDate","NodeUserActuralStartDate");
		$uniqArray = array("ProjectCode","NodeCode");
		
		$status = $this->_CURDOperation($tableName,$_POST,$attArray,$uniqArray);
				
		return $this -> ajaxReturn($status);
	  } 
}
?>
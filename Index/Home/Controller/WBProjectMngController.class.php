<?php
namespace Home\Controller;

class WBProjectMngController extends \Think\Controller {

	
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
 
  	 
}
?>
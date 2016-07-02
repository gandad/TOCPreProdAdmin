<?php
namespace Home\Controller;

class WBUpLoadFileController extends \Think\Controller {
	
	public function uploadPersonPhoto() {
		setTag('path',ABSPATH);
		$PictureOwner = $_GET["PictureOwner"];
		$DSSuffix = $_GET["DSSuffix"];
				
		$config = array(    
			'maxSize'    =>    31457280,    
			'savePath'   =>    '',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
		);

		$upload = new \Think\Upload($config);
	
		
		// 上传单个文件
		$info   =   $upload->upload();
		if(!$info)
		{// 上传错误提示错误信息       
		 	$this->error($upload->getError());    
			return $this -> ajaxReturn("{ status: 'error'}");
		 }else{// 上传成功        
				
			$fullsavename = null;
			    foreach($info as $file){
			    	$fullsavename = "./Uploads/".$file["savepath"].$file["savename"];
//					setTag('savepath', $fullsavename) ;
				}
//			$osspath = 	upload2OSS("eekavip",$PictureOwner,"D:/phpStudy4IIS/WWW/POAAdmin/" .$fullsavename);

			switch (strtolower($DSSuffix)) {
				case 'linesoul.com':
					  $bucket = 'linesoulperson';
					break;
				case 'eekabsc.com':
					  $bucket = 'eekaperson';
					break;
				default:
					  $bucket = 'eekaperson';
					break;
			}
			$osspath = 	upload2OSS($bucket,$PictureOwner,$fullsavename);

			//echo "{ status: 'server',fullsname:'". $osspath ."'}";
			echo "{ status: 'server',fullsname:'". $PictureOwner ."'}";
		 }
	}


	public function getFilePath() {
		$key = getInputValue('KeyId');
		$DSSuffix = getInputValue("DSSuffix");

			switch (strtolower($DSSuffix)) {
				case 'linesoul.com':
					  $bucket = 'linesoulperson';
					break;
				case 'eekabsc.com':
					  $bucket = 'eekaperson';
					break;
				default:
					  $bucket = 'eekaperson';
					break;
			}
		
		$response = getOSSFilePath($bucket,$key);
			
		echo $response;
	}
	
	public function importExcel2DB() {
		ini_set('memory_limit', '-1');
		set_time_limit(1200);
		
		$config = array(    
			'maxSize'    =>    314572800, //300M
			'savePath'   =>    '',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('xls','xlsx'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
		);

		$urlstr = $_SERVER['PHP_SELF'];
		$userCode = trim(substr(strrchr($urlstr,"/"),1));	
		
		$urlstr = str_replace("/UserCode/" . $userCode,"",$urlstr);
		$tableName = substr(strrchr($urlstr,"/"),1);
		
		$upload = new \Think\Upload($config);
		
		// 上传单个文件
		$info   =   $upload->upload();
		if(!$info)
		{// 上传错误提示错误信息       
		 	$this->error($upload->getError());    
			return $this -> ajaxReturn("{ status: 'error'}");
		 }else{// 上传成功        
				
		$fullsavename = null;
		  foreach($info as $file){
		   	$fullSaveName = ABS_PATH .  "/Uploads/".$file["savepath"].$file["savename"];
		}
        
		switch (strtolower($tableName))
		{
		 case "importstock":
		  $fieldArray = array("客户号","商品号","可用库存","在途库存");
		  break;
		  
		 case "importsale":
		  $fieldArray = array("客户号","商品号","单据日期","销售数量","实售金额");
		  break;
		  
		 case "importsku":
		  $fieldArray = array("商品号","款号","系列","杯","色","码组","码名","物料组名称","上市日期","吊牌价","安中零售价","商品级别","价格类别");
		  break;
		  
		 case "importparty":
		  $fieldArray = array("事业部编号","事业部名称","办事处编号","办事处名称","客户号","客户名称","等级","补货频率");
		  break;
		  
		 case "importtargeth":
		  $fieldArray = array("客户号","款号","色","杯","65/S","70/M","75/L","80/XL",
		  "85/2XL","90/3XL","95/4XL","100/FREE","105/XS");
		  break;
		  
		  case "importtargetv":
		  $fieldArray = array("客户号","商品号","目标库存");
		  break;
		}
//	
//		dump($tableName);
//		dump($fullSaveName);
//		dump($fieldArray);
//		dump($userCode);
		
		$calcObj = importExcel2DBByPHPExcel(getMyCon(),$tableName,$fullSaveName,$fieldArray,$userCode);	

		unset($info);
		unset($fieldArray);
		unset($config);
		
		return $this -> ajaxReturn("{ status: 'server',LoadDuration:'". $calcObj['loadDuration'] ."',SQLDuration:" . $calcObj['sqlDuration'] .",RecordNum:". $calcObj['recordNum'] ."}");
		}	
	}

	public function importCSV2DB() {
		ini_set('memory_limit', '-1');
		set_time_limit(1200);
		
		$config = array(    
			'maxSize'    =>    314572800, //300M
			'savePath'   =>    '',    
			'saveName'   =>    array('uniqid',''),    
			'exts'       =>    array('csv'),    
			'autoSub'    =>    true,    
			'subName'    =>    array('date','Ymd'),
		);

//		$tableName = substr(strrchr($_SERVER['PHP_SELF'],"/"),1);	
		$urlstr = $_SERVER['PHP_SELF'];
		$userCode = trim(substr(strrchr($urlstr,"/"),1));	
		
		$urlstr = str_replace("/UserCode/" . $userCode,"",$urlstr);
		$tableName = substr(strrchr($urlstr,"/"),1);
				
		$upload = new \Think\Upload($config);
		
		// 上传单个文件
		$info   =   $upload->upload();
		if(!$info)
		{// 上传错误提示错误信息       
		 	$this->error($upload->getError());    
			return $this -> ajaxReturn("{ status: 'error'}");
		 }else{// 上传成功        
				
		$fullsavename = null;
		  foreach($info as $file){
		   	$fullSaveName = ABS_PATH .  "/Uploads/".$file["savepath"].$file["savename"];
		}
        
		$sqlstr = "LOAD DATA INFILE '" . $fullSaveName . "'"; 
		$sqlstr = $sqlstr . " INTO TABLE " . $tableName;
		$sqlstr = $sqlstr . " FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' ;";
		$sqlstr = $sqlstr . " update " . $tableName . " set UserCode='" . $userCode . "' where UserCode is null;";
//		echo $sqlstr;
		
		$Model = new \Think\Model("","",getMyCon());
		$Model->execute($sqlstr);
		
		return $this -> ajaxReturn("{ status: 'server'}");
		}	
	}

	public function getImportData(){
		$TargetTable = getInputValue('TargetTable');
		$pageStr = getInputValue("Page","1,1000");
		
		$rs = M($TargetTable,"",getMyCon())
			->where(array("UserCode"=>trim(getInputValue('UserCode'))))
			->page($pageStr)
			->select();
		return $this -> ajaxReturn($rs);
	}
	
	public function clearImportData(){
		$TargetTable = getInputValue('TargetTable');
				
		$Model = new \Think\Model("","",getMyCon());
		$sqlstring = " delete from " . $TargetTable . " where UserCode='" . trim(getInputValue('UserCode')) . "';";
		$rs = $Model->execute($sqlstring);
		return $this -> ajaxReturn($rs);
	}
	
	public function saveImportData(){
		
		if(getInputValue("TargetTable")=='importsku') $sqlstr = "call _91_procSynImportSKU2SKU('". trim(getInputValue('UserCode')) ."');";
	 	if(getInputValue("TargetTable")=='importparty') $sqlstr = "call _91_procSynImportParty2Party('". trim(getInputValue('UserCode')) ."');";
	 	if(getInputValue("TargetTable")=='importstock') $sqlstr = "call _92_procSynImportTargetV2Stock('". trim(getInputValue('UserCode')) ."');";
//		echo $sqlstr;
		$Model = new \Think\Model("","",getMyCon());
		$Model->execute($sqlstr);
		return $this -> ajaxReturn("OK");
	}
}
?>
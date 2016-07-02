<?php
namespace Home\Controller;

class WBStockMngController extends \Think\Controller {
	/**
	 * 同步期初库存或人工导入的目标库存到库存表
	 */
	 public function synTarget2Stock(){
	 	if(getInputValue("TargetSource")=='Init') $sqlstr = "call _92_procSynInitTarget2Stock;";
	 	if(getInputValue("TargetSource")=='importtargeth') $sqlstr = "call _92_procSynImportTargetH2Stock('". trim(getInputValue('UserCode')) ."');";
	 	if(getInputValue("TargetSource")=='importtargetv') $sqlstr = "call _92_procSynImportTargetV2Stock('". trim(getInputValue('UserCode')) ."');";
		
		$Model = new \Think\Model("","",getMyCon());
		$Model->execute($sqlstr);
		return $this -> ajaxReturn("OK");
	 }
	
	/**
	 * 获取期初库存
	 */
		public function getInitTarget()
		{
			ini_set('memory_limit', '-1');
			set_time_limit(1200);
		
			resetCounter();
			$sqlstr = "select @x:=@x+1 as rownum,PartyCode,PartyName,PartyLevel,StyleName,ColorName,PriceType,onshelfdays,MainTypeName,SubType1Code,seriesname,SKCCode,";
			
			for($i=1;$i<=9;$i++)
			{
				if(getInputValue('getStock',1)) $sqlstr = $sqlstr . " sum(if(SizeGroup='" . $i ."',SKUStockQty,null)) as Stock". $i .",";
			}
			
			for($i=1;$i<=9;$i++)
			{
				if(getInputValue('getSale',1)) $sqlstr = $sqlstr . " sum(if(SizeGroup='" . $i ."',SKUSaleQty,null)) as Sale". $i .",";
			}
			
			for($i=1;$i<=9;$i++)
			{
				if(getInputValue('getSugTarget',1)) $sqlstr = $sqlstr . " sum(if(SizeGroup='" . $i ."',SugTargetQty,null)) as SugTarget". $i .",";
			}
			
//			for($i=1;$i<=9;$i++)
//			{
//				if(getInputValue('getTarget',1)) $sqlstr = $sqlstr . " sum(if(SizeGroup='" . $i ."',TargetQty,null)) as Target". $i .",";
//			}
			
			$sqlstr = $sqlstr . " SKCSaleQty,SKCStockQty ";
			$sqlstr = $sqlstr . "  from binittarget where 1=1 ";

			$subsqlstr =  " select distinct PartyCode from broleorg  where RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
			if(hasInput("BizUnitCode")) $subsqlstr .= " and BizUnitCode in ('" . getInputValueArray('BizUnitCode') . "')";
			if(hasInput("BranchCode")) $subsqlstr .=   " and BranchCode in ('" . getInputValueArray('BranchCode') . "')";
			if(hasInput("WHCode")) $subsqlstr .=  " and PartyCode in ('" . getInputValueArray('WHCode') . "')";
	
			if(hasInput("WHCode") || hasInput("BranchCode") || hasInput("BizUnitCode")) 
			$sqlstr = $sqlstr . " and PartyCode in ( " . $subsqlstr . ")";
				
			$sqlstr = $sqlstr . " group by PartyCode,SKCCode";

//			echo $sqlstr;
//			die();
			
			$Model = new \Think\Model("","",getMyCon());
			$rs=$Model->query($sqlstr);
			
			/**
			 * 如果导要导出数据的话
			 */
				$arraySizeName = array("65/S","70/M","75/L","80/XL","85/2XL","90/3XL","95/4XL","100/FREE","105/XS");
				 $headArr = array(
				 "rownum"=>"序号",
				"partycode"=>"客户号",
				"partyname"=>"客户",
				"partylevel"=>"级别",
				"skccode"=>"款杯色",
				"pricetype"=>"价格类型",
				"onshelfdays"=>"上柜天数",
				"stylename"=>"款",
				"colorname"=>"色",
				"seriesname"=>"系列",
				"maintypename"=>"大类",
				"skcsaleqty"=>"款色销量",
				"skcstockqty"=>"款色库存");
				
				$headArr['当前库存']=array();
				for($i=1;$i<=count($arraySizeName);$i++)
				{
					$headArr['当前库存']["stock" . $i]= $arraySizeName[$i-1];
				}	
				
				$headArr['四周销量']=array();
				for($i=1;$i<=count($arraySizeName);$i++)
				{
					$headArr['四周销量']["sale" . $i] = $arraySizeName[$i-1];
				}
				
				$headArr['期初库存']=array();
				for($i=1;$i<=count($arraySizeName);$i++)
				{
					$headArr['期初库存']["sugtarget" . $i]=$arraySizeName[$i-1];
				}		
				
				$headArr['目标库存']=array();
				for($i=1;$i<=count($arraySizeName);$i++)
				{
					$headArr['目标库存']["target" . $i]=$arraySizeName[$i-1];
				}	
				
			if(hasInput('Excel'))
			{	
				getExcel('期初库存.xlsx',$rs,$headArr,2);
				exit;
			}
			
			if(hasInput('CSV'))
			{
				for($i=1;$i<=count($arraySizeName);$i++)
				{
					$headArr['当前库存']["stock" . $i]= '库存_' . $arraySizeName[$i-1];
					$headArr['四周销量']["sale" . $i] = '销售_' . $arraySizeName[$i-1];
					$headArr['期初库存']["sugtarget" . $i]='期初_' . $arraySizeName[$i-1];
					$headArr['目标库存']["target" . $i]='目标_' . $arraySizeName[$i-1];
				}	
				
				getCSV('期初库存.csv',$rs,$headArr);
				return;
			}

			unset($headArr);
			
			return $this -> ajaxReturn($rs);
		}

/**
 *获取期初库存的统计数据
 */
 		public function getInitTargetStatistics()
		{
			resetCounter();
			$sqlstr = "select @x:=@x+1 as rownum, RegionCode,PartyCode, PartyName,PartyLevel,sum(if(SKUStockQty>0,1,0)) as CurSKUNum ,";
			$sqlstr = $sqlstr . " sum(if(SugTargetQty>0,1,0)) as SugSKUNum,sum(IsKeySize) as KeySizeNum, ";
			$sqlstr = $sqlstr . " sum(SKUSaleQty) as SKUSaleQty,Sum(SugTargetQty) as SugTargetQty,";
			$sqlstr = $sqlstr . " sum(SKUStockQty) as CurStockQty,sum(if(SugTargetQTy>SKUStockQty,SugTargetQTy-SKUStockQty,0)) as ReplenishQty,";
			$sqlstr = $sqlstr . " sum(if(SugTargetQTy<SKUStockQty,-SugTargetQTy+SKUStockQty,0)) as ReturnQty,";
			$sqlstr = $sqlstr . " 1-Sum(SugTargetQty) /sum(SKUStockQty) as ReduceStockPer, 1-sum(if(SKUStockQty>0 and SugTargetQty>0,1,0))/sum(if(SugTargetQty>0,1,0))  as ShortRatio";
			$sqlstr = $sqlstr . " from binittarget where 1=1 ";

			$subsqlstr =  " select distinct PartyCode from broleorg  where RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
			if(hasInput("BizUnitCode")) $subsqlstr .= " and BizUnitCode in ('" . getInputValueArray('BizUnitCode') . "')";
			if(hasInput("BranchCode")) $subsqlstr .=   " and BranchCode in ('" . getInputValueArray('BranchCode') . "')";
			if(hasInput("WHCode")) $subsqlstr .=  " and PartyCode in ('" . getInputValueArray('WHCode') . "')";
			if(hasInput("WHCode") || hasInput("BranchCode") || hasInput("BizUnitCode")) 
			
			$sqlstr = $sqlstr . " and PartyCode in ( " . $subsqlstr . ")";
								
			$sqlstr = $sqlstr . " group by PartyCode";

			
			$Model = new \Think\Model("","",getMyCon());
			$rs=$Model->query($sqlstr);
			return $this -> ajaxReturn($rs);
		}
 

	/**
    * 获得门店、分仓和中央仓的目标库存及库存
    */
		public function getFGWHCrossTSInfo(){
		ini_set('memory_limit', '-1');
		set_time_limit(1200);
		
		$whcode = getInputValue("WHCode","JN66");
			
		$Model = new \Think\Model("","",getMyCon());
		
		resetCounter();
		$sqlstr = "SELECT @x:=@x+1 as rownum,PartyCode,SKCCode,colorname,brandname,PriceType,SaleType,yearname,maintypename,SeriesName,TO_DAYS(CurDate())-TO_DAYS(ifnull(max(dstock.OnShelfDate),'2016-01-01')) as OnShelfDays,";
		for($i=1;$i<=9;$i++)
		{
			if(getInputValue('getTarget',1)) $sqlstr = $sqlstr . " sum(if(bsize.SizeGroup='" . $i ."',TargetQty,null)) as Target". $i .",";
		}
		for($i=1;$i<=9;$i++)
		{
			if(getInputValue('getStock',1)) $sqlstr = $sqlstr . " sum(if(bsize.SizeGroup='" . $i ."',ifnull(OnHandQty,0)+ifnull(OnRoadQty,0),null)) as Stock". $i .",";
		}			
		$sqlstr = $sqlstr . " seasonname FROM dstock left join bsku on dstock.SKUCode = bsku.skucode";
		$sqlstr = $sqlstr . " left join bsize on bsize.SizeCode = bsku.SizeCode";
		$sqlstr = $sqlstr . " where 1=1 ";
//		$sqlstr = $sqlstr . " where PartyCode='" . $whcode . "'";
		
			
		$subsqlstr =  " select distinct PartyCode from broleorg  where RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
		if(hasInput("BizUnitCode")) $subsqlstr .= " and BizUnitCode in ('" . getInputValueArray('BizUnitCode') . "')";
		if(hasInput("BranchCode")) $subsqlstr .=   " and BranchCode in ('" . getInputValueArray('BranchCode') . "')";
		if(hasInput("WHCode")) $subsqlstr .=  " and PartyCode in ('" . getInputValueArray('WHCode') . "')";

		if(hasInput("WHCode") || hasInput("BranchCode") || hasInput("BizUnitCode")) 
		$sqlstr = $sqlstr . " and PartyCode in ( " . $subsqlstr . ")";
					
		$sqlstr = $sqlstr . " group by PartyCode,SKCCode limit 0,1000000 ";

//		echo $sqlstr;
//		die();
		$Model = new \Think\Model("","",getMyCon());
		$rs=$Model->query($sqlstr);
		
		/**
		 * 如果要导出数据的话
		 */	
			 $arraySizeName = array("65/S","70/M","75/L","80/XL","85/2XL","90/3XL","95/4XL","100/FREE","105/XS");
			 $headArr = array(
			 "rownum"=>"序号",
			"partycode"=>"客户号",
			"skccode"=>"款杯色",
			"colorname"=>"色",	
			"brandname"=>"品牌",
			"seriesname"=>"系列",
			"maintypename"=>"大类",
			"pricetype"=>"价格类型",
			"yearname"=>"年份",
			"seasonname"=>"季节",
			"saletype"=>"销售类别",
			"onshelfdays"=>"上柜天数"
			);
			
			$headArr['目标库存'] = array();
			for($i=1;$i<=count($arraySizeName);$i++)
			{
				$headArr['目标库存']["target" . $i]=$arraySizeName[$i-1];
			}	
			
			$headArr['当前库存'] = array();
			for($i=1;$i<=count($arraySizeName);$i++)
			{
				$headArr['当前库存']["stock" . $i]=$arraySizeName[$i-1];
			}	
			
			if(hasInput('Excel'))
			{		
				getExcel('目标库存.xlsx',$rs,$headArr,2);
				exit;
			}

		if(hasInput('CSV'))
			{
				for($i=1;$i<=count($arraySizeName);$i++)
				{
					$headArr['目标库存']["target" . $i]='目标_' . $arraySizeName[$i-1];
					$headArr['当前库存']["stock" . $i]= '库存_' . $arraySizeName[$i-1];
				}	
				
				getCSV('目标库存.csv',$rs,$headArr);
				exit;
			}
		
		
		return $this -> ajaxReturn($rs);
		}
		// 获得门店、分仓和中央仓的目标库存及库存
		public function getFGWHTSInfo(){
			ini_set('memory_limit', '-1');
			set_time_limit(1200);
			
		$Model = new \Think\Model("","",getMyCon());
		resetCounter();
		$sqlstr = "SELECT @x:=@x+1 as rownum,dstock._Identify,PartyCode,dstock.SKUCode,SKCCode,colorname,SizeName,brandname,PriceType,SaleType,yearname,";
		$sqlstr = $sqlstr . " maintypename,seriesname,TargetQty,ifnull(OnHandQty,0)+ifnull(OnRoadQty,0) as StockQty,";
		$sqlstr = $sqlstr . " (ifnull(TargetQty,0)-ifnull(OnHandQty,0)-ifnull(OnRoadQty,0)) as SugRepQty,";
		$sqlstr = $sqlstr . " (-ifnull(TargetQty,0)+ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)) as SugRetQty";
		$sqlstr = $sqlstr . " FROM dstock left join bsku on dstock.SKUCode = bsku.skucode";
		$sqlstr = $sqlstr . " where 1=1 ";
					
		$subsqlstr =  " select distinct PartyCode from broleorg  where RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
		if(hasInput("BizUnitCode")) $subsqlstr .= " and BizUnitCode in ('" . getInputValueArray('BizUnitCode') . "')";
		if(hasInput("BranchCode")) $subsqlstr .=   " and BranchCode in ('" . getInputValueArray('BranchCode') . "')";
		if(hasInput("WHCode")) $subsqlstr .=  " and PartyCode in ('" . getInputValueArray('WHCode') . "')";

		if(hasInput("WHCode") || hasInput("BranchCode") || hasInput("BizUnitCode")) 
		$sqlstr = $sqlstr . " and PartyCode in ( " . $subsqlstr . ")";
		
//		echo $sqlstr;
//		die();
		$rs=$Model->query($sqlstr);

			$headArr = array(
			"rownum"=>"序号",
			"partycode"=>"客户号",
			"skucode"=>"SKU",
			"skccode"=>"款杯色",
			"colorname"=>"色",	
			"sizename"=>"码",	
			"brandname"=>"品牌",
			"seriesname"=>"系列",
			"maintypename"=>"大类",
			"yearname"=>"年份",
			"pricetype"=>"价格类型",
			"saletype"=>"销售类别",
			"targetqty"=>"目标库存",
			"stockqty"=>"实际库存",
			);
			
		if(hasInput('CSV'))
		{
			getCSV('目标库存.csv',$rs,$headArr);
			exit;
		}
			
		return $this -> ajaxReturn($rs);
		}
		

		// 获得目标退货仓下属退货仓（门店或分仓）的目标库存及库存
		public function getRetTargetWHSubWHTSInfo(){
		$rettargetwhcode = getInputValue("RetTargetWHCode","D03A");
		if(hasInput('SKUCode'))  $skucodestr = " and SKUCode='" . getInputValue("SKUCode") ."' ";			
		if(hasInput('SKCCode'))  $skucodestr = " and SKUCode like '%".getInputValue("SKCCode")."%' ";	

		resetCounter();
		$sqlstr = "SELECT @x:=@x+1 as rownum,dstock._Identify,dstock.PartyCode,bparty.PartyName,SKUCode,";
		$sqlstr = $sqlstr . " TargetQty,ifnull(OnHandQty,0)+ifnull(OnRoadQty,0) as StockQty,";
		$sqlstr = $sqlstr . " (ifnull(TargetQty,0)-ifnull(OnHandQty,0)-ifnull(OnRoadQty,0)) as SugRepQty,";
		$sqlstr = $sqlstr . " (-ifnull(TargetQty,0)+ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)) as SugRetQty";
		$sqlstr = $sqlstr . " FROM dstock left join bparty on dstock.partycode = bparty.partycode";
		$sqlstr = $sqlstr . " left join bparty2partyrelation on dstock.partycode = bparty2partyrelation.partycode and RelationType='补货关系'";
		$sqlstr = $sqlstr . " where ParentCode='" . $rettargetwhcode . "' " . $skucodestr . " and (ifnull(TargetQty,0)+ifnull(OnHandQty,0)+ifnull(OnRoadQty,0))>0";
		$sqlstr = $sqlstr . " order by (-ifnull(TargetQty,0)+ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)) desc limit 0,10000";

//		$rs = $sqlstr;
//dump($sqlstr);


		$Model = new \Think\Model("","",getMyCon());
		$rs=$Model->query($sqlstr);

//		return $rs;
		return $this -> ajaxReturn($rs);
		}
			
	//获得产品的历史库存
	public function getSKUHSStock() {
		$condition['PartyCode'] = getInputValue("WHCode","D03A");			
		$condition['SKUCode'] = getInputValue("SKUCode","133680012016570");
		$fieldstr = "@x:=@x+1 as rownum,date_format(RecordDate,'%c/%d') as Date,TargetQty as GreenZone,round(2*TargetQty/3,1) as YellowZone,";
		$fieldstr = $fieldstr . " round(TargetQty/3,1) as RedZone,OnHandQty as HandQty";
		resetCounter();
		$rs['imgData'] = M("dhisstock","",getMyCon())
		->field($fieldstr)
		->limit(30)
		->where($condition)
		->select();

		$rs['yValueLimit']= M("dhisstock","",getMyCon())
		->field("max(if(TargetQty>OnHandQty,TargetQty,OnHandQty)) as YUpLimit")
		->where($condition)
		->select();
		
		return $this -> ajaxReturn($rs);
	}

	//显示门店的库存结构:此函数需要优化
	   public function getPartyIndex(){

			resetCounter();
			
			$sqlstr = "select @x:=@x+1 as rownum,parentcode,parentname,partycode,partyname,partytype,partylevel,PriceType,maintypename,";
			$sqlstr .=  " middlesizenum,shortmiddlesizenum,shortmiddlesizeratio,skcnum,deadskcnum,frskcnuminparent,frskcnuminparty,";
			$sqlstr .= " frskcratiopartycover,stocktargetqty,stockonhandqty,stockonroadqty,stocktotalqty,stockdeadqty,";
			$sqlstr .= " stockdayofinventory,stockstoredeadglobalhot,stockoverinstores,stockshortinstores,stockdailyidd,";
			$sqlstr .= " sale1qty,sale7qty,sale14qty,sale21qty,sale28qty,sale35qty,sale42qty,sale49qty,sale56qty,saletotalqty,";
			$sqlstr .= " salecompleteper,saledailytdd ";
			$sqlstr .= " from zdimparty where 1=1 ";
		
		
			$subsqlstr =  " select distinct PartyCode from broleorg  where RoleCode in (". getUserRoles(getInputValue("UserCode")) .")";
			if(hasInput("BizUnitCode")) $subsqlstr .= " and BizUnitCode in ('" . getInputValueArray('BizUnitCode') . "')";
			if(hasInput("BranchCode")) $subsqlstr .=   " and BranchCode in ('" . getInputValueArray('BranchCode') . "')";
			if(hasInput("WHCode")) $subsqlstr .=  " and PartyCode in ('" . getInputValueArray('WHCode') . "')";
			if(hasInput("WHCode") || hasInput("BranchCode") || hasInput("BizUnitCode")) 
			$sqlstr = $sqlstr . " and PartyCode in ( " . $subsqlstr . ")";
			
//			echo $sqlstr;
//			die();
			
			$Model = new \Think\Model("","",getMyCon());
			$rs=$Model->query($sqlstr);
		
		
		return $this -> ajaxReturn($rs);
     }
     
     public function getWHSKCInfo(){
     		if(hasInput('WHCode'))  $condition['PartyCode'] = getInputValue("WHCode");
     		if(hasInput('ParentWHCode'))  
     		$condition['PartyCode'] = array("exp","in (select partycode from bparty2partyrelation where relationtype='补货关系' and parentcode='".  getInputValue("ParentWHCode",'A00Z003') ."')");
			
     		if(hasInput('SKCCode'))  $condition['SKCCode'] =getInputValue("SKCCode",'1326723019');
     		
			$condition['_string'] = "ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)+ifnull(TargetQty,0)+ifnull(SaleTotalQty,0)>0";
//   		$pageStr = getInputValue("Page","1,1000");
		
     		$fieldStr = "@x:=@x+1 as rownum,ParentCode,ParentName,PartyCode,PartyName,PartyLevel,SKCCode,ColorName,PriceType,SaleType,IsDeadProduct,YearName,seasonname,seasonstagename,";
			$fieldStr = $fieldStr . " seriesName,MaintypeName,seriesname,subtype1code,OnShelfDays,SaleType,TargetQty,";
			$fieldStr = $fieldStr . " StockQty,ShortStockQty,OverStockQty,Sale28Qty,SaleTotalQty,";
			$fieldStr = $fieldStr . " if(ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)+ifnull(TargetQty,0)<1,0,1) InStore,0 as 'Check'";
     		$fieldStr = getInputValue("fieldStr",$fieldStr);
			
			resetCounter();
     		$rs = M("zdimpartyskc","",getMyCon())
     		->field($fieldStr)
			->where($condition)
			->page($pageStr)
			->select();
//			echo M("zdimpartyskc","",getMyCon())->_sql();
			
     		return $this->ajaxReturn($rs);
     }

//查询一个门店没有的款色
    public function getWHSKCInfoNewSKC(){
    			
     		$storecode = getInputValue("WHCode","CZ2N");
//   		$parentcode = getInputValue("ParentCode","D03A");
     		$rs = M('vwp2partyallrel',"",getMyCon())
     		->field("ParentCode")
     		->where(array("PartyCode"=>$storecode,"RelationType"=>'补货关系'))
     		->select();
     		
     		$parentcode = $rs[0]["parentcode"];
     		
     		$sqlstr = " select @x:=@x+1 as rownum,PartyCode,PartyName,PartyLevel,SKCCode,ColorName,PriceType,SaleType,YearName,seasonname,seasonstagename,";
			$sqlstr = $sqlstr . " seriesName,MaintypeName,seriesname,OnShelfDays,SaleType,TargetQty,";
			$sqlstr = $sqlstr . " if(ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)>0,ifnull(OnHandQty,0)+ifnull(OnRoadQty,0),null) as 'StockQty',";
			$sqlstr = $sqlstr . " ShortStockQty,Sale28Qty,SaleTotalQty,0 as 'Check'";
			$sqlstr = $sqlstr . " from  zdimpartyskc as a";
			$sqlstr = $sqlstr . " where  ifnull(OnHandQty,0)+ifnull(OnRoadQty,0)+ifnull(TargetQty,0)+ifnull(SaleTotalQty,0)>0 ";
			$sqlstr = $sqlstr . " and PartyCode='" . $parentcode . "' and not exists(";
			$sqlstr = $sqlstr . " SELECT 1 FROM zdimpartyskc  as b";
			$sqlstr = $sqlstr . " WHERE b.PartyCode = '" . $storecode . "'  and b.SKCCode=a.SKCCode)";
			
			resetCounter();
			$dbt = new \Think\Model("","",getMyCon());
			$rs = $dbt->query($sqlstr);
			
     		return $this->ajaxReturn($rs);
     }
     
     public function getCWHSKU(){
     	$condition["_string"] = "ShortStockQty>0";
     	$fieldstr = "SKUCode,SKCCode,BrandName,MainTypeName,SubType1Code,PriceType,SeriesName,SaleType,OnShelfDays,";
     	$fieldstr .= "Sale28Qty,Sale14Qty,SaleTotalQty,TargetQty,OnHandQty,OnHandStockDays,";
     	$fieldstr .= "OnRoadQty,OnRoadStockDays,ShortStockQty,OverStockQty,PlanRetQty";
     	 $rs = M("vwcwhsku","",getMyCon())
     	 ->page("1,100000")
		 ->field($fieldstr)
     	 ->where($condition)
     	 ->select();
		 
		 	$headArr = array(
			"skucode"=>"SKU",
			"skccode"=>"款杯色",
			"subtype1code"=>"商品级别",	
			"brandname"=>"品牌",
			"seriesname"=>"系列",
			"maintypename"=>"大类",
			"pricetype"=>"价格类型",
			"saletype"=>"销售类别",
			"onshelfdays"=>"上柜天数",
			"sale14qty"=>"14销量",
			"sale28qty"=>"28天销量",
			"saletotalqty"=>"总销量",
			"targetqty"=>"目标库存",
			"onhandqty"=>"在手库存",
			"onhandstockdays"=>"在手库存天数",
			"onroadqty"=>"在途库存",
			"onroadstockdays"=>"在途库存天数",
			"shortstockqty"=>"缺口库存",
			"overstockqty"=>"超额库存",
			"planretqty"=>"计划退货"
			);
			

		if(hasInput('CSV'))
		{
				getCSV('总仓SKU.csv',$rs,$headArr);
				exit;
		}
			
		 return $this->ajaxReturn($rs);
     }

      public function getSKUPotentialRetList(){
      	$condition["SKUCode"] = getInputValue("SKUCode");
      	$condition["_string"] = "a.OnHandQty>a.TargetQty";
      	
     	 $rs = M("dstock as a","",getMyCon())
     	 ->join("inner join bparty as b on a.PartyCode=b.PartyCode")
     	 ->field("a.PartyCode,b.PartyName,b.PartyLevel,a.SKUCode,1 as RetSKUNum,a.OnHandQty-a.TargetQty as RetQty")
		 ->where($condition)
     	 ->page("1,100000")
     	 ->select();
		 
		 return $this->ajaxReturn($rs);
     }
}
?>
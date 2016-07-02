<?php
namespace Home\Controller;


class TestController extends \Think\Controller {
		
	
	public function Test(){
		$rs= M('vwpmix','',getMyCon())->select();
		dump($rs);
		return $this -> ajaxReturn($rs);
	}
}

?>
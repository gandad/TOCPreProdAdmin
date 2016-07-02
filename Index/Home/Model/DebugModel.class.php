<?php
namespace Home\Model;
use Think\Model;

class DebugModel extends Model{

	public function tag($label,$context)
	{
		
		$data['Label'] = $label;
		$data['Context'] = $context;
		$data['Time'] = date('Y-m-d H:i:s');//getdate();
     //   dump($data);
		M('debug',"",getMyCon())->data($data)->add();
//		$this->create($data); 为什么这样就不行了?
//		echo $this->_sql();	
	}
}

?>
	
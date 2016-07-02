<?php
return array(
	//'配置项'
					'DB_TYPE' => 'mysql',
			  		'DB_PORT' =>3306,
					'DB_CHARSET'=> 'utf8', // 字符集
					'DB_Host' => '127.0.0.1', //mac下不能使用localhost!
					'DB_User' => 'root',
					'DB_PWD'  => 'Rickywang9',
					'DB_NAME' => 'tocdist',
					'DB_DEBUG'  =>  TRUE
	//默认的数据库驱动类设置了 字段名强制转换为小写，如果你的数据表字段名采用大小写混合方式的话，需要在配置文件中增加如下设置
	//调用时一定要注意字段的大小写与数据库一致
//	'DB_PARAMS'=>array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL)
	//'TMPL_FILE_DEPR' => '_',
);


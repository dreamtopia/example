<?php
/*
 * 创建树形
 * @param Array $array 数组
 * @param Integer $pid 父级ID
 * @return Array $ret 
 */
function createTree($array, $pid = 0){
  $ret = array();
	
	foreach($array as $key => $value){
		if($value['pid'] == $pid){
			$tmp = $value;
			unset($array[$key]);
			$tmp['children'] = createTree($array, $value['id']);
			$ret[] = $tmp;
		}
	}
	
	return $ret;
}

$array  = array(
	array('id'=>1,'pid'=>'0','name'=>'11111'),
	array('id'=>2,'pid'=>'1','name'=>'22222'),
	array('id'=>3,'pid'=>'0','name'=>'33333'),
	array('id'=>4,'pid'=>'3','name'=>'44444'),
	array('id'=>5,'pid'=>'4','name'=>'55555'),
	array('id'=>6,'pid'=>'1','name'=>'66666')
);

echo '<pre>';
print_r(createTree($array));

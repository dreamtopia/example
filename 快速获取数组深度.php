<?php
/*
 * 获取数组深度
 * @param Array $array 数组
 */
function array_depth($array){
    $max_depth = 1;
    
	  foreach($array as $arr){
		    if(is_array($arr)){
		
			      $depth = array_depth($arr);
			
			      if($depth > $max_depth){
			  	    $max_depth = $depth;
			      }
  		  }
	  }
	return $depth;
}

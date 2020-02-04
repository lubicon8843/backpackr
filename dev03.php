<?php
// 문제 3) 문자열의 배열에서 웃는 이모티콘이 몇 개 있는지 숫자를 반환합니다.

function countSmileImoticon($txt){		
	$result = 0;
	
	foreach($txt as $key => $val){
	
		$match_parm = (strlen($val) == 3) ? '/([:;]{1})([-~]{1})([\)D]{1})/' : '/([:;]{1})([\)D]{1})/';
		$chk = preg_match($match_parm, $val, $match);
		if($chk == true) {
			$result++;
		}	
	}
	return $result;		
}

echo countSmileImoticon([';~', ':)', ':-D', ':fD']);

?>
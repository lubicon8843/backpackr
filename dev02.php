<?php
// 문제 2) 괄호가 올바르게 닫혔는지 확인하는 함수를 작성하세요.

function valid($str) {

	$result = true;
	$arr = str_split($str);
	$chk = $prev_cnt = count($arr);

	// 홀수이면 바로 false 반환
	if($prev_cnt%2 == 1) {
		$result = false;
	} else { 
		while(1) {
			// 앞, 뒤 괄호가 서로 맞는 값들 삭제한 배열 반환
			$arr = bracket_chk($arr);
			$prev_cnt = count($arr);

			if($prev_cnt > 0) { 	
				if($prev_cnt == $chk) {
					$result = false;
					break;
				}

				$chk = count($arr);
			} else {
				break;
			}
			
		}
	}
	return $result;
}

function bracket_chk($arr) {
	$start = array('[', '{', '(');
	$end = array(']', '}', ')');
		
	foreach($arr as $key => $val) {
		if(in_array($val, $start) && in_array($arr[$key+1], $end) && array_search($val, $start) == array_search($arr[$key+1], $end)) {
			unset($arr[$key]);
			unset($arr[$key+1]);
		}
	}

	$arr = array_values($arr);
	return $arr;
}

echo var_dump(valid("[]{}()[[]]"));
?>
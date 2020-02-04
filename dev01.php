<?php
// 문제 1) str 인자를 n번 반복하는 함수를 작성하세요.

function repeatString($cnt, $txt) {
	$result = "";
	
	for($i=1;$i<=$cnt;$i++){
		$result .= $txt;
	}
	return $result;
}

echo repeatString(6,"A");
	
?>

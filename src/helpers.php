<?php
//todo documentar
if (!function_exists('removeLastItemIfEmpty')) {
	function removeLastItemIfEmpty(&$arr) {
		$last_index = count($arr) - 1;
		//Se o último item for vazio, estão descarta
		if (empty($arr[$last_index]))
			array_pop($arr);
	}
}

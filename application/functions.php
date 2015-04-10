<?php
/**
 * Moved here from models/Quiz/GeneratedQuestion.php
 * @param unknown $vArray
 * @return unknown
 */
function randset($vArray){
	return $vArray[(rand(0,(sizeof($vArray))-1))];
}
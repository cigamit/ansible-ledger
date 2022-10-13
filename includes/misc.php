<?php

function stripUnwantedTagsAndAttrs($html_str){
	$xml = new DOMDocument();
  //Suppress warnings: proper error handling is beyond scope of example
	libxml_use_internal_errors(false);
  //List the tags you want to allow here, NOTE you MUST allow html and body otherwise entire string will be cleared
	$allowed_tags = array("b", "br", "em", "i", "li", "ol", "u", "ul", "p");
  //List the attributes you want to allow here
	$allowed_attrs = array ();
	if (!strlen($html_str)){return false;}
	if ($xml->loadHTML($html_str, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)){
	  foreach ($xml->getElementsByTagName("*") as $tag){
		if (!in_array($tag->tagName, $allowed_tags)){
		  $tag->parentNode->removeChild($tag);
		}else{
		  foreach ($tag->attributes as $attr){
			if (!in_array($attr->nodeName, $allowed_attrs)){
			  $tag->removeAttribute($attr->nodeName);
			}
		  }
		}
	  }
	}
	$d = $xml->saveHTML();
	return strip_tags($d);
}

function random_str($length) {
	$keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$str = '';
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$str .= $keyspace[random_int(0, $max)];
	}
	return $str;
}

function reindex_col($a, $c) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[] = $b[$c];
		}
	}

	return $d;
}

function reindex_arr_by_id($a) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[$b['id']] = $b;
		}
	}

	return $d;
}

function reindex_arr_by_col($a, $c) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[$b[$c]] = $b;
		}
	}

	return $d;
}

function reindex_arr_by_id_col($a, $c) {
	$d = array();

	if (!empty($a)) {
		foreach ($a as $b) {
			$d[$b['id']] = $b[$c];
		}
	}

	return $d;
}
<?php
$credentials = [
  'app_id' => '1308182092532059',
  'app_secret' => '4a05bf66f23a828aed0f5f28982bfdb2',
  'default_graph_version' => 'v2.5'
];

function eachEdge($response, $func, $maxPages = null) {
	global $fb;
	$pagesEdge = $response->getGraphEdge();
	$pageCount = 0;
	do {
	  foreach ($pagesEdge as $page) {
	    $func($page);
	  }
	  $pageCount++;
	} while ( (!$maxPages || ($maxPages && ($pageCount < $maxPages))) && $pagesEdge = $fb->next($pagesEdge));
}

function output($text) {
	echo $text . "\n";
}
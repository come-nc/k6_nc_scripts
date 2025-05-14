#!/usr/bin/php
<?php

function runCommand($command) {
	exec($command, $output, $outcode);

	if ($outcode !== 0) {
		echo implode("\n", $output)."\n";
		exit($outcode);
	}

	return $output;
}

function median(array $values): float {
	$count = count($values);
	sort($values);
	$half = floor($count / 2);
	if ($count % 2) return $values[$half];
	return ($values[$half - 1] + $values[$half]) / 2.0;
}

$inputFile = $argv[1];
$version = $argv[2] ?? $inputFile;

$testedEndpoints = runCommand("jq '. | select(.type==\"Point\" and .metric == \"http_req_duration\")|.data.tags.url' {$inputFile} -r");

$testedEndpoints = array_values(array_unique($testedEndpoints));

$data = [];

foreach ($testedEndpoints as $testedEndpoint) {
	$dataPoints = runCommand("jq '. | select(.type==\"Point\" and .metric == \"http_req_duration\" and .data.tags.url == \"{$testedEndpoint}\")|.data.value' {$inputFile}");
	$testedEndpoint = explode('/',$testedEndpoint,4)[3];
	$data[] = ['version' => $version,'url' => $testedEndpoint, 'median' => median($dataPoints)];
}

echo json_encode($data);

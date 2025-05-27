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

function aggregateFile(string $inputFile, string $version, array &$data): void {
	$testedEndpoints = runCommand("jq '. | select(.type==\"Point\" and .metric == \"http_req_duration\")|.data.tags.name' {$inputFile} -r");

	$testedEndpoints = array_values(array_unique($testedEndpoints));

	$excludeEndpoints = ['version.php','index.php/csrftoken'];

	foreach ($testedEndpoints as $testedEndpoint) {
		$dataPoints = runCommand("jq '. | select(.type==\"Point\" and .metric == \"http_req_duration\" and .data.tags.name == \"{$testedEndpoint}\")|.data.value' {$inputFile}");
		$testedEndpoint = explode('/',$testedEndpoint,4)[3] ?? $testedEndpoint;
		if (in_array($testedEndpoint, $excludeEndpoints)) {
			continue;
		}
		$data[] = ['version' => $version,'url' => $testedEndpoint, 'median' => median($dataPoints)];
	}
}

$data = [];

// $inputFile = $argv[1];
// $version = $argv[2] ?? $inputFile;
$files = array_slice($argv, 1);
foreach ($files as $file) {
	aggregateFile($file, str_replace(['-webdav.json','.json'],'',basename($file)), $data);
}

echo json_encode($data);

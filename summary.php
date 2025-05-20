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
	$testedEndpoints = runCommand("jq '. | select(.type==\"Point\" and .metric == \"http_req_duration\")|.data.tags.url' {$inputFile} -r");

	$testedEndpoints = array_values(array_unique(
		array_map(
			fn ($url) => preg_replace('@filename_[^/.]+\.txt$@', 'filename_uuid.txt', $url),
			$testedEndpoints,
		)
	));

	foreach ($testedEndpoints as $testedEndpoint) {
		$regEx = "^{$testedEndpoint}$";
		if (preg_match('@^(.+filename_)[^/.]+\.txt$@', $testedEndpoint, $matches)) {
			// var_dump($matches);
			$regEx = "^{$matches[1]}";
			$testedEndpoint = "{$matches[1]}uuid.txt";
		} else {
			// echo $testedEndpoint."\n";
		}
		$dataPoints = runCommand("jq '. | select(.type==\"Point\" and .metric == \"http_req_duration\" )|select(.data.tags.url|test(\"{$regEx}\"))|.data.value' {$inputFile}");

		$testedEndpoint = explode('/',$testedEndpoint,4)[3];
		$data[] = ['version' => $version,'url' => $testedEndpoint, 'median' => median($dataPoints)];
	}
}

$data = [];

// $inputFile = $argv[1];
// $version = $argv[2] ?? $inputFile;
$files = array_slice($argv, 1);
foreach ($files as $file) {
	aggregateFile($file, str_replace('.json','',$file), $data);
}

echo json_encode($data);

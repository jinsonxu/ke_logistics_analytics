<?php
/*********************************************************
 Quick and dirty script to prepare Routific output for use
 in MapBox
 Author: Jinson Xu
 Date: 20141018
*********************************************************/
include('libraries/krumo/class.krumo.php');


// config info
$filePath= 'data/solution_part3.json';
$outputFilePath = 'data/solution_part3.csv';
$jsonOutputFilePath = 'data/solution_part3_mapbox.json';

$markerColours = array('Driver_1' => '#AA3939', 'Driver_2' => '#AA6C39', 'Driver_3' => '#AA7F39', 
						'Driver_4' => '#AA8B39','Driver_5' => '#AA9E39','Driver_6' => '#97A637', 
						'Driver_7' => '#679933', 'Driver_8' => '#2C8437', 'Driver_9' => '#256E5D',
						'Driver_10' => '#255C69', 'Driver_11' => '#2C4870', 'Driver_12' => '#313A75', 
						'Driver_13' => '#3C3176','Driver_14' => '#482E74', 'Driver_15' => '#652770');



$locData = explode("\n",trim(file_get_contents('data/1_vrp_8_plain_routing.csv')));
$locations = array();
$x = 0;
foreach($locData as $line) {
	$cols = explode(',', $line);
	if($x > 0) {
		$locName = (strlen($cols[0]) < 6) ? '0'.$cols[0] : $cols[0];
		$locations[$locName]['latitude'] = $cols[1];		
		$locations[$locName]['longitude'] = $cols[2];
	}
	++$x;
}
krumo($locations);


$content = file_get_contents($filePath);
$working = json_decode($content);

krumo($working);
//krumo($working->output);

$solutionArray = get_object_vars($working->output->solution);
//krumo($solutionArray);
/*
$csv = "driver,location_id,location_name,latitude, longitude, arrival_time, marker-color\r\n";
foreach($solutionArray as $key => $val) {
	foreach($val as $stop) {
		$locLookupName = str_replace('Singapore ','', $stop->location_name);
		$csv .= $key .','. $stop->location_id 
					.','. $stop->location_name 
					.','.$locations[$locLookupName]['latitude'] 
					.','. $locations[$locLookupName]['longitude'] 
					.','. $stop->arrival_time 
					.','. $markerColours[$key]."\r\n";	
	}
}
*/
//krumo($csv);
//file_put_contents($outputFilePath, $csv);
//print 'Written to '. $outputFilePath;


$geoJson = new stdClass();
$geoJson->features = array();
//$csvArray = explode("\r\n", trim($csv));
$x = 0;
foreach($solutionArray as $key => $val) {
	$k = 0;
	$driverCoordinates = array();
	foreach($val as $stop) {
		$marker = new stdClass();
		$marker->type='Feature';
		$markerSymbol = ($stop->location_id=='depot') ? 'warehouse' : 'grocery';
		$locLookupName = str_replace('Singapore ','', $stop->location_name);
		
		$coords = array((float) $locations[$locLookupName]['longitude'], (float) $locations[$locLookupName]['latitude']);
		$marker->geometry = (object) array('type'=>'Point', 'coordinates'=> $coords);
		
		//lets also add the coordinates into an array to facilitate polyline creation later
		array_push($driverCoordinates, $coords);
		
		$marker->properties = new stdClass();
		$marker->properties->id = 'marker_' . strtolower(str_replace(' ','_', $stop->location_name)) . '_' . $x . '_' . $k;
		$marker->properties->title=$stop->arrival_time . ', ' . $key;
		$marker->properties->description=$stop->location_id . ', '. $stop->location_name;
		$marker->properties->{'marker-color'}=$markerColours[$key];
		$marker->properties->{'marker-symbol'}=$markerSymbol;
		$marker->properties->{'marker-size'}='small';
		array_push($geoJson->features, $marker);
		++$k;
	}
	// now for each driver's route, lets add a polyline to roughly show the route sequence.
	$lineString = new stdClass();
	$lineString->type = 'Feature';
	$lineString->geometry = (object) array('type'=> 'LineString', 'coordinates' => $driverCoordinates);
	$lineString->properties = new stdClass();
	$lineString->properties->id = 'line_' . $x;
	$lineString->properties->stroke = $markerColours[$key];
	$lineString->properties->{'stroke-opacity'} = 0.85;
	$lineString->properties->{'stroke-width'} = 4;
	
	array_push($geoJson->features, $lineString);
	
	++$x;
}
//$geoJson = substr($geoJson, 0, strlen($geoJson)-1);
$geoJson->id = 'jinsonxu.k3a3enhh';
$geoJson->type = 'FeatureCollection';


krumo($geoJson);

file_put_contents($jsonOutputFilePath, json_encode($geoJson, JSON_PRETTY_PRINT));

?>
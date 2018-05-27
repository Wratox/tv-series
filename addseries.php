<?php

$name = rawurlencode('The Crossing');		//Need to replace spaces in title with underscores

$imdbinfo = file_get_contents('http://www.omdbapi.com/?t='.$name.'&type=series&apikey=1fb60737');
$imdbinfo = json_decode($imdbinfo, true);
var_dump($imdbinfo);

function endswith($string, $test) {
    $strlen = strlen($string);
    $testlen = strlen($test);
    if ($testlen > $strlen) return false;
    return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

$name = rawurldecode($name);

$seriesdata = [
	'name' 				=> $name,						//We get the name from the form the user submitted
	'imdbid'			=> $imdbinfo['imdbID'],			//We get imdbid from omdbapi
	'status'			=> '',							//We conclude the status with data from omdbapi
	'season_count'		=> $imdbinfo['totalSeasons'],	//We get number of seasons from omdbapi
	'season_lengths'	=> '',							//We get season lengths from episode-database
	'plot'				=> $imdbinfo['Plot'],			//We get the plot from omdbapi
	'genre'				=> $imdbinfo['Genre'],			//We get the genre(s) from omdbapi
	'poster'			=> $imdbinfo['Poster']			//We get the poster from omdbapi
];

if(endswith($imdbinfo['Year'], '–')){
	$seriesdata['status'] = 'Alive';
}
else {
	$seriesdata['status'] = 'Dead';
}

/*
 * ----Not needed here but might be useful for monthly check to add newly added seasons to season_count----
 *
$season_count = 0;
$stmt = $pdo->prepare('SELECT MAX(season_number) FROM episodes WHERE imdbparentid = :imdbparentid');
$stmt->bindParam(':imdbparentid', $seriesdata['imdbid']);
$stmt->execute();
$result = $stmt->fetchAll();
$season_count = $result[0]['MAX(season_number)'];
*/

//$season_lengths = '';
$stmt = $pdo->prepare('SELECT MAX(episode_number) FROM episodes WHERE imdbparentid = :imdbparentid AND season_number = :season_count');
$i = 1;
$stmt->bindParam(':imdbparentid', $seriesdata['imdbid']);
$stmt->bindParam(':season_count', $i);

for(; $i <= $seriesdata['season_count']; $i++) {
	$stmt->execute();
	$result = $stmt->fetchColumn();
	$seriesdata['season_lengths'] .= $result . ' ';
}
var_dump($seriesdata);
//echo $season_lengths;
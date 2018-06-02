<?php
/*
 * ----Important!----
 * For writes, such as INSERT or UPDATE, it’s especially critical to still filter your data first and 
 * sanitize it for other things (removal of HTML tags, JavaScript, etc).
 * PDO will only sanitize it for SQL, not for your application.
 */
	
if(isset($_POST['name'])){	
$name = strip_tags(trim($_POST['name']));
$imdbinfo = '';
/*
 * Change to preg_match_all if we want to add support for adding multiple tv-series in one submit.
 * And surround the whole adding procedure with a foreach-loop.
 */
if(preg_match('~tt[0-9]{7}~', $name, $imdbids)){
	$imdbinfo = file_get_contents('http://www.omdbapi.com/?i='.$imdbids[0].'&type=series&apikey=1fb60737');
}
else{
	$name = rawurlencode($name);		//Need to replace spaces in title with underscores
	$imdbinfo = file_get_contents('http://www.omdbapi.com/?t='.$name.'&type=series&apikey=1fb60737');
}

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

$stmt = $pdo->prepare('SELECT MAX(episode_number) FROM episodes WHERE imdbparentid = :imdbparentid AND season_number = :season_count');
$i = 1;
$stmt->bindParam(':imdbparentid', $seriesdata['imdbid']);
$stmt->bindParam(':season_count', $i);

for(; $i <= $seriesdata['season_count']; $i++) {
	$stmt->execute();
	$result = $stmt->fetchColumn();
	$seriesdata['season_lengths'] .= $result . ' ';
}
//var_dump($seriesdata);
}
?>
<form id="addseries" action="" method="post">
	<fieldset>
		<legend>Add Series</legend>
		<p>Autocomplete options above the line are series that already exists in the database.</p>
		<label for="name">Name of the series</label>
		<input type="text" id="name" name="name" list="series" autocomplete="on" required="required" pattern="[A-Za-z0-9.,\\\/' _\-!?]+">
		<span class="error-message"><i class="fas fa-check"></i><i class="fas fa-times"></i></span>
		<datalist id="series">
		<?php
			foreach($resultSeries as $row => $moviename){
		?>
				<option value="<?php echo($moviename['name']);?>"></option>
		<?php
			}
		?>
		</datalist>
		
		<button type="submit">Add Series</button>
	</fieldset>
</form>
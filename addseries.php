<?php
/*
 * --------========SECURITY STUFF========--------
 * For writes, such as INSERT or UPDATE, it’s especially critical to still filter your data first and 
 * sanitize it for other things (removal of HTML tags, JavaScript, etc).
 * PDO will only sanitize it for SQL, not for your application.
 * Using htmlentities on data to be displayed that comes from user-input prevents XSS(Cross Site Scripting) 
 */
 
/*
 * The regex-pattern allowed for inputs, this controls what characters the user is allowed to enter.
 * Note: HTML5 input element does not use delimiters in the regex-pattern.
 * To use in php either concatenate delimiters before and after or use double quotes, which lets you insert variables inside the string: "{$variablename}"
 */
$allowedPattern = "[A-ZÅÄÖa-zåäö0-9.,\\\/' _\-!?]+";
$delimiter = "~";

function sanitizeInput($value){
	$value = trim($value); //removes whitespace(spaces, tabs, newlines and more) from beginning and end of string.
	$value = strip_tags($value); //removes NULL bytes, HTML and PHP tags from string(leaves less then/greater then symbol if it is inferred from content).
	$value = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); 
	return $value;
}

function endswith($string, $test){
	$strlen = strlen($string);
	$testlen = strlen($test);
	if ($testlen > $strlen) return false;
	return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

// Get the input and run it through the sanitizeInput-function.
$seriesInput = filter_input(INPUT_POST, 'name/id', FILTER_CALLBACK, array('options' => 'sanitizeInput'));
$foundData = false;
if(filter_var($seriesInput, FILTER_VALIDATE_REGEXP, array('options' => array('regexp'=>$delimiter.$allowedPattern.$delimiter)))){

	$imdbinfo = '';

	var_dump($seriesInput);

	/*
	 * Change to preg_match_all if we want to add support for adding multiple tv-series in one submit.
	 * And surround the whole adding procedure with a foreach-loop.
	 */
	if(preg_match('~tt[0-9]{7}~', $seriesInput, $imdbids)){
		$imdbinfo = file_get_contents('http://www.omdbapi.com/?i='.$imdbids[0].'&type=series&apikey=1fb60737');
	}
	else{
		$seriesInput = rawurlencode($seriesInput);		//Need to replace spaces in title with underscores
		$imdbinfo = file_get_contents('http://www.omdbapi.com/?t='.$seriesInput.'&type=series&apikey=1fb60737');
	}

	$imdbinfo = json_decode($imdbinfo, true);
	var_dump($imdbinfo);
	
	
	
	$seriesInput = rawurldecode($seriesInput);
	if($imdbinfo['Response'] == 'True'){
		$foundData = true;
		$seriesdata = [
			'name' 				=> $imdbinfo['Title'],			//We get the name from omdbapi
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
	}
	else{
		
	}
	var_dump($seriesdata);
}
?>
<form id="addseries" action="" method="post">
	<fieldset>
		<legend>Add Series</legend>
		<?php
			if(!$foundData){
				echo('<p>No series found with that name/ID.</p>');
			}
		?>
		<p>Autocomplete options above the line are series that already exists in the database.</p>
		<label for="name/id">Name of the series</label>
		<input type="text" id="name/id" name="name/id" list="series" autocomplete="on" required="required" pattern="<?php echo($allowedPattern);?>">
		<span class="error-message"><i class="fas fa-check"></i><i class="fas fa-times"></i></span>
		<datalist id="series">
		<?php
			/*
			 * $resultSeries is created in index.php and contains the list of series in our database
			 */
			foreach($resultSeries as $key => $value){
		?>
				<option value="<?php echo(htmlspecialchars($value['name']));?>"></option>
		<?php
			}
		?>
		</datalist>
		
		<button type="submit">Add Series</button>
	</fieldset>
</form>
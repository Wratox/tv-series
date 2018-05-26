<?php


//TODO: test me
/**
 * Returns zero indexed array with element $i 
 * representing episode count for season $i + 1
 *
 */
function get_episode_counts($series_imdb_id) {
	$stmt = $pdo->prepare(
		"SELECT MAX(season) 
		FROM episodes 
		WHERE id = '$series_imdb_id'"
	);
	
	$stmt->execute();
	$season_count = intval($stmt->fetchColumn());

	$season_lengths = array();


	$stmt = $pdo->prepare(
		"SELECT MAX(episode) 
		FROM episodes 
		WHERE 
			id = '$series_imdb_id' AND 
			season = ':season'");
	$i = 1;
	$stmt->bindParam(":season", $i);

	for(; $i <= $season_count; $i++) {
		$stmt->execute();
		$season_lengths[$i - 1] = intval($stmt->fetchColumn());
	}
	return $season_lengths;
}
?>
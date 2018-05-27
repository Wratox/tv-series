<!--
 
----series database----
 
id:          	int			unique id for internal use
name:           string		name of the tv-series
imdbid:         string		imdbid of the tv-series
status:         enum		status of the series (DEAD|ALIVE|etc.)
season_count:	int			number of seasons
season_lengths	string		space separated list of number of episodes in each season
plot			text		a short description of the tv-series
genre			string		genre(s) of the tv-series
poster			string		url to the movies poster
 

----users  database----
 
id:             int			unique id for internal use
username:       string		well, the name of the user
pass_hash:      string		hashed version of users password
 
 
----watched database----
 
id:             int			unique id for internal use
user_id:        int			owner of this entry
series_id:      int			id of the series
rating:         int			users rating of the series(may be NULL)
progress:       string		how far have the user watched
 
 
----episodes database----

id:				int			unique id for internal use
imdbparentid	string		imdbid of the tv-series the episode belongs to
season_number	int			number of the season the episode belongs to
episode_number	int			number of the episode in the season


-->
<!DOCTYPE html>
<head>
	<link href="stylesheet.css" rel="stylesheet" type="text/css" media="screen">
</head>
<body>
<?php
/*Gets the config-array*/
$config = require 'config.php';

$dsn = $config['database']['dsn'];
$username = $config['database']['username'];
$password = $config['database']['password'];

try {
	
    /*
     * Declare the error mode as exception on creation.
     * ATTR_EMULATE_PREPARES forces PDO to use prepared statements.
	 * It also helps against SQL-injection.
     */
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
    );
	
    $pdo = new PDO($dsn, $username, $password, $options);
	
} catch(PDOException $e) {

    /*
     * ----For debug use only! Remember to change!----
	 * Often when PDO fails the connection details such as
     * the DSN, username and password are leaked in the error message. By displaying
     * the error to the screen a malicious user could attack your website!
     *
     * Just provide a generic error message like: 'Application error' and log the
     * actual message (should be logged by PHP automatically if the INI 
     * configuration 'log_errors' is enabled).
     */
    exit('Connection failed: ' . $e->getMessage()); // Can echo from the exit() function.

}

$stmt = $pdo->prepare('SELECT * FROM series ORDER BY name ASC');
$stmt->execute();
$result = $stmt->fetchAll();
 
?>

<table>
    <tr>
        <th>Name</th><th>Link</th><th>Status</th><th>Seasons</th><th>Plot</th><th>Genre</th>
    </tr>

<?php
foreach($result as $row) {
?>
    <tr>
        <td><?php echo($row['name']);?></td>
        <td><a href="https://www.imdb.com/title/<?php echo($row['imdbid']);?>/"><img src="<?php echo $row['poster'];?>" height="100"></a></td>
        <td><?php echo($row['status']);?></td>
        <td><?php echo($row['season_count']);?></td>
        <td><?php echo($row['plot']);?></td>
        <td><?php echo($row['genre']);?></td>
    </tr>
<?php
}
?>

</table>
<form id="addseries" action="addseries.php" method="post">
	<fieldset>
		<legend>Add Series</legend>
		<label for="name">Name of the series</label>
		<input type="text" id="name" name="name" required="required" pattern="[A-Za-z0-9.,/\' _-!?+]+<!--|tt[0-9]{7}-->">
		<button type="submit">Add Series</button>
	</fieldset>
</form>
<?php
include 'addseries.php';
?>
</body>
</html>

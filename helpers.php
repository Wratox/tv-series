<?php

$config = require 'config.php';

//TODO: remove error msg
function formatError($e, $sql="") {
	if($sql == "")
		die($e->getMessage());
	else 
		die($sql . "<br>" . $e->getMessage());
}

// Object used to communicate with database
// Should be usable as drop in replacement for PDO
class Database{
	function __construct() {
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
			
			$dsn = $config['database']['dsn'];
			$username = $config['database']['username'];
			$password = $config['database']['password'];
			
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
			 //TODO: remove error msg
			die("Connection failed: { $e->getMessage() }"); // Can echo from the exit() function.
		}
		$this->$db = $pdo;
	}
	
	function exec($sql) {
		try {
			$rowsModified = $this->$db->exec($sql);
		} 
		catch(PDOException $e) {
			formatError($e, $sql);
		}
		return $rowsModified;
	}
	
	function query($sql) {
		try {
			$stmt = $this->$db->query($sql);
		}
		catch(PDOException $e) {
			formatError($e, $sql);
		}
		return new Statement($stmt);
	}
	
	function prepare($sql) {
		try {
			$stmt = $this->$db->prepare($sql);
		} 
		catch(PDOException $e) {
			formatError($e, $sql);
		}
		return new Statement($stmt);
	}

	private $db;
}

// Statment object similar to what would be returned by $pdo->prepare() etc.
class Statement {
	function __construct($stmt) {
		$this->$stmt = $stmt;
	}
	
	function bindParam($variableName, &$param) {
		//TODO: can bindParam() throw exceptions?
		return $this->$stmt->bindParam($variableName, $param);
	}
	
	function bindValue($variableName, $param) {
		//TODO: can bindValue() throw exceptions?
		return $this->$stmt->bindValue($variableName, $param);
	}
	
	function execute() {
		try {
			$success = $this->$stmt->execute();
		} 
		catch(PDOException $e) {
			formatError($e);
		}
		return success;
	}
	
	function fetchAll() {
		//TODO: can fetchAll() throw exceptions?
		return $this->$stmt->fetchAll();
	}
	
	function fetch() {
		//TODO: can fetch() throw exceptions?
		return $this->$stmt->fetch();
	}
	
	function fetchColumn($columnNumber = 0) {
		//TODO: can fetchColumn() throw exceptions?
		return $this->$stmt->fetchColumn($columnNumber);
	}
	
	private $stmt;
}

?>
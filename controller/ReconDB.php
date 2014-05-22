<?php
//// Class
class ReconDB {
	
	//// Variables
	
	// private $type;
	private $host;
	private $port;
	private $username;
	private $password;
	private $database;
	
	// config
	private $file;
	private $writable;
	
	// database
	private $connected;
	private $selected;
	private $link;
	
	// predefined
	private $types;
	
	//
	private $configFile = "/config.dat";
	private $backupDir = "model/";
	private $successClass = "success";
	private $failureClass = "failure";
	//
	public $serverIP;
	public $version = "&alpha;1";
	
	//// Constructor Function
	
	public function __construct() {
		
		// defaults
		// $this->host = "localhost";
		// $this->port = "3306";
		// $this->username = "";
		// $this->password = "";
		
		// database types
		$this->types = array(
			"mysql" => "MySQL"
		);
		$this->serverIP = $_SERVER["SERVER_ADDR"];
		
		// connect
		if ($this->configTest() == true) {
			if ($this->configInit()) {
				if ($this->dataConnect($this->host, $this->port, $this->username, $this->password)) {
					return true;
				}
			}
		}
		return false;
		
	}
	
	//// Configuration Functions
	
	//
	public function configTest() {
		
		// file location
		$this->file = dirname(__FILE__) . $this->configFile;
		// file handle
		$handle = @fopen($this->file, "a");
		if ($handle) {
			// success
			fclose($handle);
			$this->writable = true;
		} else {
			// failure
			$this->writable = false;
		}
		// return
		return $this->writable;
		
	}
	
	//
	public function configInit() {
		// config file
		$data = file($this->file);
		if ($data) {
			// config file is readable
			$this->host = trim(@$data[0]);
			$this->port = trim(@$data[1]);
			$this->username = trim(@$data[2]);
			$this->password = trim(@$data[3]);
			$this->database = trim(@$data[4]);
			return true;
		} else {
			// config file not found
			return false;
		}
	}
	
	/**
	 * @name Create Configuration
	 * @var type (unused as yet)
	 * @var host
	 * @var port
	 * @var password
	 * @var database
	 * @return boolean
	 * 
	 * @todo test for exact match in config + input
	 */
	public function configWrite($host, $port, $username, $password, $database) {
		//
		$this->host = ($host) ? $host : "localhost";
		$this->port = ($port) ? $port : "3306";
		$this->username = ($username) ? $username : "root";
		$this->password = ($password) ? $password : "";
		$this->database = ($database) ? $database : "";
		// file handle
		$handle = fopen($this->file, "w");
		if ($handle) {
			// success
			fwrite($handle, "$host\n$port\n$username\n$password\n$database");
			fclose($handle);
			return true;
			// failure
		} else {
			return false;
		}
	}
	// JUMPER!-func
	public function configWriteDB($database) {
		//
		$this->database = $database;
		//
		return $this->configWrite($this->host, $this->port, $this->username, $this->password, $this->database);
	}
	
	//
	public function configEnabled() {
		return (bool)$this->writable;
	}
	
	/**
	 * @name Is Configured
	 * @return boolean
	 */
	// public function isConfigured() {
		// return ($this->host && $this->pass && $this->username && $this->password) ? true : false;
	// }
	
	//// Configuration Status Functions
	
	/**
	 * Get Config Status
	 * @return CSS class
	 */
	public function getConfigStatus() {
		return ($this->configEnabled()) ? $this->successClass : $this->failureClass;
	}
	
	/**
	 * Get Config Message
	 * @return string
	 */
	public function getConfigMessage() {
		if (!$this->configEnabled()) {
			return "Configuration failed.";
		// } else if ($this->host && $this->port && $this->username && $this->password) {
			// return "Config loaded from file.";
		} else {
			return "Configuration passed.";
		}
	}
	
	//// Database Functions
	
	// temporal testing function
	public function dataTest($host, $port, $username, $password) {
		// defaults
		$host = ($host) ? $host : "localhost";
		$port = ($port) ? $port : "3306";
		$username = ($username) ? $username : "root";
		$password = ($password) ? $password : "";
		// test connection
		$this->link = mysql_connect("$host:$port", $username, $password);
		return ($this->link) ? true : false;
	}
	
	//
	public function dataConnect() {
		// run connection
		$this->link = mysql_connect("{$this->host}:{$this->port}", $this->username, $this->password);
		$this->connected = (mysql_set_charset('utf8', $this->link)) ? true : false;
		// select database
		if ($this->link) {
			// explicitly sets & unsets i.e. null is meaningful
			return $this->selected = (mysql_select_db($this->database, $this->link)) ? true : false;
		}
		// return falls through
		return $this->connected;
	}
	
	//
	public function dataConnected() {
		return (bool)$this->connected;
	}
	
	/**
	 * 
	 */
	public function getDataStatus() {
		return ($this->dataConnected()) ? $this->successClass : $this->failureClass;
	}
	
	/**
	 * 
	 */
	public function getDataMessage() {
		if ($this->dataConnected()) {
			return "Successfully connected.";
		} else {
			return "Not connected, please enter your details above.";
		}
	}
	
	//
	public function dataSelected() {
		return (bool)$this->selected;
	}
	
	/**
	 * 
	 */
	public function getSelectedStatus() {
		return ($this->dataSelected()) ? $this->successClass : $this->failureClass;
	}
	
	/**
	 * 
	 */
	public function getSelectedMessage() {
		if ($this->dataSelected()) {
			return "Successfully loaded.";
		} else {
			return "Not connected, please select a database above.";
		}
	}
	
	//
	// public function type() {
		// return $this->type;
	// }
	
	//
	public function host() {
		return $this->host;
	}
	
	//
	public function port() {
		return $this->port;
	}
	
	//
	public function username() {
		return $this->username;
	}
	
	//
	public function password() {
		return $this->password;
	}
	
	//
	public function database() {
		return $this->database;
	}
	
	//
	public function types() {
		return $this->types;
	}
	
	public function fetchDatabases() {
		//
		if (!$this->link) return false;
		
		//
		$list = mysql_list_dbs($this->link);
		$temp = array();
		echo "hello";
		while ($row = mysql_fetch_array($list)) {
			array_push($temp, $row);
		}
		print_r($temp);
		
		//
		$return = array("" => "Please select...");
		$list = mysql_list_dbs($this->link);
		while ($row = mysql_fetch_object($list)) {
			if ($row->Database != "information_schema") $return[$row->Database] = $row->Database;
		}
		return $return;
		
		//
		// $query = mysql_query("SHOW DATABASES", $this->link);
		// $return = array();
		// while ($row = mysql_fetch_assoc($query)) {
			// array_push($return, $row["Database"]);
		// }
		// return $return;
	}

	//
	// public function selectDatabase($database) {
		// //
		// return mysql_select_db($database, $this->link);
	// }

	//
	public function fetchTables($database) {
		//
		$query = "SHOW TABLES FROM $database";
		$result = mysql_query($query);
		//
		$return = array();
		while ($row = mysql_fetch_row($result)) {
			array_push($return, $row[0]);
		}
		return $return;
	}
	
	/**
	 * @name Backup
	 * @return filename || false
	 */
	function backup($tables = "*") {
		
		// get all of the tables
		if ($tables == '*') {
			$tables = array();
			$result = mysql_query('SHOW TABLES');
			while($row = mysql_fetch_row($result)) {
				$tables[] = $row[0];
			}
		} else {
			$tables = is_array($tables) ? $tables : explode(',', $tables);
		}
		
		$SQL = "";
		// cycle through
		foreach ($tables as $table) {
			$result = mysql_query("SELECT * FROM `$table`");
			if ($result) {
				$num_fields = mysql_num_fields($result);
			} else {
				$num_fields = 0;
			}
			
			$SQL .= "DROP TABLE IF EXISTS `$table`;";
			$row2 = mysql_fetch_row(mysql_query("SHOW CREATE TABLE `$table`"));
			$SQL .= "\n\n" . $row2[1] . ";\n\n";
			
			for ($i = 0; $i < $num_fields; $i++) {
				while ($row = mysql_fetch_row($result)) {
					$SQL .= "INSERT INTO `$table` VALUES(";
					for ($j = 0; $j < $num_fields; $j++) {
						$value = addslashes($row[$j]);
	//					$row[$j] = preg_replace("/\n/","\\n",$row[$j]);
	//					if (isset($row[$j])) { $SQL.= '"'.$row[$j].'"' ; } else { $SQL.= '""'; }
						$SQL .= (isset($value)) ? '"' . $value . '"' : '""';
						if ($j < ($num_fields - 1)) $SQL .= ',';
					}
					$SQL.= ");\n";
				}
			}
			$SQL.="\n\n\n";
		}
		
		// server name formatting
		$serverArray = explode(".", $this->serverIP);
		$server = "";
		foreach ($serverArray as $part) {
			$server .= str_pad((int)$part, 3, "0", STR_PAD_LEFT);
		}
		
		// variables
		$archiveName = date("Y\ym\md\dG\hi\ms\s") . "_{$server}_{$this->database}_backup.recondb.gz";
		$archivePath = $this->getBackupDir() . $archiveName;
		
		//
		$io = gzopen($archivePath, "w9");
		$bytes = gzwrite($io, $SQL);
		gzclose($io);
		
		//
		if ($bytes) {
			return $archiveName;
		} else {
			// failure
			return false;
		}
		
		$sqlName = $name . ".recondb";
		// temp phar archive path
		$pharPath = $this->getBackupDir() . $name . ".tar";
		// permanent archive name & path
		$archiveName = $sqlName . ".tar.gz";
		$archivePath = $this->getBackupDir() . $archiveName;
		
		// 
		$phar = new PharData($pharPath);
		$phar->addFromString($sqlName, $SQL);
		$pharData = $phar->compress(Phar::GZ, ".recondb.tar.gz");
		
		// housekeeping
		unset($phar);
		Phar::unlinkArchive($pharPath);
		
		if ($pharData) {
			return $archiveName;
		} else {
			// error
			return false;
		}
		
	}

	/**
	 * @name Export
	 * @todo contains random header output code
	 */
	public function export() {
		
		//
		// if (strlen($SQL) > 0) {
			// //
	    // header('Content-Description: Database Backup');
	    // header('Content-Type: application/octet-stream');
			// header('Content-Disposition: attachment; filename="'.$filename.'"');
	    // header('Content-Transfer-Encoding: binary');
	    // header('Expires: 0');
	    // header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	    // header('Pragma: public');
			// header("Content-Length: " . strlen($SQL));
			// echo $SQL;
			// //
			// return "Success!";
			// exit;
		// } else {
			// echo "No Content";
		// }
		
	}
		
	/**
	 * @name Install
	 * @return boolean
	 */
	public function install($filename) {
		
		//
		$maxBytes = 24 * 1024 * 1024;
		
		//
		$archivePath = $this->getBackupDir() . $filename;
		
		//
		$handle = gzopen($archivePath, "r");
		$SQL = gzread($handle, $maxBytes);
		gzclose($handle);
		
		//
		/* execute multi query */
		$link = mysqli_connect($this->host, $this->username, $this->password, $this->database, $this->port);
		if (mysqli_multi_query($link, $SQL)) {
			return true;
		} else {
			return false;
		}
		
	}
	
	//function mysqldump($user, $pass, $db) {
		//	$output = `mysqldump -u $user -p$pass $db > $filename`;
	//}
	
	// backupMySQL($host, $user, $pass, $name, $tables);
	
	/**
	 * @name Delete
	 */
	public function delete($filename) {
		$path = $this->getBackupDir() . $filename;
		return unlink($path);
	}
	
	/**
	 * File System Functions
	 */
	
	// 
	public function getBackups() {
		//
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getBackupDir()));
		//
		$return = array();
		while($it->valid()) {
	    if (!$it->isDot()) {
	    	//
	    	$filename = $it->getSubPathName();
				// $return .= 'SubPathName: ' . $it->getSubPathName() . "\n";
				// $return .= 'SubPath:     ' . $it->getSubPath() . "\n";
				// $return .= 'Key:         ' . $it->key() . "\n\n";
				// 
				// filename = NNNNyNNmNNdNNhNNiNNs_[database]_[transaction].sql.tar.gz
				// some creative array-use :)
				$parts = explode("_", $filename);
				// divide up
				$dateString = array_shift($parts);
				$hostString = array_shift($parts);
				$stat = array_pop($parts);
				$database = implode("_", $parts);
				// date
				$dateStringParts = preg_split("/\D/", $dateString);
				$timestamp = mktime((int)$dateStringParts[3], (int)$dateStringParts[4], (int)$dateStringParts[5], (int)$dateStringParts[1], (int)$dateStringParts[2], (int)$dateStringParts[0]);
				$date = date("Y-m-d", $timestamp);
				$time = date("H:i:s", $timestamp);
				// host
				$hostIP = "";
				for ($i = 0; $i < 4; $i++) {
					// add dot to all but the first element
					if ($hostIP) $hostIP .= ".";
					$hostIP .= (int)substr($hostString, ($i * 3), 3);
				}
				// info
				$info = explode(".", $stat);
				$type = array_shift($info);
				// $ext = array_shift($info);
				// $compression = implode(".", $info);
				$ext = implode(".", $info);
				// class object
	    	$temp = new stdClass();
				$temp->filename = $filename;
				$temp->date = $date;
				$temp->time = $time;
				$temp->server = $hostIP;
				$temp->database = $database;
				$temp->type = $type;
				$temp->extension = $ext;
	    	array_push($return, $temp);
	    }
			$it->next();
		}
		$return = array_reverse($return);
		return $return;
	}

	/**
	 * Special Accessors
	 */
	
	//
	public function getBackupDir() {
		return dirname(dirname(__FILE__)) . "/" . $this->backupDir;
	}
	
	//
	public function getBackupURL() {
		return $_SERVER["REQUEST_URI"] . $this->backupDir;
	}
	
	//
	public function validateIP() {
		// straight match - prioritise
		if ($this->serverIP != $this->database) return true;
		// then - check for localhost
		if ($this->serverIP == "127.0.0.1" && $this->host == "localhost") return true;
		// lastly - check for host name instead of address - does this work on localhost too?
		$hostIP = gethostbyname($this->host);
		if ($hostIP == $this->serverIP) return true;
		// otherwise
		return false;
		// bummer...
	}
	
}
//// End
?>
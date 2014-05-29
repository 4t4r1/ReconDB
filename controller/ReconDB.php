<?php

//// Class
class ReconDB {

	//// Variables

	// internal
	public $version = "&alpha;002";

	// config
	private $configFile = "config.dat"; // TODO Implement in another way ?
	private $configPath;
	// private $configHandle;
	private $configEnabled = false;
	// massages
	public $configMessages = array();

	// server
	private $serverType = "mysql"; // TODO Implement server types
	private $serverHost;
	private $serverPort;
	private $serverUsername;
	private $serverPassword;
	private $serverDatabase;
	private $serverSocket;
	// messages
	public $serverMessages = array();
	public $serverDatabaseMessages = array();

	// backup
	private $backupFolder = "model/";
	// messages
	public $backupMessages = array();

	// other
	private $serverTypes = array(
		"mysql" => "MySQL"
	);
	private $messageCodes = array(
		0 => "failure",
		1 => "success",
		2 => "warning",
		3 => "none"
	);

	//// CONSTANTS

	// messaging
	const FAIL = 0;
	const PASS = 1;
	const WARN = 2;
	const NONE = 3;


	/* Constructor & Destructor
	------------------------------------------------------------------------------*/

	/**
	 * @name Constructor
	 *
	 * @return void
	 */

	public function __construct() {

		// config path
		$this->configPath = dirname(__FILE__) . "/" . $this->configFile;
		if (file_exists($this->configPath)) {
			// if config
			if ($handle = @fopen($this->configPath, "r")) {

				// success
				fclose($handle);
				$this->message($this->configMessages, self::PASS, "Configuration loaded.");

			} else {

				// failure : fopen(r)
				$this->message($this->configMessages, self::FAIL, "Failed to access configuration file.");

			}
		} else {
			// if no config
			if ($handle = @fopen($this->configPath, "w")) {

				// write defaults
				$defaults = array("localhost", "3306", "root", null, null);
				fwrite($handle, implode("\n", $defaults));

				// success
				fclose($handle);
				$this->message($this->configMessages, self::WARN, "New configuration.");

			} else {
				// failure : fopen(w)
				$this->message($this->configMessages, self::FAIL, "Failed to create configuration file.");
			}

		}

		// read config into array
		$config = file($this->configPath);
		// initialise class variables
		$this->serverHost     = trim(@$config[0]);
		$this->serverPort     = trim(@$config[1]);
		$this->serverUsername = trim(@$config[2]);
		$this->serverPassword = trim(@$config[3]);
		$this->serverDatabase = trim(@$config[4]);
		//
		$this->configEnabled = true;

	}

	/**
	 * @name Destructor
	 *
	 * @return void
	 */

	public function __destruct() {

		// if ($this->configHandle) fclose($this->configHandle);

		// if ($this->serverSocket) $this->ServerSocket->close();

	}

	/* Special Functions
	------------------------------------------------------------------------------*/

	//
	public function serverIP() {
		return $_SERVER["SERVER_ADDR"];
	}

	//
	public function getBackupDir() {
		$dir = dirname(dirname(__FILE__)) . "/" . $this->backupFolder;
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		return $dir;
	}

	//
	public function getBackupURL() {
		return $_SERVER["REQUEST_URI"] . $this->backupFolder;
	}

	//
	public function message(&$array, $code, $message, $override = false) {

		// create object
		$object = new stdClass;
		$object->class = $this->messageCodes[$code];
		$object->content = $message;

		// override - clears previous messages
		if ($override) {
			// override
			$array = array($object);
		} else {
			// push
			array_push($array, $object);
		}

	}

	/* Configuration Functions
	------------------------------------------------------------------------------*/

	/**
	 * @name Write Server Configuration
	 *
	 * @param string $type Not used yet / placeholder
	 * @param string $host Server host
	 * @param string $port Server port
	 * @param string $password Server password
	 *
	 * @return bool Returns true if config file was written to, false if not
	 *
	 * @todo check for duplicate-value-rewrite
	 */

	public function configWriteServer($host, $port, $username, $password) {

		//
		$this->serverHost     = $host;
		$this->serverPort     = $port;
		$this->serverUsername = $username;
		$this->serverPassword = $password;
		//
		return $this->configWrite();

	}

	/**
	 * @name Write Database Configuration
	 *
	 * @return bool
	 */

	public function configWriteDatabase($database) {

		//
		$this->serverDatabase = $database;
		//
		return $this->configWrite();

	}

	/**
	 * @name Write Configuration
	 *
	 * @return bool
	 */

	public function configWrite() {

		// file handle
		$handle = fopen($this->configPath, "w");
		if ($handle) {
			// success
			fwrite($handle, "{$this->serverHost}\n{$this->serverPort}\n{$this->serverUsername}\n{$this->serverPassword}\n{$this->serverDatabase}");
			fclose($handle);
			// TODO Return
			// return true; ??
		} else {
			// failure : fopen()
			$this->message($this->configMessages, self::FAIL, "Failed to write configuration.", true);
			// TODO Return
			// return false; ??
		}

		//
		return $this->serverInit();

	}


	/* Configuration Template Functions
	------------------------------------------------------------------------------*/

	/**
	 * @name Get Config Status
	 *
	 * @return bool
	 */

	public function getConfigStatus() {
		return $this->configEnabled;
	}


	/* Server Functions
	------------------------------------------------------------------------------*/

	//
	public function serverInit() {

		// connect
		$this->serverSocket = @new mysqli($this->serverHost, $this->serverUsername, $this->serverPassword, null, (int)$this->serverPort);
		if (!$this->serverSocket->connect_errno) {
			// success
			$this->message($this->serverMessages, self::PASS, "Socket connected.");
			//
			if ($this->serverSocket->set_charset("utf8")) {
				if ($this->serverSocket->select_db($this->serverDatabase)) {
					// validate IP
					if (!$this->serverValidate()) {
						$this->message($this->serverMessages, self::WARN, "Server &amp; Database IPs differ.");
					}
					// success
					$this->message($this->serverDatabaseMessages, self::PASS, "Database selected.");
					$this->message($this->backupMessages, self::NONE, "You&apos;re currently using the &ldquo;<span class='success'>{$this->serverDatabase}</span>&rdquo; database.");
					return true;
				} else {
					// warning : select_db()
					$this->message($this->serverDatabaseMessages, self::WARN, "No database selected.");
					$this->serverDatabase = null;
					return true;
				}
			} else {
				// failure : set_charset()
				// TODO Error ??
				// TODO Message ??
			}
		} else {
			// failure : mysqli() / serverSocket
			$this->serverSocket = null;
			$this->message($this->serverMessages, self::FAIL, "Connection failed.");
		}
		return false;

	}

	/**
	 * @name Server Validate
	 *
	 * @return bool
	 */

	private function serverValidate() {

		// straight match - prioritise
		if ($this->serverHost == $this->serverIP()) return true;

		// then - check for localhost
		// if ($this->serverHost == "localhost" && $this->serverIP() == "127.0.0.1") return true;

		// lastly - check for host name instead of address - does this work on localhost too? - yes @ 29 May 2014.
		if (gethostbyname($this->serverHost) == $this->serverIP()) return true;

		// otherwise
		return false;
		// bummer...
	}


	/* Server Template Functions
	------------------------------------------------------------------------------*/

	/**
	 * @name Get Server Status
	 *
	 * @return bool
	 */

	public function getServerStatus() {
		return (bool)$this->serverSocket;
	}

	/**
	 * @name Get Server Database Status
	 *
	 * @return bool
	 */

	public function getServerDatabaseStatus() {
		return (bool)($this->getServerStatus() && (bool)$this->serverDatabase);
	}


	/* Server Helper Functions (Getters)
	------------------------------------------------------------------------------*/

	// TODO Implement server type
	// public function type() {
		// return $this->serverType;
	// }

	//
	public function getServerHost() {
		return $this->serverHost;
	}

	//
	public function getServerPort() {
		return $this->serverPort;
	}

	//
	public function getServerUsername() {
		return $this->serverUsername;
	}

	//
	public function getServerPassword() {
		return $this->serverPassword;
	}

	//
	public function getServerDatabase() {
		return $this->serverDatabase;
	}

	//
	public function getServerTypes() {
		return $this->serverTypes;
	}


	/* Server Database Template Functions
	------------------------------------------------------------------------------*/

	/**
	 * @name Has Server Databases
	 *
	 * @return bool
	 */

	public function hasServerDatabases() {

		//
		if ($this->serverSocket) {

			// mysql
			$result = $this->serverSocket->query("SHOW DATABASES");
			while ($row = $result->fetch_object()) {
				if ($row->Database != "information_schema") {
					// return on first file
					// TODO Expand checks
					return true;
				}
			}
		}

		// no valid files
		return false;

	}

	/**
	 * @name Get Server Databases
	 * Gets array of local database names
	 *
	 * @return array
	 */

	public function getServerDatabases() {

		//
		if ($this->serverSocket) {

			// initial
			$return = array("" => "Please select...");

			// mysql
			$result = $this->serverSocket->query("SHOW DATABASES");
			while ($row = $result->fetch_object()) {
				if ($row->Database != "information_schema") {
					$return[$row->Database] = $row->Database;
				}
			}

			// return databases
			return $return;
		} return false;

	}


	/* Backup Functions
	------------------------------------------------------------------------------*/

	/**
	 * @name Has Backups
	 *
	 * @return bool
	 */

	public function hasBackups() {

		//
		$dir = $this->getBackupDir();
	  if (!is_readable($dir)) return null;
	  $handle = opendir($dir);
	  while (false !== ($entry = readdir($handle))) {
	    if ($entry != "." && $entry != "..") {
	      return true;
	    }
	  }
	  return false;

	}

	/**
	 * @name Get Backups
	 *
	 * @return array
	 */

	public function getBackups() {
		//
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getBackupDir()));
		if (!$it->valid()) {
			// failure
			return false;
		}
		//
		$return = array();
		while($it->valid()) {
	    if (!$it->isDot()) {

				// filename = NNNNyNNmNNdNNhNNiNNs_[database]_[transaction].sql.tar.gz
	    	$filename = $it->getSubPathName();

				// some creative array-use :)
				$parts = explode("_", $filename);

				// date
				$dateString = array_shift($parts);
				$dateStringParts = preg_split("/\D/", $dateString);
				$timestamp = mktime((int)$dateStringParts[3], (int)$dateStringParts[4], (int)$dateStringParts[5], (int)$dateStringParts[1], (int)$dateStringParts[2], (int)$dateStringParts[0]);
				$date = date("Y-m-d", $timestamp);
				$time = date("H:i:s", $timestamp);

				// host
				$hostString = array_shift($parts);
				$hostIP = "";
				for ($i = 0; $i < 4; $i++) {
					// add dot to all but the first element
					if ($hostIP) $hostIP .= ".";
					$hostIP .= (int)substr($hostString, ($i * 3), 3);
				}

				// info
				$stat = array_pop($parts);
				$info = explode(".", $stat);
				$type = array_shift($info);
				// $ext = array_shift($info);
				// $compression = implode(".", $info);
				$ext = implode(".", $info);

				// database
				$database = implode("_", $parts);

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


	/* Controller Functions
	------------------------------------------------------------------------------*/

	/**
	 * @name Backup
	 *
	 * @return string || false
	 *
	 * @todo Impement selective tables
	 */

	function backup($selector = "*") {

		//
		$this->serverInit();
		$tables = array();

		//
		if ($this->serverSocket) {
			if ($selector == "*") {

				// all tables
				$result = $this->serverSocket->query("SHOW TABLES");
				while ($row = $result->fetch_row()) {
					$table = $row[0];
					if ($table != "information_schema") {
						$tables[] = $table;
					}
				}
			} else {
				// selective tables
				// convert to array if CSV string
				$tables = is_array($tables) ? $tables : explode(',', $tables);
			}

		}

		$SQL = "";
		// cycle through
		foreach ($tables as $table) {

			// drop
			$SQL .= "DROP TABLE IF EXISTS `$table`;";

			// create
			$create = $this->serverSocket->query("SHOW CREATE TABLE `$table`");
			$script = $create->fetch_row();
			$SQL .= "\n\n" . $script[1] . ";\n\n";

			// values
			$result = $this->serverSocket->query("SELECT * FROM `$table`");
			$num_fields = $result->field_count;
			// loop over columns
			for ($i = 0; $i < $num_fields; $i++) {
				while ($row = $result->fetch_row()) {
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
		$serverArray = explode(".", $this->serverIP());
		$server = "";
		foreach ($serverArray as $part) {
			$server .= str_pad((int)$part, 3, "0", STR_PAD_LEFT);
		}

		// variables
		$archiveName = date("Y\ym\md\dG\hi\ms\s") . "_{$server}_{$this->serverDatabase}_backup.recondb.gz";
		$archivePath = $this->getBackupDir() . $archiveName;

		// compress
		$io = gzopen($archivePath, "w9");
		$bytes = gzwrite($io, $SQL);
		gzclose($io);

		// return
		if ($bytes) {
			// success
			$this->message($this->backupMessages, self::PASS, "Successfully backed up.", true);
			return $archiveName;
		} else {
			// failure
			$this->message($this->backupMessages, self::FAIL, "Back-up failed.", true);
			return false;
		}

	}

	////

	//function mysqldump($user, $pass, $db) {
		//	$output = `mysqldump -u $user -p$pass $db > $filename`;
	//}

	/**
	 * @name Export
	 *
	 * @return void
	 *
	 * @todo contains random header output code (for now)
	 */

	// public function export() {

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

	// }

	/**
	 * @name Install
	 *
	 * @return boole
	 */

	public function install($filename) {

		// init server
		$this->serverInit();

		// get SQL data
		$maxBytes = 24 * 1024 * 1024;
		$archivePath = $this->getBackupDir() . $filename;
		$handle = gzopen($archivePath, "r");
		$SQL = gzread($handle, $maxBytes);
		gzclose($handle);

		// sql multi-query
		if ($this->serverSocket->multi_query($SQL)) {
			do {
				/* store first result set */
				if ($result = $this->serverSocket->store_result()) {
					// while ($row = $result->fetch_row()) {
						// printf("%s\n", $row[0]);
					// }
					$result->free();
				}
			} while ($this->serverSocket->next_result());
			// success
			$this->message($this->backupMessages, self::PASS, htmlentities("Backup successfully installed."), true);
			return true;
		} else {
			// failure : multi_query()
			$this->message($this->backupMessages, self::FAIL, htmlentities("Backup installation failed: [{$this->serverSocket->errno}] {$this->serverSocket->error}"), true);
			return false;
		}

	}

	/**
	 * @name Delete
	 *
	 * @return bool
	 */

	public function delete($filename) {

		//
		$this->serverInit();

		// unlink if exists
		$path = $this->getBackupDir() . $filename;
		if (file_exists($path)) {
			if (unlink($path)) {
				// success
				$this->message($this->backupMessages, self::PASS, htmlentities("File successfully deleted."), true);
				return true;
			} else {
				// failure : unlink()
				$this->message($this->backupMessages, self::FAIL, htmlentities("Failed to delete file."), true);
			}
		} else {
			// failure : file_exists()
			$this->message($this->backupMessages, self::WARN, htmlentities("File not found."), true);
		}

		// default
		return false;

	}

}
//// End
?>
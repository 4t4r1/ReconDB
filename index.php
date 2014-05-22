<?php

	/**
	 * INI
	 */
	ini_set ('magic_quotes_gpc', 0);
	ini_set('date.timezone', 'Europe/London');
	// ini_set( 'upload_max_size' , '16M');
	// ini_set( 'post_max_size', '16M');
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	//
	ini_set('phar.readonly', 0);

	/**
	 * Includes
	 */
	include_once("controller/ReconDB.php");
	
	/**
	 * Initialize Classes
	 */	
	$recon = new ReconDB();
	
	// local messaging
	$contentClass = null;
	$content = htmlentities("Initialising...");
	
	//
	if ($recon->configEnabled()) {
		
		/**
		 * POST Controller
		 */
		
		// print_r($_POST);
		//
		$action = @$_POST["action"];
		// if ($action == null) $action = @$_GET["action"];
		//
		switch ($action) {
			case "server":
				// POST variables
				// $type = @$_POST["type"];
				$host = @$_POST["host"];
				$port = @$_POST["port"];
				$username = @$_POST["username"];
				$password = @$_POST["password"];
				$database = @$_POST["database"];
				//
				if ($recon->dataTest($host, $port, $username, $password)) {
					// remember: $type
					// success
					if ($recon->configWrite($host, $port, $username, $password, $database)) {
						// success
						$recon->dataConnect();
					} else {
						// failure
						echo "Fata Error : configWrite() fail.";
						exit;
					}
				} else {
					// failure
					echo "Fata Error : dataTest() fail.";
					exit;
				}
				//
				break;
			// case "import":
				// //
				// $content = "I can't do that yet :(";
				// $contentClass = "failure";
				// break;
			// case "export":
				// //
				// $content = "I can't do that yet :(";
				// $contentClass = "failure";
				// break;
			case "backup":
				//
				$filename = $recon->backup();
				//
				$content = htmlentities("I've just backed up, would you like to");
				$content .= " <a href='{$recon->getBackupURL()}$filename'>download the file?</a>";
				$contentClass = "success";
				break;
			// case "implode":
				// //
				// $content = "I've just emploded the database - yeehaa!,)";
				// $contentClass = "success";
				// break;
			// case "destroy":
				// //
				// $content = "You just tried to kill me, biatch!";
				// $contentClass = "alert";
				// break;
			case "install":
				//
				$filename = @$_POST["filename"];
				if ($filename) {
					if ($recon->install($filename)) {
						$content = htmlentities("I have just installed $filename");
						$contentClass = "success";
					} else {
						$content = htmlentities("I failed to install $filename");
						$contentClass = "failure";
					}
				} else {
					// no filename
					$content = htmlentities("I can't find the file you asked me to install..?");
					$contentClass = "alert";
				}
				break;
			// case "download":
				//
				// break;
			case "delete":
				//
				$filename = @$_POST["filename"];
				if ($filename) {
					if ($recon->delete($filename)) {
						$content = htmlentities("I've just deleted $filename");
						$contentClass = "success";
					} else {
						$content = htmlentities("I failed to delete $filename");
						$contentClass = "failure";
					}
				} else {
					// no filename
					$content = htmlentities("I can't find the file you asked me to delete..?");
					$contentClass = "alert";
				}
				//
				break;
			case "database":
				// POST variables
				$database = @$_POST["database"];
				//
				if ($recon->configWriteDB($database)) {
					// success
					$recon->dataConnect();
				} else {
					// failure
					echo "Fata Error : configWriteDB() fail.";
					exit;
				}
				// only break if no database - otherwise switch to default
				if (!$database) break;
				// break;
			default:
				//
				$contentClass = null;
				$content = "You&apos;re currently using the &ldquo;{$recon->database()}&rdquo; database.";
				break;
		}
		
	}
	
?>

<!doctype html>
<html lang="en">
	
	<head>
		<meta charset="utf-8">
		<title>Index | ReconDB</title>
		<link type="text/css" href="view/css/normalise.css" rel="stylesheet"></link>
		<link type="text/css" href="view/css/main.css" rel="stylesheet"></link>
	</head>
	
	<body>
		
		<div class="menu">

		<h1>ReconDB <?php echo $recon->version; ?></h1>
		<h2>Server IP : <?php echo $recon->serverIP; ?></h2>
		<p class="message <?php echo $recon->getConfigStatus(); ?>"><?php echo $recon->getConfigMessage(); ?></p>
		
		<h2>Server Connection</h2>
		<div class="view">
			<form name="server" action="" method="post">
				<input type="hidden" name="action" value="server" />
				<input type="hidden" name="database" value="<?php echo $recon->database(); ?>" />
				<ul class="menu clear">
					<li class="field">
						<label for="type">Type :</label>
						<select name="type" disabled style="color: #fff;">
<?php foreach ($recon->types() as $key => $value): ?>
							<option value="<?= $key; ?>"><?= $value; ?></option>
<?php endforeach; ?>
						</select>
					</li>
					<li class="field">
						<label for="host">Host :</label>
						<input name="host" type="text" value="<?= $recon->host(); ?>" size="18" placeholder="localhost" />
					</li>
					<li class="field">
						<label for="port">Port :</label>
						<input name="port" type="text" value="<?= $recon->port(); ?>" size="6" placeholder="3306" />
					</li>
					<li class="field">
						<label for="username">Username :</label>
						<input name="username" type="text" value="<?= $recon->username(); ?>" />
					</li>
					<li class="field">
						<label for="password">Password :</label>
						<input name="password" type="password" value="<?= $recon->password(); ?>" />
					</li>
					<li class="action">
						<input class="button" name="submit" type="submit" value="connect to server" />
					</li>
				</ul>
			</form>
		</div>
<?php if (!$recon->validateIP()): ?>
		<p class="message alert">Server &amp; Database IPs differ.</p>
<? endif; ?>
		<p class="message <?php echo $recon->getDataStatus(); ?>"><?php echo $recon->getDataMessage(); ?></p>
		
<?php if ($recon->dataConnected()): ?>
		<h2>Database</h2>
		<div class="view">
			<form name="database" action="" method="post">
				<input type="hidden" name="action" value="database" />
				<ul class="horizontal clear">
					<li class="field">
						<label for="database">Database :</label>
						<select name="database">
<?php foreach ($recon->fetchDatabases() as $key => $database): ?>
							<option value="<?= $key; ?>" <?php if ($database == $recon->database()) echo "selected=selected"; ?>><?= $database; ?></option>
<?php endforeach; ?>
						</select>
						<!-- <input name="database" type="text" value="<?= $recon->database(); ?>" /> -->
					</li>
					<li class="action">
						<input class="button" name="submit" type="submit" value="change database" />
					</li>
				</ul>
			</form>
		</div>
		<p class="message <?php echo $recon->getSelectedStatus(); ?>"><?php echo $recon->getSelectedMessage(); ?></p>
<? endif; ?>

		</div>
		<div class="display">

<? if ($recon->dataSelected()): ?>
		<!-- menu -->
		<h2>Actions</h2>
		<div class="view">
			<!-- <h1>Menu</h1> -->
			<ul class="horizontal">
				<!-- <li>
					<form name="import" action="" method="post">
						<input type="hidden" name="action" value="import">
						<input class="button" name="submit" type="submit" value="Import" />
					</form>
				</li>
				<li>
					<form name="export" action="" method="post">
						<input type="hidden" name="action" value="export">
						<input class="button" name="submit" type="submit" value="Export" />
					</form>
				</li> -->
				<li>
					<form name="backup" action="" method="post">
						<input type="hidden" name="action" value="backup">
						<input class="button" name="submit" type="submit" value="Back-up" onclick="return confirmGeneric();" />
					</form>
				</li>
				<!-- <li>
					<form name="implode" action="" method="post">
						<input type="hidden" name="action" value="implode">
						<input class="button" name="submit" type="submit" value="Implode" onclick="return confirmImplode();" />
					</form>
				</li>
				<li>
					<form name="destroy" action="" method="post">
						<input type="hidden" name="action" value="destroy">
						<input class="button" name="submit" type="submit" value="Destroy" onclick="return confirmDestroy();" />
					</form>
				</li> -->
			</ul>
		</div>
		<p class="<?php echo $contentClass; ?>"><?php echo $content; ?></p>
		<br />
		<!-- history -->
	<?php
		$backups = $recon->getBackups();
		if ($backups):
	?>
		<h2>Local Database Back-ups</h2>
		<div class="view nopad">
			<table class="clear">
					<tr>
						<th>date</th>
						<th>time</th>
						<th>server</th>
						<th>database</th>
						<th>type</th>
						<th>extension</th>
						<th>actions</th>
					</tr>
		<?php foreach ($backups as $backup): ?>
				<tr>
					<td><? echo $backup->date; ?></td>
					<td><? echo $backup->time; ?></td>
					<td><? echo $backup->server; ?></td>
					<td class="<?php if ($backup->database == $recon->database()) echo "success"; ?>"><? echo $backup->database; ?></td>
					<td><? echo $backup->type; ?></td>
					<td><? echo $backup->extension; ?></td>
					<td class="actions">
						<form name="install" action="" method="post">
							<input type="hidden" name="action" value="install" />
							<input type="hidden" name="filename" value="<?php echo $backup->filename; ?>" />
							<input type="submit" name="submit" value="install" onclick="return confirmInstall();" />
						</form>
						<a class="action" href="<?php echo $recon->getBackupURL() . $backup->filename; ?>">download</a>
						<form name="delete" action="" method="post">
							<input type="hidden" name="action" value="delete" />
							<input type="hidden" name="filename" value="<?php echo $backup->filename; ?>" />
							<input type="submit" name="submit" value="delete" onclick="return confirmDelete();" />
						</form>
						<!-- <a class="action" href="?action=install&filename=<?php echo $backup->filename; ?>" onclick="return confirmInstall();">install</a>
						<a class="action" href="?action=delete&filename=<?php echo $backup->filename; ?>" onclick="return confirmDelete();">delete</a> -->
					</td>
				</tr>
		<?php endforeach; ?>
			</table>
	<?php else: ?>
		<h2 class="alert">No Local Database Back-ups</h2>
	<?php endif; ?>
		</div>
<script type="text/javascript">
//
function confirmGeneric() {
	return (confirm("You sure?"));
}
//
function confirmImplode() {
	return (confirm("You sure?\n\nThis will clear the existing database entirely!"));
}
//
function confirmDestroy() {
	return (confirm("You sure?\n\nThis will completely remove this tool, but leave the database intact."));
}
//
function confirmInstall() {
	return (confirm("Are you sure?\n\nThis will clear the exising database and replace it with this file."));
}
//
function confirmDelete() {
	return (confirm("Are you sure?\n\nThis will irretrievably delete this file."));
}
</script>
<?php endif; ?>

		</div>

	</body>
	
</html>
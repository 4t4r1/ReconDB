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
	// ini_set('phar.readonly', 0);

	/**
	 * Includes
	 */

	include_once("controller/ReconDB.php");

	/**
	 * Initialize Classes
	 */

	$recon = new ReconDB();

	//
	if ($recon->getConfigStatus()) {

		/**
		 * POST Controller
		 */

		//
		// print_r($_POST);
		$action = @$_POST["action"];
		//
		switch ($action) {

			case "server":
				// POST variables
				// $type = @$_POST["type"]; // TODO Implement server type
				$host = @$_POST["host"];
				$port = @$_POST["port"];
				$username = @$_POST["username"];
				$password = @$_POST["password"];
				// write
				$recon->configWriteServer($host, $port, $username, $password);
				break;

			case "database":
				// POST variables
				$database = @$_POST["database"];
				//
				$recon->configWriteDatabase($database);
				break;

			// case "import":
				// break;
			// case "export":
				// break;

			case "backup":
				//
				$filename = $recon->backup();
				break;

			// case "implode":
				// break;
			// case "destroy":
				// break;

			case "install":
				//
				$filename = @$_POST["filename"];
				$recon->install($filename);
				break;

			// case "download":
				// break;

			case "delete":
				//
				$filename = @$_POST["filename"];
				$recon->delete($filename);
				break;

			default:
				//
				$recon->serverInit();
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

			<!-- Config -->
			<h1>ReconDB <?php echo $recon->version; ?></h1>
			<h2>Server IP : <?php echo $recon->serverIP(); ?></h2>
<?php foreach ($recon->configMessages as $message): ?>
			<p class="message <?php echo $message->class; ?>"><?php echo $message->content; ?></p>
<?php endforeach; ?>

			<!-- Server -->
			<h2>Server Connection</h2>
			<div class="view">
				<form name="server" action="" method="post">
					<input type="hidden" name="action" value="server" />
					<input type="hidden" name="database" value="<?php echo $recon->getServerDatabase(); ?>" />
					<ul class="menu clear">
						<li class="field">
							<label for="type">Type :</label>
							<select name="type" disabled style="color: #fff;">
<?php foreach ($recon->getServerTypes() as $key => $value): ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
<?php endforeach; ?>
							</select>
						</li>
						<li class="field">
							<label for="host">Host :</label>
							<input name="host" type="text" value="<?php echo $recon->getServerHost(); ?>" size="18" placeholder="localhost" />
						</li>
						<li class="field">
							<label for="port">Port :</label>
							<input name="port" type="text" value="<?php echo $recon->getServerPort(); ?>" size="6" placeholder="3306" />
						</li>
						<li class="field">
							<label for="username">Username :</label>
							<input name="username" type="text" value="<?php echo $recon->getServerUsername(); ?>" placeholder="root" />
						</li>
						<li class="field">
							<label for="password">Password :</label>
							<input name="password" type="password" value="<?php echo $recon->getServerPassword(); ?>" />
						</li>
						<li class="action">
							<input class="button" name="submit" type="submit" value="connect to server" />
						</li>
					</ul>
				</form>
			</div>
<?php foreach ($recon->serverMessages as $message): ?>
			<p class="message <?php echo $message->class; ?>"><?php echo $message->content; ?></p>
<?php endforeach; ?>

			<!-- Database -->
<?php if ($recon->hasServerDatabases()): ?>
			<h2>Database</h2>
			<div class="view">
				<form name="database" action="" method="post">
					<input type="hidden" name="action" value="database" />
					<ul class="horizontal clear">
						<li class="field">
							<label for="database">Database :</label>
							<select name="database">
	<?php foreach ($recon->getServerDatabases() as $key => $database): ?>
								<option value="<?= $key; ?>" <?php if ($database == $recon->getServerDatabase()) echo "selected=selected"; ?>><?= $database; ?></option>
	<?php endforeach; ?>
							</select>
						</li>
						<li class="action">
							<input class="button" name="submit" type="submit" value="change database" />
						</li>
					</ul>
				</form>
			</div>
	<?php foreach ($recon->serverDatabaseMessages as $message): ?>
			<p class="message <?php echo $message->class; ?>"><?php echo $message->content; ?></p>
	<?php endforeach; ?>
<?php endif; ?>

		</div>
		<div class="display">

<?php if ($recon->getServerDatabaseStatus()): ?>
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
				<li>
					<form name="refresh" action="" method="post">
						<input type="hidden" name="action" value="refresh">
						<input class="button" name="submit" type="submit" value="Refresh" />
					</form>
				</li>
			</ul>
		</div>
	<?php foreach ($recon->backupMessages as $message): ?>
			<p class="message <?php echo $message->class; ?>"><?php echo $message->content; ?></p>
	<?php endforeach; ?>

		<br />

		<!-- history -->
		<h2>Local Database Back-ups</h2>
		<p><?php echo $recon->getBackupDir(); ?></p>
	<?php if ($recon->hasBackups()): ?>
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
		<?php foreach ($recon->getBackups() as $backup): ?>
				<tr>
					<td><? echo $backup->date; ?></td>
					<td><? echo $backup->time; ?></td>
					<td><? echo $backup->server; ?></td>
					<td class="<?php if ($backup->database == $recon->getServerDatabase()) echo "success"; ?>"><? echo $backup->database; ?></td>
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
					</td>
				</tr>
		<?php endforeach; ?>
			</table>
	<?php else: ?>
		<p class="alert">No back-ups found.</p>
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
<?	$starttime = time();
	require("class.progressbar.php");
	require("gallery.php");
	
	$event = new Event($_REQUEST["event"]);
	if (!$event->hasMissingThumbs()) {
		header("Location: index.php?event=" . urlencode($_REQUEST["event"]));
		exit();
	}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
	<title>Photo Gallery Events List</title>
	<link rel="stylesheet" href="styles.css" type="text/css" />
	<meta http-equiv="refresh" content="30;url=index.php?event=<?= $_REQUEST["event"] ?>" />
</head>

<body>
	<h1><?= $event->Title; ?></h1>
<?	// Check to see if the directory is writeable...
	if (!$event->correctPermissions()) {
		die("Cannot generate the thumbnails for this directory as either the directory or some of the images in it are not world-writeable!");
	}
?>
	Please wait while the thumbnails for this page are generated...<br />

<?	$prb = new ProgressBar(200, 40);
	$prb->left = 50;
	$prb->top = 80;
	$prb->min = 0;
	$prb->max = $event->missingThumbCount();
	$prb->drawHtml();
	ob_flush();
	
	// Loop through the photos, causing each one to generate it's own thumbnail
	$count = 0;
	$maxtime = ini_get("max_execution_time");
//	die("max time is $maxtime");
	$t1 = time();
	$tdiff = $t1 - $starttime;
	$t2 = 0;
	foreach($event->Photos as $photo) {
		if ($photo->makeThumbnail());		// Will do nothing if thumb already exists
		$prb->moveIt(++$count);				// Increase the progress bar
		ob_flush();
		$t2 = time();
		if ((((($t2 - $t1) / $count) * ($count + 1)) + $tdiff) > $maxtime) {
?>
	<!-- About to timeout, inserting refresh script... -->
	<script type="text/javascript">
	<!--
		window.location.href = window.location.href;
	// -->
	</script>
<?		}
	}
	
?>
	<script type="text/javascript">
	<!--
		// Redirect them as we've finished...
		window.location.href = "index.php?event=<?= $_REQUEST["event"] ?>";
	// -->
	</script>
</body>
</html>
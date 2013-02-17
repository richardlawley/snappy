<?	require("gallery.php");		

	if (isset($_POST["cmdNewDir"])) {
		if (strlen($_POST["txtNewDir"]) > 0) {
			mkdir($_POST["txtNewDir"], 0777);
		}
	}
/********************************************************************************/
	if (!isset($_REQUEST["event"]) && !isset($_REQUEST["photo"])) {
		$page = file_get_contents("rootindex.template");
		$page = str_replace("%GALLERYNAME%", $gallery->Name, $page);
		$page = str_replace("%EVENTLIST%", $gallery->getEventList(), $page);
		
	} else if (isset($_REQUEST["event"]) && !isset($_REQUEST["photo"])) {
/********************************************************************************/
		$event = new Event($_REQUEST["event"]);
		if ($event->hasMissingThumbs()) {
			header("Location: createthumbnails.php?event=" . urlencode($_REQUEST["event"]));
			exit();
		}
		
		$page = file_get_contents("eventindex.template");
		$page = str_replace("%EVENTNAME%", $event->Title, $page);
		$page = str_replace("%NAVLINKS%", $event->getIndexLinks(), $page);
		$page = str_replace("%THUMBLIST%", $event->getThumbnails(), $page);
		$page = str_replace("%DESCRIPTION%", $event->getDescription(), $page);
		
	} else if (isset($_REQUEST["event"]) && isset($_REQUEST["photo"])) {	
/********************************************************************************/
		$event = new Event($_REQUEST["event"]);
		$photo = $event->Photos[$_REQUEST["photo"]];

		if (isset($photo->Prev)) $prevthumb = $photo->Prev->getThumbnailHTML();
		else $prevthumb = "";
		if (isset($photo->Next)) $nextthumb = $photo->Next->getThumbnailHTML();
		else $nextthumb = "";
		$fslink = "";
		$fsize = "";
		if ($photo->hasFullSize()) {
			$fslink = "<a href=\"" . $photo->getFullSize() . "\">Full Size Version</a>";
			$fsize = @filesize($photo->getFullSize());
			if ($fsize) {
				$fsize /= 1024;
				$fsize = "(" . number_format($fsize, 0) . "k)";
			} else $fsize = "";
		}

		$page = file_get_contents("photopage.template");
		$page = str_replace("%EVENTNAME%", $event->Title, $page);
		$page = str_replace("%NAVLINKS%", $event->getIndexLinks(), $page);
		$page = str_replace("%PREVTHUMB%", $prevthumb, $page);
		$page = str_replace("%NEXTTHUMB%", $nextthumb, $page);
		$page = str_replace("%PHOTO%", "<img src=\"" . $photo->getFilename() . "\" />", $page);
		$page = str_replace("%FSLINK%", $fslink, $page);
		$page = str_replace("%FSSIZE%", $fsize, $page);
		
	} else {
		$page = "";

	}
	

	// If you need a common header file, include it here
	// include("header.inc.php");
	echo $page;

	// If you need a common footer file, include it here
	// include("footer.inc.php");
	

?>
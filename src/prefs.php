<?php

	/// This is the title for the gallery that will be displayed on the main index page
	$gallery->Name = "Photo Gallery";

	/// This is the template used to display the event title.  Supported variables are:
	///		%URL%		The page that displays the photos for this event
	///		%NAME%		The name of this event
	///		%NUMPHOTOS%	Number of photos in this event
	$gallery->EventTemplate = "<div class=\"thumbnail\"><a href=\"%URL%\"><img border=\"0\" src=\"folder.gif\" alt=\"%NAME%\"><br />%NAME% (%COUNTINCPHOTOS%)</a></div>";
	
	/// This is the template used to display the thumbnails on the page for each event
	///		%URL%		URL of the page to display this photo
	///		%THUMB%		Thumbnail for this photo
	///		%CAPTION%	Caption for this photo
	///		%NAME%		Filename of this photo
	///		%VIEWCOUNT%	Number of times this photo has been viewed
	$gallery->ThumbTemplate = "<div class=\"thumbnail\"><a href=\"%URL%\"><img border=\"0\" src=\"%THUMB%\" alt=\"%CAPTION%\"><br />%NAME%</a></div>";
	
	/// Maximum size of the photos
	$gallery->MaxWidth = 640;
	$gallery->MaxHeight = 640;
?>
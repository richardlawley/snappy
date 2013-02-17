<?	
/*******************************************************************************
 * Snappy: Simple PHP Photo Gallery with automatic resizing and thumbnail generation
 * Copyright (C) 2003 Richard Lawley
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 ******************************************************************************/

	session_start();		// Start a session- we want to save some details

	/**
	 * The PhotoGallery class gets the initial list of events (directories) from the main root,
	 * and stores some information about templates and names.
	 */
	class PhotoGallery {
		var $EventTemplate;		// Template used for events
		var $ThumbTemplate;		// Template used for thumbnails
		var $PhotoTemplate;		// Template used for photos
		var $MaxWidth;			// Maximum width of photos
		var $MaxHeight;			// Maximum height of photos
		var $Name;
		
		/**
		 * Gets the list of events available from the base directory of this gallery
		 */
		function getEventList() {
			$events = array();
			$dirs = array();
			$ret = "";

			
			// Look through all subdirectories, getting the name for each one...
			$d = opendir(".");
			while (false !== ($file = readdir($d))) {
				// Check to see if it's a dir...
				if (is_dir($file) && (substr($file, 0, 1) != ".")) array_push($dirs, $file);
			}
			closedir($d);
			sort($dirs);
			foreach($dirs as $dir) {
				$event = new Event($dir);
				$ret .= $event->getThumbnailHTML() . "\n";
			}

			return $ret;
		}
	}

	/**
	 * The Event class encapsulates an event (directory) of the gallery.  When initialised it loads the details
	 * including the photos available.  It contains agregate functions that operate over all of the photos
	 */
	class Event {
		var $BasePath = "";
		var $Dir;
		var $Events;
		var $Photos;
		var $Title;
		
		/**
		 * Loads all of the photos in this event
		 */
		function Event($path) {
			global $gallery;
			
			// Split the path up if it contains any /s
			$pathparts = split("/", $path);
			if (sizeof($pathparts) == 0) die("Path not set");
			else {
				$this->Dir = array_pop($pathparts);
				$this->BasePath = join("/", $pathparts);
			}
			
			// Read all the photos and dirs into arrays
			$files = array();
			$dirs = array();
			$this->Photos = array();
			$this->Events = array();
			$dir = @opendir($this->getFullPath());
			while (false !== ($file = readdir($dir))) {
				$fn = $this->getFullPath() . "/" . $file;
				if (is_file($fn) && eregi("\.jpg$", $file) && !eregi("\.thumb\.jpg$", $file) && !eregi("\.full\.jpg$", $file)) array_push($files, $file);
				else if (is_dir($fn) && !eregi("^\.", $file)) array_push($dirs, $file);
			}
			closedir($dir);
			
			// Sort the arrays
			sort($files);
			sort($dirs);
			
			// Make event and photo objects from the names
			foreach($dirs as $dir) {
				$newpath = $dir;
				$this->Events[$dir] = new Event($this->getFullPath() . "/" . $dir);
			}
			foreach($files as $file) {
				$this->Photos[$file] = new Photo($this, $file);
				if (isset($prev)) {
					$this->Photos[$file]->Prev = &$prev;
					$prev->Next = &$this->Photos[$file];
				}
				$prev = &$this->Photos[$file];
			}
			
			// Initialise the event...
			if (file_exists($this->getFullPath() . "/title.txt")) $this->Title = file_get_contents($this->getFullPath() . "/title.txt");
			else $this->Title = $this->Dir;
		}
		
		/**
		 * Renders this event as a thumbnail on an index page
		 */
		function getThumbnailHTML() {
			global $gallery;
			
			$out = $gallery->EventTemplate;
			$out = str_replace("%URL%", "index.php?event=" . urlencode($this->getFullPath()), $out);
			$out = str_replace("%NAME%", "" . $this->Title, $out);
			$out = str_replace("%COUNTINCPHOTOS%", sizeof($this->Photos) . " photo" . (sizeof($this->Photos)>1?"s":""), $out);
			$out = str_replace("%COUNT%", sizeof($this->Photos), $out);
			
			return $out;
		}
		
		/**
		 * Determines whether all files in the directory have the correct permissions
		 */
		function correctPermissions() {
			if (!is_writeable($this->getFullPath())) return false;
			foreach($this->Photos as $photo) {
				if (!is_writeable($photo->getFilename())) return false;
			}
			
			return true;
		}
		

		/**
		 * Generates the links for the index line
		 */
		function getIndexLinks($sep = " : ") {
			$sPath = $this->Dir;
			if ($this->BasePath != "") $sPath = $this->BasePath . "/$sPath";
			$sThis = "<a href=\"index.php?event=" . urlencode($sPath) . "\">" . $this->Title . "</a>";
			if ($this->BasePath == "") return $sThis;
			else {
				$par = new Event($this->BasePath);
				return $par->getIndexLinks() . $sep . $sThis;
			}
		}
		
		/**
		 * Checks all of the photos in this event for missing thumbnails
		 */
		function hasMissingThumbs() {
			foreach($this->Photos as $photo) {
				if (!$photo->hasThumbnail()) return true;
			}
			return false;
		}
		
		/**
		 * Gets the full path of this event relative to the gallery root 
		 */
		function getFullPath() {
			if ($this->BasePath == "") return $this->Dir;
			else return $this->BasePath . "/" . $this->Dir;
		}
		
		/**
		 * Determines the number of photos that need thumbnails making
		 */
		function missingThumbCount() {
			$count = 0;
			foreach($this->Photos as $photo) {
				if (!$photo->hasThumbnail()) $count++;
			}
			return $count;
		}
		
		/**
		 * Generates some output for listing the thumbnails in an event
		 */
		function getThumbnails() {
			global $gallery;
		
			if ($this->hasMissingThumbs()) echo "Some thumbnails are missing...";
		
			// Display the directories...
			foreach($this->Events as $event) {
				$ret .= $event->getThumbnailHTML();
			}
			
			// Now the photos...
			foreach($this->Photos as $key=>$photo) {
				$ret .= $photo->getThumbnailHTML();
			}

			return $ret;		
		}
		
		function getPhoto($filename) {
			return new Photo($this, $filename);
		}
		
		/**
		 * Loads the description file for this event and converts it to HTML
		 */
		function getDescription() {
			if (file_exists($this->getFullPath() . "/description.txt")) {
				$file = file_get_contents($this->getFullPath() . "/description.txt");
				
				$file = str_replace("\r\n", "<br />", $file);
				$file = str_replace("\n", "<br />", $file);
				
				return $file;
			} else return "";
		}
	}
	
	/**
	 * The Photo class encapsulates all of the operations regarding a single photo.  Photos are jpeg images not ending in 
	 * .thumb.jpg or .full.jpg.  These extra files are used to represent thumbnail and fullsize images respectively.  
	 * Assuming this is not prevented by the host system, thumbnails and resized images can be generated automatically
	 */
	class Photo {
		var $Event;
		var $Path;
		var $Prev;
		var $Next;
		
		/**
		 * Constructor for the Photo class- stores information locally
		 */
		function Photo(& $event, $path) {
			$this->Event = &$event;
			$this->Path = $path;
		}
		
		/**
		 * Returns the path relative to the gallery root to the main (web-sized) image
		 */
		function getFilename() {
			return $this->Event->getFullPath() . "/" . $this->Path;
		}
		
		/**
		 * Returns the path relative to the gallery root to the thumbnail image
		 */
		function getThumbnail() {
			return $this->Event->getFullPath() . "/" . str_replace(".jpg", ".thumb.jpg", strtolower($this->Path));
		}
		
		/**
		 * Returns the path relative to the gallery root to the fullsize image
		 */
		function getFullSize() {
			return $this->Event->getFullPath() . "/" . str_replace(".jpg", ".full.jpg", strtolower($this->Path));
		}
		
		/**
		 * Determines whether this photo has a corresponding thumbnail image
		 */
		function hasThumbnail() {
			return file_exists($this->getThumbnail());
		}
		
		/**
		 * Determines whether this photo has a corresponding fullsize image
		 */
		function hasFullSize() {
			return file_exists($this->getFullSize());
		}
		
		/**
		 * To be Implemented: Return the caption of this photo
		 */
		function getCaption() {
			return "";
		}
		
		/**
		 * To be Implemented: Returns the number of times this photo has been viewed
		 * @param $bIncrease	Should the count be increased at the same time?
		 */
		function getViewCount($bIncrease) {
			return -1;
		}
		
		/**
		 * Produces the HTML based on a template to represent this photo in a thumbnail list
		 */
		function getThumbnailHTML() {
			global $gallery;
			
			$out = $gallery->ThumbTemplate;
			$out = str_replace("%URL%", "index.php?event=" . urlencode($this->Event->getFullPath()) . "&photo=" . urlencode($this->Path), $out);
			$out = str_replace("%CAPTION%", $this->getCaption(), $out);
			$out = str_replace("%THUMB%", $this->getThumbnail(), $out);
			$out = str_replace("%NAME%", $this->Path, $out);

			return $out . "\n";
		}
		
		/**
		 * Creates a thumbnail for this photo if one doesn't already exist
		 */
		function makeThumbnail() {
			// Also resize the image...
			$this->resizeImage();
			
			if (!$this->hasThumbnail()) {
				$thumb = getThumbnail($this->getFilename());
				ImageJPEG($thumb, $this->getThumbnail());
				ImageDestroy($thumb);
			}
		}
		
		/**
		 * Resizes this image to the maximum sizes defined by the preferences
		 */
		function resizeImage() {
			global $gallery;
			
			// Get the extents from the gallery
			$maxwidth = $gallery->MaxWidth;
			$maxheight = $gallery->MaxHeight;
		
			$img = ImageCreateFromJPEG($this->getFilename());
			// Only resize if it's bigger than 640 wide or high
			$srcw = imagesx($img);
			$srch = imagesy($img);
			
			if (($srcw > $maxwidth) || ($srch > $maxheight)) {
				// Compute the new size (using current aspect ratio)
				$destw = $srcw;
				$desth = $srch;

				if ($destw > $maxwidth) {
					$destw = $maxwidth;
					$desth = $maxwidth * ($srch / $srcw);
				}
				if ($desth > $maxheight) {
					$desth = $maxheight;
					$destw = $maxheight * ($srcw / $srch);
				}
			
				// Create the resized image
				$newimg = ImageCreateTrueColor($destw, $desth);
				ImageCopyResampled($newimg, $img, 0, 0, 0, 0, $destw, $desth, $srcw, $srch);
				ImageDestroy($img);
				
				// Move the current image to the fullsize name
				rename($this->getFilename(), $this->Event->getFullPath() . "/" . strtolower(str_replace(".jpg", ".full.jpg", strtolower($this->Path))));
				
				// Save the resized image
				ImageJPEG($newimg, $this->getFilename());
			} else {
				ImageDestroy($img);
			}
		}
	}

	// Start up the gallery object
	$gallery = new PhotoGallery();	// Create the gallery object
	require("prefs.php");			// Load the preferences

	function &getThumbnail($fn) {
		$img = ImageCreateFromJPEG($fn);
		$thumb = &getThumbnailFromImage($img);
		ImageDestroy($img);
		return $thumb;
	}
	
	function &getThumbnailFromImage($img) {
		$srcw = imagesx($img);
		$srch = imagesy($img);

		$desth = 75;
		$destw = round(75 * ($srcw / $srch));

		if (function_exists("ImageCreateTrueColor")) $thumb = ImageCreateTrueColor($destw, $desth);
		else $thumb = ImageCreate($destw, $desth);
		
		ImageCopyResampled($thumb, $img, 0, 0, 0, 0, $destw, $desth, $srcw, $srch);
		return $thumb;
	}
	

?>
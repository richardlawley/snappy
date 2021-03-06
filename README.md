snappy
======

Snappy is a simple PHP Photo gallery. It has almost no dependencies (PHP 4.2 and GD2), works fine in safe mode and does not require any complicated setting up.

Features
* Simple, customisable photo gallery
* Few requirements
* Works in SAFE MODE
* Automatically resizes pictures and creates thumbnails
* Supports nested subdirectories

Requirements
* PHP 4.2 or above
* GD2 Library

Installation
* Unzip the contents of the archive into the base directory for your gallery (e.g. ~/public_html/photos)
* Create directories beneath this directory to contain your photos (e.g. ~/public_html/photos/myholiday)
* Ensure that the directory to hold the images is writeable by the webserver (e.g. chmod 777 ~/public_html/photos/myholiday)
* Copy your photos into that directory, and ensure they are world-readable (e.g. chmod 744 ~/public_html/photos/myholiday/*)
* View the page through your webserver, e.g. http://www.mypage.com/photos/

Notes
* The first time you view the photos for an event, the photos will be resized (if necessary) and thumbnails generated. This process can easily take longer than the max execution time set on the webserver. This is automatically detected by the page, which will stop and restart just before the execution time limit.
* If the photos you put in are too big, they will be resized. The originals will be renamed myphoto.full.jpg, and a link will be made available to the full size copy. If you delete the full size copies to save space, the links will not show.

Known Bugs
* Progressbar doesn't work in Mozilla Firebird. This is a reused class from phpclasses.org.

Future Work
* Implement captions for photos
* Implement recording number of times a photo is viewed
* Store objects in session so page reloads are quicker

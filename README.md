# DS-LoadMusicStick
Synology DS App um Musikdateien auf einen USB Stick für einen CD Wechsel zu laden

Mehr Details: https://www.synology-forum.de/threads/neue-app-loadmusicstick.58610/

Vorherige Lösung: https://github.com/Starwhooper/RandomFileCopy

Version 3.3 build 201604##
* fix issue with 0byte files
* apptype change to popup
* end of support for DSM 4

Version 3.2 build 20160421
* send mail after job
* consider musicfiles outside the typical "music" folder

Version 3.1 build 20160418
* fix filesize interpretation since DSM 6
* fix layout of header
* link for feedback via e-mail or forum added
* align foldersize and filecount to right
* extent destination storagesize number 
* fix issue to recognize running job under DSM 6
* disable function to delete files from destination.
* create folder by genre

Version 3.0 build 20151003
* use MediaServer Database instead to seek file by self
* seperate deletion to own skript
* offer Genres as selection
* check if USB is really mount (in the past was only check if the mointpoint folder exist)
* change select all/none checkboxes to java function
* filetype will self detected and need no predefination
* supports now also eSATA as destination
* CD01-CD06 option provides only 50 files per folder (more could the radio not handle)
* convert mp3 to lower rates
* list dates and convert info in logfile
* remember selected sources from previous session
* remember selected configuration from previous session

Version 2.2 build 20141117
* implement delete function
* Add full licence text during installation

Version 2.1
* link to filemanager

Version 2.0
* create bash script to handle to long working task of copy and convert and monitor it

Version 1.11
* remember selection of folder from /volume1/music
* implement button to check/uncheck all folder in /volume1/music
* implement option, to break at first time when a file is to big
* implement option to set a limit of free space
* fix issue with chars like ÄÜÖ on the destination file
* implement option to delete the files from the destination (but it is not active)

Version 1.10
* show in german, if user set languagesetting to german

Version 1.9
* add more errormessage to provide more detaisl in case of errors

Version 1.8 build
* make it compatible with DSM 4.0-2265 and newer version
* add this CHANGELOG document
* note in case of no write permission on USB drive

Version 1.7 build 20141018
* first public release


-------------------requirements------------------------------------
* Init3rdParty (php 5.3 or newer) https://www.cphub.net/ 
* MediaServer (Synology package)

-------------------todo for next version------------------------------------

*texte im protocol übersetzen
* multiuser, damit auch nicht Admin account die App nutzen kann
* Im protokoll die „war zu groß“ meldung weniger gefährlich anzeigen und mehr im design das „Added to job“ darstellen
* reload button um seite zu aktualisieren

== install/uninstall process ==
* implement a log to check over the package manager when the app does 
* implement a upgrade function
* implement to offer a new version if avaiable
* remove checkbox if you want start the app after installation on DMS 4

== GUI ==
* prompt anything during the copy process, maybe show the id3 image during the copy
* translate all textes
* refresh size on destination USB after delete the content

== Icons ==
** implement icon for DSM 4
** implement icon for DSM 4 package managaer
** implement big icon for package manager

== filename output ==
* check chars in destination filename, implement option to remove all non standard chars

== copy options ==
copy in folder differd by genre
copy only 50 files, if it choose (currently it copy more)



More new ideas: May you post here http://www.synology-forum.de/showthread.html?58610-neue-App-quot-LoadMusicStick-quot or send it my via mail to thiemo@schuff.eu

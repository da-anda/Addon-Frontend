Addon-Frontend
==============

XBMC Addon Frontend

A PHP/MYSQL website Frontend repository for XBMC Addons

Requirements
============
PHP
MYSQL
Web server

Setup
=====

1) Clone the repository into your web server folder
2) Create a new MYSQL Database called "xbmcrepo"
3) Import the .sql file structure into this database
4) Create a new file in the includes directory called developmentConfiguration.php
5) Edit this file in notepad and copy and paste any settings you want to override locally
6) Test the site in a web browser
7) If you get a cache folder error, you may have to create a /cache folder in the root with writable permissions
8) Run the sync.php file from the install folder to populate the database

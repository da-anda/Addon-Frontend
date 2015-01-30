Addon-Frontend
==============

Kodi Addon Frontend

A PHP/MYSQL website Frontend repository for Kodi Addons

Requirements
============
PHP

MYSQL

Web server

Setup
=====

1) Clone the repository into your web server folder

2) Create a new MYSQL Database

3) Import the .SQL file structure into this database

4) Create a new file in the /includes folder called developmentConfiguration.php

5) Edit this file in notepad and copy and paste any settings you want to override locally (like database connection)

6) Test the site in a web browser

7) If you get a cache folder error, you may have to create a /cache folder in the root with writeable permissions

8) Run the sync.php file from the install folder to populate the database
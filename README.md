Web Unit Test
=============

![Screenshot](screenshot.png?raw=true "Interface preview")

Preparation
-----------

Create project dir in webserver.
Extract the .zip content in the directory you've just created.

Database
--------

Fill in the conf.ini with the right credentials.
Import the structure from the file Model/db.sql.
Import the data from the file Model/db-fixtures.sql.
You can now launch the website to see if it works.

Launching Java Tests
--------------------

You have to change the project directory's rights like this (with the user's webserver) :
> chown -R www-data ./


//
// Steps in database transfer:

1. export the avscience db from snowpilot.org , cd to /usr/local/mysql and do a ./bin/mysqldump -uroot -p avscience > avsc_date.sql or something. zipped, it is around 5 MB, unzipped 57MB

2. import it into a new location, give it a nice name, on mtavalanche.com server is fine.

3. in each of the following four documents, change the dbname, username, pw, etc at the top of the script. Get ready to run each of them one way or another: copy and paste into a drupal php page; or upload to server

4. populate_xml_field.php - this script searches through the (local) db and finds any records without PIT_XML field filled in, and queries the kahrlconsulting.com server to retrieve the xml info. That is then put back into the (local avscience) database.

5. populate_pit_fields.php -this script fills in data that is normally packed into the PIT_XML or PIT_DATA fields, like activities, is it a ski area pit, representative of BC, etc, etc, etc.

6. populate_layers_table.php - this actually does two things, depending on blocks that are commented out or not: it can populate ( potentially OVER POPULATE) the layers table; and it can fill in iLayerNumber and iDepth fields in the PIT_TABLE. I say OVER populate becuase if you run the same script on the same set of pits twice, it will double create all of those layers. So, if I find I have made a mistake, I usually 'truncate table layers' and then rebuild the table by re-running this script [ this is different from the previous two, which can be re run on the same data]. 42K layers ~= 5900 pits which actually have layers

7. populate_shear_tests_table.php - similar to layers, fills in the shear_tests table with test types and results; also should not be re-run



Did all of the ecscores get properly transfered from PIT_XML to the shear tests table? check the number of results for this:
SELECT DISTINCT (
pid
)
FROM `shear_tests`
WHERE code = 'EC'
AND (
score = 'ECTP'
OR 'score' = 'ECTN'
)
 
 
Versus this: 
SELECT *
FROM `PIT_TABLE`
WHERE (
PIT_XML LIKE '%score=\"ECTP\"%'
OR PIT_XML LIKE '%score=\"ECTN\"'
)
Mark Karhl:

This is the URL for getting a pit image from the serial number:

http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=PITIMAGE&SERIAL=20204

 The API to push pits into the system is ready. To send in a pit, just post your document (in SnowPilot xml format) to the URL below. It should then show up in 'Web Databse' list in the desktop application. I've unit tested it, but could use more testing, so let me know how it is. Let me know if you need help debugging, etc.

for avscience_dev, test db:
http://www.kahrlconsulting.com:8087/avscience/PitServlet?TYPE=XMLPIT_SEND


for avscience, main db
http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=XMLPIT_SEND
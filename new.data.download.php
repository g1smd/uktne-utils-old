<?php

# include parseCSV class.
  require_once('lib-parsecsv/parsecsv.lib.php');

# create new parseCSV object.
  $csv = new parseCSV();

# Parse '_sabc.csv' using automatic delimiter detection...
  $csv->auto('example-data/_sabc-new.csv');


# include UKtneUtils class.
  require_once('uktne-utils.lib.php');

# create new UKtneUtils object.
# Parse data and reformat...
  $csvnew = new UKtneUtils($csv->titles, $csv->data);


# now we have data in $csvnew->datanew we can copy it back
# to the $csv->titles and $csv->data structure for output
  $csv->titles = $csvnew->titlesnew;
  $csv->data = $csvnew->datanew;

# then we output the file to the browser as a downloadable file...
  $csv->output('_sabc-three-column.csv');

# ...when the first parameter is given and is not null, the
# output method will itself send the correct headers and the
# data to download the output as a CSV file. If it's not set
# or is set to null, output will only return the generated
# CSV output data, and will not output to the browser itself.

?>

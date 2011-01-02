<pre>
<?php

# include parseCSV class.
  require_once('lib-parsecsv/parsecsv.lib.php');

# create new parseCSV object.
  $csv = new parseCSV();

# Parse '_sabc.csv' using automatic delimiter detection...
  $csv->auto('example-data/_sabc-new.csv');


# include uktne-utils class.
  require_once('uktne-utils.lib.php');

# create new uktne-utils object.
# Parse data and reformat...
  $csvnew = new uktne-utils($csv->titles, $csv->data);

?>
</pre>
<style type="text/css" media="screen">
	table { background-color: #BBB; }
	th { background-color: #EEE; font-size: 80%; }
	td { background-color: #FFF; font-size: 75%; }
</style>
<table border="0" cellspacing="1" cellpadding="3">
	<tr>
		<?php foreach ($csvnew->titlesnew as $value): ?>
		<th><?php echo $value; ?></th>
		<?php endforeach; ?>
	</tr>
	<?php foreach ($csvnew->datanew as $key => $row): ?>
	<tr>
		<?php foreach ($row as $value): ?>
		<td><?php echo $value; ?></td>
		<?php endforeach; ?>
	</tr>
	<?php endforeach; ?>
</table>

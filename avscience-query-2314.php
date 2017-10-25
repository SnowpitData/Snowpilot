<?php

include_once (DRUPAL_ROOT.'/sites/default/db_settings.php' );

	Database::addConnectionInfo('avscience_db', 'default', $test_db );// $avsci_db_info
	
	db_set_active('avscience_db');
	
$where_clause = '';
	
	
$state = ''; $mtn_range = ''; $obsdatemin = '' ; $obsdatemax = ''; $username = ''; $loc_name = ''; $serial =''; $state_options = ''; $region_options = ''; $test_pit_checked = ''; $affil = '';
if (isset( $_GET['STATE']) && $_GET['STATE'] <> ''  ){
    $where_clause .=  " AND STATE = '" .  $_GET['STATE']."'" ; 
		$state = $_GET['STATE'];
}

$unique_states = "SELECT distinct ( STATE ) FROM `PIT_TABLE` ORDER BY length(STATE) , STATE"; 
$states_list = db_query($unique_states);

while ( $states = $states_list->fetch() )	{
	$selected = ($states->STATE == $state) ? ' selected' : '';
  $state_options .=  "<option value ='" . $states->STATE . "'". $selected ."  > ". $states->STATE ."</option>
		" ;
}

if (isset( $_GET['MTN_RANGE']) && $_GET['MTN_RANGE'] <> ''  ){
    $where_clause .=  " AND MTN_RANGE LIKE '%" .  $_GET['MTN_RANGE']."%'" ;
		$mtn_range = $_GET['MTN_RANGE'];
}

$unique_regions = "SELECT distinct ( MTN_RANGE ) FROM `PIT_TABLE` "; 
$regions_list = db_query($unique_regions);

while ( $region = $regions_list->fetch() )	{
  $region_options .=  "<option value ='" . $region->MTN_RANGE . "'  \> 
		" ;
}

if (isset( $_GET['OBS_DATE_MIN']) && $_GET['OBS_DATE_MIN'] <> ''  ){
    $where_clause .=  " AND OBS_DATE > '" .  $_GET['OBS_DATE_MIN']."'" ;
		$obsdatemin = $_GET['OBS_DATE_MIN'];
}


if (isset( $_GET['OBS_DATE_MAX']) && $_GET['OBS_DATE_MAX'] <> ''  ){
    $where_clause .=  " AND OBS_DATE < '" .  $_GET['OBS_DATE_MAX']."'" ;
		$obsdatemax = $_GET['OBS_DATE_MAX'];
}


if (isset( $_GET['USERNAME']) && $_GET['USERNAME'] <> ''  ){
    $where_clause .=  " AND USERNAME LIKE '%" .  $_GET['USERNAME']."%'" ;
		$username = $_GET['USERNAME'];
}


if (isset( $_GET['LOC_NAME']) && $_GET['LOC_NAME'] <> ''  ){
    $where_clause .=  " AND LOC_NAME LIKE '%" .  $_GET['LOC_NAME']."%'" ;
		$loc_name = $_GET['LOC_NAME'];
}

if ( $_GET['testpit'] <> '1'  ){
    $where_clause .=  " AND TEST_PIT != 1" ;
}else{
	$test_pit_checked = ' checked';
}

if (isset( $_GET['SERIAL']) && $_GET['SERIAL'] <> ''  ){
    $where_clause .=  " AND SERIAL = '" .  $_GET['SERIAL']."'" ;
		$serial = $_GET['SERIAL'];
}

if (isset( $_GET['AFFIL']) && $_GET['AFFIL'] <> ''  ){
    $where_clause .=  " AND PIT_DATA LIKE '%affil~1" .  $_GET['AFFIL']."%'" ;
		$affil = $_GET['AFFIL'];
}
	
?>
<form action = "/node/2314" method = "get" class = "form-item">
	<datalist id = "regions">
		<?php echo $region_options; ?>
	</datalist>
	State: <select type = "select" name = "STATE" id = "STATE" value ="<?php echo $state; ?>"/>
	<?php echo $state_options; ?>
  </select>
  <br/>Range/Region: <input type = "text" name ="MTN_RANGE" id = "MTN_RANGE" list = "regions" multiple value ="<?php echo $mtn_range; ?>"/>
	<div class = "description">The 'Range/Region' field will auto-populate with available values from the database. <br />Use partial phrases to match many terms. <br />( e.g. 'Brid' will match 'Bridger Mountains' , 'Northern Bridgers' , 'Bridger Bowl').</div>
	<br />Observation date, minimum: <input type = "date" name = "OBS_DATE_MIN" value= "<?php echo $obsdatemin; ?>" /> 
	<br />Observation date, maximum: <input type = "date" name = "OBS_DATE_MAX" value= "<?php echo $obsdatemax; ?>" /> 
	<br />Username 'contains' : <input type = "text" name = "USERNAME" value= "<?php echo $username; ?>" /> 
	<br />Affiliation 'contains' : <input type = "text" name = "AFFIL" value= "<?php echo $affil; ?>" /> 
	<br />Location Name 'contains' : <input type = "text" name = "LOC_NAME" value= "<?php echo $loc_name; ?>" />
	<br />Include test Pits?<input type = 'checkbox' name = 'testpit' value = '1' <?php echo $test_pit_checked; ?>/> 
	<br />SERIAL of snowpit: <input type = "text" name = "SERIAL" value= "<?php echo $serial; ?>"> 
	

<br /><input type = "submit" name = "submit" value = "Search" />

</form>

<?php

dsm($_GET) ;


global $user;
dsm($user);
if ( in_array('administrator' , $user->roles )){
	
	$where_clause .= '';
}elseif( in_array('authenticated user' , $user->roles)){
	$where_clause .= " AND ( SHARE = '1' OR (SHARE = '0' AND USERNAME = '$user->name') ) ";
	
}else{
	$where_clause .= " AND SHARE = '1' ";
	
}
	//
	$query = "SELECT * FROM `PIT_TABLE` WHERE SERIAL > 0  ". $where_clause . " LIMIT 100 ";
	$query2 = "SELECT count(SERIAL) FROM `PIT_TABLE` WHERE SERIAL > 0  ". $where_clause .' LIMIT 100 ';
  $results = db_query($query);
	$records = array();
  while ( $record = $results->fetch() )	{
	  $records[] = $record;
  }
	$count_results = db_query($query2)->fetchField(); 
  dsm($query);
	db_set_active();

$header = array( 'Pit Name' , 'Date' , 'User',  'Location - State' , 'Location - Range' , 'Pit ID (Serial)' , 'Image'  );

foreach ( $records as $record){
	if( file_exists( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' )){
		$img_url = '/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg';
		
	}else{
	  //$kc_img_src = file_get_contents( "http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=PITIMAGE&SERIAL=".$record->SERIAL );
		
		// should use file get contents here
		//$local_image = fopen( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' , 'w');
		//fwrite($local_image, $kc_img_src);
	//	fclose($local_image);
    $img_url = "<img src = 'http://www.kahrlconsulting.com/pits/".$record->SERIAL . ".jpg' style = 'width: 450px;' />   "   ;
	 
	}
   $rows[] = array( $record->PIT_NAME , $record->OBS_DATE, $record->USERNAME,  $record->STATE , $record->RANGE , $record->SERIAL , $img_url );

}
$attributes = array();  
$caption = 'Snowpit query results';
$colgroups = array();
$sticky = FALSE;
$empty = "No Rows!";

//print theme( 'table', array( 'header' => $header,
 //                           'rows' => $rows ) );
														//
														//  Set up the pager
														// 
														$per_page = 10;
														// Initialize the pager
														$current_page = pager_default_initialize(count($rows), $per_page);
														// Split your list into page sized chunks
														$chunks = array_chunk($rows, $per_page, TRUE);
														// Show the appropriate items from the list
														$output = theme('table', array('header' => $header, 'rows' => $chunks[$current_page]));
														// Show the pager
														$output .= theme('pager', array('quantity',count($rows)));
				
														print t('<h2>Showing 10 results of total: '. $count_results.'</h2>');
														
														print($output);
														

?>
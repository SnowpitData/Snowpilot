<?php

include_once (DRUPAL_ROOT.'/sites/default/db_settings.php' );
global $user;
	Database::addConnectionInfo('avscience_db', 'default', $test_db );// $avsci_db_info
	
	db_set_active('avscience_db');
	
$where_clause = '';
	
	
$state = ''; $mtn_range = ''; $obsdatemin = '' ; $obsdatemax = ''; $username = ''; $loc_name = ''; $serial =''; $state_options = ''; $region_options = ''; $test_pit_checked = ''; $affil = ''; $adv_where_query = '';
if (isset( $_GET['STATE']) && $_GET['STATE'] <> ''  ){
    $where_clause .=  " AND STATE = '" .  $_GET['STATE']."'" ; 
    $state = $_GET['STATE'];
}
// States dropdown list
$unique_states = "SELECT distinct ( STATE ) FROM `PIT_TABLE` ORDER BY length(STATE) , STATE"; 
$states_list = db_query($unique_states);

while ( $states = $states_list->fetch() )	{
	$selected = ($states->STATE == $state) ? ' selected' : '';
  $state_options .=  "<option value ='" . $states->STATE . "'". $selected ."  > ". $states->STATE ."</option>
		" ;
}


if (isset( $_GET['MTN_RANGE']) && count( $_GET['MTN_RANGE'] )  ){
	
    $where_clause .=  " AND ( ";
    foreach ($_GET['MTN_RANGE'] as $range ){
    	$where_clause .= " MTN_RANGE = '" .  $range."' OR" ;
			
    }
		$where_clause = substr($where_clause, 0, -3 );
		$where_clause .= " ) ";
}

$unique_regions = "SELECT distinct ( MTN_RANGE ) FROM `PIT_TABLE` ORDER BY MTN_RANGE ASC "; 
$regions_list = db_query($unique_regions);

while ( $region = $regions_list->fetch() )	{
	$range_selected = (in_array( $region->MTN_RANGE , $_GET['MTN_RANGE'] ) && isset($_GET['MTN_RANGE'])) ? ' selected' : '';
  $region_options .=  "<option value ='" . $region->MTN_RANGE . "' ".$range_selected." >".$region->MTN_RANGE."</option>
		"  ;
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

if ( NULL != ( $_GET['ADV_WHERE_QUERY']) && $_GET['ADV_WHERE_QUERY'] <>''){
	$where_clause .=  " AND ". $_GET['ADV_WHERE_QUERY'];
	$adv_where_query = $_GET['ADV_WHERE_QUERY'];
}

	
?>
<div class = "view-snowpit-list">
	<div class= "views-exposed-form">
<form action = "/node/2314" method = "get" class = "form-item">
<div class = "geo-selection">
	  <div style = "display: inline-block; width : 160px; vertical-align: top;">
	  	Location/Snowpit Name<br /><input type = "text" name = "LOC_NAME" value= "<?php echo $loc_name; ?>" />
  		<div class = 'description'>Uses 'contains'. You need only partial match of the name.</div>
  	</div>
    <div style = "display: inline-block; width : 160px; vertical-align: top;">
  	State/Province/Country<br />
	  <select type = "select" name = "STATE" id = "STATE" value ="<?php echo $state; ?>">
  	  <?php echo $state_options; ?>
    </select>
    </div>
	  <div style = "display: inline-block;" >
	  	Range/Region: <br/>
  	<select name ="MTN_RANGE[]" id = "MTN_RANGE" multiple  style = "height: 140px;">
	  	<?php echo $region_options; ?>
	  </select>
    </div>
		<div class = "date-selection" style = "display: inline-block; vertical-align: top; " >
			Observation Date<br />
		  <div style ="display: inline-block;">minimum: <br /><input type = "date" name = "OBS_DATE_MIN" value= "<?php echo $obsdatemin; ?>" /> </div>
		  <div style ="display: inline-block;">maximum: <br /><input type = "date" name = "OBS_DATE_MAX" value= "<?php echo $obsdatemax; ?>" /> </div>
		</div>
		<div class = "users-orgs-selection"  style = "display: inline-block; vertical-align: top;" >
			<div >Username 'contains': <br /><input type = "text" name = "USERNAME" value= "<?php echo $username; ?>" /> </div>
		  <div >Affiliation 'contains' : <br /><input type = "text" name = "AFFIL" value= "<?php echo $affil; ?>" /> </div>
		</div>
  </div>
	<div class = "researcher-query">
		<?php
		if ( in_array('administrator' , $user->roles ) || in_array('researcher' , $user->roles ) ){
		 echo ' 	<div style ="display: inline-block;">Advanced WHERE query : <input type = "text" name = "ADV_WHERE_QUERY" value= "' . $adv_where_query . '" /></div>';
		}
		?>
	</div>
	<div class = "tests-button">
		<div style ="display: inline-block;"><input type = "submit" name = "submit" value = "Get Pits" /></div>
	  <div style ="display: inline-block;">Include test Pits?<input type = 'checkbox' name = 'testpit' value = '1' <?php echo $test_pit_checked; ?> /> </div>
  </div>
	<!--p>SERIAL of snowpit: <input type = "text" name = "SERIAL" value= "<?php echo $serial; ?>" / --> 
	

<br />

</form>
</div>
<?php

$per_page = 10;


if ( in_array('administrator' , $user->roles )){
	$where_clause .= '';
}elseif( in_array('authenticated user' , $user->roles)){
	$where_clause .= " AND ( SHARE = '1' OR (SHARE = '0' AND USERNAME = '$user->name') ) ";
	
}else{
	$where_clause .= " AND SHARE = '1' ";
	
}
	//
	$page_num = $_GET['page'];
	$query = "SELECT LOC_NAME, OBS_DATE, USERNAME, STATE, MTN_RANGE, SERIAL FROM `PIT_TABLE` WHERE SERIAL > 0  ". $where_clause . " ORDER BY OBS_DATE DESC LIMIT " . $page_num * $per_page ."," . $per_page;
	$query2 = "SELECT count(SERIAL) FROM `PIT_TABLE` WHERE SERIAL > 0  ". $where_clause ;
  $results = db_query($query);
	$count_results = db_query($query2)->fetchField(); 
	db_set_active();
	
	//$records = array();
	$rows = array();
  while ( $record = $results->fetch() )	{
	 // $records[] = $record;
	 
	/* if( FALSE && !file_exists( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' )){
			$img_url = '/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg';
			$kc_img_src = file_get_contents( "http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=PITIMAGE&SERIAL=".$record->SERIAL );
		
			$local_image = fopen( DRUPAL_ROOT.$img_url , 'w');
			fwrite($local_image, $kc_img_src);
			fclose($local_image);
		}
	*/	
		$pre_img_url = '/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg';
		$layers_image = '/sites/default/files/avscience-profiles/layers/layers-serial-'.$record->SERIAL. '.jpg';
		
		if( !file_exists( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/layers/layers-serial-'.$record->SERIAL. '.jpg' )){
			if( !file_exists( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' )){
				
				$kc_img_src = file_get_contents( "http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=PITIMAGE&SERIAL=".$record->SERIAL );
				$local_image = fopen( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' , 'w');
				fwrite($local_image, $kc_img_src);
				fclose($local_image);
				sleep(1);
			}
			$pre_img = imagecreatefromjpeg( DRUPAL_ROOT.$pre_img_url);
			$new_img = imagecreatetruecolor(466,613);
			$result = imagecopy($new_img, $pre_img, 0,0, 14,140,466,613 );
						
			imagejpeg( $new_img , DRUPAL_ROOT. $layers_image );
			
		}
		
		//
		//
		//  
		  $links = array();
		  $links[] = array('title' => t('JPG'), 'href' => '/sites/default/files/avscience-profiles/graph-serial-'. $record->SERIAL .'.jpg');
		  $links[] = array('title' => t('SnowPilot XML'), 'href' => '/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg');
			
			$this_item = '
              <div class = "item-wrapper">
			  <div class="views-field views-field-title">' . $record->LOC_NAME . '</div>  
			  <div class="field-content views-graph-image"><a href="' . $pre_img_url  . '"><img src="'. $layers_image .'"></a></div>    
			  <div class="field-content views-field-field-date-time"><span class="views-snowpit-region-range">'.$record->STATE . ' » '.$record->MTN_RANGE . '</span>
			<br>
			<span class="views-user-name">'. $record->USERNAME . '</span>
			<br>
			<span class="date-display-single" property="dc:date"  >'. $record->OBS_DATE .' </span>
			<span>'. theme('ctools_dropdown', array('title' => t('Download Snowpit'), 'links' => $links)) .'</span>
			</div> </div>';
		
		
		
			$rows[$x][1] .= $this_item ;
		
		
		$img_url = '<img src = "' .$layers_image . '" /> ';
    
 	//    '<img src = "/sites/default/files/avscience-profiles/graph-serial-' .$record->SERIAL . '.jpg" style = "width: 450px;" />   '  
		
  }


$header = array( '' , '' , '',  '' , '' );

/*
foreach ( $records as $record){
 if( FALSE && !file_exists( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' )){
		$img_url = '/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg';
		$kc_img_src = file_get_contents( "http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=PITIMAGE&SERIAL=".$record->SERIAL );
		
		$local_image = fopen( DRUPAL_ROOT.$img_url , 'w');
		fwrite($local_image, $kc_img_src);
		fclose($local_image);
	}

	
		  
	// Uncomment this line to generate new images on kahrlconsulting server. Slows way down, though
	// $kc_img_src = file_get_contents( "http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=PITIMAGE&SERIAL=".$record->SERIAL );
		
		// should use file get contents here
		//$local_image = fopen( DRUPAL_ROOT.'/sites/default/files/avscience-profiles/graph-serial-'.$record->SERIAL. '.jpg' , 'w');
		//
		//ctools_include('modal');
		//ctools_modal_add_js();
		
		//ctools_modal_text_button('popup link', $img_url, 'alt', ''); 
    //$img_url = '<img src = "/sites/default/files/avscience-profiles/graph-serial-' .$record->SERIAL . '.jpg" style = "width: 450px;" />   '   ;
	 
//	}
   $rows[] = array( $record->PIT_NAME , $record->OBS_DATE, $record->USERNAME/*,  $record->STATE , $record->MTN_RANGE , $record->SERIAL , 
	 '<img src = "/sites/default/files/avscience-profiles/graph-serial-' .$record->SERIAL . '.jpg" style = "width: 450px;" />   ' );

}
*/

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
														// Initialize the pager
														$current_page = pager_default_initialize($count_results, $per_page);
														// Split your list into page sized chunks
														$chunks = array_chunk($rows, $per_page, TRUE);
														// Show the appropriate items from the list
														$output = '<div class = "view-content">'. theme('table', array('header' => $header, 'rows' => $rows /*$chunks[$current_page]*/ ));
														// Show the pager
														$output .= theme('pager', array('quantity',$count_results)). '</div></div>';
				
														$display_per_page = $per_page < $count_results ? $per_page : $count_results;
														$begining = $per_page * $page_num + 1; $ending = $per_page * $page_num + $per_page;
														print t('<h2>Showing results '. $begining  . ' through ' . $ending  . ' of total: '. $count_results.'</h2>');
														
														print($output);
		
?>
<?php
include_once( DRUPAL_ROOT . '/sites/all/libraries/ForceUTF8/Encoding.php');
use \ForceUTF8\Encoding;

//phpinfo();
/// function to load PIT_XML and populate the 

function snowpilot_avscience_populate_xml( $SERIAL ){

	error_reporting(E_ALL);
  $result_code = array( 'continue' => TRUE,  'message'=> '');

	if ( $SERIAL == '' || !is_numeric( $SERIAL) || is_null( $SERIAL) ) {
		$result_code['continue'] = FALSE;
		$result_code['message'] = "No SERIAL given.";
	}else{
		$node_nid = avscience_is_drupal_pit( $SERIAL );
		if ( $node_nid > 0  ){
			$node = node_load($node_nid);
		  $pit_xml = snowpilot_node_write_pitxml($node);
		}else{
			$kc_serial = avscience_connect_fetch_kc_serial($SERIAL);
			if ( !($pit_handle = fopen ('http://kahrlconsulting.com:8084/avscience/PitServlet?TYPE=XMLPIT&SERIAL='.$kc_serial, 'r' )) ){
				$result_code = array('continue'=> FALSE, 'message'=> $SERIAL.'Unable to open connection with kahrlconsutling pit servlet.');	
			}else{
				$pit_xml = '';
				$pit_xml = stream_get_contents($pit_handle);
				///echo 'Pit xml: '.$pit_xml.'<br />';
				fclose($pit_handle);
			}
		}
		if ( $pit_xml != ''){
			$destination_link = mysqli_connect("localhost","jimurl","dRkV5iWqM3a54e5Z","jimurl_snowpilot_avscience") ;
			$query2= "UPDATE PIT_TABLE SET PIT_XML = '".str_replace("'", "''" , $pit_xml) ."' WHERE SERIAL = ".$SERIAL;
	
			if( $result2 = mysqli_query($destination_link, $query2) ){
				$result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' successfully added XML');
			}else{
				$result_code = array( 'continue' => FALSE, 'message'=> $SERIAL.' unable to write XML: '. $query2.' and '.mysqli_error($destination_link));
			}
		}else { 
			$result_code = array( 'continue' => FALSE, 'message'=> $SERIAL.' No pit XML was returned by kahrlconsulting.com pitservlet!'); 
		}	

  }
	return $result_code;
}

function avscience_connect_fetch_kc_serial($SERIAL){
	$destination_link = mysqli_connect("localhost","jimurl","dRkV5iWqM3a54e5Z","jimurl_snowpilot_avscience") ;
	$query0 = 'SELECT LOCAL_SERIAL FROM PIT_TABLE WHERE SERIAL = '. $SERIAL ;
	if( $result0 = mysqli_query($destination_link, $query0) ){
		if ( $row0['LOCAL_SERIAL'] == '' ){
			watchdog('avscience_connect', 'local_serial value is not set for this snowpit: '.$SERIAL );
			dsm( 'local_serial value is not set for this snowpit: '.$SERIAL. ' , using original SERIAL value w/o lookup.');
			return $SERIAL;
		}
	  while($row0 = mysqli_fetch_array($result0))	{
			$full_pit_list_url = "http://www.kahrlconsulting.com:8084/avscience/PitServlet?TYPE=XMLPITLIST_FROMQUERY&QUERY=".  urlencode("WHERE LOCAL_SERIAL = '". $row0['LOCAL_SERIAL']. "'");
			$options = array(
			    'http' => array(
			        'protocol_version' => '1.0',
			        'method' => 'GET'
			    )
			);
			$context = stream_context_create($options);
			
			$serial_lookup = file_get_contents($full_pit_list_url  , false , $context );
			preg_match( '/<id>(.*)<\/id>/' , $serial_lookup, $kc_serial );
			//dsm($kc_serial[1]);
			if ( count ( $kc_serial) > 2 ){ 
				watchdog('avscience_connect', 'Multiple local_serial values for this snowpit: '.$SERIAL );
				dsm( 'multiple records with this local_serial value, second serial from KC: '.$kc_serial[2]);
				$return_serial = $kc_serial[1];
			}elseif ( count ( $kc_serial) == 2 ){
				$return_serial = $kc_serial[1];
			}else{
				$return_serial = $SERIAL;
			}
			//return;
	  }
		
	}
	mysqli_free_result($result0);
	
	return $return_serial;
}


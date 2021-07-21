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

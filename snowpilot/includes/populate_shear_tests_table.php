<?php
include_once( DRUPAL_ROOT . '/sites/all/libraries/ForceUTF8/Encoding.php');
use \ForceUTF8\Encoding;
function populate_shear_tests_table($SERIAL){

	include_once (DRUPAL_ROOT.'/sites/default/db_settings.php' );
	Database::addConnectionInfo('avscience_db', 'default', $test_db );// $avsci_db_info
	
	$result_code = array('continue'=> TRUE, 'message' => 'default message tests');
	if ( is_null( $SERIAL) ) {
    $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' No serial given.');
	}else{

		//var_dump($link);
		//consultation:
	  db_set_active('avscience_db');
				$query = "SELECT SERIAL,PIT_XML
					        FROM `PIT_TABLE`   
				          WHERE SERIAL = ".$SERIAL or die("Error in the consult.." . mysqli_error($link));

				$result = db_query( $query);
			  db_set_active();
			
		//display information:
		$shear_test_attributes = array('code', 'dateString','comments', 'sdepth', 
		'depthUnits',  'quality', 'ecScore', 'numberOfTaps', 'ctScore', 'score', 's','releaseType',
		'lengthOfColumn', 'fractureCat'  , 'lengthOfCut');

	
		while($row = $result->fetch() ) {

			$doc = new DOMDocument();
		
			$corrected_encoding = Encoding::toUTF8($row->PIT_XML);

			if ($corrected_encoding == '' ) { /*var_dump($row['PIT_XML'] );*/ continue;}
			if ($doc->loadXML($corrected_encoding)) {
				$shear_tests = $doc->getElementsByTagName('Shear_Test_Result');
				//var_dump($attr_list);
				foreach($shear_tests as $shear_test){	
					
					$values_list = array();		
					$values_list[] = " pid = ".$row->SERIAL;
					foreach ($shear_test_attributes as $attr){
						//
						//  This block funnells both ecScore and number of Taps fields into the ecScore field.
						//  Only if ecscore is not set and number of taps is
						//
						if ( !($shear_test->getAttribute($attr)) ||
							$shear_test->getAttribute($attr) == '' ||
								$shear_test->getAttribute($attr) == ' ') continue;
						if ( $attr == 'ecScore' ){ /// this little routine failed to work to opopulate the ecScore field in cases where number of Taps was set, probably the if statement is too restrictive 
							// also fixable via update ... where ... 
							if ( !($shear_test->getAttribute('ecScore')) && (($shear_test->getAttribute('numberOfTaps')) && $shear_test->getAttribute('numberOfTaps') != '' )){
								$new_ecscore =$shear_test->getAttribute('numberOfTaps');		
							}else{ $new_ecscore = $shear_test->getAttribute($attr);
							}
							$values_list[] = $attr ." = '".$new_ecscore."' ";
						}elseif ( $attr == 'dateString' ){
							// datefield needs some reformatting too
							$values_list[] = $attr. " = '" . substr($shear_test->getAttribute($attr) , -4,4) . "/" . substr($shear_test->getAttribute($attr) , 0,5)."' ";	
						}else{// we'll add more elseif here to accomadate different $attribute formatting needs
							// for non ecScore  fields, just spit it out
							$values_list[] =  $attr. " = '". $shear_test->getAttribute($attr)."' " ; 				
						}
					}
				  db_set_active('avscience_db');
				
					$query3 = "INSERT INTO shear_tests SET ". implode(', ', $values_list );
          $result3 = db_query($query3);
					if($result3){
				      $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' Successfully added shear test.');	
					}	else { 
						$result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' Problem with the SQL insert statement');	
				  }
				  db_set_active('default');
					
				}
			} 	
		}
	}
	return $result_code;
}


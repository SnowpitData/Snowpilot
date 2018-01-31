<?php
include_once( DRUPAL_ROOT . '/sites/all/libraries/ForceUTF8/Encoding.php');
use \ForceUTF8\Encoding;

function snowpilot_avscience_populate_pit_fields($SERIAL){
	$precip_labels = array( 'Snow < 0.5 cm/hr', 'None', 'NIL' ,'Snow   5 cm/hr', 'Snow - 5 cm/hr', 'Snow - 2 cm/hr', 'Snow   2 cm/hr' ,
	'Snow < 1 cm/hr', 'Snow   1 cm/hr', 'Snow - 1 cm/hr', 'Mixed rain and snow','Graupel or hail', 'Light Rain < 2.5mm/hr', 'Very light rain - mist',
	'Moderate rain < 7.5mm/hr', 'Heavy Rain > 7.5mm/hr', 'Snow - 10 cm/hr');
	$precip_codes = array( 'S-1','NO', 'NO' ,'S5', 'S5', 'S2', 'S2', 'S-1', 'S1', 'S1', 'RS', 'GR' , 'RL', 'RV', 'RM', 'RH', 'S10'); 

	$sky_cover_labels = array( 'Clear', 'Fog', 'sky not visible', 'sky 8/8 covered', 'sky 4/8 to 8/8 covered', 
	'sky > half covered', 'sky 3/8 to 4/8 covered', 'sky < 1/2 covered', 'sky < 1/8 covered', 'sky < 2/8 covered'); 
	$sky_cover_codes = array( 'CLR' ,'X' , 'X', 'OVC','BKN' , 'BKN', 'SCT' , 'SCT', 'FEW', 'FEW' );

	$wind_speed_labels = array('Light Breeze', 'Moderate', 'Calm', 'Strong', 'gale force winds');
	$wind_speed_codes = array( 'L', 'M', 'C' ,'S', 'X');


  $result_code = array( 'continue' => TRUE, 'message'=> 'default message- pit fields');
	if ( $SERIAL == '' || !is_numeric( $SERIAL) || is_null( $SERIAL) ) {
    $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' No serial given.');
	}else{
	$link = mysqli_connect("localhost","jimurl","dRkV5iWqM3a54e5Z","jimurl_snowpilot_avscience") ;

	//var_dump($link);
	//consultation:

	$query = "SELECT SERIAL,PIT_XML, OBS_DATE, OBS_DATETIME
	FROM `PIT_TABLE` 
	WHERE SERIAL = ".$SERIAL;

	//execute the query.

	$result = mysqli_query($link, $query)  or die("Error in the consult.." . mysqli_error($link));

	//d
		$pit_attributes = array('skiAreaPit', 'aviPit', 'bcPit', 'aviLoc', 'skiBoot','heightOfSnowpack', 'calculatedHoS', 'crownObs' ,'measureFrom', 'surfacePen', 'pitNotes','prof', 'iLayerNumber','windspeed', 'sky', 'precip', 'iDepth' );


	while($row = mysqli_fetch_array($result)) {
	
			$doc = new DOMDocument();
			//var_dump($doc);
			if ( $row['PIT_XML'] != '') {
				$corrected_encoding = Encoding::toUTF8($row['PIT_XML']);
				
				$corrected_encoding = str_replace('grainSuffix="<"' ,'grainSuffix="-"' , $corrected_encoding);
				$corrected_encoding = str_replace('grainSuffix=">"' ,'grainSuffix="+"' , $corrected_encoding);
				$corrected_encoding = str_replace($sky_cover_labels ,$sky_cover_codes , $corrected_encoding);
				$corrected_encoding = str_replace($precip_labels ,$precip_codes , $corrected_encoding);
				
				$doc->loadXML($corrected_encoding);
				$pits = $doc->getElementsByTagName('Pit_Observation');
				if ( $pits->length == 0 ) dsm( "XML import creation failed for ". $SERIAL . ' probably due to illegal charachters ( &, < , etc ): '. $corrected_encoding);
				foreach($pits as $pit_info){
					
					$values_list = array();
					foreach ($pit_attributes as $attr){	// looping through to add values for each of the standard attributes
						$attr_val = $pit_info->getAttribute($attr);
						
						if ( ($pit_info->getAttribute($attr) != '' ) && ( $pit_info->getAttribute($attr) != ' ') ){
							if ( in_array($attr, array('heightOfSnowpack', 'measureFrom', 'aviLoc', 'skiBoot',  'pitNotes', 'surfacePen', 'iLayerNumber' ,'iDepth') ) ){
						    $values_list[] =  $attr ." = '" . str_replace( "'" , "''", $attr_val). "' ";	
						  }elseif( $attr == 'windspeed' ){
									  $values_list[] =  "WIND_SPEED = '" . str_replace($wind_speed_labels, $wind_speed_codes, $pit_info->getAttribute($attr) ). "' ";
							}elseif($attr == 'precip'){
							  $values_list[] =  "PRECIP = '" . str_replace($precip_labels, $precip_codes, $pit_info->getAttribute($attr) ). "' ";
							}elseif( $attr == 'sky'){
							  $values_list[] =  "SKY_COVER = '" . str_replace($sky_cover_labels, $sky_cover_codes, $pit_info->getAttribute($attr) ). "' ";
								

							}elseif($attr == 'calculatedHoS'){
							  $HOS = $pit_info->getAttribute('heightOfSnowpack');
						  	if ( $HOS <> '' ){ /// HOS is explicitly given, use that
							  	//echo "HOS: ".$HOS;
						
						  		$values_list[] =  $attr ." = '" . $pit_info->getAttribute('heightOfSnowpack'). "' ";	
						  	}elseif ( $pit_info->getAttribute('measureFrom') == 'bottom'){ // HOS not given, we will calculate, as long as this is a from bottom pit
					    		$layers = $doc->getElementsByTagName('Layer');
						  		$max = 0;
						  		foreach ($layers as $layer){
							  		$max = ($layer->getAttribute('startDepth') > $max) ? $layer->getAttribute('startDepth') : $max ;
						  			$max = ($layer->getAttribute('endDepth') > $max) ? $layer->getAttribute('endDepth') : $max;
						  		}	
									if ($max > 0 ) $values_list[] = $attr ." = '". $max."' ";
						  	}else{ // calculated HOS is not knowable
								// we won't actually even add this to the update query 
						  	}
					
						}else{ // all other fields are t/f
						  	$tf_val = $pit_info->getAttribute($attr) == 'true' ? '1' : '0' ;
							
								$values_list[] =  $attr ." = '" . $tf_val. "' ";	
						  } // the attribute is the calculated HOS , so better calc it up now!
						}
					
					}
					$users = $doc->getElementsByTagName('User');
					foreach( $users as $user){
					  $tf_val = ($user->getAttribute('prof') == "true") ? '1' : '0';
					  $values_list[] = " prof = '". $tf_val."' ";
					}
					
					//
				}	
			
				foreach($pits as $pit_info){
					if ( count( $values_list)){
					  $query3 = "UPDATE PIT_TABLE SET ". implode( ',', $values_list ) ." WHERE SERIAL = " . $row['SERIAL'];
				  }
					//echo "query: ".$query3. "<br />";
					// Uncomment this line to set the iLayer and iDepth fields in the PIT_TABLE 
					if (!($result3 = mysqli_query($link, $query3))){
						 $result_code = array( 'continue' => FALSE, 'message'=>  $SERIAL. ' MYSQLi Error updating pit fields'." query: ".$query3 );	
					 }
				
					$query4 = "UPDATE PIT_TABLE set OBS_DATETIME = OBS_DATE where SERIAL = ". $SERIAL;
					if (!($result4 = mysqli_query($link, $query4))){  
						$result_code['message'] .= " Error updating obsdatetime: " . "  full query: ". $query4;	
					}
				} 


			
	    } 	
	}
}
  return $result_code;
}


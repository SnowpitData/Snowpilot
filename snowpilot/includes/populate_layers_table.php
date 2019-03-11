<?php
include_once( DRUPAL_ROOT . '/sites/all/libraries/ForceUTF8/Encoding.php');
use \ForceUTF8\Encoding;


function populate_layers_table($SERIAL){
	global $user;
	$account = user_load($user->uid);
	if ( !(user_has_role( 3, $account )) && !(user_has_role( 5, $account ) )){ 
		return MENU_ACCESS_DENIED;
	}
	include_once (DRUPAL_ROOT.'/sites/default/db_settings.php' );
	Database::addConnectionInfo('avscience_db', 'default', $test_db );// $avsci_db_info

$grain_type_codes = array('PP', 'PPco' , 'PPnd', 'PPpl', 'PPsd', 'PPir', 'PPgp', 'PPhl', 'PPip', 'PPrm', 
'MM', 'MMrp', 'MMci', 
'DF', 'DF' ,'DFdc', 'DFbk', 'DFbk',
'RG', 'RGsr', 'RGlr', 'RGwp', 'RGxf', 
'FC', 'FCso', 'FCsf', 'FCxr' , 'FCxr',
'DH', 'DHcp', 'DHpr', 'DHch', 'DHla', 'DHxr', 
'SH', 'SHsu','SHcv', 'SHxr', 
'MF', 'MFcl', 'MFpc', 'MFsl', 'MFcr', 
'IF', 'IFil', 'IFic', 'IFbi', 'IFrc', 'IFsc', 
'RG',  'DHch','MFpc','MF' , 'RGxf', 'DF' , 
'MFcl', 'MFcr' , 'DF' , 'FCsf', 'FCso' , 
'PPip', 'MFpc', 'MMCi', 'FC', 'DHcp', 'FCsf', 'RGwp', 'PP', 'FCxr','SHsu','IF', 'DHcp'); 

$grain_type_labels = array( 'Precipitation Particles', 'Columns', 'Needles', 'Plates', 'Stellars, Dendrites', 'Irregular crystals', 'Graupel', 'Hail','Ice Pellets', 'Rime', 
'Machine-made', 'Round polycrystalline particles', 'Crushed Ice particles'  ,
'Decomposing fragmented particles',  'Decomposing & fragmented precip. particles',  'Partly decomposed precipitation particles', 'Highly broken particles', 'Wind-broken precipitation parti',
'Rounded grains', 'Small rounded particles', 'Large rounded particles', 'Wind crust', 'Mixed forms - rounded'  ,
'Faceted crystals', 'Solid Faceted particles' , 'Small faceted cystals', 'Mixed forms faceted', 'Rounding faceted particles',
'Depth hoar', 'Cup-shaped crystals/depth hoar' , 'Hollow prisms', 'Chains of depth hoar' , 'Large striated crystals', 'Rounding depth hoar', 
'Surface hoar', 'Surface deposits and crusts', 'Cavity or crevasse hoar', 'Rounding surface hoar', 
'Melt forms' , 'Clustered rounded grains', 'Rounded polycrystals', 'Slush', 'Melt-freeze crust',
'Ice Formations', 'Ice layer', 'Ice column', 'Basal ice', 'Rain crust', 'Sun crust',
'Rounded Grains', 'PPco of depth hoar', 'Rounded poly-crystals','Wet Grains', 'Mixed forms rounded', 'Decomposing fragmenting particl', 
'clustered rounded grains' , 'melt-freeze crust', 'Decomposing fragmenting particles', 'small faceted particles', 'Solid faceted particles', 
'Ice pellets', 'Round polycrystaline particles' ,'Crushed ice particles','Faceted Crystals', 'Hollow cups' , 'Near surface faceted particles' , 'Wind packed', 'Precipitation particles','Faceted rounded particles', 'SH crystals' ,'Ice formations', 'Cup-shaped Crystals');

$dryness_labels = array( 'Dry', 'Moist', 'Wet', 'Very Wet', 'Slush' );
$dryness_codes = array( 'D', 'M', 'W', 'V' , 'S' );
$result_code = array( 'continue' => TRUE, 'message'=>'default message-layers');
//phpinfo();
/// function to load array



//var_dump($link);
//consultation:
    if ( $SERIAL == '' || !is_numeric( $SERIAL) || is_null( $SERIAL) ) {
	    $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' No serial given.');
    }else{
  db_set_active('avscience_db');
			$query = "SELECT SERIAL,PIT_XML
				        FROM `PIT_TABLE`   
			          WHERE SERIAL = ".$SERIAL or die("Error in the consult.." . mysqli_error($link));

			$result = db_query( $query);
		  db_set_active();
			
			//display information:
			$layer_attributes = array('startDepth', 'endDepth', 'layerNumber',  'waterContent', 'grainType', 'grainType1', 'grainSize', 'grainSize1','grainSizeUnits1' , 'grainSizeUnits2' , 'grainSuffix' , 'grainSuffix1', 'hardness1' , 'hardness2' , 'hsuffix1' , 'hsuffix2' , 'density1' , 'density2' , 'fromTop', 'multipleHardness' , 'multipleDensity' , 'multipleGrainType' , 'multipleGrainSize');
			//	echo "<table><thead>";    
	
			//echo "<td>SERIAL</td><td>". implode("</td><td>" , $layer_attributes) . "</td>";
	
			//	echo "</thead><tbody>";
			$count = 0;
			 while ( $row = $result->fetch() ) {
				$doc = new DOMDocument();
				//var_dump($doc);
				$corrected_encoding = Encoding::toUTF8($row->PIT_XML);
				$corrected_encoding = str_replace('grainSuffix="<"' ,'grainSuffix="-"' , $corrected_encoding);
				$corrected_encoding = str_replace('grainSuffix=">"' ,'grainSuffix="+"' , $corrected_encoding);
				$corrected_encoding = str_replace('sky < 2/8 covered' ,'FEW' , $corrected_encoding);
				$corrected_encoding = str_replace('Snow < 0.5 cm/hr' ,'S-1' , $corrected_encoding);
				if ( $row->PIT_XML != '') {
					$doc->loadXML($corrected_encoding);

					$pits = $doc->getElementsByTagName('Pit_Observation');
					foreach($pits as $pit_info){
						//var_dump($pit_info);
						$iLayerNumberPit = $pit_info->getAttribute('iLayerNumber') ;
						$iDepthPit = $pit_info->getAttribute('iDepth')	;	
					}	
			
					$layers = $doc->getElementsByTagName('Layer');
					/*
					*    Uncomment this block to populate the 'layers' table with info from the PIT_XML field
					*/
			
					$counter = 0;
					foreach($layers as $layer){
						$values_list = " '".$row->SERIAL. "', ";  // Prepending the id and pid fields here
						foreach ($layer_attributes as $attr){							// looping through to add values for each of the standard attributes
					
							if (in_array($attr , array('fromTop' , 'multipleHardness' , 'multipleDensity' , 'multipleGrainType' , 'multipleGrainSize') ) ){
								$values_list  .=  ($layer->getAttribute($attr) == 'true') ? " '1' ," :  " '0' ,";
							}elseif( in_array( $attr , array('grainType1', 'grainType') )  ){ 
									$values_list .=  "'".str_replace( $grain_type_labels, $grain_type_codes , $layer->getAttribute($attr) ). "',";
							}elseif( $attr == 'waterContent'){
							  $values_list .= "'".str_replace( $dryness_labels, $dryness_codes , $layer->getAttribute($attr) ). "',";
								//dsm(str_replace( $dryness_labels, $dryness_codes , $layer->getAttribute($attr) ));  // $layer->getAttribute($attr). "HELLOOOOOOooooooooo".$values_list;
							}else{$values_list .=   " '" . $layer->getAttribute($attr). "',";				}	
						}	
							//	echo "<td>iLayer: ".$iLayerNumberPit."  so: ";
							//echo ($layer->getAttribute('layerNumber') == $iLayerNumberPit) ?  $iDepthPit : " '' ";
							//	echo "</td><td>"  ;
				
							//	echo "</td>";
							$values_list .= ($layer->getAttribute('layerNumber') == $iLayerNumberPit) ?  " '1' ," :  " '' ,"  ;  // Post-pending the iLayerNumber (bool) and iDepth fields values, and finish up 
							$values_list .= ($layer->getAttribute('layerNumber') == $iLayerNumberPit ) ?  "'". $iDepthPit. "'" : " '' " ; // no trailing comma since this is the end of the var list
				
							//	echo "</tr>\n";
							$counter ++;
					
							// does pit and layer already exist in db?
					
							$query2 = "SELECT id FROM layers where `pid` = " .$SERIAL ." AND layerNumber = ".$layer->getAttribute('layerNumber');
						  db_set_active('avscience_db');
							
							$id_results = array();
							$result2 = db_query($query2);
							while ( $row2 = $result2->fetch() ) {
								$id_results[] = $row2->id ;
								
							}
							if ( count($id_results) == 0 ){
								$query3 = "INSERT INTO layers ( `pid`, `startDepth` , `endDepth`,  `layerNumber` , `waterContent` , `grainType1`, `grainType2` , `grainSize1` , `grainSize2` , `grainSizeUnits1` , `grainSizeUnits2` , `grainSuffix1` , `grainSuffix2` , `hardness1` , `hardness2` , `hsuffix1` , `hsuffix2` , `density1` , `density2` , `fromTop` , `multipleHardness` , `multipleDensity` , `multipleGrainType` , `multipleGrainSize`, `iLayerNumber`, `iDepth` )". 
									" VALUES ( ".  $values_list . "  )";
								$result3 = db_query($query3);
								if (!$result3){			
									$result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' Could not insert new layer: '.$layer->getAttribute('layerNumber') . " ".$query3);	
								}else { 
									$result_code = array( 'continue' => TRUE, 'message'=> "Successfullly inserted layer". $layer->getAttribute('layerNumber') ." for SERIAL: ". $SERIAL)  ; 
								}
						
							}else{
								//$result_code = array( 'continue' => TRUE, 'message'=> "<br />Layers already exist for serial: ".$SERIAL. ' TRY THIS: '.$query2);
							}
						  db_set_active('default');
											
					
				
				}
						// end uncommenting of the 'populate layers' block 
			} else{ $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.'No pit XML returned from query. '. $query );  }	
		}
  }
	return $result_code;
}



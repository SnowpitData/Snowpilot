<?php
include_once( DRUPAL_ROOT . '/sites/all/libraries/ForceUTF8/Encoding.php');
use \ForceUTF8\Encoding;
function populate_layers_table($SERIAL){

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
'PPip', 'MFpc', 'MMCi', 'FC', 'DHcp', 'FCsf', 'RGwp', 'PP', 'FCxr'); 

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
'Ice pellets', 'Round polycrystaline particles' ,'Crushed ice particles','Faceted Crystals', 'Hollow cups' , 'Near surface faceted particles' , 'Wind packed', 'Precipitation particles','Faceted rounded particles');

$dryness_labels = array( 'Dry', 'Moist', 'Wet', 'Very Wet', 'Slush' );
$dryness_codes = array( 'D', 'M', 'W', 'V' , 'S' );
$result_code = array( 'continue' => TRUE, 'message'=>'default message-layers');
//phpinfo();
/// function to load array

$link = mysqli_connect("localhost","jimurl","dRkV5iWqM3a54e5Z","jimurl_snowpilot_avscience") ;

//var_dump($link);
//consultation:
    if ( $SERIAL == '' || !is_numeric( $SERIAL) || is_null( $SERIAL) ) {
	    $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' No serial given.');
    }else{

			$query = "SELECT SERIAL,PIT_XML
				        FROM `PIT_TABLE`   
			          WHERE SERIAL = ".$SERIAL or die("Error in the consult.." . mysqli_error($link));

			$result = mysqli_query($link, $query);

			//display information:
			$layer_attributes = array('startDepth', 'endDepth', 'layerNumber',  'waterContent', 'grainType', 'grainType1', 'grainSize', 'grainSize1','grainSizeUnits1' , 'grainSizeUnits2' , 'grainSuffix' , 'grainSuffix1', 'hardness1' , 'hardness2' , 'hsuffix1' , 'hsuffix2' , 'density1' , 'density2' , 'fromTop', 'multipleHardness' , 'multipleDensity' , 'multipleGrainType' , 'multipleGrainSize');
			//	echo "<table><thead>";    
	
			//echo "<td>SERIAL</td><td>". implode("</td><td>" , $layer_attributes) . "</td>";
	
			//	echo "</thead><tbody>";
			$count = 0;
			while($row = mysqli_fetch_array($result)) {
				$doc = new DOMDocument();
				//var_dump($doc);
				$corrected_encoding = Encoding::toUTF8($row['PIT_XML']);
				
				$corrected_encoding = str_replace('grainSuffix="<"' ,'grainSuffix="\<"' , $corrected_encoding);
				$corrected_encoding = str_replace('grainSuffix=">"' ,'grainSuffix="\>"' , $corrected_encoding);
				$corrected_encoding = str_replace('sky < 2/8 covered' ,'SCT' , $corrected_encoding);
				$corrected_encoding = str_replace('Snow < 0.5 cm/hr' ,'S-1' , $corrected_encoding);
				
				if ( $row['PIT_XML'] != '') {
					$doc->loadXML($corrected_encoding);
					//var_dump($doc);
					$pits = $doc->getElementsByTagName('Pit_Observation');
					$pit_attributes = array('iLayerNumber' , 'iDepth');
					foreach($pits as $pit_info){
						//var_dump($pit_info);
						$iLayerNumberPit = $pit_info->getAttribute('iLayerNumber') ;
						$iDepthPit = $pit_info->getAttribute('iDepth')	;	
					}	
					/*		
					foreach($pits as $pit_info){
					//echo $pit_info->getAttribute('iLayerNumber');
					$query3 = "UPDATE PIT_TABLE SET `iLayerNumber` = '". $pit_info->getAttribute('iLayerNumber') ."' , `iDepth` = '". $pit_info->getAttribute('iDepth') ."' WHERE SERIAL = " . $row['SERIAL'];
			
					//echo "query: ".$query3. "<br />";
					// Uncomment this line to set the iLayer and iDepth fields in the PIT_TABLE 
					$result3 = mysqli_query($link, $query3);	
					$count++;			
					} 
					*/		
			
					$layers = $doc->getElementsByTagName('Layer');
					/*
					*    Uncomment this block to populate the 'layers' table with info from the PIT_XML field
					*/
			
					$counter = 0;
					foreach($layers as $layer){
					
						$values_list = " '' , '".$row['SERIAL']. "', ";  // Prepending the id and pid fields here
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
							$values_list .= ($layer->getAttribute('layerNumber') == $iLayerNumberPit) ?  " '1' ," :  " '0' ,"  ;  // Post-pending the iLayerNumber (bool) and iDepth fields values, and finish up 
							$values_list .= ($layer->getAttribute('layerNumber') == $iLayerNumberPit ) ?  "'". $iDepthPit. "'" : " '' " ; // no trailing comma since this is the end of the var list
				
				
							//	echo "</tr>\n";
							$counter ++;
					
							// does pit and layer already exist in db?
					
							$query2 = "SELECT id FROM layers where `pid` = " .$SERIAL ." AND layerNumber = ".$layer->getAttribute('layerNumber');
					
							$result2 = mysqli_query($link, $query2);
							$id_results = mysqli_fetch_assoc($result2);
							if ( count($id_results) == 0 ){
								$query3 = "INSERT INTO layers ( `id` , `pid`, `startDepth` , `endDepth`,  `layerNumber` , `waterContent` , `grainType1`, `grainType2` , `grainSize1` , `grainSize2` , `grainSizeUnits1` , `grainSizeUnits2` , `grainSuffix1` , `grainSuffix2` , `hardness1` , `hardness2` , `hsuffix1` , `hsuffix2` , `density1` , `density2` , `fromTop` , `multipleHardness` , `multipleDensity` , `multipleGrainType` , `multipleGrainSize`, `iLayerNumber`, `iDepth` ) 
									VALUES ( ".  $values_list . "  )";
								$result3 = mysqli_query($link, $query3);
								if (!$result3){			
									$result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.' Could not insert new layer: '.$layer->getAttribute('layerNumber') . mysqli_error($link). " ".$query3);	
								}else { 
									//$result_code = array( 'continue' => TRUE, 'message'=> "Successfullly inserted layer". $layer->getAttribute('layerNumber') ." for SERIAL: ". $SERIAL)  ; 
								}
						
							}else{
								$result_code = array( 'continue' => TRUE, 'message'=> "<br />Layers already exist for serial: ".$SERIAL. ' TRY THIS: '.$query2);
							}
							mysqli_free_result($result2);
					
					
				
				}
						// end uncommenting of the 'populate layers' block 
			} else{ $result_code = array( 'continue' => TRUE, 'message'=> $SERIAL.'No pit XML returned from query. '. $query );  }	
		}
  }
	return $result_code;
}

?>


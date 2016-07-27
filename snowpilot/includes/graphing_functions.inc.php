<?php

function snowpilot_layers_density_xlate(&$all_layers){
	usort($all_layers, 'layer_depth_val');
	$global_min = $all_layers[0]->y_val_top; $all_layers[max(array_keys($all_layers))]->y_val ;  $global_max = 751; // this max and min are pixel depths
	
	//dsm($all_layers);
	foreach($all_layers as $x => $layer){
    /// first, lets check to make sure thaere is a 'next' layer down there, and if there is a collision with it
				
	   if (  $x == 0 ) {   //the first layer is a special case
		 	$layer->y_val_top_xlate = $global_min;		
			continue;
		 }
		 $cg = array();
		 if (snowpilot_collision_check_down($all_layers[$x-1], $layer ) ){
				$layer->collision_flag = TRUE;
				//$all_layers[$x-1]->collision_flag = TRUE;
				//$cg[$x-1] = array ('y_val' => $all_layers[$x-1]->y_val , 'y_val_top' => $all_layers[$x-1]->y_val_top );
				$cg[$x] = array ('y_val' => $layer->y_val , 'y_val_top' => $layer->y_val_top );
				//
				//  Loop here to look for conflicts between the cg and the layer(s) above
				//
				$prev_test = $x-1;
				while ( ( $prev_test >= 0 ) && snowpilot_collision_check_cg_up($cg, $all_layers[$prev_test]) ){
				//	dsm('new cycle');
					$cg = array ( $prev_test => array( 'y_val' => $all_layers[$prev_test]->y_val, 'y_val_top' => $all_layers[$prev_test]->y_val_top )) + $cg;	 
					$all_layers[$prev_test]->collision_flag = TRUE;
				//	dsm($cg);
					//dsm($all_layers[$prev_test-1]);
					$prev_test = $prev_test - 1;
				}
				if ( count ($cg)){ snowpilot_write_xlations($cg, $all_layers);}
				$cg = array();
				
			}else{ // no conflict with the layer below ( or no layer )
				/*if ($x == 1) { 
					$cg[$x] = array ('y_val' => $layer->y_val , 'y_val_top' => $layer->y_val_top );
					snowpilot_write_xlations($cg, $all_layers);
				}else*/
				
				if ( isset($all_layers[$x-1]->collision_flag) && $all_layers[$x-1]->collision_flag == TRUE){ // layer above IS a collision, make it 20 pixels high, and set that to the top of current layer too		
					$all_layers[$x-1]->y_val_xlate = $all_layers[$x-1]->y_val_top_xlate + 20;
					$layer->y_val_top_xlate =	$all_layers[$x-1]->y_val_xlate;
				}else{   // layer obove is not a collision, write it all straight across
				
					$layer->y_val_top_xlate = $layer->y_val_top;
					$all_layers[$x-1]->y_val_xlate = $layer->y_val_top;	
	
				}
				// make sure the bottom line of the last layer is in the right spot
				// TODO: updated so that the last layer is at least 20 pixels tall, if it is  a 'hanging' layer, i.e., not stuck ot the bottom of the graph
				if ( $x == count($all_layers) - 1 ){ 	$all_layers[$x]->y_val_xlate	= $all_layers[$x]->y_val; }
				
			}
			
		}

}


function snowpilot_collision_check_down($layerx, $layery){
	if (($layerx->y_val_top_xlate + 20 > $layery->y_val-20) || $layery->y_val_top+20 >$layery->y_val){
		return TRUE;
	}
	return FALSE;
	
}

function cg_max($cg){
	$max = NULL;
	$min = NULL;
	foreach ($cg as $key => $test){
		$min = $test->y_val > $min ? $test->y_val : $min;
	}
	$max = count($cg)*20 +$min;
	
	return $max;
	
} 


function st_collision_check_down($test_x, $test_y, &$cg = array(), $measure_from = 'bottom'){
//	dsm (cg_max($cg));
	if (  isset ( $test_x->collision_flag) && $test_x->collision_flag == TRUE && $test_y->y_position <= cg_max($cg) ){
		$test_y->collision_flag = TRUE;
		$cg[] = $test_x;
		return TRUE;
			
	}elseif ( ($test_x->y_position+20 >= $test_y->y_position))  { // better
		$test_y->collision_flag = TRUE;
		$test_x->collision_flag = TRUE;
		$cg[] = $test_x; $cg[] = $test_y;
		return TRUE;
	}
	return FALSE;
	
}
//
// _strip_dupe_tests - this function strips out the 
// 
	

function _strip_dupe_tests(&$test_results, $pit_depth, $measure_from ){
	foreach ($test_results as $x => $test){
		if ( $test->multiple > 0){
		  $test->y_val = snowpit_graph_pixel_depth($test->field_depth['und'][0]['value'], $pit_depth, $measure_from );
			$simple_test_results[] = $test;
	  }else{
			// dont set y_val, dont set str[]
	  }
	}
	return $simple_test_results;
}


function snowpilot_write_xlations($cg, &$all_layers){
	// these work fine in testing but getting a little unneeded now
	//$cg = array_reverse($cg, TRUE);
	$counter = 0;
	foreach($cg as $x => $cg_layer){
		$all_layers[$x]->y_val_top_xlate =  _cg_stats($cg, 'cg_top') + 20* $counter;  //dsm ($all_layers[$x-1]);
		
		if ( $x > 0 ) { $all_layers[$x-1]->y_val_xlate = _cg_stats($cg, 'cg_top') + 20 * $counter; }
		// make sure the bottom line of the last layer is in the right spot
		if ( $x == count($all_layers) - 1 ){ 	$all_layers[$x]->y_val_xlate	= $all_layers[$x]->y_val; }
		//dsm($all_layers);		
		$counter ++;											
	}		
																									
	return $all_layers;
}

///
//   this function multiple up needs to be called, repeatedly, after each new collisions while going DOWN 
//   that is because each new detection of a layer below could make the height of a cg taller, 
//    meaning that it could interact with an existing cg group

function snowpilot_collision_check_cg_up($cg, $layer){
	
	//if ($layer->y_val > 250 ) dsm( $cg, $layer);
	if (( _cg_stats($cg, 'cg_top')  < $layer->y_val_top_xlate + 20) || ( isset($layer->collision_flag) && ($layer->collision_flag == TRUE ) ) ){
		return TRUE;
	}
	return FALSE;
}
// this function may not be necessary!
/*
function snowpilot_collision_check_multiple($cg, $layer){
	_cg_stats(&$cg);
	//dsm('newlayer');dsm($cg_bottom); dsm($layer->y_val_top);
	if (( $cg['cg_top'] < $layer->y_val_top_xlate  && $cg_top > $layer->y_val_xlate  )
			|| ($cg_bottom >  $layer->y_val_top )   ){
				
				return TRUE; 
			}
			return FALSE; 
}
*/


function _cg_stats($cg, $stat = NULL, $global_min = 157 , $global_max = 751){
	$span = count($cg) * 20; // span of pixels

	$first_item = array_slice($cg, 0, 1);
	$last_item = end ($cg);
	//dsm($first_item);
	$length = ($last_item['y_val'] - $first_item[0]['y_val_top'])/2;
	//dsm($length); //dsm($span);
	$cg_top = ($last_item['y_val'] - $first_item[0]['y_val_top'])/2 + $first_item[0]['y_val_top']  - $span/2 ;
	$cg_bottom = ($last_item['y_val'] - $first_item[0]['y_val_top']) + $first_item[0]['y_val_top'] + $span/2 ;
	
	
	
	//$cg_top =  $sum/count($cg)-$span/2;
	//$cg_bottom =  $sum/count($cg) + $span/2; 
	
	if ($cg_top < $global_min ){ $cg_top = $global_min; $cg_bottom = $global_min + $span ; }
	
	if ( $stat){ 
		switch ($stat):
			case 'cg_top':
			return $cg_top; break;
			case 'cg_bottom':
			return $cg_bottom; break;
			case 'span':
			return $span; break;
		endswitch;
	}
}


function _h2pix($h, $all = FALSE){
	$h2pix = 
		array(
			'F-' => 423,
			'F' => 399,
			'F+' => 375,
			'4F-' => 351,
	 		'4F' => 327,
			'4F+' => 303,
			'1F-' => 279,
			'1F' => 255,
			'1F+' => 231,
			'P-' => 207,
			'P' => 183,
			'P+' => 159,
			'K-' => 135,
			'K' => 111,
			'K+' => 87,
			'I-' => 63,
			'I' => 39,
			//'' => 451
		); 
		if ($all){
			return $h2pix;
		}else{
			return $h2pix[$h];
		}
}

// breaks $node->body into 1, 2, or 3 lines depending on how long it is
// a fairly simpllistic algorithm: if the whole thing is too long for the line, chop it into ( at a space )
// if the pieces are still too long, chop it in thirds
// TODO: this function will have to take into account extra-long notes entries; 
// after three break point, we need to keep going to get them short enough; but also leave a [ More Notes ] item on jpgs; and 
// and for pdf output, an extra sheet of paper
// $short : boolean - whether long texts will be truncated at three lines ( for png or jpg output; or full, for pdf output .... )

function _output_formatted_notes($string, $font , $short = TRUE){

	  $pointer = 0;
  	$last_space = 0 ;
		while ( substr($string ,$pointer) <> '' ){

			//dsm(substr($string, $pointer, 1));
	  	if (substr($string, $pointer, 1) == ' ' ) {
	  		$line = imagettfbbox(9,0,$font,substr($string, 0, $pointer));
	  		if ( $line[2] < 935){ 
	  			$last_space = $pointer;
					$pointer ++;		
	  		}else{ 
	  			$parts[] = substr($string, 0, $last_space);
		  		$string = substr( $string, $last_space);
					$pointer = 0;
					$last_space = 0 ;
	  		}
	  	}else{
	  		$pointer++;
	  	}		
			//
			// This is for the case where we are finally at the end of the string
			//
			if ( substr($string, $pointer) == '') {
			  $parts[] = substr($string, 0, $pointer);
				$string = '' ; 
		  }	
			//
			//  This makes sure that there is enough room at the end of the fourth line to include " [ ... more notes ]". which will be added in the main function, not this helper function
			//
			if ( $short && isset ($parts) && count ( $parts ) > 4 ){
				$line = imagettfbbox(9,0,$font,$parts[3]);
				while ( $line[2] > 800 ){
					$parts[3] = substr($parts[3],0, strlen($parts[3]) -1 );
					$line = imagettfbbox(9,0,$font,$parts[3]);
				}
			}
		}
	return $parts;
}

function snowpilot_draw_layer_polygon(&$img, $layer, $color, $filled = TRUE){
	if ( !isset($layer->field_hardness['und'][0])){ // error checking in case that hardness is not set
			$pink_problem = imagecolorallocate($img,254, 240, 240);
			$dark_pink = imagecolorallocate($img, 142, 47, 11); //#8c2e0b , the border for warning messages
			$black = imagecolorallocate($img,0,0,0);
			$value_font = '/sites/all/libraries/fonts/Arial Bold.ttf';
			
			$points = array(15, $layer->y_val_top,  447 , $layer->y_val_top, 447, $layer->y_val, 15, $layer->y_val);
			imagefilledpolygon($img, $points, 4, $pink_problem) ;
			imagettftext($img, 13, 0, 35, ($layer->y_val - $layer->y_val_top)/2 + $layer->y_val_top, $black, $value_font, 'No Hardness specified, please Update Snowpit');///
			imagepolygon($img,$points, 4, $dark_pink);
			
			
	}else{  // hness IS set, a basic part of a layer  ; excess layers should be already stripped by _validate
	$hness = $layer->field_hardness['und'][0]['value'];
	if ( $layer->field_use_multiple_hardnesses['und'][0]['value'] == '1' &&
		isset ($layer->field_hardness2['und'][0]['value'])){	
			$hness2 = $layer->field_hardness2['und'][0]['value'];
			$points = array(_h2pix($hness), $layer->y_val_top,  447 , $layer->y_val_top, 447, $layer->y_val, _h2pix($hness2), $layer->y_val);
			if ($filled) {
				imagefilledpolygon($img, $points, 4, $color);
			}else{
				imagepolygon($img,$points, 4, $color);
			}
		}else{
			if ($filled) {
				imagefilledrectangle($img, _h2pix($hness), $layer->y_val, 446 , $layer->y_val_top, $color) ;
			}else{
				imagerectangle($img, _h2pix($hness), $layer->y_val, 447 , $layer->y_val_top, $color );
			}
		}
	}
		
	
}

function _snowpilot_option_padding($tid){
	$tid2spaces = array(
		'33' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Precipitation particles
		'34' => '', // Decomposing & fragmented PP
		'35' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Rounded Grains
		'36' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Faceted crystals
		'37' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  // depth hoar
		'38' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  // surface hoar
		'39' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  // melt forms
		'40' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  // ice formations
		'41' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  // machine made snow
		
		// Precipitation Particles types
		'42' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', //PP -> columns
		'43' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // PP -> Needles
		'44' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // PP -> plates
		'45' => '&nbsp;', // PP -> stellars, dendrites
		'46' => '&nbsp;&nbsp;&nbsp;', // irregular crystals
		'47' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // graupel
		'48' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Hail
		'49' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Ice pellets
		'50' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // rime
		
		// Decomposing and fragmented precip particles
		'104' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // partly decomposed PP
		'78' => '&nbsp;', // wind-broken precip particles
		//  Rounded grain types
		'79' => '&nbsp;&nbsp;&nbsp;&nbsp;', // small rounded particles
		'80' => '&nbsp;&nbsp;&nbsp;&nbsp;', //large rounded particles
		'81' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', //Wind packed
		'82' => '&nbsp;', // faceted rounded particles
		// Faceted crystal types
		'105' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Solid faceted particles
		'83' => '&nbsp;', // Near surface faceted particles
		'84' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  // Rounding faceted particles 
		
		// Surface Hoar types
		'90' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // surface hoar crystals
		'91' => '&nbsp;', // cavity or crevasse hoar
		'92' => '&nbsp;&nbsp;&nbsp;&nbsp;', // Rounding surface hoar
		// Depth Hoar types
		'85' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Hollow cups
		'86' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', //Hollow Prizms
		'87' => '&nbsp;&nbsp;&nbsp;&nbsp;', // Chains of depth hoar
		'88' => '&nbsp;', // large striated crystals
		'89' => '&nbsp;&nbsp;&nbsp;', // rounding depth Hoar
		
		// Melt forms types
		'93' => '&nbsp;',// clustered rounded grains
		'94' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', //rounded polycrystals
		'95' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // Slush
		'96' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', //Melt-freeze crust
		
		// Ice Formations
		'97' => '&nbsp;&nbsp;&nbsp;&nbsp;',// Ice Layer
		'98' => '&nbsp;',// Ice column
		'99' => '&nbsp;&nbsp;&nbsp;',// Basal Ice
		'100' => '&nbsp;',//  Rain crust
		'101' => '&nbsp;&nbsp;&nbsp;',// Sun crust
		
		// Machine made snow types
		'102' => '&nbsp;', // rounded Polycrystalline particles
		'103' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', // crushed Ice Particles  
		
	);
	if ( is_numeric($tid) && isset($tid2spaces[$tid])){
	  return $tid2spaces[$tid];
	}else{
		return '';
	}
}


function _tid2snowsymbols($tid = NULL, $all = FALSE){
	
	
	$tid2snowsymbols = array(
		'33' => '&#x0792;', // Precipitation particles
		'34' => '&#x0794;', // Decomposing & fragmented PP
		'35' => '&#x0795;', // Rounded Grains
		'36' => '&#x0779;', // Faceted crystals
		'37' => '&#x077c;',  // depth hoar
		'38' => '&#x0781;',  // surface hoar
		'39' => '&#x0799;',  // melt forms
		'40' => '&#x079a;',  // ice formations
		'41' => '&#x0793;',  // machine made snow
		
		// Precipitation Particles types
		'42' => '&#x079b;', //PP -> columns
		'43' => '&#x079c;', // PP -> Needles
		'44' => '&#x079d;', // PP -> plates
		'45' => '&#x079e;', // PP -> stellars, dendrites
		'46' => '&#x079f;', // irregular crystals
		'47' => '&#x07a0;', // graupel
		'48' => '&#x07a1;', // Hail
		'49' => '&#x07a2;', // Ice pellets
		'50' => '&#x07A3;', // rime
		
		// Decomposing and fragmented precip particles
		'104' => '&#x0794;', // partly decomposed PP
		'78' => '&#x07A7;', // wind-broken particles
		//  Rounded grain types
		'79' => '&#x07A8;', // small rounded particles
		'80' => '&#x07A9;', //large rounded particles
		'81' => '&#x07AA;', //Wind packed
		'82' => '&#x07ab;', // faceted rounded particles
		// Faceted crystal types
		'105' => '&#x0779;', // Solid faceted particles
		'83' => '&#x077A;', // Near surface faceted particles
		'84' => '&#x077b;',  // Rounding faceted particles 
		
		// Surface Hoar types
		'90' => '&#x0798;', // surface hoar crystals
		'91' => '&#x0782;', // cavity or crevasse hoar
		'92' => '&#x0783;', // Rounding surface hoar
		// Depth Hoar types
		'85' => '&#x077c;', // Hollow cups
		'86' => '&#x077d;', //Hollow Prizms
		'87' => '&#x077e;', // Chains of depth hoar
		'88' => '&#x077f;', // large striated crystals
		'89' => '&#x0780;', // rounding depth Hoar
		
		// Melt forms types
		'93' => '&#x0784;',// clustered rounded grains
		'94' => '&#x0785;', //rounded polycrystals
		'95' => '&#x0786;', // Slush
		'96' => '&#x0787;', //Melt-freeze crust
		
		// Ice Formations
		'97' => '&#x0788;',// Ice Layer
		'98' => '&#x0789;',// Ice column
		'99' => '&#x078A;',// Basal Ice
		'100' => '&#x078B;',//  Rain crust
		'101' => '&#x078C;',// Sun crust
		
		// Machine made snow types
		'102' => '&#x07A4;', // rounded Polycrystalline particles
		'103' => '&#x07A5;', // crushed Ice Particles  
		
	);
	
	if ($tid == NULL || $all ) { return $tid2snowsymbols;
	}elseif( is_numeric($tid) && isset($tid2snowsymbols[$tid])){ 
		return $tid2snowsymbols[$tid]; 
	}else{ return ''; }
	
}


function snowpit_graph_pixel_depth($depth, $pit_depth, $meas_from = 'bottom'){
	$pixels_per_cm = 594 / (int) $pit_depth ;
	
	$h = ($meas_from == 'top') ? (157 + $depth * $pixels_per_cm) : (751 - $depth * $pixels_per_cm );
	return $h; 
}


function _set_stability_test_pixel_depths(&$test_results, $pit_depth, $measure_from = 'bottom'){

	//dsm($test_results);
	foreach ($test_results as $x => $test){
		//
		// We need to see if there are multiple test scores that are identical and indicate as "2x", etc. 
		// If so we 'continue 3; to skip the last processing in the foreach loop, so test->y_position is never set.
		//
		$test->multiple = 1;
		$depth_true = isset($test->field_depth['und'][0]['value']) ? $test->field_depth['und'][0]['value'] : 0 ;
		$test->y_position = snowpit_graph_pixel_depth($depth_true, $pit_depth, $measure_from);
		foreach ( $test_results as $test_compare){
			if ( ($test->item_id != $test_compare->item_id) &&
				( $test->field_stability_test_type == $test_compare->field_stability_test_type  ) &&
				( $test->field_stability_test_score == $test_compare->field_stability_test_score  ) &&
				( $test->field_depth == $test_compare->field_depth  ) &&
				( isset ($test_compare->multiple) && ($test_compare->multiple > 0))

			){ 		// so the two tests ARE exactly alike for those previous fields; how about for test-specific fields?

 			 switch ($test->field_stability_test_type['und'][0]['value']){
 				  case 'ECT':
 					  if (( $test->field_ec_score == $test_compare->field_ec_score) &&
							  ( $test->field_shear_quality == $test_compare->field_shear_quality) &&
								( $test->field_fracture_character == $test_compare->field_fracture_character)
 						) {$test->multiple += 1; $test_compare->multiple = 0; unset($test_compare); continue 3;	}
 					break;
					case 'CT':
				  if (( $test->field_ct_score == $test_compare->field_ct_score) &&
						  ( $test->field_shear_quality == $test_compare->field_shear_quality) &&
							( $test->field_fracture_character == $test_compare->field_fracture_character)
					) {$test->multiple += 1; $test_compare->multiple = 0;  unset($test_compare); continue 3;	}
					case 'PST':
				  if (( $test->field_length_of_isolated_col_pst == $test_compare->field_length_of_isolated_col_pst) &&
						  ( $test->field_length_of_saw_cut == $test_compare->field_length_of_saw_cut) &&
							( $test->field_data_code_pst == $test_compare->field_data_code_pst)
					) {$test->multiple += 1; $test_compare->multiple = 0;  unset($test_compare); continue 3;	}
					break;
					case 'RB':
				  if (( $test->field_length_of_isolated_col_pst == $test_compare->field_length_of_isolated_col_pst) &&
					  ( $test->field_shear_quality == $test_compare->field_shear_quality) &&
							( $test->field_release_type == $test_compare->field_release_type)
					) {$test->multiple += 1; $test_compare->multiple =0;  unset($test_compare); continue 3;	}				
					break;
					case 'ST':
					case 'SB':
					$test->multiple += 1; $test_compare->multiple =0 ;  unset($test_compare); continue 3;	
					break;
				} 
			} // end of "yes, this could be a repeated test" processing
		}	// end of looping through test-comparision
	 		
	} // end of looping through test results in general
	// now we begin to set the y_position
	$st_cg = array();
  $simple_test_results = _strip_dupe_tests($test_results, $pit_depth, $measure_from );

	// loop through it again to set the y_position to correct height
	foreach ( $simple_test_results as $x => $test){
  	if($x <> 0 && st_collision_check_down($simple_test_results[$x - 1], $test, $st_cg )  ){ 
  		$test->collision_flag = TRUE;
			$test->y_position = $simple_test_results[$x - 1]->y_position +20;

    }else{
  	 if ( count ($st_cg)){
			 //dsm($st_cg);
  		 //st_write_xlate_group( $st_cg, $test_results);
	  	 $st_cg = array();
		
  	 }else{ // this is a singluar item, write straight acrose
  		 $test->y_position = snowpit_graph_pixel_depth($test->field_depth['und'][0]['value'], $pit_depth, $measure_from );
		
	   }
	  }
	 } // end of second looping through
	 // stability test write xlate group
	 //dsm($test_results);
	 $test_results = $simple_test_results;
	return $simple_test_results;
}


function _generate_specifics_string($node) {
	$string = '';
	$included_fields = array( 'field_practice_pit', 'field_pit_dug_in_a_ski_area',  
		'field_pit_is_representative_of_backcountry','field_adjacent_to_avy', 'field_near_avalanche', /* a list type field, rather than boolean */
	  'field_collapsing_widespread', 'field_collapsing_localized', 'field_cracking', 'field_recent_activity_on_similar', 'field_recent_activity_on_differe',
		'field_instability_rapidly_rising' , 'field_ski_tracks_on_slope', 'field_we_skiied_slope', 'field_snowmobile_tracks_on_slope', 'field_we_snowmobiled_slope','field_poor_pit_location' ); // etc
	
		$specifics = array();
	
	// then we loop through the array and add them each to the string.
	foreach($included_fields as $key => $field){
		
		if (isset( $node->$field) ){
			// Anything beyond the specific field here ,  will require accessing via the $node  object  
			
			$field_item = $node->$field;
			if ( isset($field_item['und'][0]) && ($field_item['und'][0]['value'] != '0')){
				$item_full = field_info_instance('node', $field, 'snowpit_profile');
				switch ($field){
					case 'field_adjacent_to_avy':
						$specifics[] = $item_full['label'].": ".$node->field_near_avalanche['und'][0]['value'];
					break;
					case 'field_near_avalanche':
					break;
					
					case 'field_stability_on_similar_slope':
						$specifics[] = $item_full['label'].": ". $node->field_stability_on_similar_slope['und'][0]['value'];
					break;
					
					default:
						$specifics[] = $item_full['label'];
					break;
					
				}
			}
		}
	}
  return implode('; ', $specifics);
}


function snowpilot_snowpit_graph_header_write($node){	
	// also add user account info to this:
	$user_account = user_load($node->uid);
	$snowpit_unit_prefs = snowpilot_unit_prefs_get($node, 'node');
	$pit_depth_arr = _snowpilot_find_pit_depth($node);
	$pit_depth = $pit_depth_arr['und'][0]['value'];
// Image Variables
$width = 994;
$height = 840;
//imageloadfont()
// Create GD Image

$img = imagecreatetruecolor($width, $height);
// Assign some colors
$black = imagecolorallocate($img, 0, 0, 0);
$white = imagecolorallocate($img, 255, 255, 255);
$purple_layer = imagecolorallocate($img, 154, 153, 213);
$red_layer = imagecolorallocate($img, 178, 36, 35);
$blue_outline = imagecolorallocate($img, 15, 8, 166);
$pink_problem = imagecolorallocate($img,254, 240, 240);

// Set background color to white
imagefill($img, 0, 0, $white);
// Label Y axis and draw horizontal lines
$label_font = '/sites/all/libraries/fonts/Arial.ttf';
$value_font = '/sites/all/libraries/fonts/Arial Bold.ttf';
$snowsymbols_font ='/sites/all/libraries/fonts/ArialMT28.ttf';

      imagettftext($img, 11, 0, 14, 17, $black, $value_font, $node->title);
			// Location information
      if ( isset ($node->field_loaction['und'][0]['tid'])){
				$term_obj = taxonomy_term_load($node->field_loaction['und'][0]['tid']);
				$loc_name = $term_obj->name;
				$parents = taxonomy_get_parents($node->field_loaction['und'][0]['tid']);
				$parents_name = '';
				foreach($parents as $parent ){
					$parents_name .= $parent->name;
				}
				
				if ( isset ($parents_name)){
			    imagettftext($img, 11, 0, 14, 35, $black, $value_font, $parents_name);
				}
				if ( isset ($loc_name)){
        	imagettftext($img, 11, 0, 14, 53, $black, $value_font, $loc_name); 
				}
			}
      $text_pos = imagettftext($img, 11, 0, 14, 71, $black, $label_font, 'Elevation: ');
			if (isset($node->field_elevation['und'])){
				imagettftext($img, 11, 0, $text_pos[2], 71, $black, $value_font, $node->field_elevation['und'][0]['value'] .' '.$node->field_elevation_units['und'][0]['value']);
 	 		}
      $text_pos = imagettftext($img, 11, 0, 14, 89, $black, $label_font, 'Aspect: ');
			if (isset($node->field_aspect['und'])){
				$aspect = field_view_field('node', $node, 'field_aspect');
        imagettftext($img, 11, 0, $text_pos[2], 89 , $black, $value_font ,$aspect[0]['#markup']);
			}
			$text_pos = imagettftext($img, 11, 0, 14, 107, $black, $label_font, 'Specifics: ');
			$specifics = _generate_specifics_string($node);
			imagettftext($img, 9, 0, $text_pos[2], 107, $black, $value_font, $specifics );
			// Observer
			imagettftext($img, 11, 0, 183 , 17, $black,  $value_font, $user_account->name . " (". $user_account->field_first_name['und'][0]['value']. " ". $user_account->field_last_name['und'][0]['value'].")");
			imagettftext($img, 11, 0, 183, 35, $black, $value_font, date('D M j H:i Y (T) ', 
			strtotime($node->field_date_time['und'][0]['value']." ". $node->field_date_time['und'][0]['timezone_db']))); //Date / Time of observation

			$text_pos = imagettftext($img, 11, 0, 183, 53, $black, $label_font, "Co-ord: ");
			if ($snowpit_unit_prefs['field_coordinate_type'] != 'UTM'){
				if (isset($node->field_latitude['und']) && isset($node->field_longitude['und'])){
					imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, 
						number_format($node->field_latitude['und'][0]['value'] , 5).
						$node->field_latitude_type['und'][0]['value'].", ". 
						number_format($node->field_longitude['und'][0]['value'] , 5).
						$node->field_longitude_type['und'][0]['value']);
				}
			}else{ // Not Lat long, their preference is UTM
				if(isset($node->field_east['und']) && isset($node->field_north['und'])){
					imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, 
						$node->field_utm_zone['und'][0]['value'].' '.
						$node->field_east['und'][0]['value'] .
					  $node->field_longitude_type['und'][0]['value'].' '.				
					  $node->field_north['und'][0]['value'] .
					  $node->field_latitude_type['und'][0]['value']
				  );
				}
			}
			
			$text_pos = imagettftext($img, 11, 0, 183, 71, $black, $label_font, "Slope Angle: ");
			if (isset($node->field_slope_angle['und'])){
				$slope_angle = field_view_field('node', $node, 'field_slope_angle');
				imagettftext($img , 11, 0, $text_pos[2], 71, $black, $value_font, $slope_angle[0]['#markup'] );
			}
			$text_pos = imagettftext($img, 11, 0, 183, 89, $black, $label_font, "Wind Loading: ");
			if (isset($node->field_wind_loading['und'])){
				imagettftext($img, 11, 0, $text_pos[2], 89, $black, $value_font, $node->field_wind_loading['und'][0]['value'] );
			}
			$text_pos = imagettftext($img, 11, 0, 429, 17, $black, $label_font, "Stability on similar slopes: ");
			if(isset($node->field_stability_on_similar_slope['und'])){
				$similar_stability = field_view_field('node', $node, 'field_stability_on_similar_slope') ;
				imagettftext($img, 11, 0, $text_pos[2], 17, $black, $value_font, $similar_stability[0]['#markup'] );
			}
			$text_pos  = imagettftext($img, 11, 0, 429, 35, $black, $label_font, "Air Temperature: ");
			if(isset($node->field_air_temp['und'])){
				$air_temp = field_view_field('node', $node, 'field_air_temp');
			  imagettftext($img, 11,0, $text_pos[2], 35, $black, $value_font, $air_temp[0]['#markup']."&#176;". $snowpit_unit_prefs['field_temp_units'] );
			}
			$text_pos = imagettftext($img, 11, 0, 429, 53, $black, $label_font, "Sky Cover: ");
			if (isset($node->field_sky_cover['und'])){
				$sky_cover = field_view_field('node', $node, 'field_sky_cover'); 
			imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, html_entity_decode($sky_cover[0]['#markup']) );
			}
			$text_pos = imagettftext($img, 11, 0, 429, 71, $black, $label_font, "Precipitation: " );
			if ( isset($node->field_precipitation['und'])){
				$precipitation = field_view_field('node', $node, 'field_precipitation');
			  imagettftext($img, 11, 0, $text_pos[2] , 71, $black, $value_font, $precipitation[0]['#markup'] );
			}
			$text_pos = imagettftext($img, 11, 0, 429, 89, $black, $label_font, "Wind: ");
			if (isset($node->field_wind_direction['und'][0]['value'])){
				$text_pos = imagettftext($img, 11 , 0, $text_pos[2]+4, 89, $black , $value_font, snowpilot_cardinal_wind_dir($node->field_wind_direction['und'][0]['value'] ));			
			}
			if (isset($node->field_wind_speed['und'][0]['value'])){
				$wind_speed = field_view_field('node', $node, 'field_wind_speed');
				 imagettftext($img, 11 , 0, $text_pos[2], 89, $black , $value_font, " ".$wind_speed[0]['#markup']);
			}
			
			
			imagettftext( $img, 11, 0 , 805, 17, $black, $label_font, 'Layer Notes');
			
			$textpos = imagettftext($img, 11, 0, 14,779, $black, $label_font, 'Notes: ');
			if ( isset($node->body['und'][0]) && $node->body['und'][0]['safe_value'] != '' ){ 
				$notes_lines = _output_formatted_notes($node->body['und'][0]['safe_value'], $value_font);
				foreach($notes_lines as $x => $line){
					if ($x <= 3 ){
					  imagettftext($img, 9, 0, $textpos[2], 779 + $x * 19 ,$black, $value_font,$line);
					}else {
						imagettftext($img, 9, 0, 870, 779 + 3 * 19 ,$red_layer, $value_font, "[ ... more notes ]");
						break;
					}
				}
			}			
			//  write stability tests column and comments 
			//  TODO : expand into its own function
			if (isset($node->field_test['und'])){
				$ids = array();
				foreach($node->field_test['und'] as $test) {  $ids[] = $test['value'];}
				
				$test_results = field_collection_item_load_multiple($ids);
				uasort($test_results, 'depth_val');
				//
				// reversing the order of the test results makes it work when outputting the Stability test results on the graph when measuring from top
				if ( $snowpit_unit_prefs['field_depth_0_from'] == 'top'){
				  $test_results = array_reverse($test_results);
				}
				$bak = _set_stability_test_pixel_depths($test_results, $pit_depth, $snowpit_unit_prefs['field_depth_0_from']); // this sets a $test->y_position = integer which is where the line and text should go in the column on the right
				$comment_count = 0;
				$textpos = array();
				if ( isset( $node->field_total_height_of_snowpack['und'][0]['value'])  ){ 
					$textpos = imagettftext($img, 9 , 0, 645, 17, $black, $value_font, 'HS'. $node->field_total_height_of_snowpack['und'][0]['value'] );  
					$comment_count = 1;
				}
				if ( isset( $node->field_surface_penetration['und'] )  && ( $node->field_surface_penetration['und'][0]['value'] == 'boot' ) && (  $node->field_boot_penetration_depth['und'][0]['value'] != '' )){	
					$xpos = ( count($textpos) ) ? $textpos[2] + 5  : 645 ;
						imagettftext($img,9, 0, $xpos, 17, $black, $value_font , 'PF'.$node->field_boot_penetration_depth['und'][0]['value']  );
						$comment_count = 1;
				}elseif(isset( $node->field_surface_penetration['und'] )  && ( $node->field_surface_penetration['und'][0]['value'] == 'ski' ) && (  $node->field_ski_penetration['und'][0]['value'] != '' )){
					
				}
					
				imagettftext( $img, 11, 0 , 645, $comment_count*13 + 20, $black, $label_font, 'Stability Test Notes');
				
				foreach ( $test_results as $x => $test){
					if ( isset($test->field_stability_test_type['und'][0]['value']) && isset( $test->field_depth['und']) ){
				  	// this use of imageline will need to be updated to include some kind of cluster management
						if (isset( $test->y_position) ){ // if this has been 'multipled' with another stb test, the y_position won't be set
							imageline($img, 707, $test->y_position, 941, $test->y_position, $black);
							imagettftext($img, 9, 0, 712, $test->y_position - 5,$black, $label_font, stability_test_score_shorthand($test, $snowpit_unit_prefs) );
						}
						if ( count($test->field_stability_comments) ){
							if ( $comment_count < 5 ){
								imagettftext($img, 9, 0, 645, $comment_count*13 + 35, $black, $value_font,$test->field_depth['und'][0]['value'].': '.$test->field_stability_comments['und'][0]['safe_value'] );
								$comment_count++;
							}else{
								imagettftext($img, 7, 0, 645, $comment_count*13 + 31, $red_layer , $label_font, '[ More Stability Test Notes ... ]' );
							}
						}
					}
				}
			}
			// end stability test column
			
			// write rho column info
			//
			if (isset($node->field_density_profile['und'])){
				foreach ( $node->field_density_profile['und'] as $x => $density_item){
					$density = field_collection_item_load($density_item['value']);
								
					// this use of imageline will need to be updated to include some kind of cluster management
					imageline($img, 667, snowpit_graph_pixel_depth($density->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from']), 707, snowpit_graph_pixel_depth($density->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from']),$black);
					imagettftext($img, 8, 0, 669, snowpit_graph_pixel_depth($density->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'])-5,$black, $label_font, $density->field_density_top['und'][0]['value']);
				}
			}
			//
			//  Prep for the 2 Cycles trhough layers 
			// 
			
			if ( isset($node->field_layer['und'])){
				
				$ids = array();
				foreach ($node->field_layer['und'] as $lay ){ $ids[] = $lay['value']; }
				$all_layers = field_collection_item_load_multiple($ids);
				
				
				foreach($all_layers as $x => $layer){
					if($snowpit_unit_prefs['field_depth_0_from'] == 'top'){
						$layer->y_val_top =		$y_val_top = round(snowpit_graph_pixel_depth($layer->field_bottom_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] )); 
						$layer->y_val = $y_val = round(snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] )); 
					}else{
						$layer->y_val =		$y_val = round(snowpit_graph_pixel_depth($layer->field_bottom_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] )); 
						$layer->y_val_top =		$y_val_top = round(snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] )); 
					}
				}
				
				$keyed_all_layers = $all_layers;
									
				snowpilot_layers_density_xlate($keyed_all_layers);
				// this solo line goes across the top of the top layer. Could be programmed later if we decide to include the 'headspace' above the top of the pit
				imageline($img, 483, $keyed_all_layers[0]->y_val_top, 667, $keyed_all_layers[0]->y_val_top, $black);
				///
				// IN this loop, we set the items in the 'density managed' column - grain types, sizes, moisture, etc.
				//
				$comment_counter = 0;
				foreach($keyed_all_layers as $x => $layer){	
					imageline($img, 511, $layer->y_val_xlate,  667, $layer->y_val_xlate, $black); // 'density managed' column - grain types, sizes, moisture, etc.
					imageline($img, 483, $layer->y_val, 491, $layer->y_val, $black); // a little tick to start outthe angle transferred stuff
					imageline($img, 491, $layer->y_val, 511,$layer->y_val_xlate, $black ); // the diagonal line connect
				
					
				// Calculate grain type image(s) for this layer
				$grain_type_image ='';
				if ( isset($layer->field_grain_type['und'])){
					$grain_type_image = isset($layer->field_grain_type['und'][1]['tid'] ) ? _tid2snowsymbols($layer->field_grain_type['und'][1]['tid']) :  _tid2snowsymbols($layer->field_grain_type['und'][0]['tid']);
				}	
				$secondary_grain_type = '';
					if (isset($layer->field_grain_type_secondary['und'])){
						$secondary_grain_type_image = isset($layer->field_grain_type_secondary['und'][1]['tid'] ) ? _tid2snowsymbols($layer->field_grain_type_secondary['und'][1]['tid']) :  _tid2snowsymbols($layer->field_grain_type_secondary['und'][0]['tid']);
						$secondary_grain_type = ' ('. $secondary_grain_type_image . ')';
					}
				//output grain symbols
					imagettftext($img, 10, 0, 525 , ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $snowsymbols_font, $grain_type_image.$secondary_grain_type);
				
				// calculate grain size string
					$grain_size_string = isset($layer->field_grain_size['und']) ? $layer->field_grain_size['und'][0]['value'] : '' ;
					if ( $layer->field_use_multiple_grain_size['und'][0]['value'] == '1' && isset( $layer->field_grain_size_max['und'][0]['value'])) $grain_size_string .= ' - ' . $layer->field_grain_size_max['und'][0]['value'];
				
				// Ouptut grain sizes
					$textpos = imagettftext($img, 10, 0, 584, ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $label_font, $grain_size_string );
				
				// calculate & ouput layer moisture	
					if ( isset($layer->field_water_content['und'] )){
						$moisture = $layer->field_water_content['und'][0]['value'];
				 	 	imagettftext($img, 10, 0, $textpos[2]+5, ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $label_font, $moisture );
				 	}
				
				// Output Layer comments
					if (isset($concern_delta) && ($concern_delta == $layer->item_id) ){
						if ($comment_counter < 5){
					  	imagettftext($img, 9, 0, 805, $comment_counter*13 + 35, $black, $value_font,$layer->field_bottom_depth['und'][0]['value'].'-'.$layer->field_height['und'][0]['value'].": Problematic Layer");
						  $comment_counter++;
					  }else{
							imagettftext($img, 7, 0, 805, $comment_counter*13 + 31, $red_layer, $label_font, '[ More Layer Comments ... ]');
					  }
					}
					if (isset($layer->field_comments['und']) ){
						if ( $comment_counter <5 ){
						  imagettftext($img, 9, 0, 805, $comment_counter*13 + 35, $black, $value_font,
							$layer->field_bottom_depth['und'][0]['value'].'-'.$layer->field_height['und'][0]['value']. ': '.$layer->field_comments['und'][0]['safe_value']);
						  $comment_counter++;
					  }else{
						  imagettftext($img, 7, 0, 805, $comment_counter*13 + 31, $red_layer, $label_font, '[ More Layer Comments ... ]');
					  }
					}
					snowpilot_draw_layer_polygon($img, $layer, $purple_layer, TRUE);  // the fill
					snowpilot_draw_layer_polygon($img, $layer, $blue_outline, FALSE);  // the outline
					// this mark the layer if its a critical layer, and save some 
					if ($layer->field_this_is_my_layer_of_greate['und'][0]['value'] == '1'){
						$x_redline = _h2pix($layer->field_hardness['und'][0]['value']); $y_redline_top = $layer->y_val_top; $y_redline_bottom = $layer->y_val; /// 
						$concern_delta = $layer->item_id;
						imagettftext($img, 9, 0, 805, $comment_counter*13 + 35, $black, $value_font,
							$layer->field_bottom_depth['und'][0]['value'].'-'.$layer->field_height['und'][0]['value']. ': Problematic Layer');
						$comment_counter++;
					} 			
								
				}
			} 
			// now that we are done drawing all the layers, we can overprint the red layer of concern
			if (isset($concern_delta)){
				switch ($all_layers[$concern_delta]->field_concern['und'][0]['value']){
					case 'entire layer':
						snowpilot_draw_layer_polygon($img, $all_layers[$concern_delta], $red_layer, TRUE);			  
					break;
					case 'top':
						imageline($img, $x_redline, $y_redline_top+2, 446, $y_redline_top+2, $red_layer );
						imageline($img, $x_redline, $y_redline_top+1, 446, $y_redline_top+1, $red_layer );
					break;
					case 'bottom':
						imageline($img, $x_redline, $y_redline_bottom-2, 446, $y_redline_bottom-2, $red_layer );
						imageline($img, $x_redline, $y_redline_bottom-1, 446, $y_redline_bottom-1, $red_layer );
					break;
				}
			}
			
			// Temperature Profile:
			// If we have temp profile readings,then we'll make the tick marks
			
			if ( isset($node->field_temp_collection['und'])){
				$ids = array();
				foreach ($node->field_temp_collection['und'] as $temp ){ $ids[] = $temp['value']; }
				$all_temps = field_collection_item_load_multiple($ids);
				//dsm($all_temps);
				uasort($all_temps, 'depth_val');
				//dsm($all_temps);
				if ($snowpit_unit_prefs['field_temp_units'] == 'C'){
					$pixels_per_degree =  -433/10 ;
					$x= 0; while ($x >=-10 ){ //  tickmarks
						imageline($img, 447 - $pixels_per_degree * $x, 132, 447-$pixels_per_degree * $x, 140, $black );
						imagettftext($img, 9, 0, 441 - $pixels_per_degree * $x, 130, $black, $label_font, $x  );
						$x--;
					}

				}else{ /// Temperature unites = 'F'
					$pixels_per_degree = -433/18 ;
					$x= 32; while ($x >=14 ){  // tickmarks
						imageline($img, 447 - $pixels_per_degree * ( $x - 32), 132, 447-$pixels_per_degree * ($x-32) , 140, $black );
						imagettftext($img, 9, 0, 441 - $pixels_per_degree * ( $x - 32), 130, $black, $label_font, $x  );
						$x = $x-2;
					}

				} // end temp units toggle
				
				// draw points, and line, different $cx calculations for F or C
				$prev_x=0; $prev_y = 0; 
				foreach($all_temps as $x=> $temp){
					$cx =  ($snowpit_unit_prefs['field_temp_units'] == 'C') ?  447 - $pixels_per_degree * ($temp->field_temp_temp['und'][0]['value']) :
					447 - $pixels_per_degree * ($temp->field_temp_temp['und'][0]['value'] - 32);
					if( $cx >= 15 && $cx <= 447 ){
						// draw point
						imagefilledellipse($img, $cx, snowpit_graph_pixel_depth($temp->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] ), 6, 6, $red_layer );
					// draw line
						if (($prev_x <=447 && $prev_x >=15 ) && $prev_y){ 
							imageline($img, $cx, snowpit_graph_pixel_depth($temp->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] ) , $prev_x, $prev_y, $red_layer); 
						}
					}
					// save this point location to use to draw the next line
					$prev_x = $cx ; $prev_y = snowpit_graph_pixel_depth($temp->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] );
				
				}
				
			} // and of drawingthe temperature profile
						
			// cycle through and make tick marks
			$x = 0;
			while ( $x <= $pit_depth){
				$y_val = round(snowpit_graph_pixel_depth($x, $pit_depth, $snowpit_unit_prefs['field_depth_0_from']));
				
				imageline($img, 660 , $y_val, 667, $y_val,$black);
				imageline($img, 511 , $y_val, 518, $y_val,$black);
				imageline($img, 14 , $y_val, 22, $y_val, $black);
				imageline($img, 440, $y_val, 447, $y_val, $black);
				
				imagettftext($img, 10, 0, 456, $y_val+5, $black, $label_font, $x );
				$x+=10;
			}
			
			// Now we make the 5cm tick marks
			$x = 5;
			while ( $x <= $pit_depth){
				$y_val = round(snowpit_graph_pixel_depth($x, $pit_depth, $snowpit_unit_prefs['field_depth_0_from']));
				
				imageline($img, 664 , $y_val, 667, $y_val,$black);
				imageline($img, 511 , $y_val, 515, $y_val,$black);
				imageline($img, 14, $y_val, 18, $y_val, $black);
				imageline($img, 443, $y_val, 447,$y_val, $black);
				
				//imagettftext($img, 10, 0, 638, round(snowpit_graph_pixel_depth($x, $node, 'bottom'))+5, $black, $label_font, $x );
				$x+=10;
			}
			//
			
	imagettftext($img, 10, 0 , 742, 122, $black ,$label_font, "Stability tests");
			
	imagettftext($img , 10, 0, 681, 118, $black, $label_font, "&#x3c1;"); // Rho symbol for density
	imagettftext($img, 10, 0 , 675,135, $black, $label_font , _density_unit_fix($snowpit_unit_prefs['field_density_units']) );
	
	// the rectabngle around stability and density columns
  imagerectangle( $img , 667 ,140 , 941, 751, $black);
	
	// the rectangle around the layers hardness profile
	imagerectangle($img, 14, 140, 447,751, $black );
	
	//the tickmarks for hardness across the bottom and top, and labels
	foreach ( _h2pix(NULL, TRUE) as $hardness => $pixels ){
		if ( substr($hardness, -1 ) != '+' && substr($hardness, -1) != '-' ){
			imageline( $img , $pixels, 140, $pixels, 156, $black);
			imageline( $img, $pixels, 734, $pixels, 751, $black);
			imagettftext($img, 10, 0 , $pixels - 5, 765, $black, $label_font, $hardness);
			//imagettftext($img, 10, 0, $pixels- 5, 172, $black, $label_font, $hardness);
		} else{ // it is a + or - declaration, shorter ticks and no label
			imageline( $img , $pixels, 140, $pixels, 145, $black);
			imageline( $img, $pixels, 746, $pixels, 751, $black);	
		}
		
		
	}
	
	
	imageline( $img , 707, 140, 707, 751, $black );
	imageline($img, 483,140, 667,140,$black); // finish line across top
	imageline($img, 483,751, 667,751,$black); // finish line across bottom
	imageline($img, 483,140 , 483, 751 , $black); // left edge, first vert line
	imageline($img, 511,140 , 511, 751 , $black); // beginning of crystal form column
	imageline($img, 575,135, 575, 751, $black  ); //beginning of crystal size column
	
	imagettftext($img, 10, 0 , 554, 122, $black ,$label_font, "Crystal");
	imagettftext($img, 10, 0 , 516,137, $black, $label_font , "Form");
	imagettftext($img, 10, 0 , 580,137, $black, $label_font , "Size (mm)");
	
	//imageline($img, );
	
	
	// Output the png image
	$filename = 'graph-'.$node->nid ;
	imagejpeg($img, '/Users/snowpilot/Sites/snowpilot/sites/default/files/snowpit-profiles/'.$filename. '.jpg',100);
	
	imagepng($img, '/Users/snowpilot/Sites/snowpilot/sites/default/files/snowpit-profiles/'.$filename. '.png');
	snowpilot_snowpit_crop_layers_write($img,$node->nid);
// Destroy GD image
imagedestroy($img);

return;
}

function snowpilot_snowpit_crop_layers_write($img,$nid){
	$new_img = imagecreatetruecolor(466,613);
	$result = imagecopy($new_img, $img, 0,0, 14,140,466,613 );
	//$new_img = imagescale($new_img,350);
	$filename = 'layers-'.$nid ;
	
	imagepng($new_img, '/Users/snowpilot/Sites/snowpilot/sites/default/files/snowpit-profiles/'.$filename. '.png');	
}

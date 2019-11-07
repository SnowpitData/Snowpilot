<?php

function imagettftext_cr(&$im, $size, $angle, $x, $y, $color, $fontfile, $text)
        {
            // retrieve boundingbox
            $bbox = imagettfbbox($size, $angle, $fontfile, $text);
           
            // calculate deviation
            $dx = ($bbox[2]-$bbox[0])/2.0 - ($bbox[2]-$bbox[4])/2.0;         // deviation left-right
            $dy = ($bbox[3]-$bbox[1])/2.0 + ($bbox[7]-$bbox[1])/2.0;        // deviation top-bottom
           
            // new pivotpoint
            $px = $x-$dx;
            $py = $y-$dy;
           
            return imagettftext($im, $size, $angle, $px, $py, $color, $fontfile, $text);
        }

function snowpilot_layers_density_xlate(&$all_layers, $snowpit_unit_prefs, $global_max = 751){
	usort($all_layers, 'layer_depth_val');
	//if this is a measure-from-top pit, flip the order of layers, and the top and bottom of each layer
	if ( $snowpit_unit_prefs['field_depth_0_from'] == 'top') {
		array_reverse($all_layers);
		foreach ( $all_layers as $layer){
			$top = $layer->y_val_top;
      $bottom = $layer->y_val;
			$layer->y_val_top = $bottom;
			$layer->y_val = $top;	
		}
	}
	
	$global_min = $all_layers[0]->y_val_top; 
	
	//dsm($all_layers);
	foreach($all_layers as $x => $layer){
    /// first, lets check to make sure thaere is a 'next' layer down there, and if there is a collision with it
				
	   if (  $x == 0 ) {   //the first layer is a special case
		 	$layer->y_val_top_xlate = $global_min;	
			// We have to do this incase this is a one-layer pit
			// This $layer->y_val_xlate value will be overwritten if the pit has multiple layers; we need to make sure it is at least 20 pixels high ( hence ternary function )
			if ($layer->y_val > $layer->y_val_top_xlate +20 ){ /// We have a first layer that is thick enough ( > 20 px )
			  $layer->y_val_xlate =	$layer->y_val  ;
				
			}else{ /// OK, we have a very narrow first layer
				$layer->y_val_xlate = $layer->y_val_top_xlate +20;
				$layer->collision_flag = TRUE;
			}
			continue;
		 }
		 
		 $cg = array();
		 //
		 // We might have a collision group under one of two circumstances: the layer is less than 20px high, or the layer from above ( if there is one) bumps into it
		 if ( isset ( $all_layers[$x-1] ) && snowpilot_collision_check_down($all_layers[$x-1] , $layer )  ){
				$layer->collision_flag = TRUE;

				$cg[$x] = array ('y_val' => $layer->y_val , 'y_val_top' => $layer->y_val_top );
				//
				//  Loop here to look for conflicts between the cg and the layer(s) above
				//
				$prev_test = $x-1;
				while ( ( $prev_test >= 0 ) && snowpilot_collision_check_cg_up($cg, $all_layers[$prev_test]) ){
					$cg = array ( $prev_test => array( 'y_val' => $all_layers[$prev_test]->y_val, 'y_val_top' => $all_layers[$prev_test]->y_val_top )) + $cg;	 
					$all_layers[$prev_test]->collision_flag = TRUE;
					$prev_test = $prev_test - 1;
				}
				if ( count ($cg)){ snowpilot_write_xlations($cg, $all_layers, $global_max);}
				$cg = array();
				
			}else{ // no conflict with the layer below ( or no layer )
				/*if ($x == 1) { 
					$cg[$x] = array ('y_val' => $layer->y_val , 'y_val_top' => $layer->y_val_top );
					snowpilot_write_xlations($cg, $all_layers);
				}else*/
				
				if ( isset($all_layers[$x-1]->collision_flag) && $all_layers[$x-1]->collision_flag == TRUE){ // layer above IS a collision, make it 20 pixels high, and set that to the top of current layer too		
					$all_layers[$x-1]->y_val_xlate = $all_layers[$x-1]->y_val_top_xlate + 20;
					$layer->y_val_top_xlate =	$all_layers[$x-1]->y_val_xlate;
				}else{   // layer above is not a collision, write it all straight across
				
					$layer->y_val_top_xlate = $layer->y_val_top;
					$all_layers[$x-1]->y_val_xlate = $layer->y_val_top;	
	
				}
				// make sure the bottom line of the last layer is in the right spot
				// most of this should be taken care of in the snowpilot_write_xlations function
				// the only case handled here, is if this is a non-collided layer.
				if ( count ($cg) == 0 ) { 
					$layer->y_val_xlate = $layer->y_val; 
				}
				
			}
			
		}

}

function _temp_profile_find_min_temp($all_temps,$min_temp = -10 ){

	foreach($all_temps as $temp){
		if ($min_temp > $temp->field_temp_temp['und'][0]['value']) $min_temp = $temp->field_temp_temp['und'][0]['value'];	
	}
	return $min_temp;
		
}

function snowpilot_collision_check_down($layerx, $layery){
	if (($layerx->y_val_top_xlate + 20 > $layery->y_val-20)  || $layery->y_val_top+20 >$layery->y_val ){
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
	

function _strip_dupe_tests(&$test_results ){
	foreach ($test_results as $x => $test){
				
		if ( $test->multiple > 0){
			$simple_test_results[] = $test;
	  }
	}
	return $simple_test_results;
}


function snowpilot_write_xlations($cg, &$all_layers, $global_max = 751){

	$counter = 0;
	foreach($cg as $x => $cg_layer){
		$all_layers[$x]->y_val_top_xlate =  _cg_stats($cg, 'cg_top') + 20* $counter;  //dsm ($all_layers[$x-1]);
		
		if ( $x > 0 ) { $all_layers[$x-1]->y_val_xlate = _cg_stats($cg, 'cg_top') + 20 * $counter; }
		// make sure the bottom line of the last layer is in the right spot
		if ( $x == count($all_layers) - 1 ){ 	// this is the last layer ...
			if ( _cg_stats($cg, 'cg_bottom') < $global_max) {
				//since this is the last layer in the whole group, we can rely upon it being also the last layer in the collision group ( $cg )
			  $all_layers[$x]->y_val_xlate	= _cg_stats($cg, 'cg_bottom');
      }	else{
      	// this collision group bumps into the bottom of the snowpit, below $global_max pixels
				// basically, a lot of thin layers near the ground on a bottom_up snowpit
				// TODO : there is the possibility that this cg bumps into one that is above it in the stack;
				//        we need to check for this case
				// move the entire CG upwards by $span above $global_max and then re-ripple down from there at 20 intervals
				$new_span_top = $global_max - _cg_stats($cg,'span');
				$countery = 0; 
				foreach ( $cg as $y => $cgy_layer){
					// time to reset everything according to the bottom of the cg based at 751
					//$cgy_layer->y_val_xlate
					$all_layers[$y-1]->y_val_xlate = $all_layers[$y]->y_val_top_xlate = $countery * 20 + $new_span_top ;
					
					$countery++;
				}
				// we can rely on the bottom of the last layer of the collision group being pasted to the bottom of the graph. 
				$all_layers[$y]->y_val_xlate = $global_max;
      }
		}
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

function _cg_stats($cg, $stat = NULL, $global_min = 157 , $global_max = 751){
	$span = count($cg) * 20; // span of pixels

	$first_item = array_slice($cg, 0, 1);
	$last_item = end ($cg);
	$length = ($last_item['y_val'] - $first_item[0]['y_val_top'])/2;
	$cg_top = ($last_item['y_val'] - $first_item[0]['y_val_top'])/2 + $first_item[0]['y_val_top']  - $span/2 ;
	$cg_bottom = ($last_item['y_val'] - $first_item[0]['y_val_top'])/2 + $first_item[0]['y_val_top'] + $span/2 ;

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
function _bhg2pix($bhg, $scale = 'linear'){
	if ( $scale <> 'exponential'){
		//$bhg_val_2pix
		if ( ($bhg >= 0)  && ($bhg <0.21) ){
		  $bhg_val =  450 - $bhg * 414 ;
		}elseif ( $bhg >= 0.21 && $bhg < 1.1 ){
		  $bhg_val =  380 - $bhg * 80.9 ;
		}elseif ( ($bhg >= 1.1) && ($bhg < 4.69) ){
			$bhg_val = 313.05 - $bhg * 20.06;
		}elseif (($bhg >=4.69 ) && ($bhg < 59  )){
			$bhg_val = 225 - $bhg * 1.33 ; 
		}else{
			$bhg_val = 445;
		}
		//dsm($bhg);
	  
	}else{ // exponential scale
		if ( ($bhg >= 0)  && ($bhg <0.21) ){
		  $bhg_val =  450 - $bhg * 152.4 ;
		}elseif ( $bhg >= 0.21 && $bhg < 1.1 ){
		  $bhg_val =  429 - $bhg * 48.3 ;
		}elseif ( ($bhg >= 1.1) && ($bhg < 4.69) ){
			$bhg_val = 397.2 - $bhg * 19.27;
		}elseif (($bhg >=4.69 ) && ($bhg < 59  )){
			$bhg_val = 317.4 - $bhg * 2.23 ; 
		}else{
			$bhg_val = 445;
		}
		//dsm($bhg);
	  
	}
		return $bhg_val;
}

function _h2pix($h, $all = FALSE, $scale = 'linear'){
	if ( $scale <> 'exponential'){
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
			'I+' => 15,
			//'' => 451
		); 
	}else{
		  $h2pix = array(
			'F-' => 439,
			'F' => 435,
			'F+' => 427,
			'4F-' => 411,
	 		'4F' => 399,
			'4F+' => 387,
			'1F-' => 366,
			'1F' => 351,
			'1F+' => 329,
			'P-' => 285,
			'P' => 255,
			'P+' => 223,
			'K-' => 149,
			'K' => 111,
			'K+' => 97,
			'I-' => 82,
			'I' => 63,
			'I+' => 15,
			//'' => 451
			);		
	}
		if ($all){
			return $h2pix;
		}else{
			return $h2pix[$h];
		}
}

function _output_formatted_notes($string, $font){

	  $pointer = 0;
  	$last_space = 0 ;
		
		while ( substr($string ,$pointer) <> '' ){

			//dsm(substr($string, $pointer, 1));
			//  a linebreak character.
			if(substr($string, $pointer, 1) == '
'){  	  	$parts[] = substr($string, 0, $pointer);
		  		$string = substr( $string, $pointer+1);
					$pointer = 0;
					$last_space = 0 ;
			}elseif (substr($string, $pointer, 1) == ' ' ) {
	  		$line = imagettfbbox(9,0,$font,substr($string, 0, $pointer));
	  		if ( $line[2] < 930 ){ 
	  			$last_space = $pointer;
					$pointer ++;	
	  		}else{ 
	  			$parts[] = substr($string, 0, $last_space);
		  		$string = substr( $string, $last_space+1);
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
		
		}
	return $parts;
}

function snowpilot_draw_layer_polygon(&$img, $layer, $color, $filled = TRUE, $hardness_scale ){
	if ( !isset($layer->field_hardness['und'][0])){ // error checking in case that hardness is not set
			$pink_problem = imagecolorallocate($img,254, 240, 240);
			$dark_pink = imagecolorallocate($img, 142, 47, 11); //#8c2e0b , the border for warning messages
			$black = imagecolorallocate($img,0,0,0);
			$value_font = '/sites/all/libraries/fonts/Arial Bold.ttf';
			
			$points = array(15, $layer->y_val_top,  447 , $layer->y_val_top, 447, $layer->y_val, 15, $layer->y_val);
			imagefilledpolygon($img, $points, 4, $pink_problem) ;
			imagettftext($img, 13, 0, 35, ($layer->y_val - $layer->y_val_top)/2 + $layer->y_val_top, $black, $value_font, 'No Hardness specified');
			imagepolygon($img,$points, 4, $dark_pink);
			
			
	}else{  // hness IS set, a basic part of a layer  ; excess layers should be already stripped by _validate
	$hness = $layer->field_hardness['und'][0]['value'];
	if ( $layer->field_use_multiple_hardnesses['und'][0]['value'] == '1' &&
		isset ($layer->field_hardness2['und'][0]['value'])){	
			$hness2 = $layer->field_hardness2['und'][0]['value'];
			$points = array(_h2pix($hness, FALSE, $hardness_scale), $layer->y_val_top,  447 , $layer->y_val_top, 447, $layer->y_val, _h2pix($hness2, FALSE, $hardness_scale), $layer->y_val);
			if ($filled) {
				imagefilledpolygon($img, $points, 4, $color);
			}else{
				imagepolygon($img,$points, 4, $color);
			}
		}else{
			if ($filled) {
				imagefilledrectangle($img, _h2pix($hness, FALSE, $hardness_scale), $layer->y_val, 446 , $layer->y_val_top, $color) ;
			}else{
				imagerectangle($img, _h2pix($hness, FALSE, $hardness_scale), $layer->y_val, 447 , $layer->y_val_top, $color );
			}
		}
	}
		
	
}

function _tid2snowsymbols($tid = NULL, $all = FALSE){
	
	
	$tid2snowsymbols = array(
		'33' => '&#xe000;', // Precipitation particles
		'34' => '&#xe002;', // Decomposing & fragmented PP
		'35' => '&#xe003;', // Rounded Grains
		'36' => '&#xe004;', // Faceted crystals
		'37' => '&#xe005;',  // depth hoar
		'38' => '&#xe006;',  // surface hoar
		'39' => '&#xe007;',  // melt forms
		'40' => '&#xe008;',  // ice formations
		'41' => '&#xe001;',  // machine made snow
		
		// Precipitation Particles types
		'42' => '&#xe009;', //PP -> columns
		'43' => '&#xe00a;', // PP -> Needles
		'44' => '&#xe00b;', // PP -> plates
		'45' => '&#xe00c;', // PP -> stellars, dendrites
		'46' => '&#xe00d;', // irregular crystals
		'47' => '&#xe00e;', // graupel
		'48' => '&#xe00f;', // Hail
		'49' => '&#xe010;', // Ice pellets
		'50' => '&#xe011;', // rime
		
		// Decomposing and fragmented precip particles
		'104' => '&#xe014;', // partly decomposed PP
		'78' => '&#xe015;', // wind-broken particles
		//  Rounded grain types
		'79' => '&#xe016;', // small rounded particles
		'80' => '&#xe017;', //large rounded particles
		'81' => '&#xe018;', //Wind packed
		'82' => '&#xe019;', // faceted rounded particles
		// Faceted crystal types
		'105' => '&#xe01a;', // Solid faceted particles
		'83' => '&#xe01b;', // Near surface faceted particles
		'84' => '&#xe01c;',  // Rounding faceted particles 
		
		// Surface Hoar types
		'90' => '&#xe022;', // surface hoar crystals
		'91' => '&#xe023;', // cavity or crevasse hoar
		'92' => '&#xe024;', // Rounding surface hoar
		// Depth Hoar types
		'85' => '&#xe01d;', // Hollow cups
		'86' => '&#xe01e;', //Hollow Prizms
		'87' => '&#xe01f;', // Chains of depth hoar
		'88' => '&#xe020;', // large striated crystals
		'89' => '&#xe021;', // rounding depth Hoar
		
		// Melt forms types
		'93' => '&#xe025;',// clustered rounded grains
		'94' => '&#xe026;', //rounded polycrystals
		'95' => '&#xe027;', // Slush
		'96' => '&#xe028;&#xe007;', //Melt-freeze crust
		
		// Ice Formations
		'97' => '&#xe029;',// Ice Layer
		'98' => '&#xe02a;',// Ice column
		'99' => '&#xe02b;',// Basal Ice
		'100' => '&#xe02c;',//  Rain crust
		'101' => '&#xe02d;',// Sun crust
		
		// Machine made snow types
		'102' => '&#xe012;', // rounded Polycrystalline particles
		'103' => '&#xe013;', // crushed Ice Particles  
		'_none' => '' //none
		
	);
	
	if ($tid == NULL || $all ) { return $tid2snowsymbols;
	}elseif( is_numeric($tid) && isset($tid2snowsymbols[$tid])){ 
		return $tid2snowsymbols[$tid]; 
	}else{ return ''; }
	
}


function snowpit_graph_pixel_depth($depth, $pit_depth, $meas_from = 'bottom', $global_max = 750, $pit_min = 0){
	$pixels_per_cm = ((int) $global_max - 157) / ($pit_depth - $pit_min );
	
	$h = ($meas_from == 'top') ? (157 + $depth * $pixels_per_cm) : ($global_max - ($depth - $pit_min)* $pixels_per_cm );
	return $h; 
}

function arrowline_imageline( &$img, $stab_test_start, $original_depth ,  $y_position ){
	$factor = ($y_position - $original_depth) ; //slope
	$hypotenuse = sqrt(16 * 16 + $factor * $factor );
	//dsm(atan($factor/18) * 180/ M_PI);
	//dsm( sin (atan($factor/18) ) );
	//dsm( "cos: ".cos (atan($factor/18) ) * $hypotenuse );
	
	$arrowline['tilt'] = atan($factor/18) * 180/ M_PI ;
	$arrowline['hypotenuse'] = sqrt(18 * 18 + $factor * $factor );
	$c= $factor * $factor ;
	$arrowline['xstart'] = $stab_test_start + cos (atan($factor/18) ) * $hypotenuse* ($hypotenuse -16 )/45   ;
	$arrowline['ystart'] =  $original_depth + sin (atan($factor/18) ) * $hypotenuse *($hypotenuse -16 )/45  ;
							
	return $arrowline;
}


function _set_stability_test_pixel_depths(&$test_results, $pit_depth, $measure_from = 'bottom', $global_max = 750, $pit_min = 0 ){

	foreach ($test_results as $x => $test){
		//
		// We need to see if there are multiple test scores that are identical and indicate as "2x", etc. 
		// If so we 'continue 3; to skip the last processing in the foreach loop, so test->y_position is never set.
		//
		$test->multiple = 1;
		$test->y_position = snowpit_graph_pixel_depth(  $test->field_depth['und'][0]['value'] , $pit_depth, $measure_from, $global_max, $pit_min);
		foreach ( $test_results as $y => $test_compare){
			if ( ($test->item_id != $test_compare->item_id) &&
				( $test->field_stability_test_type == $test_compare->field_stability_test_type  ) &&
				( $test->field_stability_test_score == $test_compare->field_stability_test_score  ) &&
				( $test->field_depth == $test_compare->field_depth  ) &&
				( isset ($test_compare->multiple) && ($test_compare->multiple > 0) )

			){ 		// so the two tests ARE exactly alike for those previous fields; how about for test-specific fields?

 			 switch ($test->field_stability_test_type['und'][0]['value']){
 				  case 'ECT':
 					  if (( $test->field_ec_score == $test_compare->field_ec_score) &&
							  ( $test->field_shear_quality == $test_compare->field_shear_quality) &&
								( $test->field_fracture_character == $test_compare->field_fracture_character)
 						) {$test->multiple += $test_results[$y]->multiple; /* Add the old multiple value onto this one */ $test_results[$y]->multiple = 0; unset($test_compare); continue 3;	}
 					break;
					case 'CT':
				  
					if (( $test->field_ct_score == $test_compare->field_ct_score) &&
						  ( $test->field_shear_quality == $test_compare->field_shear_quality) &&
							( $test->field_fracture_character == $test_compare->field_fracture_character)
					) {
						$test->multiple += $test_compare->multiple; /* Add the old multiple value onto this one */ 
						$test_results[$y]->multiple = 0;  
						unset($test_compare); 
						continue 3;	}
					break;
					case 'PST':
				  if (( $test->field_length_of_isolated_col_pst == $test_compare->field_length_of_isolated_col_pst) &&
						  ( $test->field_length_of_saw_cut == $test_compare->field_length_of_saw_cut) &&
							( $test->field_data_code_pst == $test_compare->field_data_code_pst)
					) {$test->multiple += $test_results[$y]->multiple; /* Add the old multiple value onto this one */ $test_results[$y]->multiple = 0;  unset($test_compare); continue 3;	}
					break;
					case 'RB':
				  if (( $test->field_stability_test_score_rb == $test_compare->field_stability_test_score_rb) &&  
					    ( $test->field_shear_quality == $test_compare->field_shear_quality) &&
							( $test->field_release_type == $test_compare->field_release_type)
					) {$test->multiple += $test_results[$y]->multiple; /* Add the old multiple value onto this one */ $test_results[$y]->multiple = 0;  unset($test_compare); continue 3;	}				
					break;
					case 'ST':
					case 'SB':
					  $test->multiple += $test_results[$y]->multiple; /* Add the old multiple value onto this one */ $test_results[$y]->multiple = 0;  unset($test_compare); continue 3;	
					break;
				} 
			} // end of "yes, this could be a repeated test" processing
		}	// end of looping through test-comparision
	 		
	} // end of looping through test results in general
	// now we begin to set the y_position
	
	$st_cg = array();
  $simple_test_results = _strip_dupe_tests($test_results);
	$no_result_iter = 0;
	// loop through it again to set the y_position to correct height
	foreach ( $simple_test_results as $x => $test){		
	  if ( ($measure_from == 'top' && $test->field_depth['und'][0]['value'] <> $pit_depth) ||
		     ($measure_from == 'bottom' && $test->field_depth['und'][0]['value'] <> $pit_min) )
	  { 
  	  if($x <> 0 && st_collision_check_down($simple_test_results[$x - 1], $test, $st_cg )  ){ 
  		  $test->collision_flag = TRUE;	
		    $test->y_position = $simple_test_results[$x - 1]->y_position +20 ;
		  }else{
        if ( count ($st_cg)){
  	      $st_cg = array();
				}else{ // this is a singluar item, write straight across
	        $test->y_position = snowpit_graph_pixel_depth( $test->field_depth['und'][0]['value'], $pit_depth, $measure_from,$global_max, $pit_min ) ;
        }
			}
		}else{ 
		  $test->y_position =  snowpit_graph_pixel_depth( $test->field_depth['und'][0]['value'], $pit_depth, $measure_from,$global_max, $pit_min ) - 17 * $no_result_iter-8;
		  $no_result_iter++;
		}
	}
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
			
			$field_item = $node->$field;
			if ( isset($field_item['und'][0]) && ($field_item['und'][0]['value'] != '0')){
				$item_full = field_info_instance('node', $field, 'snowpit_profile');
				switch ($field){
					case 'field_adjacent_to_avy':
						$specifics[] = $item_full['label'].": ".$node->field_near_avalanche['und'][0]['value'];
					break;
					case 'field_near_avalanche':
					break;
					case'field_practice_pit':
					if ($field_item['und'][0]['value'] ==='1'){ 
					  $specifics[] = $item_full['label'];
					}
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
//
//  simple substitution for the field_tester_1 update
// the case of a field_tester operating on this will   
//
function snowpilot_tester_fields_update($stability){
	if( substr($stability['#items'][0]['value'], 0 , 4 ) == 'fair' ){
		return 'Fair';
		
	}else{
		
		return $stability[0]['#markup'];
	}
	return;
}

function snowpilot_snowpit_graph_header_write($node, $format='jpg'){	
	// also add user account info to this:
	$user_account = user_load($node->uid);
	$snowpit_unit_prefs = snowpilot_unit_prefs_get($node, 'node');
	$pit_depth = _snowpilot_find_pit_depth($node);
	$pit_min = _snowpilot_find_pit_min($node);
	if ( ( isset ( $node->field_total_height_of_snowpack['und'][0]['value'] )  
		&& (  abs($node->field_total_height_of_snowpack['und'][0]['value'] - $pit_depth)>15  ) 
	  && (($node->field_display_full_profile['und'][0]['value'] <> 1 ) || !isset($node->field_display_full_profile['und'][0]['value']) )) 
			
	|| ( ( !isset( $node->field_display_full_profile['und'][0]['value'] ) || ($node->field_display_full_profile['und'][0]['value'] <> 1 ) ) && $pit_min >14 )){
			$shrunken_pit = TRUE;
			$global_max =  701 ;
		}else{
			$shrunken_pit = FALSE;
			$global_max =  751 ;
			$pit_min = 0; // we reset pit_min to zero so it comes out the correct height scaling on the final graph
		}
		
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

$label_font = '/sites/all/libraries/fonts/Arial.ttf';
$value_font = '/sites/all/libraries/fonts/Arial Bold.ttf';
$snowsymbols_font ='/sites/all/libraries/fonts/SnowSymbolsIACS.ttf';


// Label Y axis and draw horizontal lines

      imagettftext($img, 11, 0, 14, 17, $black, $value_font, $node->title);
			// Location information
      if ( isset ($node->field_loaction['und'][0]['tid'])){
				$term_obj_region = taxonomy_term_load($node->field_loaction['und'][0]['tid']);
				imagettftext($img, 11, 0, 14, 53, $black, $value_font, $term_obj_region->name); 
				if ( isset ( $node->field_loaction['und'][1]['tid'] )){
					$term_obj_region = taxonomy_term_load($node->field_loaction['und'][1]['tid']);
					imagettftext($img, 11, 0, 14, 35, $black, $value_font, substr($term_obj_region->name , 0, 24 ));
				}
			}
      $text_pos = imagettftext($img, 11, 0, 14, 71, $black, $label_font, 'Elevation: ');
			if (isset($node->field_elevation['und'])){
				imagettftext($img, 11, 0, $text_pos[2], 71, $black, $value_font, $node->field_elevation['und'][0]['value'] .' '.$node->field_elevation_units['und'][0]['value']);
 	 		}
      $text_pos = imagettftext($img, 11, 0, 14, 89, $black, $label_font, 'Aspect: ');
			if (isset($node->field_aspect['und'])){
				if ( isset ( $node->field_direction_format['und'] ) && ($node->field_direction_format['und'][0]['value'] == 'cardinal') /* cardnial type aspect */ ){
					$aspect = field_view_field('node', $node, 'field_aspect_cardinal');
				}else{ // the default, azimuth degrees
				  $aspect = field_view_field('node', $node, 'field_aspect');
			  }
        imagettftext($img, 11, 0, $text_pos[2], 89 , $black, $value_font ,$aspect[0]['#markup']);
			}
			$text_pos = imagettftext($img, 11, 0, 14, 107, $black, $label_font, 'Specifics: ');
			$specifics = _generate_specifics_string($node);
			imagettftext($img, 9, 0, $text_pos[2], 107, $black, $value_font, $specifics );
			// Observer
			imagettftext($img, 11, 0, 193 , 17, $black,  $value_font, $user_account->field_first_name['und'][0]['value']. " ". $user_account->field_last_name['und'][0]['value']);
			imagettftext($img, 11, 0, 193, 35, $black, $value_font, date(snowpilot_date_output_format($snowpit_unit_prefs['field_loaction_0']), 
			strtotime($node->field_date_time['und'][0]['value']))); //Date / Time of observation
			
			$text_pos = imagettftext($img, 11, 0, 193, 53, $black, $label_font, "Co-ord: ");
			if ($snowpit_unit_prefs['field_coordinate_type'] == 'lat_long'){
				if (isset($node->field_latitude['und']) && isset($node->field_longitude['und'])){
					imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, 
						number_format($node->field_latitude['und'][0]['value'] , 5).
						$node->field_latitude_type['und'][0]['value'].", ". 
						number_format($node->field_longitude['und'][0]['value'] , 5).
						$node->field_longitude_type['und'][0]['value']);
				}
			}elseif ( $snowpit_unit_prefs['field_coordinate_type'] == 'UTM' ){ // Not Lat long, their preference is UTM
				if(isset($node->field_east['und']) && isset($node->field_north['und'])){
					imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, 
						$node->field_utm_zone['und'][0]['value'].' '.
						$node->field_east['und'][0]['value'] .
					  $node->field_longitude_type['und'][0]['value'].' '.				
					  $node->field_north['und'][0]['value'] .
					  $node->field_latitude_type['und'][0]['value']
				  );
				}
			}elseif ($snowpit_unit_prefs['field_coordinate_type'] == 'MGRS'){
				if(isset($node->field_mgrs_easting['und']) && isset($node->field_mgrs_northing['und'])){
					imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, 
						$node->field_utm_zone['und'][0]['value'].' '.
						$node->field_100_km_grid_square_id['und'][0]['value'].' '.
						$node->field_mgrs_easting['und'][0]['value'] .' '.
					  $node->field_mgrs_northing['und'][0]['value'] 
				  );
				}
				
				
			}
			
			$text_pos = imagettftext($img, 11, 0, 193, 71, $black, $label_font, "Slope Angle: ");
			if (isset($node->field_slope_angle['und'])){
				$slope_angle = field_view_field('node', $node, 'field_slope_angle');
				imagettftext($img , 11, 0, $text_pos[2], 71, $black, $value_font, $slope_angle[0]['#markup'] );
			}
			$text_pos = imagettftext($img, 11, 0, 193, 89, $black, $label_font, "Wind Loading: ");
			if (isset($node->field_wind_loading['und'])){
				imagettftext($img, 11, 0, $text_pos[2], 89, $black, $value_font, $node->field_wind_loading['und'][0]['value'] );
			}
			$text_pos = imagettftext($img, 11, 0, 444, 17, $black, $label_font, "Stability: ");
			if(isset($node->field_stability_on_similar_slope['und'])){
				$similar_stability = field_view_field('node', $node, 'field_stability_on_similar_slope') ;
				//snowpilot_tester_fields_update($similar_stability);
				imagettftext($img, 11, 0, $text_pos[2], 17, $black, $value_font, snowpilot_tester_fields_update($similar_stability) );
			}
			$text_pos  = imagettftext($img, 11, 0, 444, 35, $black, $label_font, "Air Temperature: ");
			if(isset($node->field_air_temp['und'])){
				$air_temp = field_view_field('node', $node, 'field_air_temp');
				$air_temp_value =  number_format($air_temp['#items'][0]['value'] , 1 , '.' , '')+0;
			  imagettftext($img, 11,0, $text_pos[2], 35, $black, $value_font, $air_temp_value ."&#176;". $snowpit_unit_prefs['field_temp_units'] );
			}
			$text_pos = imagettftext($img, 11, 0, 444, 53, $black, $label_font, "Sky Cover: ");
			if (isset($node->field_sky_cover['und'])){
				$sky_cover = field_view_field('node', $node, 'field_sky_cover'); 
			imagettftext($img, 11, 0, $text_pos[2], 53, $black, $value_font, $sky_cover['#items'][0]['value'] );
			}
			$text_pos = imagettftext($img, 11, 0, 444, 71, $black, $label_font, "Precipitation: " );
			if ( isset($node->field_precipitation['und'])){
				$precipitation = field_view_field('node', $node, 'field_precipitation');
			  imagettftext($img, 11, 0, $text_pos[2] , 71, $black, $value_font, $precipitation['#items'][0]['value'] );
			}
			$text_pos = imagettftext($img, 11, 0, 444, 89, $black, $label_font, "Wind: ");
			if (isset($node->field_wind_direction['und'][0]['value'])){
				$text_pos = imagettftext($img, 11 , 0, $text_pos[2]+4, 89, $black , $value_font, snowpilot_cardinal_wind_dir($node->field_wind_direction['und'][0]['value'] ));			
			}
			if (isset($node->field_wind_speed['und'][0]['value'])){
				$wind_speed = field_view_field('node', $node, 'field_wind_speed');
				 imagettftext($img, 11 , 0, $text_pos[2], 89, $black , $value_font, " ".$wind_speed[0]['#markup']);
			}
			
			imagettftext( $img, 11, 0 , 700, 17, $black, $label_font, 'Layer Notes:');
			//imageline( $img , 680, 24, 959,24, $black);

	
			$comment_count = 0;
			$textpos = array();
			if ( isset( $node->field_total_height_of_snowpack['und'][0]['value'])  ){ 
				$pretextpos = imagettftext($img, 11 , 0, 625, 17, $black, $label_font, 'HS:');
				imagettftext($img, 11 , 0, $pretextpos[2] , 17, $black, $value_font,  $node->field_total_height_of_snowpack['und'][0]['value'] )  ;
				
				$comment_count += 1;
			}
			if ( isset( $node->field_surface_penetration['und'] )  
				&& ( isset($node->field_boot_penetration_depth['und']) )
			  && (  $node->field_boot_penetration_depth['und'][0]['value'] != '' )){	
					$finalpos = imagettftext($img,11, 0, 625, 17+$comment_count* 18, $black, $label_font , 'PF:' );
					imagettftext($img,11, 0, $finalpos[2], 17+$comment_count* 18, $black, $value_font , $node->field_boot_penetration_depth['und'][0]['value']  );
					
					$comment_count += 1;
			}
			if(isset( $node->field_surface_penetration['und'] )  
			    && isset($node->field_ski_penetration['und'][0]['value']) 
			    &&  (  $node->field_ski_penetration['und'][0]['value'] != '' ) ){
				    $finalpos = imagettftext($img,11, 0, 625, 17+$comment_count* 18, $black, $label_font , 'PS:' );
				    imagettftext($img, 11, 0, $finalpos[2]+2, 17+$comment_count* 18, $black, $value_font, $node->field_ski_penetration['und'][0]['value'] );
				  	$comment_count += 1;
			}
			
			$xtra_specifics = ''; // we are setting this to empty string early on.
			//  write stability tests column and comments 
			//  TODO : expand into its own function
			//
			// Looping through stability test results
			//
			$stab_test_start = 707;
			
			if (isset($node->field_test['und'])){
				$ids = array();
				foreach($node->field_test['und'] as $test) {  $ids[] = $test['value'];
				}
				$test_results = field_collection_item_load_multiple($ids);
				// Here we go ahead and set a value for no-release type test results: ECTX, CTN, etc
				foreach($test_results as $test) {
					if ( !isset($test->field_depth['und'][0]['value'] ) ){
						$test->field_depth['und'][0]['value'] = ($snowpit_unit_prefs['field_depth_0_from'] == 'top') ? $pit_depth : $pit_min ;
					}
				}
				uasort($test_results, 'depth_val');
				//
				// reversing the order of the test results makes it work when outputting the Stability test results on the graph when measuring from top
				if ( $snowpit_unit_prefs['field_depth_0_from'] == 'top'){
				  $test_results = array_reverse($test_results);
				}
				$bak = _set_stability_test_pixel_depths($test_results, $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max  ,$pit_min ); // this sets a $test->y_position = integer which is where the line and text should go in the column on the right
				//dsm($test_results);
				//imagettftext( $img, 11, 0 , 625, $comment_count*18 + 17, $black, $label_font, 'Stability Test Notes');
				
				foreach ( $test_results as $x => $test){
					//dsm($test);
					
					if ( isset($test->field_stability_test_type['und'][0]['value']) && isset( $test->y_position) ){
						if ( $test->multiple ){ // if this has been 'multipled' with another stb test, the y_position won't be set

							$original_depth = snowpit_graph_pixel_depth(  $test->field_depth['und'][0]['value'] , $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min);
							
							$arrowline = arrowline_imageline($img, $stab_test_start, $original_depth ,  $test->y_position);
							$factor = $test->y_position - $original_depth;
							imageline($img, $arrowline['xstart']+1, $arrowline['ystart'] , $stab_test_start+17, $test->y_position, $black); //angled arrow to stability test 
		
							
							// Deprecated method of writing in little arrow lines
					/*		imageline($img, $arrowline['xstart'], $arrowline['ystart'], $arrowline['xstart']+8* cos( - atan( $factor/ 16) - .4) +1 , $arrowline['ystart'] - 8*sin( -atan( $factor/ 16) - .4), $black );
							imageline($img, $arrowline['xstart'], $arrowline['ystart'], $arrowline['xstart']+8* cos( - atan( $factor/ 16) - .32) +1 , $arrowline['ystart'] - 8*sin( -atan( $factor/ 16) - .32), $black );
							
							
							imageline($img, $arrowline['xstart'], $arrowline['ystart'], $arrowline['xstart']+8* cos( - atan( $factor/ 16) + .4)  +1, $arrowline['ystart'] - 8*sin( -atan( $factor/ 16) + .4), $black );
							imageline($img, $arrowline['xstart'], $arrowline['ystart'], $arrowline['xstart']+8* cos( - atan( $factor/ 16) + .32)  +1, $arrowline['ystart'] - 8*sin( -atan( $factor/ 16) + .32), $black );
					*/	
					/*  Other deprecated what of writing arrows
			imagettftext_cr($img, 7, 0-$arrowline['tilt'], $arrowline['xstart'] + 3.2 * abs(cos( (atan($factor/16) )) *  cos( (atan($factor/16) )) * cos( (atan($factor/16) ))), $arrowline['ystart'], $black, $value_font, '&#x25c4;');
			//imagettftext($img, 7, 0-$arrowline['tilt'], $arrowline['xstart'] - abs(( sin (atan($factor/16) )) * ( sin (atan($factor/16) )) *( sin (atan($factor/16) ))) * 3 , $arrowline['ystart']- ((sin(atan($factor/16) )) * sin(atan($factor/16) ) * sin(atan($factor/16) )      )*6 +4 ,$black, $value_font, '&#x25c4;');  // the little arrow that points to where the test result is at

			$test_pos = imagettftext($img, 8, 0, $stab_test_start + 19, $test->y_position+5,$black, $label_font, stability_test_score_shorthand($test, $snowpit_unit_prefs) );
			if ( count($test->field_stability_comments) ){
				imagettftext($img, 9, 0, $test_pos[2] +5 , $test->y_position+5 , $black, $value_font,$test->field_stability_comments['und'][0]['safe_value'] );
			}
					
					*/
							//dsm(cos( (atan($factor/16) ) )); 
							$offset = atan( $factor/ 16) < 0 ? 0 : atan( $factor/ 16) ;
							imagettftext_cr($img, 6, 0 -$arrowline['tilt'], $arrowline['xstart']  -2.5* (1- cos( (atan($factor/16) )) )  + $offset+2, $arrowline['ystart'], $black, $value_font, '&#x25c4;');
							
							
							//imagettftext($img, 7, 0-$arrowline['tilt'], $arrowline['xstart'] - abs(( sin (atan($factor/16) )) * ( sin (atan($factor/16) )) *( sin (atan($factor/16) ))) * 3 , $arrowline['ystart']- ((sin(atan($factor/16) )) * sin(atan($factor/16) ) * sin(atan($factor/16) )      )*6 +4 ,$black, $value_font, '&#x25c4;');  // the little arrow that points to where the test result is at
	
							$test_pos = imagettftext($img, 8, 0, $stab_test_start + 19, $test->y_position+5,$black, $label_font, stability_test_score_shorthand($test, $snowpit_unit_prefs) );
							if ( count($test->field_stability_comments) ){
								imagettftext($img, 9, 0, $test_pos[2] +5 , $test->y_position+5 , $black, $value_font,$test->field_stability_comments['und'][0]['safe_value'] );
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
					imageline($img, 667, snowpit_graph_pixel_depth($density->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'],$global_max, $pit_min ), $stab_test_start, snowpit_graph_pixel_depth($density->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min ),$black);
					imagettftext($img, 8, 0, 671, snowpit_graph_pixel_depth($density->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min )+12,$black, $label_font, $density->field_density_top['und'][0]['value']);
				}
			}

			// set surface grain type image and size
			
			if ( isset($node->field_surface_grain_type['und'][0]['tid']) ){
				// insert grain type image
				$surf_grain_type_image = _tid2snowsymbols($node->field_surface_grain_type['und'][0]['tid']);
				imagettftext($img, '12', 0, 520 , 154 , $black, $snowsymbols_font, $surf_grain_type_image);
			}
			if ( isset($node->field_surface_grain_size['und'][0]['value']) ){

				imagettftext($img, '8', 0, 580 , 154 , $black, $label_font, $node->field_surface_grain_size['und'][0]['value']);
			}
			//
			//  Prep for the 2 Cycles through layers 
			// 
			
			if ( isset($node->field_layer['und'])){
				
				$ids = array();
				foreach ($node->field_layer['und'] as $lay ){ $ids[] = $lay['value']; }
				$all_layers = field_collection_item_load_multiple($ids);
				
				
				foreach($all_layers as $x => $layer){
					if($snowpit_unit_prefs['field_depth_0_from'] == 'top'){
						$layer->y_val_top =		$y_val_top = round(snowpit_graph_pixel_depth($layer->field_bottom_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'],$global_max,$pit_min ),2); 
						$layer->y_val = $y_val = round(snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] , $global_max ),2 ); 
					}else{
						$layer->y_val =		$y_val = round(snowpit_graph_pixel_depth($layer->field_bottom_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] , $global_max, $pit_min ),2 ); 
						$layer->y_val_top =		$y_val_top = round(snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min ),2 ); 
					}
				}
				
				$keyed_all_layers = $all_layers;
									
				snowpilot_layers_density_xlate($keyed_all_layers, $snowpit_unit_prefs, $global_max);
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
						$font_size = ( $grain_type_image == '&#xe028;&#xe007;' ) ? 9 : 12;
						$grain_image_pos = imagettftext($img, $font_size, 0, 520 , ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $snowsymbols_font, $grain_type_image);
					  
					}	
					
					if (isset($layer->field_grain_type_secondary['und'])){
						$secondary_grain_type_image = isset($layer->field_grain_type_secondary['und'][1]['tid'] ) ? _tid2snowsymbols($layer->field_grain_type_secondary['und'][1]['tid']) :  _tid2snowsymbols($layer->field_grain_type_secondary['und'][0]['tid']);
						$font_size = ( $secondary_grain_type_image == '&#xe028;&#xe007;' ) ? 9 : 12;
						$x_pos1 =  isset($grain_image_pos[2]) ? $grain_image_pos[2]+3 : 520;
						$second_grain_image_pos = imagettftext($img, $font_size, 0, $x_pos1 , ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5,
						   $black, $snowsymbols_font, '('.$secondary_grain_type_image.')' );
						
					}
				//output grain symbols

				// calculate grain size string
					$grain_size_string = isset($layer->field_grain_size['und']) ? $layer->field_grain_size['und'][0]['value'] : '' ;
					if ( isset( $layer->field_grain_size_max['und'][0]['value'])) $grain_size_string .= '-' . $layer->field_grain_size_max['und'][0]['value'];
				
				// Ouptut primary grain sizes
					$textpos = imagettftext($img, 8, 0, 580, ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $label_font, $grain_size_string );
					// output secondary grain size
					if ( isset ( $layer->field_grain_size_secondary['und'][0]['value']) && $layer->field_grain_size_secondary['und'][0]['value'] <> ''){						
						if ( $textpos[ 2 ] > 600){  // a "wide load" grain size field, breaking into two rows. This will cause problems on a narrow layer at 20 pixels or less.
						// bump down this down
						  $textpos2 = imagettftext($img, 8, 0, 580, ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +15, $black, $label_font, '(' .$layer->field_grain_size_secondary['und'][0]['value'] . ')' );
					  }else{
						  $textpos2 = imagettftext($img, 8, 0, $textpos[ 2 ] +1, ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $label_font, '(' . $layer->field_grain_size_secondary['und'][0]['value'] . ')' );
					  }
					}
					
				// calculate & output layer moisture	
					if ( isset($layer->field_water_content['und'] )){
						$moisture = $layer->field_water_content['und'][0]['value'];
				 	 	imagettftext($img, 8, 0, 623, ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5, $black, $label_font, $moisture );
				 	}
				
				// Output Layer comments
				  $layer_bottom = $layer->field_bottom_depth['und'][0]['value'] + 0 ;
				  $layer_top = $layer->field_height['und'][0]['value'] + 0;
					$layer_y_val_text = ($layer->y_val_xlate - $layer->y_val_top_xlate)/2 + $layer->y_val_top_xlate +5;
			

					/// 
					//   Collisions check with the tests 
					//   then, write layer notes next to layer
					// also in this block, we attempt to move layer comments with conflicts down by 18 px , perhaps to just bump into the next test result row. After two bump-downs, we give 
					//  up and $conflict = TRUE; so no ouptting that layer notes in the graph
					$conflict = FALSE ; 
					if ( isset ($layer->field_comments['und'][0]) ){
						$iter = 0 ;
					  foreach ( $test_results as $result ){
							if ( ($result->y_position  > $layer_y_val_text -20  ) &&  $result->y_position < $layer_y_val_text +8 && ( $iter <= 2) ){
								$layer_y_val_text = $result->y_position+20; 
								$iter++ ;
								//dsm( "first level conflict"); dsm( "values, layertext: ". $layer_y_val_text . ', test pos: '.$result->y_position);
								if ( (($result->y_position  > $layer_y_val_text -17  ) &&  $result->y_position < $layer_y_val_text +8) || $iter > 2  ) {
								  $conflict = TRUE;
							  }
							}
					  }
						//
						// Now, collision check with other Layers
						//
						foreach ( $keyed_all_layers as $y => $compare_layer ){
							if ( isset ($compare_layer->field_comments['und'][0]) ){
								if ( ($compare_layer->y_val_text  > $layer_y_val_text -20  ) &&  $compare_layer->y_val_text < $layer_y_val_text +8 && ( $iter <= 2) ){
									$layer_y_val_text = $compare_layer->y_val_text+20; 
									$iter++ ;
									//dsm( "first level conflict"); dsm( "values, layertext: ". $layer_y_val_text . ', test pos: '.$result->y_position);
									if ( (($compare_layer->y_val_text  > $layer_y_val_text -17  ) &&  $compare_layer->y_val_text < $layer_y_val_text +8) || $iter > 2  ) {
									  $conflict = TRUE;
								  }
								}
							}
						}

					  if  ( !$conflict  ) {
							$layer->y_val_text = $layer_y_val_text ;
							$layer_place_pos = imagettftext($img, 9, 0, $stab_test_start +5, $layer_y_val_text +5, $black, $label_font, $layer_top .'-'. $layer_bottom .$snowpit_unit_prefs['field_depth_units'].': ');
							imagettftext($img, 9, 0, $layer_place_pos[2] , $layer_y_val_text +5, $black, $value_font, $layer->field_comments['und'][0]['safe_value']); 
						}
												
						if ( $comment_counter <5 ){

						  $textpos2 = imagettftext($img, 9, 0, 682, $comment_counter*13 + 35, $black, $label_font,
							$layer_top.'-'.$layer_bottom. $snowpit_unit_prefs['field_depth_units'].': ');
							imagettftext( $img, 9, 0, $textpos2[2]+1, $comment_counter*13 + 35, $black, $value_font,  $layer->field_comments['und'][0]['safe_value']);
					  }else{
							if( $comment_counter == 5 ) { 
								$xtra_specifics .= '. Additional Layer Comments: ';
						    imagettftext($img, 7, 0, 685, $comment_counter*13 + 31, $red_layer, $label_font, '[ '. t("More Layer Comments below") . ' ]');
							}
							$xtra_specifics .= $layer_bottom.'-'.$layer_top. $snowpit_unit_prefs['field_depth_units'].': '.$layer->field_comments['und'][0]['safe_value'].'; ';
					  }
					  $comment_counter++;
				  }
				  
					// write density measurements that are from the 'Layers' tab into the rho column ( in addition to Densities )
					if ( isset ( $layer->field_density_top['und'][0]['value'] )){
						imageline($img, 667, snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min), $stab_test_start, snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min),$black);
						imagettftext($img, 8, 0, 670, snowpit_graph_pixel_depth($layer->field_height['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min)+13,$black, $label_font, $layer->field_density_top['und'][0]['value']);
						
					}
					
					snowpilot_draw_layer_polygon($img, $layer, $purple_layer, TRUE, $snowpit_unit_prefs['hardnessScaling']);  // the fill
					snowpilot_draw_layer_polygon($img, $layer, $blue_outline, FALSE, $snowpit_unit_prefs['hardnessScaling']);  // the outline
					// this mark the layer if its a critical layer, and save some 
					if ($layer->field_this_is_my_layer_of_greate['und'][0]['value'] == '1'){
						$x_redline = _h2pix($layer->field_hardness['und'][0]['value'], FALSE, $snowpit_unit_prefs['hardnessScaling']); 
						$x_redline_bottom = _h2pix($layer->field_hardness['und'][0]['value'], FALSE, $snowpit_unit_prefs['hardnessScaling']);
					  if ( isset ( $layer->field_hardness2['und'][0]['value'])) $x_redline_bottom = _h2pix($layer->field_hardness2['und'][0]['value'], FALSE, $snowpit_unit_prefs['hardnessScaling']);
						
						$y_redline_top = $layer->y_val_top; $y_redline_bottom = $layer->y_val; /// 
						$concern_delta = $layer->item_id;
						
						if ($comment_counter < 5){
						 	//imagettftext($img, 9, 0, 682, $comment_counter*13 + 35, $black, $value_font, $layer_bottom.'-'.$layer_top.": Problematic layer");
							
							
						  $textpos3 = imagettftext($img, 9, 0, 682, $comment_counter*13 + 35, $black, $label_font,
							   $layer_top.'-'.$layer_bottom. $snowpit_unit_prefs['field_depth_units'].':');
							imagettftext( $img, 9, 0, $textpos3[2]+1, $comment_counter*13 + 35, $black, $value_font,  " Problematic layer");
							
							
						}else{
							if( $comment_counter == 5 ) { 
								$xtra_specifics .= '. Additional Layer Comments: ';
						    imagettftext($img, 7, 0, 685, $comment_counter*13 + 31, $red_layer, $label_font, '[ '. t("More Layer Comments below") . ' ]');
							}
							$xtra_specifics .= $layer_bottom.'-'.$layer_top.": " . t("Problematic layer") . '; ';
						}
						
						$comment_counter++;
					} 			
								
				}
			} 
			// now that we are done drawing all the layers, we can overprint the red layer of concern
			if (isset($concern_delta)){
				$layer_part = isset($all_layers[$concern_delta]->field_concern['und'][0]['value'] ) ? $all_layers[$concern_delta]->field_concern['und'][0]['value'] : 'entire layer';
				switch ($layer_part){
					case 'entire layer':
						
						//full-on red layer was too much; withdrawn for now; in favor of perhaps diagonal lines of red indicating layer of concern ( all layer ) in future
						// 
					break;
					case 'top':
						imageline($img, $x_redline, $y_redline_top+2, 446, $y_redline_top+2, $red_layer );
						imageline($img, $x_redline, $y_redline_top+1, 446, $y_redline_top+1, $red_layer );
					break;
					case 'bottom':
						imageline($img, $x_redline_bottom, $y_redline_bottom-2, 446, $y_redline_bottom-2, $red_layer );
						imageline($img, $x_redline_bottom, $y_redline_bottom-1, 446, $y_redline_bottom-1, $red_layer );
					break;
				}
			}
			
		
			// Temperature Profile:
			// If we have temp profile readings,then we'll make the tick marks
			
			if ( isset($node->field_temp_collection['und'])){
				$ids = array();
				foreach ($node->field_temp_collection['und'] as $temp ){ $ids[] = $temp['value']; }
				$all_temps = field_collection_item_load_multiple($ids);

				uasort($all_temps, 'depth_val');
				$min_temp = ($snowpit_unit_prefs['field_temp_units'] == 'F') ? 22 : -8 ;
				$min_temp = _temp_profile_find_min_temp($all_temps, $min_temp) - 2 ;
				$temp_span = ($snowpit_unit_prefs['field_temp_units'] == 'F') ? 32 - $min_temp :  0 - $min_temp ;
				$pixels_per_degree =  433/$temp_span ;

				if ($snowpit_unit_prefs['field_temp_units'] == 'C'){
					$increment = ($temp_span > 14 )? 2 : 1;
					$x= 0; while ($x >=$min_temp ){ //  tickmarks
						imageline($img, 447 + $pixels_per_degree * $x, 132, 447 + $pixels_per_degree * $x, 140, $black );
						imagettftext($img, 9, 0, 441 + $pixels_per_degree * $x, 130, $black, $label_font, $x  );
						$x = $x - $increment;
					}

				}else{ /// Temperature units = 'F'
					$increment = ($temp_span > 5) ? 2 : 1;
					$x= 32; while ($x >=$min_temp ){  // tickmarks
						imageline($img, 447 - $pixels_per_degree * ( 32-$x), 132, 447-$pixels_per_degree * (32-$x) , 140, $black );
						imagettftext($img, 9, 0, 441 - $pixels_per_degree * (32- $x), 130, $black, $label_font, $x  );
						$x = $x - $increment;
					}

				} // end temp units toggle
				
				// draw points, and line, different $cx calculations for F or C
				$prev_x=0; $prev_y = 0; 
				foreach($all_temps as $x=> $temp){
					$cx =  ($snowpit_unit_prefs['field_temp_units'] == 'C') ?  447 + $pixels_per_degree * ($temp->field_temp_temp['und'][0]['value']) :
					447 - $pixels_per_degree * (32 - $temp->field_temp_temp['und'][0]['value']);
					if( $cx >= 14 && $cx <= 447 ){
						// draw point
						imagefilledellipse($img, $cx, snowpit_graph_pixel_depth($temp->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] , $global_max, $pit_min), 6, 6, $red_layer );
					// draw line
						if (($prev_x <=447 && $prev_x >=14 ) && $prev_y){ 
							imageline($img, $cx, snowpit_graph_pixel_depth($temp->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] , $global_max, $pit_min) , $prev_x, $prev_y, $red_layer); 
						}
					}
					// save this point location to use to draw the next line
					$prev_x = $cx ; $prev_y = snowpit_graph_pixel_depth($temp->field_depth['und'][0]['value'], $pit_depth, $snowpit_unit_prefs['field_depth_0_from'] , $global_max, $pit_min);
				
				}
				
			} // end of drawing the temperature profile
						
			// cycle through and make depth tick marks
			$x = round($pit_min) - round($pit_min)%10 + 10;
			
			while ( $x <= $pit_depth){
				$y_val = round(snowpit_graph_pixel_depth($x, $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min));
				imageline($img, $stab_test_start-7 , $y_val, $stab_test_start, $y_val,$black);
				imageline($img, 511 , $y_val, 518, $y_val,$black);
				imageline($img, 14 , $y_val, 22, $y_val, $black);
				imageline($img, 440, $y_val, 447, $y_val, $black);
				
				if ( abs($y_val - round(snowpit_graph_pixel_depth( $pit_depth , $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min))) > 10 
				     &&   abs($y_val - round(snowpit_graph_pixel_depth( $pit_min , $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min))) > 10      ) { 
					imagettftext($img, 10, 0, 456, $y_val+5, $black, $label_font, $x );
				}
				$x+=10;
			}
			$y_val_final = round(snowpit_graph_pixel_depth($pit_depth, $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min));
			// final HoS at top or bottom
			if ( $shrunken_pit && $snowpit_unit_prefs['field_depth_0_from'] == 'top'){
			  imagettftext($img, 10, 0, 456,  756 , $black, $label_font, $node->field_total_height_of_snowpack['und'][0]['value'] );
			  imageline($img, 440, 751, 447, 751, $black);
				imagettftext($img, 10, 0, 456, $global_max+5, $black, $label_font, round($pit_depth, 1) );
			  imagettftext($img, 10, 0, 456,  162 , $black, $label_font, '0' );
			}elseif ( $shrunken_pit ){
			  imagettftext($img, 10, 0, 456,  $y_val_final+5 , $black, $label_font, $node->field_total_height_of_snowpack['und'][0]['value'] );
			  imageline($img, 440, $y_val_final , 447, $y_val_final, $black);
				imagettftext($img, 10, 0, 456, $global_max+5, $black, $label_font, round($pit_min, 1) );
				// Also need a '0' at the bottom of the pit since we never looped all the way down there
			  imagettftext($img, 10, 0, 456,  751+5 , $black, $label_font, '0' );
			  imageline($img, 440, 751, 447, 751, $black);
				
			}else{
			  imagettftext($img, 10, 0, 456, $y_val_final+5, $black, $label_font, $node->field_total_height_of_snowpack['und'][0]['value'] );
			  imageline($img, 440, $y_val_final, 447, $y_val_final, $black);
				$zero_pixel_val = $snowpit_unit_prefs['field_depth_0_from'] == 'top' ? 162 : 756 ;
			  imagettftext($img, 10, 0, 456, $zero_pixel_val, $black, $label_font, '0' );
				
 			}
			
		
			// Now we make the 5cm tick marks
			$x = round($pit_min) - round($pit_min)%5 + 5;
			while ( $x <= $pit_depth){
				$y_val = round(snowpit_graph_pixel_depth($x, $pit_depth, $snowpit_unit_prefs['field_depth_0_from'], $global_max, $pit_min));
				
				imageline($img, $stab_test_start-3 , $y_val, $stab_test_start, $y_val,$black);
				imageline($img, 511 , $y_val, 515, $y_val,$black);
				imageline($img, 14, $y_val, 18, $y_val, $black);
				imageline($img, 443, $y_val, 447,$y_val, $black);
				
				//imagettftext($img, 10, 0, 638, round(snowpit_graph_pixel_depth($x, $node, 'bottom'))+5, $black, $label_font, $x );
				$x+=5;
			}
			
			//
			
	imagettftext($img, 10, 0 , $stab_test_start +20, 122, $black ,$label_font, "Stability tests & Layer comments");
			
	imagettftext($img , 10, 0, $stab_test_start -26, 118, $black, $label_font, "&#x3c1;"); // Rho symbol for density
	imagettftext($img, 10, 0 , $stab_test_start -32,135, $black, $label_font , _density_unit_fix($snowpit_unit_prefs['field_density_units']) );
	
	// the rectabngle around stability and density columns
  imagerectangle( $img , 667 ,140 , 979, 751, $black);
	
	// the rectangle around the layers hardness profile
	imagerectangle($img, 14, 140, 447,751, $black );
	// line at left side of rho column
	//imageline( $img , 820, 140, 820 , 751, $black);
	
	
	//the tickmarks for hardness across the bottom and top, and labels
	foreach ( _h2pix(NULL, TRUE, $snowpit_unit_prefs['hardnessScaling'] ) as $hardness => $pixels ){
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
	
	
	imageline( $img , $stab_test_start, 140, $stab_test_start, 751, $black ); //vertical line at left edge of stability tests
	imageline($img, 483,140, 667,140,$black); // finish line across top
	imageline($img, 483,751, 667,751,$black); // finish line across bottom
	imageline($img, 483,140 , 483, 751 , $black); // left edge, first vert line
	imageline($img, 511,140 , 511, 751 , $black); // beginning of crystal form column
	imageline($img, 575,135, 575, 751, $black  ); //beginning of crystal size column
	imageline($img, 617,140, 617, 751, $black  ); //beginning of crystal moisture column
	
	if ( $shrunken_pit ){ // this inserts the zigazag line at the bottom of a shrunken pit
		imagefilledrectangle($img, 446, 736, 710, 741, $white  );
	  imagettftext($img, 10, 0 , 447, 738, $black, $snowsymbols_font, 'YYYYY');
	  imagettftext($img, 10, 0 , 447, 743, $black, $snowsymbols_font, 'YYYYY');
		imagefilledrectangle($img, 708, 729, 719, 745, $white  );
		imageline($img, 447, 739, 447, 741,$black);
	}
	
	imagettftext($img, 10, 0 , 554, 122, $black ,$label_font, t("Crystal"));
	imagettftext($img, 10, 0 , 516,137, $black, $label_font , t("Form"));
	imagettftext($img, 10, 0 , 580,137, $black, $label_font , t("Size"));
	imagettftext($img, 10, 0 , 616,137, $black, $label_font , t("Moisture"));
	
	
	// write out the small image before laying the watermark
	snowpilot_snowpit_crop_layers_write($img,$node->nid);
	
	// Snowpilot water mark logo _100
	$sp_watermark = imagecreatefrompng(DRUPAL_ROOT.'/sites/all/themes/sp_theme/images/SnowPilot_Watermark_BlueOrange_100.png');
	imagecopy ( $img , $sp_watermark , 35 , 170 , 0 , 0, 160 , 99);
	imagedestroy($sp_watermark); 	
	
	// writing the pit notes now that we have any extra layer or stability test notes that didn't fit
	$textpos = imagettftext($img, 11, 0, 14,779, $black, $label_font, 'Notes: ');
	$final_notes_string = (isset($node->body['und'][0]) && $node->body['und'][0]['safe_value'] != '' )  ? $node->body['und'][0]['safe_value'] . $xtra_specifics : $xtra_specifics;
	if ( $final_notes_string <> '' ){ 
		$notes_lines = _output_formatted_notes($final_notes_string, $value_font);
    if ( count($notes_lines) > 3 ){
	  	$rescaled_img = imagecreatetruecolor('994', 840 + (count($notes_lines)-3) * 19);
		  imagefill($rescaled_img, 0, 0, $white);
			$resultes = imagecopy ( $rescaled_img , $img , 0 , 0 , 0 , 0, 994, 840);
			$img = $rescaled_img;
		}
		foreach($notes_lines as $x => $line){
			imagettftext($img, 9, 0, $textpos[2], 779 + $x * 19 ,$black, $value_font,$line);
		}
	}	

	// Output the jpg and png image
	$fileroot = DRUPAL_ROOT.'/sites/default/files/snowpit-profiles/' . substr($node->nid, 0, -3 ). '/graph/graph-' .$node->nid;
	
	imagejpeg($img, $fileroot. '.jpg',100);
	imagepng($img, $fileroot. '.png');
	
	snowpilot_imagepdf($fileroot);
// Destroy GD image
//imagedestroy($img);
if ($format == 'jpg') {
	return $img;
}else{
  return $img;}
}

function snowpilot_snowpit_crop_layers_write($img,$nid){
	$new_img = imagecreatetruecolor(466,613);
	$result = imagecopy($new_img, $img, 0,0, 14,140,466,613 );
	
	imagepng($new_img, DRUPAL_ROOT.'/sites/default/files/snowpit-profiles/' . substr($nid, 0, -3 ). '/layers/layers-'.$nid. '.png');	
}

function snowpilot_imagepdf($fileroot){
	include_once(DRUPAL_ROOT.'/sites/all/libraries/fpdf181/fpdf.php');
	$Size = getimagesize($fileroot.'.png');
	$nid = substr($fileroot,strpos($fileroot,'graph/graph-')+12 );
		
	if ( $Size[1] == 840 ){
		$pdf = new FPDF();
		$pdf->AddPage('L');
		$pdf->Image($fileroot.'.png', 0,0,0,205);
		$pdf->Output(DRUPAL_ROOT.'/sites/default/files/snowpit-profiles/'. substr($nid, 0, -3 ) .'/pdf/pdf-'.$nid.'.pdf' ,'F');
	}elseif ( $Size[1] < 994 ){
		$pdf = new FPDF();
		$pdf->AddPage('L','Letter');
		$pdf->Image($fileroot.'.png', 0,0,0, 215.9);
		$pdf->Output(DRUPAL_ROOT.'/sites/default/files/snowpit-profiles/'. substr($nid, 0, -3 ) .'/pdf/pdf-'.$nid.'.pdf' ,'F');
	}else{
		
		$pdf = new FPDF();
		$pdf->AddPage('P','Letter');
		$pdf->Image($fileroot.'.png', 0,0,215.9, 0);
		$pdf->Output(DRUPAL_ROOT.'/sites/default/files/snowpit-profiles/'. substr($nid, 0, -3 ) .'/pdf/pdf-'.$nid.'.pdf' ,'F');
		
	}
	return ;
}





<?php

/*
//  This function creates a pitxml based on the node information passed to it. should be compliant with the snowpilot datamodel.
//  $format can be 'full' or 'restricted' - full will return the User element as well, shouldn't be saved.
//  $regenerate_pit will rebuild the xml pit from drupal data even if it already exists
*/
function snowpilot_node_write_pitxml($node, $format = 'restricted', $regenerate_pit = FALSE ){
	$xml_filename = '/sites/default/files/snowpit-xml/node-'.$node->nid.'.xml';
	if ( !file_exists(DRUPAL_ROOT. $xml_filename) || $regenerate_pit ){
		watchdog('snowpilot', "Snowpit $node->nid xml does not exist or will be regenerated.");
		$snowpilot_xmldoc = new DOMDocument('1.0', 'UTF-8');
		$snowpilot_PitCore = $snowpilot_xmldoc->createElement("Pit_Observation"); $snowpilot_xmldoc->appendChild($snowpilot_PitCore);
		$snowpilot_User = $snowpilot_xmldoc->createElement("User"); 
		//
		//  We only include user data if this is a public pit, not group or private
		//  TODO: This could be expanded to include viibility options for node permissions according to the active user,
		//  however, only the 'User-info free' version should be saved to the snowpit-xml directory, for private or group pits.
		// this could be moved to the very end of the function, where the saving and returning of values happens
		// or left up here and rolled in with the 'if file exists stuff above
	  if ( $node->field_snowpit_visibility['und'][0]['value'] == 'public' || $format == 'full'){
			$snowpilot_PitCore->appendChild($snowpilot_User);
		}
		$snowpilot_Location = $snowpilot_xmldoc->createElement("Location"); $snowpilot_PitCore->appendChild($snowpilot_Location);
		// ...but we have multiple layers
	  // Likewise, multiple shear test results
		$snowpilot_Density = $snowpilot_xmldoc->createElement("Density_Profile");
		$snowpilot_Temp = $snowpilot_xmldoc->createElement("Temperature_Profile");
	
		//PitCore Attributes: activities, aviLoc,iLayerNumber, bld, stability,avipit,incline,winDir,skiAreaPit ,bcPit, testPit,windspeed,aspect,skiBoot,measureFrom,sky,sufacePen, windloading,heightOfSnowpack,precip , serial, version,pitNotes,crownObs,timestamp,iDepth
		$activities = $snowpilot_xmldoc->createAttribute("activities");
		$activities->value = _generate_specifics_string($node);
		$snowpilot_PitCore->appendChild($activities);
	
		//aviLoc - or 'Pit is near avalanche at what point?'
		$aviLoc = $snowpilot_xmldoc->createAttribute("aviLoc");
		$aviLoc->value = isset($node->field_near_avalanche['und'][0]['value']) ? $node->field_near_avalanche['und'][0]['value'] : '' ;
		$snowpilot_PitCore->appendChild($aviLoc);
		// iLayerNumber
		// this implementation assumes that the layers are in numerical order from top down; item_id is a unique id in field collections, can't use it.
		$iLayerNumber = $snowpilot_xmldoc->createAttribute("iLayerNumber");	
		$iDepth = $snowpilot_xmldoc->createAttribute("iDepth");
		$ids = array();
		foreach ($node->field_layer['und'] as $lay ){ $ids[] = $lay['value']; }
		$all_layers = field_collection_item_load_multiple($ids);
		$iLayerNumber_value = 1;	
		$iLayerNumber->value = '';
		foreach($all_layers as $x => $layer){
			if ($layer->field_this_is_my_layer_of_greate['und'][0]['value'] == '1') { 
			
				$iLayerNumber->value = $iLayerNumber_value ; 
				$iDepth->value = 	($layer->field_concern['und'][0]['value'] == 'top') ? $layer->field_height['und'][0]['value'] : $layer->field_bottom_depth['und'][0]['value'] ;
				break; 
			}else{
				$iLayerNumber_value++;
			}
		}
	  $snowpilot_PitCore->appendChild($iLayerNumber);
		$snowpilot_PitCore->appendChild($iDepth);
		//
		//bld is the build number from the snowpilot desktop app. for this case, we will use drupal version 7.xx 
		$bld = $snowpilot_xmldoc->createAttribute("bld");
		$bld->value = VERSION;
		$snowpilot_PitCore->appendChild($bld);
		
		// nid is Node id
		$node_id = $snowpilot_xmldoc->createAttribute("nid");
		$node_id->value = $node->nid;
		$snowpilot_PitCore->appendChild($node_id);
		
		//
		// stability
		$stability = $snowpilot_xmldoc->createAttribute("stability");
		//dsm($node->field_stability_on_similar_slope['und'][0]['value']);
		$similar_stability = field_view_field('node', $node, 'field_stability_on_similar_slope');
		$stability->value = isset($node->field_stability_on_similar_slope['und'][0]['value']) ? $similar_stability[0]['#markup'] : '' ;
		$snowpilot_PitCore->appendChild($stability);
		//
		// aviPit
		//
		$aviPit = $snowpilot_xmldoc->createAttribute("aviPit");
		$crownObs = $snowpilot_xmldoc->createAttribute("crownObs");

		$aviPit->value = (isset($node->field_adjacent_to_avy['und'][0]['value'] ) && $node->field_adjacent_to_avy['und'][0]['value'] == 1  ) ? 'true' : 'false';
		$snowpilot_PitCore->appendChild($aviPit);
		$crownObs->value = $aviPit->value;
		$snowpilot_PitCore->appendChild($crownObs); // CrownObs is a synonym for aviPit in the avscience_db
		//
		//
		//  aviLoc - location of pit relative to the Avalanche: crown, flank, none ( unlike desktop app, this will only be populated in the case of aviPit == true )
		//
		$aviLoc = $snowpilot_xmldoc->createAttribute("aviLoc");
		if (isset($node->field_adjacent_to_avy['und'][0]['value']) && ($node->field_adjacent_to_avy['und'][0]['value'] == 1) && isset($node->field_near_avalanche['und'][0]['value'] ) ){
		  $aviLoc->value = $node->field_near_avalanche['und'][0]['value'] ;
		}else{
			$aviLoc->value = '';
		}
		$snowpilot_PitCore->appendChild($aviLoc);
	
		// incline
		$incline = $snowpilot_xmldoc->createAttribute("incline");
		$incline->value = isset($node->field_slope_angle['und'][0]['value']) ? $node->field_slope_angle['und'][0]['value'] : '' ;
		$snowpilot_PitCore->appendChild($incline);
		//
		// winDir
		$winDir = $snowpilot_xmldoc->createAttribute("winDir");
		$winDir->value = isset($node->field_wind_direction['und'][0]['value']) ? snowpilot_cardinal_wind_dir($node->field_wind_direction['und'][0]['value']) : '' ;
		$snowpilot_PitCore->appendChild($winDir);
		//
		//skiAreaPit
		$skiAreaPit = $snowpilot_xmldoc->createAttribute("skiAreaPit");
		$skiAreaPit->value = (isset($node->field_pit_dug_in_a_ski_area['und'][0]['value']) && $node->field_pit_dug_in_a_ski_area['und'][0]['value'] == 1 ) ? 'true' : 'false' ;
		$snowpilot_PitCore->appendChild($skiAreaPit);
		//
		//
		// bcPit        Trinary: true , false ,or '' unset
		// we leavre unset if the 'skiAreaPit' variable is unset, or false
	
		$bcPit = $snowpilot_xmldoc->createAttribute("bcPit");
		if ( !isset($node->field_pit_is_representative_of_b['und'][0]['value']) || !isset($node->field_pit_dug_in_a_ski_area['und'][0]['value']) || $node->field_pit_dug_in_a_ski_area['und'][0]['value'] == 0){
			$bcPit->value = '';
		}elseif($node->field_pit_is_representative_of_b['und'][0]['value'] == '1' ){
			$bcPit->value = 'true';
		}else{
			$bcPit->value = 'false';
		}
		$snowpilot_PitCore->appendChild($bcPit);
		//
		// testpit
		//
		$testPit = $snowpilot_xmldoc->createAttribute("testPit");
		$testPit->value = (	isset($node->field_practice_pit['und'][0]['value']) && $node->field_practice_pit['und'][0]['value'] == 1  ) ? 'true' : 'false' ;
		$snowpilot_PitCore->appendChild($testPit);
		//
		//windspeed
		$windspeed = $snowpilot_xmldoc->createAttribute("windspeed");
		$wind_speed = field_view_field('node', $node, 'field_wind_speed');
		$windspeed->value = (isset($node->field_wind_speed['und'][0]['value']) ) ? $wind_speed[0]['#markup'] : '' ;
		$snowpilot_PitCore->appendChild($windspeed);
		//
		// aspect
		$aspect = $snowpilot_xmldoc->createAttribute("aspect");
		$aspect->value = isset($node->field_aspect['und'][0]['value']) ?  $node->field_aspect['und'][0]['value'] : '' ;
		$snowpilot_PitCore->appendChild($aspect);
		//
		// skiBoot   - Surface Penetration  
		// set whether dki or boot penetration is used; and identify the value of each
		$skiBoot = $snowpilot_xmldoc->createAttribute("skiBoot");
		$surfacePen = $snowpilot_xmldoc->createAttribute("surfacePen");
		if ( !isset($node->field_surface_penetration['und'][0]['value']) ){
			$skiBoot->value = '';
		}elseif($node->field_surface_penetration['und'][0]['value'] == 'boot' ){
			$skiBoot->value = 'boot';
			$surfacePen->value = isset($node->field_boot_penetration_depth['und'][0]['value']  ) ?
				$node->field_boot_penetration_depth['und'][0]['value'] : '' ;
		}else{
			$skiBoot->value = 'ski';
			$surfacePen->value = isset($node->field_ski_penetration['und'][0]['value']  ) ?
				$node->field_ski_penetration['und'][0]['value'] : '' ;
		}
		$snowpilot_PitCore->appendChild($skiBoot);
		$snowpilot_PitCore->appendChild($surfacePen);
		//
		//  measureFrom
		$measureFrom = $snowpilot_xmldoc->createAttribute("measureFrom");
		$measureFrom->value = (isset($node->field_depth_0_from['und'][0]['value']) && $node->field_depth_0_from['und'][0]['value'] == 'top' ) ? 'top' : 'bottom' ; 
		$snowpilot_PitCore->appendChild($measureFrom);
		//
		// Sky coverage
		//
		$skyCoverage = $snowpilot_xmldoc->createAttribute('sky');
		if (isset($node->field_sky_cover['und'])){
			$skyCoverage->value = $node->field_sky_cover['und'][0]['value'];
		}
		$snowpilot_PitCore->appendChild($skyCoverage);
		//
		//  Wind Loading
		//
		$windLoading = $snowpilot_xmldoc->createAttribute('windLoading');
		if (isset ( $node->field_wind_loading['und'][0]['value'])){
			$windLoading->value = $node->field_wind_loading['und'][0]['value'];
		}
		$snowpilot_PitCore->appendChild($windLoading);
		//
		//  heightof Snowpack
		//
		$HoSnowpack = $snowpilot_xmldoc->createAttribute('heightOfSnowpack');
		if (isset ( $node->field_total_height_of_snowpack['und'][0]['value'])){
			$HoSnowpack->value = $node->field_total_height_of_snowpack['und'][0]['value'];
		}
		$snowpilot_PitCore->appendChild($HoSnowpack);
		//
		//  Precipitation
		//
		$precipitation = $snowpilot_xmldoc->createAttribute('precip');
		if ( isset ( $node->field_precipitation['und'][0]['value']) ){
			$precipitation->value = $node->field_precipitation['und'][0]['value'];
		}
		$snowpilot_PitCore->appendChild($precipitation);

		//
		//  Serial number - must be unique to this pit
		//  To maintain continuity with the desktop app, it should be drupal + nodeid + timestamp ( unix, w/ milliseconds) of posting the item
		//  php microtime won't work here; we jsut use a millisecond timestamp padded with 0
		//
		$serial_num = $snowpilot_xmldoc->createAttribute('serial');
		$account = user_load($node->uid);
		$serial_num->value = 'drupal-nid-'.$node->nid .'-'. $node->created . '000';
		$snowpilot_PitCore->appendChild($serial_num);
		//
		//  version - this will include drupal bld number ( starting at 7.5x ) and then also include the snowpilot.module version, and the browser type and version (similar to desktop app)
		//  I don't think browser type is directly supported within drupal, need browsecap module (?)
		//
		$web_version = $snowpilot_xmldoc->createAttribute('version');
		$snowpilot_version = '0.1'; // this will need to be read from the module version eventually
		$web_version->value = VERSION.'-'.$snowpilot_version ; // and then we'll need to append the browser and OS if we can.
		$snowpilot_PitCore->appendChild($web_version);
		//
		// Pit Notes- a potentially long text field, with wierd foregin characters and illegal characters
		//
		$pit_notes = $snowpilot_xmldoc->createAttribute('pitNotes');
		if ( isset( $node->body['und'])){
			$pit_notes->value = $node->body['und'][0]['safe_value'];
		}
		$snowpilot_PitCore->appendChild($pit_notes);
		//
		//  timestamp
		//
		$timestamp=$snowpilot_xmldoc->createAttribute('timestamp');
		$timestamp->value = strtotime($node->field_date_time['und'][0]['value']) . '000';
		$snowpilot_PitCore->appendChild($timestamp);
		//
		// Snowpilot name: User; drupal object: account
		//
		//
		//

	
		$unit_prefs = snowpilot_unit_prefs_get($node, 'node');
	
		$preferences = array();
		foreach ( $unit_prefs as $key => $pref){
			if (substr( $key, 0 ,6 ) != 'field_'){	 
			 
		  	$preferences[$key] = $snowpilot_xmldoc->createAttribute( $key );
		  	$user_preferences[$key] = $snowpilot_xmldoc->createAttribute( $key );
				
		  	$preferences[$key]->value = $pref;
				$user_preferences[$key]->value = $pref;
				
		  	$snowpilot_User->appendChild($user_preferences[$key]);
				$snowpilot_PitCore->appendChild($preferences[$key]);
		  }
		}
		//
		//  share
		//   this needs to become trinary, not just boolean
		//   A tricky way to do this: anything but 'true' is interpretted as false, some we set to one of 'true' ( which is 'public') , 'private' , or 'group'
		//
		$UserpitShare = $snowpilot_xmldoc->createAttribute('share');	
		$pitShare = $snowpilot_xmldoc->createAttribute('share');
		
		$UserpitShare->value = $pitShare->value = ($node->field_snowpit_visibility['und'][0]['value'] == 'public') ? 'true' : $node->field_snowpit_visibility['und'][0]['value'];
		
		$snowpilot_User->appendChild($UserpitShare);
		$snowpilot_PitCore->appendChild($pitShare);
		
		//
		// The $user object will only be included on public snowpits
		
		$username = $snowpilot_xmldoc->createAttribute('username'); // this doesn't actually exist in the snowpilot data model, but seems good to include
		$username->value = $account->name;
		$snowpilot_User->appendChild($username);
		//$snowpilot_User->removeChild('range');

		//
		//  useSymbols (?)
		//
		//if ()
		$useSymbols = $snowpilot_xmldoc->createAttribute( 'useSymbols');
		$useSymbols->value = 'true';
		$snowpilot_User->appendChild($useSymbols);
	
		//
		// full name
		//
		$fullName = $snowpilot_xmldoc->createAttribute('name');
		$firstName = $snowpilot_xmldoc->createAttribute('first');
		$lastName = $snowpilot_xmldoc->createAttribute('last');
		$firstName->value = isset($account->field_first_name['und']) ? $account->field_first_name['und'][0]['safe_value'] : '' ;
		$lastName->value = isset( $account->field_last_name['und'] ) ? $account->field_last_name['und'][0]['safe_value'] : '' ;
		$space = ($firstName->value <> '' && $lastName->value <> '') ? ' ' : '';
		$fullName->value = $firstName->value . $space . $account->field_last_name['und'][0]['safe_value'];
	
	
		$snowpilot_User->appendChild($firstName);
		$snowpilot_User->appendChild($lastName);
		$snowpilot_User->appendChild($fullName);
	
	

		//
		//  phone of submitter
		//
		$userPhone = $snowpilot_xmldoc->createAttribute('phone');
		$userPhone->value = $account->field_phone['und'][0]['value'];
		$snowpilot_User->appendChild($userPhone);
	
		//
		// prof - professional checkbox
		//
		$userProf = $snowpilot_xmldoc->createAttribute('prof');
		$userProf->value = (isset( $account->field_professional['und'][0]['value'] ) && $account->field_professional['und'][0]['value'] == '1') ? 'true' : 'false';
		$snowpilot_User->appendChild($userProf);
	
		//
		// email
		//
		$userEmail = $snowpilot_xmldoc->createAttribute('email');
		$userEmail->value = $account->mail;
		$snowpilot_User->appendChild($userEmail);
	
		//
		// affil - Affilliation
		//
		$profAffil = $snowpilot_xmldoc->createAttribute('affil');
		$pitprofAffil  = $snowpilot_xmldoc->createAttribute('affil');
		
		$pitprofAffil->value = $profAffil->value = isset ($node->field_org_ownership['und'][0]['tid']) ? taxonomy_term_load($node->field_org_ownership['und'][0]['tid'])->name : '';
		// Commenting out  where affiliation is attached as a pit core attribute- 
		//$snowpilot_PitCore->appendChild($pitprofAffil);
		
		//we will leave affil in the  User
		$snowpilot_User->appendChild($profAffil);
		//
		//  Location Element
		//
	
		// ID is required, even if blank
		//
		$id = $snowpilot_xmldoc->createAttribute('ID');
		$snowpilot_Location->appendChild($id);
		// Coordinate type: UTM or Lat / Long
		//
	
		$coordType = $snowpilot_xmldoc->createAttribute('type');
		$coordType->value = ($unit_prefs['coordType'] == 'UTM' ) ? 'UTM' : 'LATLONG';
		$snowpilot_Location->appendChild($coordType);
	
	
		//zone
		$UTMzone = $snowpilot_xmldoc->createAttribute('zone');
		$UTMzone->value = $unit_prefs['zone'];
		$snowpilot_Location->appendChild($UTMzone);
	
		//
		// whether Lat long is measure N or S of equator; E or W of 0 degrees
		$nstype = $snowpilot_xmldoc->createAttribute('ns');
		$nstype->value = $unit_prefs['latType'];
		$snowpilot_Location->appendChild($nstype);
	
		$ewtype = $snowpilot_xmldoc->createAttribute('ew');
		$ewtype->value = $unit_prefs['longType'];
		$snowpilot_Location->appendChild($ewtype);
	
		//
		//  East and North values for utm coordtype
		//
		$location_east = $snowpilot_xmldoc->createAttribute('east');
		$location_east->value = $unit_prefs['field_east'];
		$snowpilot_Location->appendChild($location_east);
	
		$location_north = $snowpilot_xmldoc->createAttribute('north');
		$location_north->value = $unit_prefs['field_north'];
		$snowpilot_Location->appendChild($location_north);
	
	
	
		//
		// Latitude
		$latitude = $snowpilot_xmldoc->createAttribute('lat');
		$latitude->value = $unit_prefs['field_latitude'];
		$snowpilot_Location->appendChild($latitude);
		//
		// Longitude
		$longitude = $snowpilot_xmldoc->createAttribute('longitude');
		$longitude->value =  $unit_prefs['field_longitude'];
		$snowpilot_Location->appendChild($longitude);
		//
		// Elevation
		$elevation = $snowpilot_xmldoc->createAttribute('elv');
		$elevation->value = isset($node->field_elevation['und'][0]['value']) ? $node->field_elevation['und'][0]['value'] : '';
		$snowpilot_Location->appendChild($elevation);
		//
		// State
	  $location_state = $snowpilot_xmldoc->createAttribute('state');
		$location_state->value = $unit_prefs['state'];
		$snowpilot_Location->appendChild($location_state);
		//
		// Range
		$location_range = $snowpilot_xmldoc->createAttribute('range');
		$location_range->value = $unit_prefs['range'];
		$snowpilot_Location->appendChild($location_range);
		//
		// Name
		$location_name = $snowpilot_xmldoc->createAttribute('name');
		$location_name->value = $node->title;
		$snowpilot_Location->appendChild($location_name);
	
		//	
		$counter = 1;
	  foreach($all_layers as $x => $layer){
			$snowpilot_Layer = $snowpilot_xmldoc->createElement("Layer"); 
		
			// grain size units
		  $gsu1 = $snowpilot_xmldoc->createAttribute('grainSizeUnits1');
		  $gsu2 = $snowpilot_xmldoc->createAttribute('grainSizeUnits2');
		
			$gsu2->value = $gsu1->value = 'mm';
			$snowpilot_Layer->appendChild($gsu1);
			$snowpilot_Layer->appendChild($gsu2);
			//
			//  grain size
			$gs1 = $snowpilot_xmldoc->createAttribute('grainSize');
			$gs2 = $snowpilot_xmldoc->createAttribute('grainSize1');	
			$gs1->value = isset($layer->field_grain_size['und'][0]['value']) ? $layer->field_grain_size['und'][0]['value'] : '';
			$gs2->value = isset($layer->field_grain_size_max['und'][0]['value']) ? $layer->field_grain_size_max['und'][0]['value'] : '';
			$snowpilot_Layer->appendChild($gs1);
			$snowpilot_Layer->appendChild($gs2);	
			//
			// grain type
			$gt1 = $snowpilot_xmldoc->createAttribute('grainType');
			$gt2 =  $snowpilot_xmldoc->createAttribute('grainType1'); 
			$gt1->value = isset($layer->field_grain_type['und'][0]['tid']) ? htmlentities(taxonomy_term_load($layer->field_grain_type['und'][0]['tid'])->description ): '';
			$gt2->value = isset($layer->field_grain_type_secondary['und'][0]['tid']) ? htmlentities(taxonomy_term_load($layer->field_grain_type_secondary['und'][0]['tid'])->description) : '';
			$snowpilot_Layer->appendChild($gt1);
			$snowpilot_Layer->appendChild($gt2);
			//
			// hardness
			$hness1 = $snowpilot_xmldoc->createAttribute('hardness1');
			$hness2 = $snowpilot_xmldoc->createAttribute('hardness2');
			$hness1->value = isset($layer->field_hardness['und'][0]['value']) ? $layer->field_hardness['und'][0]['value'] : '';
			$hness2->value = isset($layer->field_hardness2['und'][0]['value']) ? $layer->field_hardness2['und'][0]['value'] : '';
		
			$snowpilot_Layer->appendChild($hness1);
			$snowpilot_Layer->appendChild($hness2);
			//
			// LayerNumber
			$layer_num = $snowpilot_xmldoc->createAttribute('layerNumber');
			$layer_num->value = $counter;
		
			$snowpilot_Layer->appendChild($layer_num);
			//
			// water content
			$waterContent = $snowpilot_xmldoc->createAttribute('waterContent');
			$waterContent->value = isset($layer->field_water_content['und'][0]['value']) ? $layer->field_water_content['und'][0]['value'] : '';
			$snowpilot_Layer->appendChild($waterContent);
		
			//
			//startDepth
			$startDepth = $snowpilot_xmldoc->createAttribute('startDepth');
			$startDepth->value = isset($layer->field_height['und'][0]['value']) ? $layer->field_height['und'][0]['value'] : '';
			$snowpilot_Layer->appendChild($startDepth);
			//
			//endDepth
			$endDepth = $snowpilot_xmldoc->createAttribute('endDepth');
			$endDepth->value = isset($layer->field_bottom_depth['und'][0]['value']) ? $layer->field_bottom_depth['und'][0]['value'] : '';
			$snowpilot_Layer->appendChild($endDepth);
			//
			// multiples ...
			// We make a little array and loop through it so this is easier.
			$multiples = array ('multipleHardness' => 'field_use_multiple_hardnesses' ,
			  'multipleGrainType' => 'field_use_multiple_grain_type',
			  'multipleGrainSize' => 'field_use_multiple_grain_size');
			foreach ( $multiples as $key => $multiple ){
				$multi_val[$multiple] = $snowpilot_xmldoc->createAttribute($key);
				$layer_multiple = $layer->$multiple;
				//dsm($layer_multiple['und']);
				$multi_val[$multiple]->value =  ( isset( $layer_multiple['und'][0]['value'] ) && $layer_multiple['und'][0]['value'] == 1 ) ? 'true' : 'false';
				$snowpilot_Layer->appendChild($multi_val[$multiple]);
			}
		
		
			$snowpilot_PitCore->appendChild($snowpilot_Layer);
			//
			$counter++;
		}
		$ids = array();
		if ( isset( $node->field_test['und'] ) ){
		  foreach ($node->field_test['und'] as $test ){ $ids[] = $test['value']; }
		  $shear_tests = field_collection_item_load_multiple($ids);
	  }
		if ( count($shear_tests) ){
			foreach ( $shear_tests as $shear_test ){
				$snowpilot_ShearTest = $snowpilot_xmldoc->createElement("Shear_Test_Result");
				//
				// code
				$test_code = $snowpilot_xmldoc->createAttribute('code');
				$test_code->value = $shear_test->field_stability_test_type['und'][0]['value'];
				$snowpilot_ShearTest->appendChild($test_code);
				//
				// sdepth
				$sdepth = $snowpilot_xmldoc->createAttribute('sdepth');
				$sdepth->value = isset($shear_test->field_depth['und'][0]['value']) ? $shear_test->field_depth['und'][0]['value'] : '';
				$snowpilot_ShearTest->appendChild($sdepth);
				//
				//
				//depthUnits
				$depthUnits = $snowpilot_xmldoc->createAttribute('depthUnits');
				$depthUnits->value = $unit_prefs['depthUnits'];
				$snowpilot_ShearTest->appendChild($depthUnits);
				//
				// score
				$test_score = $snowpilot_xmldoc->createAttribute('score');
				$test_score->value = isset($shear_test->field_stability_test_score['und'][0]['value']) ? $shear_test->field_stability_test_score['und'][0]['value'] : '';
				$snowpilot_ShearTest->appendChild($test_score);
		
				//
				// ecScore
				$ecScore = $snowpilot_xmldoc->createAttribute('ecScore');
				$ecScore->value = isset($shear_test->field_ec_score['und'][0]['value']) ? $shear_test->field_ec_score['und'][0]['value'] : '';
				$snowpilot_ShearTest->appendChild($ecScore);
				
				//
				// ctScore
				$ctScore = $snowpilot_xmldoc->createAttribute('ctScore');
				$ctScore->value = isset($shear_test->field_ct_score['und'][0]['value']) ? $shear_test->field_ct_score['und'][0]['value'] : '';
				$snowpilot_ShearTest->appendChild($ctScore);
		
				//
				// quality
				$shear_quality = $snowpilot_xmldoc->createAttribute('quality');
				$shear_quality->value  = isset($shear_test->field_shear_quality['und'][0]['value']) ? $shear_test->field_shear_quality['und'][0]['value'] : '';
				$snowpilot_ShearTest->appendChild($shear_quality);
		
				//
				// dateSTring
				$dateString = $snowpilot_xmldoc->createAttribute('dateString');
				$dateString->value = 	isset($node->field_date_time['und'][0]['value']) ? date( 'm/d/Y' , strtotime($node->field_date_time['und'][0]['value'])) : '';
				$snowpilot_ShearTest->appendChild($dateString);
		
				//
				// numberOfTaps
				$numTaps = $snowpilot_xmldoc->createAttribute('numberOfTaps');
		
				if ( ($shear_test->field_stability_test_type['und'][0]['value'] == 'ECT')){
				  $numTaps->value = $ecScore->value ;
			  }elseif ($shear_test->field_stability_test_type['und'][0]['value'] == 'CT' ){
					$numTaps->value = isset($shear_test->field_ct_score['und'][0]['value'] ) ? $shear_test->field_ct_score['und'][0]['value']  : '';
				}else{ $numTaps->value = ''; }
				$snowpilot_ShearTest->appendChild($numTaps);
		
		
				// fractureCat
				$fractureCat = $snowpilot_xmldoc->createAttribute('fractureCat');
				$fractureCat->value = $unit_prefs['fractureCat'];
				$snowpilot_ShearTest->appendChild($fractureCat);
		
				//
				// fractureCharacter
				$test_character = $snowpilot_xmldoc->createAttribute('character');
				$test_character->value =  ($unit_prefs['fractureCat'] == 'fracture_character') && isset($shear_test->field_fracture_character['und'][0]['value']) ? $shear_test->field_fracture_character['und'][0]['value'] : '' ;
				$snowpilot_ShearTest->appendChild($test_character);
				//
				// comments
				$comments = $snowpilot_xmldoc->createAttribute('comments');
				$comments->value = isset($shear_test->field_stability_comments['und'][0]['value']) ? $shear_test->field_stability_comments['und'][0]['value'] : '';
		
				// s="ECTP Q1 5 11/30/2014.15:39:6"
				//
				//s
				$date_part = date( 'm/d/Y.H:i:s:0' , strtotime($node->field_date_time['und'][0]['value']));
				$test_s = $snowpilot_xmldoc->createAttribute('s');
				$test_s->value =  $test_score->value.' '.$shear_quality->value.$test_character->value.' '. $sdepth->value. ' ' . $date_part;
				$snowpilot_ShearTest->appendChild($test_s);
				//
				// lengthOfCut
				$lengthOfCut = $snowpilot_xmldoc->createAttribute('lengthOfCut');
				$lengthOfCut->value = isset($shear_test->field_length_of_saw_cut['und'][0]['value']) ? $shear_test->field_length_of_saw_cut['und'][0]['value'] : '' ;
				$snowpilot_ShearTest->appendChild($lengthOfCut);
				//
				// lengthOfColumn
				$lengthOfColumn = $snowpilot_xmldoc->createAttribute('lengthOfColumn');
				$lengthOfColumn->value = isset($shear_test->field_length_of_isolated_col_pst['und'][0]['value']) ? $shear_test->field_length_of_isolated_col_pst['und'][0]['value'] : '' ;
				$snowpilot_ShearTest->appendChild($lengthOfColumn);		
		
				//
				// releaseType
				$releaseType = $snowpilot_xmldoc->createAttribute('releaseType');
				$releaseType->value =  ($shear_test->field_stability_test_type['und'][0]['value'] == 'RB') && isset($shear_test->field_release_type['und'][0]['value']) ? $shear_test->field_release_type['und'][0]['value'] : '' ;
				$snowpilot_ShearTest->appendChild($releaseType);		
		
				//
				// dataCode
				// this is not actually anywhere in the snowpilot data model specs, but needs to be.
				$dataCode = $snowpilot_xmldoc->createAttribute('dataCode');
				$dataCode->value = ($shear_test->field_stability_test_type['und'][0]['value'] == 'PST') && isset($shear_test->field_data_code_pst['und'][0]['value']) ? $shear_test->field_data_code_pst['und'][0]['value'] : '' ;
				$snowpilot_ShearTest->appendChild($dataCode);				
		
				$snowpilot_PitCore->appendChild($snowpilot_ShearTest);
		
			}
		}
		$ids = array();
		$profile = array();
	
		//dsm($node->field_density_profile);
		if ( isset($node->field_density_profile['und']) && (count( $node->field_density_profile['und']) > 0)){
	  	foreach ($node->field_density_profile['und'] as $density ){ $ids[] = $density['value']; }
	  	$densitys = field_collection_item_load_multiple($ids);

		  foreach ( $densitys as $density ){
		    $profile[] = $density->field_depth['und'][0]['value'] . '_' . $density->field_density_top['und'][0]['value'];
	    }
		}

		$snowpilot_Density = $snowpilot_xmldoc->createElement('Density_Profile');
		//
		// profile
		$density_prof = $snowpilot_xmldoc->createAttribute('profile');
		$density_prof->value = implode( '?', $profile );
		$snowpilot_Density->appendChild($density_prof);
	
		//
		// depthUnits
		$depthUnits = $snowpilot_xmldoc->createAttribute('depthUnits');
		$depthUnits->value =  $unit_prefs['depthUnits'];
		$snowpilot_Density->appendChild($depthUnits);
	
		//
		//densityUnits
	
		$densityUnits = $snowpilot_xmldoc->createAttribute('densityUnits');
		$densityUnits->value =  $unit_prefs['rhoUnits'];
		$snowpilot_Density->appendChild($densityUnits);
		
		$snowpilot_PitCore->appendChild($snowpilot_Density);
		
	
		$outXML = $snowpilot_xmldoc->saveXML();
		$formatted_xml = new DOMDocument('1.0', 'UTF-8');
		$formatted_xml->preserveWhiteSpace = false;
		$formatted_xml->formatOutput = true;
		$formatted_xml->loadXML($outXML);
		$final_xml = $formatted_xml->saveXML();
		//dsm($final_xml);
		//
		//  Only save the file to the public filesystem if it is a public pit
		//
	  if ( $node->field_snowpit_visibility['und'][0]['value'] == 'public' ){	
		  $xml_filehandle = fopen(DRUPAL_ROOT.$xml_filename, 'w');
		  $value = fwrite($xml_filehandle, $final_xml );
		  fclose($xml_filehandle);
		  watchdog('snowpilot', "Snowpit $node->nid fwrite results: ". $value);
	  }
		return $final_xml;
	}else{		
		$xml_filehandle = fopen(DRUPAL_ROOT.$xml_filename, 'r');
		$xmlvalues = fread($xml_filehandle,64292);
		fclose($xml_filehandle);
		return $xmlvalues;
	}
}


function snowpilot_node_write_caaml($node){
	$unit_prefs = snowpilot_unit_prefs_get($node, 'node');
	$account = user_load($node->uid);
	$snowpilot_caaml = new DOMDocument('1.0', 'UTF-8');
	$snowpilot_SnowProfile = $snowpilot_caaml->createElement( 'SnowProfile'); $snowpilot_caaml->appendChild($snowpilot_SnowProfile);
	$snowpilot_SnowProfile->setAttributeNS(
	  'http://www.w3.org/2001/XMLSchema-instance',
	  'xsi:schemaLocation',
	  'http://caaml.org/Schemas/V5.0/Profiles/SnowProfileIACS http://caaml.org/Schemas/V5.0/Profiles/SnowprofileIACS/CAAMLv5_SnowProfileIACS.xsd');
	
	
	$snowpilot_SnowProfile->setAttributeNS('http://www.snowpilot.org', 'app:NodeID' , $node->nid);
	$snowpilot_SnowProfile->setAttributeNS('http://www.opengis.net/gml', 'gml:id' ,'test');
	$snowpilot_metaDataProperty = $snowpilot_caaml->createElement('metaDataProperty'); $snowpilot_SnowProfile->appendChild($snowpilot_metaDataProperty);
	$snowpilot_validTime = $snowpilot_caaml->createElement('validTime'); $snowpilot_SnowProfile->appendChild($snowpilot_validTime);
	$snowpilot_snowProfileResultsOf = $snowpilot_caaml->createElement('snowProfileResultsOf'); $snowpilot_SnowProfile->appendChild($snowpilot_snowProfileResultsOf);
	$snowpilot_locRef = $snowpilot_caaml->createElement('locRef'); $snowpilot_SnowProfile->appendChild($snowpilot_locRef );
	//
	//  MetaDataProperty Object
	//
	$MetaData = $snowpilot_caaml->createElement('MetaData');
	
	$MetaData->appendChild($snowpilot_caaml->createElement('dateTimeReport', date ( 'c', $node->created)));
	
	$srcRef = $snowpilot_caaml->createElement('srcRef');
	  $Operation = $snowpilot_caaml->createElement('Operation');
	    $Operation->appendChild($snowpilot_caaml->createElement('name', taxonomy_term_load($account->field_professional_affiliation['und'][0]['tid'])->name) );
	    $contactPerson = $snowpilot_caaml->createElement('contactPerson');
			  $Person = $snowpilot_caaml->createElement('Person');
				$Person->appendChild( $snowpilot_caaml->createElement('name' , $account->name ));
			$contactPerson->appendChild($Person);
		$Operation->appendChild($contactPerson);
	$srcRef->appendChild($Operation);
	$MetaData->appendChild($srcRef);
	
	$customData = $snowpilot_caaml->createElement('customData');
	  $customData->appendChild($snowpilot_caaml->createElement('app:projectID', 'Snowpilot'));
	$MetaData->appendChild($customData);
	
	$snowpilot_metaDataProperty->appendChild($MetaData);
	// End of $snowpilot_metaDataProperty
	
	// Observation Time
	//
	$TimeInstant = $snowpilot_caaml->createElement('TimeInstant');
	  $TimeInstant->appendChild($snowpilot_caaml->createElement('timePosition' , date ( 'c', strtotime($node->field_date_time['und'][0]['value'])) ) );
	$snowpilot_validTime->appendChild($TimeInstant);
	
	
	
	//
	//
	// locRef - location reference
	$ObsPoint = $snowpilot_caaml->createElement('ObsPoint');
	  $ObsPoint->appendChild($snowpilot_caaml->createElement ('name' , $node->title ));
	  $ObsPoint->appendChild($snowpilot_caaml->createElement('obsPointSubType', 'Snowprofile Site'));
	  $validElevation = $snowpilot_caaml->createElement('validElevation');
		  $ElevationPosition = $snowpilot_caaml->createElement('ElevationPosition');
			$ElevationPosition->setAttribute('uom', $unit_prefs['field_elevation_units'] );
			$ElevationPosition->appendChild( $snowpilot_caaml->createElement('position' , $node->field_elevation['und'][0]['value']) );
	  $validElevation->appendChild($ElevationPosition);
	 $ObsPoint->appendChild($validElevation);
	 $validAspect = $snowpilot_caaml->createElement('validAspect');
	  $AspectPosition = $snowpilot_caaml->createElement('AspectPosition');
		$AspectPosition->appendChild($snowpilot_caaml->createElement('position', snowpilot_cardinal_wind_dir($node->field_aspect['und'][0]['value'])) ) ;	  
	  $validAspect->appendChild($AspectPosition);
	 $ObsPoint->appendChild($validAspect);
	
	$validSlopeAngle = $snowpilot_caaml->createElement('validSlopeAngle');
	  $slopeAnglePosition = $snowpilot_caaml->createElement('slopeAnglePosition');
		$slopeAnglePosition->setAttribute('uom' , 'deg');
		$slopeAnglePosition->appendChild($snowpilot_caaml->createElement('position' , $node->field_slope_angle['und'][0]['value'] ));
	 $validSlopeAngle->appendChild($slopeAnglePosition);
	$ObsPoint->appendChild($validSlopeAngle);
	 
	$pointLocation = $snowpilot_caaml->createElement('pointLocation');
	  $gmlPoint = $snowpilot_caaml->createElement('gml:Point');
		$gmlPoint->setAttribute('srsDimension' , '2');
		$gmlPoint->setAttribute('srsname' , 'urn:ogc:def:crs:OGC:1.3:CRS84');
		$gmlPoint->appendChild($snowpilot_caaml->createElement('gml:pos', $node->field_latitude['und'][0]['value'] .' ' . $node->field_longitude['und'][0]['value']) );
	$pointLocation->appendChild($gmlPoint);
	
	$ObsPoint->appendChild($pointLocation);
	$snowpilot_locRef->appendChild($ObsPoint);
	//
	// End Location Reference
	// Beginning snow Profile resultsof
	$SnowProfileMeasurements = $snowpilot_caaml->createElement('SnowProfileMeasurements');
	  $SnowProfileMeasurements->setAttribute('dir' , ($unit_prefs['measureFrom'] == 'top') ? 'top down' : 'bottom up' );
		$SnowProfileMeasurements->appendChild($snowpilot_caaml->createElement('comment', $node->body['und'][0]['safe_value']));
		$profileDepth = $snowpilot_caaml->createElement('profileDepth', _snowpilot_find_pit_depth($node) );
		  $profileDepth->setAttribute( 'uom' , $unit_prefs['field_depth_units'] );
	    $SnowProfileMeasurements->appendChild( $profileDepth) ;
		
		$SnowProfileMeasurements->appendChild($snowpilot_caaml->createElement('skyCond', $node->field_sky_cover['und'][0]['value']) ) ;
		$SnowProfileMeasurements->appendChild($snowpilot_caaml->createElement('precipTI' , isset($node->field_precipitation['und'][0]['value']) ? $node->field_precipitation['und'][0]['value'] : '')); // this is not CAAML formatted
		$windSpd =$snowpilot_caaml->createElement('windSpd', $node->field_wind_speed['und'][0]['value']); // the values of this too, need to be set in the drupal db
		$windSpd->setAttribute('uom' , '');
		$SnowProfileMeasurements->appendChild($windSpd) ;
		//wind Direction
		$winddir = $snowpilot_caaml->createElement('windDir');
		$windDirAspectPosition = $snowpilot_caaml->createElement('AspectPosition');
		$windDirAspectPosition->appendChild($snowpilot_caaml->createElement('position', snowpilot_cardinal_wind_dir($node->field_wind_direction['und'][0]['value'])));
		$winddir->appendChild($windDirAspectPosition);	
		$SnowProfileMeasurements->appendChild($winddir);
		// HoS
		$heightOfSnowpack = $snowpilot_caaml->createElement('hS');
		  $hsComponents = $snowpilot_caaml->createElement('Components');
		    $snowHeight = $snowpilot_caaml->createElement('snowHeight', isset ( $node->field_total_height_of_snowpack['und'][0]['value']) ? $node->field_total_height_of_snowpack['und'][0]['value'] : '' );
	    	$snowHeight->setAttribute('uom' , $unit_prefs['field_depth_units']);
				
		  $hsComponents->appendChild($snowHeight);
	  $heightOfSnowpack->appendChild($hsComponents);		
		$SnowProfileMeasurements->appendChild($heightOfSnowpack);
		// Ski Penetration
		if ( isset($node->field_surface_penetration['und'][0]['value']) && $node->field_surface_penetration['und'][0]['value'] == 'ski'){
			$penetration = $snowpilot_caaml->createElement('penetrationSki', $node->field_ski_penetration['und'][0]['value']);
	
		}elseif ( isset($node->field_surface_penetration['und'][0]['value']) && $node->field_surface_penetration['und'][0]['value'] == 'boot'){
			$penetration = $snowpilot_caaml->createElement('penetrationFoot', isset($node->field_boot_penetration_depth['und'][0]['value']) ? $node->field_boot_penetration_depth['und'][0]['value'] : '' );		
			
		}
		if ( isset($penetration) ){
			$penetration->setAttribute('uom', $unit_prefs['field_depth_units']);
			$SnowProfileMeasurements->appendChild($penetration);
		}
		$ids = array();
		foreach ($node->field_layer['und'] as $lay ){ $ids[] = $lay['value']; }
		$all_layers = field_collection_item_load_multiple($ids);
		$stratProfile = $snowpilot_caaml->createElement('stratProfile');
		
		foreach ( $all_layers as $x=>$layer){
			$Layer = $snowpilot_caaml->createElement('Layer');
			
			$Layer->appendChild($depthTop = $snowpilot_caaml->createElement('depthTop', $layer->field_height['und'][0]['value']));
			$depthTop->setAttribute('uom', $unit_prefs['field_depth_units']);
			
			$Layer->appendChild($thickness = $snowpilot_caaml->createElement('thickness', abs($layer->field_height['und'][0]['value'] - $layer->field_bottom_depth['und'][0]['value'] ) ) );
			$thickness->setAttribute('uom', $unit_prefs['field_depth_units']);
			
			if ( isset($layer->field_grain_type['und'])){
			  $Layer->appendChild($snowpilot_caaml->createElement('grainFormPrimary' , taxonomy_term_load($layer->field_grain_type['und'][0]['tid'])->description) );  // this needs to be changed to description as soon as the particle abbreviations are populated into the grain types taxonomy 'description' field. IT also breaks whenever there is a & in a name
		  }
			if ( isset($layer->field_grain_type_secondary['und'])){
			  $Layer->appendChild($snowpilot_caaml->createElement('grainFormSecondary' , taxonomy_term_load($layer->field_grain_type_secondary['und'][0]['tid'])->description) );  // this needs to be changed to description as soon as the particle abbreviations are populated into the grain types taxonomy 'description' field. IT also breaks whenever there is a & in a name
		  }
			//
			// Grain size
			if ( isset($layer->field_grain_size['und'])){
				$grainSize = $snowpilot_caaml->createElement('grainSize');
				$grainSize->setAttribute('uom', 'mm');
				  $GSComponents = $snowpilot_caaml->createElement('Components');
					$GSComponents->appendChild( $snowpilot_caaml->createElement('avg', $layer->field_grain_size['und'][0]['value']));
					if ( isset($layer->field_grain_size_max['und']) ){
						$GSComponents->appendChild( $snowpilot_caaml->createElement('avgMax', $layer->field_grain_size_max['und'][0]['value']));
					}		
				  $grainSize->appendChild($GSComponents);	
				$Layer->appendChild($grainSize);		
			}
			//
			// Hardness
			if ( isset( $layer->field_hardness['und'])){
			  $Layer->appendChild( $hardness = $snowpilot_caaml->createElement( 'hardness' , $layer->field_hardness['und'][0]['value']));
				$hardness->setAttribute('uom', '');
			}
			if ( isset ( $layer->field_hardness2['und'])) {
			  $Layer->appendChild( $hardness2 = $snowpilot_caaml->createElement( 'hardness' , $layer->field_hardness2['und'][0]['value']));
				$hardness2->setAttribute('uom', '');			
			}
			//
			// Water content
			if ( isset(  $layer->field_water_content['und'] )){
			  $Layer->appendChild($lwc = $snowpilot_caaml->createElement('lwc', $layer->field_water_content['und'][0]['value']));
				$lwc->setAttribute('uom', '');
			}
			
			
			
			$stratProfile->appendChild($Layer);
		}
		$SnowProfileMeasurements->appendChild($stratProfile);
		//
		// Termperature profile
		
		if ( isset ( $node->field_temp_collection['und'] )){
			$ids = array();
			foreach ($node->field_temp_collection['und'] as $temp ){ $ids[] = $temp['value']; }
			$all_temps = field_collection_item_load_multiple($ids);
			$tempProfile = $snowpilot_caaml->createElement('tempProfile');
			$tempProfile->setAttribute('uomDepth' , $unit_prefs['field_depth_units'] );
			$tempProfile->setAttribute('uomTemp', 'deg'.$unit_prefs['field_temp_units']);
			foreach ($all_temps as $temp ){
				if ( isset ( $temp->field_depth['und'])){
					$Obs = $snowpilot_caaml->createElement('Obs');
					$Obs->appendChild($snowpilot_caaml->createElement('depth', $temp->field_depth['und'][0]['value'] ));
					$Obs->appendChild($snowpilot_caaml->createElement('snowTemp', $temp->field_temp_temp['und'][0]['value'] ));
				}
				$tempProfile->appendChild($Obs);
			}
			$SnowProfileMeasurements->appendChild($tempProfile);
		}
		//
		//  Denstiy Profile
		//
		if ( isset ( $node->field_density_profile['und'] )){
			$ids = array();
			foreach ($node->field_density_profile['und'] as $dens ){ $ids[] = $dens['value']; }
			$all_densities = field_collection_item_load_multiple($ids);
		
			$densityProfile = $snowpilot_caaml->createElement('densityProfile');
			$densityProfile->setAttribute('uomDepthTop' , $unit_prefs['field_depth_units']);
			$densityProfile->setAttribute('uomThickness' , $unit_prefs['field_depth_units']);
			$densityProfile->setAttribute('uomDensity' , $unit_prefs['field_density_units']);
		
			foreach( $all_densities as $density){
				$Layer = $snowpilot_caaml->createElement('Layer');
				$Layer->appendChild($snowpilot_caaml->createElement('depthTop', $density->field_depth['und'][0]['value']));
				$Layer->appendChild($snowpilot_caaml->createElement('thickness', ''));
				$Layer->appendChild($snowpilot_caaml->createElement('density', $density->field_density_top['und'][0]['value']));
			
				$densityProfile->appendChild($Layer);
			}
			$SnowProfileMeasurements->appendChild($densityProfile);
		}
		//
		//  stability Tests
		//
		$ids = array();
		foreach ($node->field_test['und'] as $test ){ $ids[] = $test['value']; }
		$shear_tests = field_collection_item_load_multiple($ids);
    $stbTests = $snowpilot_caaml->createElement('stbTests');

		foreach ( $shear_tests as $shear_test ){
			switch( $shear_test->field_stability_test_type['und'][0]['value']){
				case 'CT':
				$ComprTest = $snowpilot_caaml->createElement('ComprTest');
				if ( $shear_test->field_stability_test_score['und'][0]['value'] <> 'CTN'){
					$ComprTest->appendChild( $failedOn = $snowpilot_caaml->createElement('failedOn') );
					$Layer = $snowpilot_caaml->createElement('Layer');
					$Layer->appendChild( $depthTop = $snowpilot_caaml->createElement('depthTop', $shear_test->field_depth['und'][0]['value']));
					  $depthTop->setAttribute('uom', $unit_prefs['field_depth_units']);
					$failedOn->appendChild($Layer);
					$Results = $snowpilot_caaml->createElement('Results');
					if ( isset( $shear_test->field_fracture_character['und'] )){
						$Results->appendChild($snowpilot_caaml->createElement('fractureCharacter', $shear_test->field_fracture_character['und'][0]['value']  ));
					}
					if ( isset( $shear_test->field_shear_quality['und'] )){
						$Results->appendChild($snowpilot_caaml->createElement('fractureQuality', $shear_test->field_shear_quality['und'][0]['value']  ));
					}
					if ( isset( $shear_test->field_stability_test_score_ct['und'] )){
						$Results->appendChild($snowpilot_caaml->createElement('testScore', $shear_test->field_ct_score['und'][0]['value']  ));
					}else{
						$Results->appendChild($snowpilot_caaml->createElement('testScore', $shear_test->field_stability_test_score_ct['und'][0]['value']  ));
					}
					
					$failedOn->appendChild($Results);
					
					
					
				}else{
					$ComprTest->appendChild($snowpilot_caaml->createElement('noFailure'));
					
				}
				$stbTests->appendChild($ComprTest);
				break;
				case 'ECT':
				  $ExtColumnTest = $snowpilot_caaml->createElement('ExtColumnTest');
					  if ( $shear_test->field_stability_test_score['und'][0]['value'] <> 'ECTX'){
				  	  $failedOn = $snowpilot_caaml->createElement('failedOn') ;
			  			  $Layer = $snowpilot_caaml->createElement('Layer');
						    $Layer->appendChild( $depthTop = $snowpilot_caaml->createElement('depthTop', $shear_test->field_depth['und'][0]['value'] ));
						  	  $depthTop->setAttribute('uom' , $unit_prefs['field_depth_units']);
					  	$failedOn->appendChild($Layer);
				  			$Results = $snowpilot_caaml->createElement('Results');
			  				if ( isset( $shear_test->field_fracture_character['und'] )){
							  	$Results->appendChild($snowpilot_caaml->createElement('fractureCharacter', $shear_test->field_fracture_character['und'][0]['value']  ));
						  	}
					   		if ( isset( $shear_test->field_shear_quality['und'] )){
								  $Results->appendChild($snowpilot_caaml->createElement('fractureQuality', $shear_test->field_shear_quality['und'][0]['value']  ));
							  }
								if ( isset( $shear_test->field_ec_score['und'] )){
									$Results->appendChild($snowpilot_caaml->createElement('testScore', $shear_test->field_ec_score['und'][0]['value']  ));
								}else{
									$Results->appendChild($snowpilot_caaml->createElement('testScore', $shear_test->field_stability_test_score_ect['und'][0]['value']  ));
								}
						  $failedOn->appendChild($Results);
						
					  $ExtColumnTest->appendChild($failedOn);
							
						//
						//
					}else{
						$ExtColumnTest->appendChild($snowpilot_caaml->createElement('noFailure'));
						
					}
					$stbTests->appendChild($ExtColumnTest);
				break;
			}
			
			
			
		}
		$SnowProfileMeasurements->appendChild($stbTests);
	
	$snowpilot_snowProfileResultsOf->appendChild($SnowProfileMeasurements);
	
	
	
	$outXML = $snowpilot_caaml->saveXML();
	$formatted_xml = new DOMDocument('1.0', 'UTF-8');
	$formatted_xml->preserveWhiteSpace = false;
	$formatted_xml->formatOutput = true;
	$formatted_xml->loadXML($outXML);
	$final_xml = $formatted_xml->saveXML();
	//dsm($final_xml);
	return $final_xml;
	
}



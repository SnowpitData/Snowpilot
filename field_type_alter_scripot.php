<?php
// from http://drupal.stackexchange.com/questions/79378/changing-a-field-type-from-integer-to-decimal
// Change this to your field name, obvs.
$field = 'field_length_of_isolated_col_pst';

// Update the storage tables
$tables = array('field_data', 'field_revision');
foreach ($tables as $table) {
  $tablename = $table .'_'. $field;
  $fieldname = $field .'_value';
  db_change_field($tablename, $fieldname, $fieldname, array(
    'type' => 'numeric',
    'precision' => 10,
    'scale' => 1,
    'not null' => FALSE,
  ));
}

// Fetch the current field configuration
$field_config = db_query("SELECT data FROM {field_config} WHERE field_name = :field_name", array(
    ':field_name' => $field,
  ))
  ->fetchObject();
$data = unserialize($field_config->data);

// Update the settings entry
$data['settings'] = array(
  'precision' => 10,
  'scale' => 1,
  'decimal_separator' => '.',
);

// Store the new field config, update the field type at the same time
db_update('field_config')
  ->fields(array(
    'data' => serialize($data),
    'type' => 'number_decimal',
		'module' => 'number',
  ))
  ->condition('field_name', $field)
  ->execute();    

// If you are confident about what bundles have instances of this field you can
// go straight to the db_query with a hardcoded entity_type / bundle.
$instances = field_info_field_map();
foreach ($instances[$field]['bundles'] as $entity_type => $bundles) {
  foreach ($bundles as $bundle) {

    // Fetch the field instance data
    $field_config_instance = db_query("SELECT data FROM {field_config_instance}
                                       WHERE field_name = :field_name
                                       AND entity_type = :entity_type
                                       AND bundle = :bundle", array(
        ':field_name' => $field,
        ':entity_type' => $entity_type,
        ':bundle' => $bundle,
      ))
      ->fetchObject();
    $data = unserialize($field_config_instance->data);

    // Update it with the new display type
    $data['display']['default']['type'] = 'number_decimal';

    // Store it back to the database
    db_update('field_config_instance')
      ->fields(array('data' => serialize($data)))
      ->condition('field_name', $field)
      ->condition('entity_type', $entity_type)
      ->condition('bundle', $bundle)
      ->execute();

  }
}


/*


sql = 
UPDATE `field_revision_field_temp_temp` SET `field_temp_temp_value` = replace( field_temp_temp_value, ',', '.' ) 

UPDATE `field_data_field_temp_temp` SET `field_temp_temp_value` = replace( field_temp_temp_value, ',', '.' ) 

run script above

also in field_config table , change from type = text to type = number

update field_config -> data field iff needed

use this to update field_config_isntance:

UPDATE `field_config_instance` SET data = 'a:8:{s:5:"label";s:11:"Temperature";s:6:"widget";a:5:{s:6:"weight";s:2:"30";s:4:"type";s:6:"number";s:6:"module";s:6:"number";s:6:"active";i:0;s:8:"settings";a:0:{}}s:8:"settings";a:6:{s:3:"min";s:3:"-40";s:3:"max";s:2:"32";s:6:"prefix";s:0:"";s:6:"suffix";s:0:"";s:10:"exclude_cv";b:0;s:18:"user_register_form";b:0;}s:7:"display";a:1:{s:7:"default";a:5:{s:5:"label";s:5:"above";s:4:"type";s:12:"number_float";s:6:"weight";s:2:"17";s:8:"settings";a:0:{}s:6:"module";N;}}s:8:"required";i:0;s:11:"description";s:0:"";s:13:"default_value";N;s:10:"exclude_cv";i:1;}' WHERE field_name = 'field_temp_temp'


UPDATE `field_config_instance` SET data = `a:6:{s:5:"label";s:25:"Length of Isolated Column";s:6:"widget";a:4:{s:4:"type";s:14:"number_decimal";s:6:"weight";s:2:"14";s:8:"settings";a:1:{s:4:"size";i:60;}s:6:"module";s:6:"number";}s:8:"settings";a:3:{s:15:"text_processing";i:0;s:18:"user_register_form";b:0;s:10:"exclude_cv";b:0;}s:7:"display";a:1:{s:7:"default";a:5:{s:5:"label";s:5:"above";s:4:"type";s:14:"number_decimal";s:8:"settings";a:0:{}s:6:"module";s:4:"text";s:6:"weight";i:9;}}s:8:"required";b:0;s:11:"description";s:0:"";}'  WHERE field_name = 'field_length_of_isolated_col_pst'
?
*/

?>
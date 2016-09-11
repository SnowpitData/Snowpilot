<?php
// from http://drupal.stackexchange.com/questions/79378/changing-a-field-type-from-integer-to-decimal
// Change this to your field name, obvs.
$field = 'field_height';

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

?>
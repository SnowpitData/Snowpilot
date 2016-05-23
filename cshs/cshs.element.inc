<?php

/**
 * Implements hook_element_info_alter().
 */
//function cshs_element_info_alter(&$elements){
//  dpm($elements);
//}

/**
 * Implements hook_element_info().
 */
function cshs_element_info() {
  $type['cshs'] = array(
    '#input' => TRUE,
    '#multiple' => FALSE,
    '#process' => array(
      // Add process functions used for a regular select.
      'form_process_select',
      'ajax_process_form',
      // And add our custom process function.
      'cshs_element_process',
    ),
    '#element_validate' => array(
      'cshs_element_validate',
    ),
    '#theme' => 'cshs_select',
    '#theme_wrappers' => array('form_element'),
    // Custom attributes.
    '#none_value' => CSHS_DEFAULT_NONE_VALUE,
    '#none_label' => t(CSHS_DEFAULT_NONE_LABEL),
    '#parent' => 0,
    '#force_deepest' => FALSE,
    '#labels' => array(),
    '#label' => '',
  );
  return $type;
}

function cshs_element_process($element, $form_state) {
  $path = drupal_get_path('module', 'cshs');

  // Add CSS and JS.
  $element += array(
    '#attached' => array(
      'css' => array(
        "$path/css/cshs.css",
      ),
      'js' => array(
        "$path/js/libs/jquery.simpler-select.js",
        "$path/js/cshs.js",
      ),
    ),
  );

  // Pass settings to JS.
  $id = $element['#id'];
  $settings_js = array(
    'cshs' => array(
      $id => array(
        'noneLabel' => $element['#none_label'],
        'noneValue' => $element['#none_value'],
        'labels' => $element['#labels'],
      ),
    ),
  );

  $element['#attached']['js'][] = array(
    'data' => $settings_js,
    'type' => 'setting',
  );
  return $element;
}

/**
 * Validation handler.
 */
function cshs_element_validate(&$element, &$form_state) {
  $value = $element['#value'];
  $title = $element['#label'];

  // Collect settings.
  $force_deepest = $element['#force_deepest'];
  $none_value = $element['#none_value'];

  // If _none, then empty the value.
  if ($value == $none_value) {
    form_set_value($element, NULL, $form_state);
  }

  // See if we are on the field settings form.
  if ($form_state['complete form']['#form_id'] == 'field_ui_field_edit_form') {
    return;
  }

  // Check if not empty.
  if ($element['#required'] && $value == $none_value) {
    form_error($element, t('!name field is required.', array('!name' => $title)));
  }

  // Do we want to force the user to select terms from the deepest level?
  if ($force_deepest && $value != $none_value) {
    // Try to load a term.
    $term = taxonomy_term_load($value);

    // See if it has children.
    $children = array();
    if ($term) {
      $vid = $term->vid;

      // Does the selected term has any children?
      $children = taxonomy_get_children($value, $vid);
    }

    // Set an error if it has children.
    if ($value == $none_value || count($children)) {
      form_error($element, t('You need to select a term from the deepest level in field %field_name.', array('%field_name' => $title)));
    }
  }
}
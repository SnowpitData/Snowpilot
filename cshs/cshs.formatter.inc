<?php

/**
 * @file
 * All implementations of field formatters.
 */

/**
 * Implements hook_field_formatter_info().
 */
function cshs_field_formatter_info() {
  return array(
    'cshs_term_reference_hierarchical_text' => array(
      'label' => t('Full hierarchy'),
      'field types' => array('taxonomy_term_reference'),
      'settings' => array(
        'linked' => FALSE,
        'separator' => ' » ',
      ),
    ),
    'cshs_term_reference_group_by_root' => array(
      'label' => t('Hierarchy grouped by root'),
      'field types' => array('taxonomy_term_reference'),
      'settings' => array(
        'linked' => FALSE,
        'separator' => ' » ',
      ),
    ),
    'cshs_term_reference_flexible_hierarchy' => array(
      'label' => t('Flexible hierarchy'),
      'description' => t('Allows to specify the output with tokens.'),
      'field types' => array('taxonomy_term_reference'),
      'settings' => array(
        'linked' => FALSE,
        'format' => '[term:name]',
      ),
    ),
  );
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function cshs_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $element = array();

  // Add settings for all CSHS formatters.
  if ($display['module'] == 'cshs') {
    $element['linked'] = array(
      '#title' => t('Link to term page'),
      '#type' => 'checkbox',
      '#default_value' => $settings['linked'],
    );
  }

  // Add special formatter settings.
  switch($display['type']) {
    case 'cshs_term_reference_hierarchical_text':
    case 'cshs_term_reference_group_by_root':
      $element['separator'] = array(
        '#title' => t('Separator'),
        '#description' => t('Specify a separator which will be placed in between the different hierarchy levels.'),
        '#type' => 'textfield',
        '#default_value' => $settings['separator'],
      );
      break;
    case 'cshs_term_reference_flexible_hierarchy':
      $element['format'] = array(
        '#title' => t('Format'),
        '#description' => t('Specify a format for each field item by using tokens.'),
        '#type' => 'textfield',
        '#default_value' => $settings['format'],
      );
      break;
  }

  return $element;
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function cshs_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $summary = '';

  if ($display['module'] == 'cshs') {
    $summary .= t('Linked to term page: !linked', array('!linked' => $settings['linked'] ? t('Yes') : t('No'))) . '<br />';
  }

  if ($display['type'] == 'cshs_term_reference_hierarchical_text' || $display['type'] == 'cshs_term_reference_group_by_root') {
    $summary .= t('Separator: !separator', array('!separator' => empty($settings['separator']) ? t('None') : $settings['separator'])) . '<br />';
  }

  if ($display['type'] == 'cshs_term_reference_flexible_hierarchy') {
    $summary .= t('Format: !format', array('!format' => $settings['format'])) . '<br />';
  }

  return $summary;
}

/**
 * Implements hook_field_formatter_view().
 */
function cshs_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $element = array();
  $settings = $display['settings'];
  if ($display['type'] == 'cshs_term_reference_hierarchical_text') {
    foreach ($items as $delta => $item) {
      $linked = isset($settings['linked']) && $settings['linked'];
      $separator = drupal_strlen(trim($settings['separator'])) == 0 ? ' ' : $settings['separator'];

      // Load the term.
      $term = taxonomy_term_load($item['tid']);

      // Get the parents.
      $parents = _cshs_get_parents($term);

      // Build the output string.
      $terms = array();
      foreach ($parents as $parent) {
        $terms[] = $linked ? l($parent->name, "taxonomy/term/{$parent->tid}") : $parent->name;
      }
      $terms = implode($separator, $terms);

      $element[$delta]['#markup'] = $terms;
    }
  }
  if ($display['type'] == 'cshs_term_reference_group_by_root') {
    $linked = isset($settings['linked']) && $settings['linked'];
    $separator = drupal_strlen(trim($settings['separator'])) == 0 ? ' ' : $settings['separator'];

    // Build the roots.
    $roots = array();
    foreach ($items as $delta => $item) {
      // Load the term.
      $term = taxonomy_term_load($item['tid']);

      // Get the parents.
      $parents = _cshs_get_parents($term);

      // Get the root.
      $root = array_shift($parents);
      if (!isset($roots[$root->tid])) {
        $roots[$root->tid] = array(
          'title' => $root->name,
          'terms' => array(),
        );
      }

      // Build the output string.
      if (count($parents)) {
        // If some childs are left...
        $terms = array();
        foreach ($parents as $parent) {
          $terms[] = $linked ? l($parent->name, "taxonomy/term/{$parent->tid}") : $parent->name;
        }
        $terms = implode($separator, $terms);

        // Add terms to the root.
        $roots[$root->tid]['terms'][] = $terms;
      }
    }

    // Add each root to the render array.
    foreach ($roots as $root_tid => $root) {
      $element[$root_tid] = array('#markup' => theme('cshs_term_group', $root));
    }
  }
  if ($display['type'] == 'cshs_term_reference_flexible_hierarchy') {
    foreach ($items as $delta => $item) {
      $linked = isset($settings['linked']) && $settings['linked'];

      $term = taxonomy_term_load($item['tid']);
      $formatted_text = token_replace($settings['format'], array('term' => $term));
      $formatted_text = $linked ? l($formatted_text, "taxonomy/term/{$term->tid}") : $formatted_text;

      $markup = $formatted_text;
      $element[$delta] = array('#markup' => $markup);
    }
  }
  return $element;
}

/**
 * Helper to return an array of (localized) parents of a given term.
 */
function _cshs_get_parents($term) {
  $parents = array();
  // Get all parents.
  $parents = taxonomy_get_parents_all($term->tid);
  // Flip the order.
  $parents = array_reverse($parents);
  if (module_exists('i18n_taxonomy')) {
    // Localize terms.
    $parents = i18n_taxonomy_localize_terms($parents);
  }
  return $parents;
}

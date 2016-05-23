<?php

/**
 * @file
 * Definition of cshs_handler_filter_term_node_tid.
 */

/**
 * Filter by term id (including selection by client-side hierarchical select).
 *
 * @ingroup views_filter_handlers
 */
class cshs_handler_filter_term_node_tid extends views_handler_filter_term_node_tid {

  /**
   * Define settings for this filter handler.
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['type'] = array('default' => 'cshs');
    $options['level_labels'] = array('default' => '');
    return $options;
  }

  function extra_options_form(&$form, &$form_state) {
    parent::extra_options_form($form, $form_state);

    $form['type']['#options'] += array(
      'cshs' => t('Client-side hierarchical select'),
    );
  }

  /**
   * Define the form which is displayed, when this filter is set to "exposed".
   */
  function expose_form(&$form, &$form_state) {
    parent::expose_form($form, $form_state);

    // Disable the "multiple" option in the exposed form settings.
    $form['expose']['multiple']['#access'] = FALSE;

    // Add a textfield for labels per level.
    $form['level_labels'] = array(
      '#type' => 'textfield',
      '#title' => t('Labels per hierarchy-level'),
      '#description' => t('Enter labels for each hierarchy-level separated by comma.'),
      '#default_value' => isset($this->options['level_labels']) ? $this->options['level_labels'] : '',
    );
  }

  /**
   * Define the actual exposed form.
   */
  function value_form(&$form, &$form_state) {
    global $language;

    parent::value_form($form, $form_state);

    $vocabulary = taxonomy_vocabulary_machine_name_load($this->options['vocabulary']);
    if (empty($vocabulary) && $this->options['limit']) {
      $form['markup'] = array(
        '#markup' => '<div class="form-item">' . t('An invalid vocabulary is selected. Please change it in the options.') . '</div>',
      );
      return;
    }

    if ($this->options['type'] == 'cshs' && $this->options['exposed']) {
      // Set the element type to CSHS.
      $form['value']['#type'] = 'cshs';

      // Get the options in a special form.
      $options = _cshs_get_options($this->options['vocabulary']);
      $form['value']['#options'] = $options;

      // Add a "none" option.
      //$form['value']['#options'][CSHS_DEFAULT_NONE_VALUE] = CSHS_DEFAULT_NONE_LABEL;
      _cshs_add_none_option($form['value']['#options']);
      $form['value']['#default_value'] = CSHS_DEFAULT_NONE_VALUE;

      // Set the level labels if defined.
      $labels = array();
      if (isset($this->options['level_labels']) && strlen($this->options['level_labels'])) {
        $labels = drupal_explode_tags($this->options['level_labels']);
      }
      $form['value']['#labels'] = $labels;
    }

    if (empty($form_state['exposed'])) {
      // Retain the helper option.
      $this->helper->options_form($form, $form_state);
    }
  }

  function admin_summary() {
    // Set up $this->value_options for the parent summary.
    $this->value_options = array();

    if ($this->value == 'All') {
      $this->value = NULL;
    }

    return parent::admin_summary();
  }

}

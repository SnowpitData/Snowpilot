<?php

/**
 * @file
 * Definition of snowpilot_handler_snowpits.
 */

/**
 * Description of what my handler does.
 */
class snowpilot_handler_snowpits extends views_handler_field {
  /**
   * Add some required fields needed on render().
   */
  function construct() {
    parent::construct();
    $this->additional_fields['field_latitude'] = array(
      'table' => 'field_data_field_latitude',
      'field' => 'field_latitude_value',
    );
		$this->additional_fields['field_longitude'] = array(
			'table' => 'field_data_field_longitude',
			'field' => 'field_longitude_value',
		);
  }

  /**
   * Loads additional fields.
   */
  function query() {
    $this->ensure_my_table();
    $this->add_additional_fields();
  }

  /**
   * Default options form.
   */
  function option_definition() {
    $options = parent::option_definition();

    $options['option_a'] = array('default' => '');
    $options['option_b'] = array('default' => '');

    return $options;
  }

  /**
   * Creates the form item for the options added.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $form['option_a'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => $this->options['option_a'],
      '#description' => t('Some description.'),
      '#weight' => -10,
    );

    $form['option_b'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => $this->options['option_b'],
      '#description' => t('Some description.'),
      '#weight' => -9,
    );
  }

  /**
   * Renders the field handler.
   */
  function render($values) {
    // This ensures that even if Views changes the field name within the query, the handler will still work.
    // Inspect the $this->aliases array to find the key of the field that you need.
    $value = $this->get_value($values, 'field_latitude');

		
		return '<div id = "marker'. $Pit->nid .'" style = "width:450px; height:380px;border: 2px solid #162f50; display: none; float: right;">
    <a href = "https://snowpilot.org/sites/default/files/snowpit-profiles/graph-' . $Pit->nid. '.jpg" class = "colorbox" data-colorbox-gallery="gallery-all" >
		<img src = "https://snowpilot.org/sites/default/files/snowpit-profiles/graph-' . $Pit->nid. '.jpg" width = 450px />
		</a>
		</div>';
  }
}
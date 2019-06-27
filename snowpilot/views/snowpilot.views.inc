<?php

/**
 * @file
 * Views definitions for snowpilot module.
 */

/**
 * Implements hook_views_data_alter().
 */
function snowpilot_views_data_alter(&$data) {
  $data['node']['snowpits'] = array(
    'title' => t('Snowpits Mapping'),
    'help' => t('Mapping a set of snowpits'),
    'field' => array(
      'handler' => 'snowpilot_handler_snowpits',
    ),
  );
}
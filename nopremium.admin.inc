<?php
/**
 * @file
 * Written by Henri MEDOT <henri.medot[AT]absyx[DOT]fr>
 * http://www.absyx.fr
 */

function nopremium_settings_form() {
  $form['message'] = array(
    '#type' => 'fieldset',
    '#title' => t('Premium message'),
  );
  $form['message']['nopremium_message'] = array(
    '#type' => 'textfield',
    '#title' => t('Default message'),
    '#description' => t('This message will apply to all content types with blank messages below.'),
    '#default_value' => variable_get('nopremium_message', 'The full content of this page is available to premium users only.'),
  );
  foreach (node_type_get_names() as $type => $name) {
    $form['message']['nopremium_message_'. $type] = array(
      '#type' => 'textfield',
      '#title' => t('Message for @type content type', array('@type' => $name)),
      '#default_value' => variable_get('nopremium_message_'. $type, ''),
    );
  }

  return system_settings_form($form);
}

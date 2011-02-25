<?php

/**
 * @file
 * Written by Henri MEDOT <henri.medot[AT]absyx[DOT]fr>
 * http://www.absyx.fr
 */



/**
 * Implementation of hook_rules_condition_info()
 */
function nopremium_rules_condition_info() {
  $items = array();
  $defaults = array(
    'arguments' => array(
      'node' => array('type' => 'node', 'label' => t('Content')),
    ),
    'module' => 'Node',
  );
  $items['rules_condition_content_is_premium'] = $defaults + array(
    'label' => t('Content is premium'),
  );
  return $items;
}



/**
 * Condition: Check if the node is premium content.
 */
function rules_condition_content_is_premium(&$node, $settings) {
  return $node->premium == 1;
}



/**
 * Label callback for the condition.
 */
function rules_condition_content_is_premium_label($settings, $argument_labels) {
  return t('@node is premium content', $argument_labels);
}

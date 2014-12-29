<?php

/**
 * Implements hook_views_data_alter().
 */
function entity_reference_view_widget_views_data_alter(array &$data) {
  foreach (\Drupal::entityManager()->getDefinitions() as $entity_type_id => $entity_type) {
    $base_table = $entity_type->getBaseTable();
    $data[$base_table]['entity_reference_view_widget'] = array(
      'title' => $entity_type_id,
      'group' => t('Entity Reference View Widget'),
      'help' => t('Provide a checkbox to select the row for an entity reference.'),
      'field' => array(
        'id' => 'checkbox',
        'argument' => array(
          'entity_id' => $entity_type->getKey('id'),
        ),
      ),
    );
  }
}

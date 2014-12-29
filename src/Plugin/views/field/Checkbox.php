<?php

/**
 * @file
 * Definition of Drupal\entity_reference_view_widget\Plugin\views\field\Checkbox.
 */

namespace Drupal\entity_reference_view_widget\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Default implementation of the base field plugin.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("checkbox")
 */
class Checkbox extends FieldPluginBase {


  public function query() {
    // Do nothing -- to override the parent query.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return '<input name="entity_ids[]" type="checkbox" value="' . $values->{$this->definition['argument']['entity_id']} . '" />';
  }

}

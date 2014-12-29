<?php

/**
 * @file
 * Definition of Drupal\entity_reference_view_widget\Plugin\views\display\ViewDisplay.
 */

namespace Drupal\entity_reference_view_widget\Plugin\views\display;

use Drupal\views\Plugin\views\display\DisplayPluginBase;

/**
 * The plugin that handles an Entity Reference View Widget display.
 *
 * "entity_reference_view_widget_display" is a custom property, used with
 * \Drupal\views\Views::getApplicableViews() to retrieve all views with a
 * 'Entity Reference' display.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "entity_reference_view_widget",
 *   title = @Translation("Entity Reference View Widget"),
 *   help = @Translation("Selects referenceable entities for an entity reference view widget."),
 *   register_theme = FALSE,
 *   uses_menu_links = FALSE,
 *   theme = "views_view",
 *   entity_reference_view_widget_display = TRUE
 * )
 */
class ViewDisplay extends DisplayPluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAttachments.
   */
  protected $usesAttachments = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::defineOptions().
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Force the style plugin to 'entity_reference_style' and the row plugin to
    // 'fields'.
    $options['style']['contains']['type'] = array('default' => 'table');
    $options['defaults']['default']['style'] = FALSE;
    $options['row']['contains']['type'] = array('default' => 'fields');
    $options['defaults']['default']['row'] = FALSE;
    $options['use_ajax']['default'] = TRUE;

    return $options;
  }

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::getType().
   */
  protected function getType() {
    return 'entity_reference_view_widget';
  }

}

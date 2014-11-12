<?php

/**
 * @file
 * Contains \Drupal\entity_reference_view_widget\Plugin\Field\FieldWidget\ViewWidget.
 */

namespace Drupal\entity_reference_view_widget\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\String;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;


/**
 * Plugin implementation of the 'entity_reference_view_widget' widget.
 *
 * @FieldWidget(
 *   id = "entity_reference_view_widget",
 *   label = @Translation("View"),
 *   description = @Translation("An advanced, view-based widget."),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = TRUE
 * )
 */
class ViewWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'view' => '',
      'pass_argument' => TRUE,
      'close_modal' => FALSE,
      'allow_duplicates' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $displays = Views::getApplicableViews('entity_reference_view_widget_display');
    $options = array();

    foreach ($displays as $data) {
      list($view, $display_id) = $data;
      $options[$view->storage->id() . '|' . $display_id] = String::checkPlain($view->storage->label() . ' | ' . $view->storage->getDisplay($display_id)['display_title']);
    }

    $element['view'] = array(
      '#type' => 'select',
      '#title' => t('View'),
      '#default_value' => $this->getSetting('view'),
      '#options' => $options,
      '#description' => t('Specify the View to use for selecting items. Only views that have an "Entityreference View Widget" display are shown.'),
      '#required' => TRUE,
    );
    $element['pass_argument'] = array(
      '#type' => 'checkbox',
      '#title' => t('Pass selected entity ids to View'),
      '#default_value' => $this->getSetting('pass_argument'),
      '#description' => t('If enabled, the View will get all selected entity ids as the first argument. Useful for excluding already selected items.'),
    );
    $element['close_modal'] = array(
      '#type' => 'checkbox',
      '#title' => t('Close modal window after submitting the items'),
      '#default_value' => $this->getSetting('close_modal'),
      '#description' => t('If enabled, the modal window will close after you had selected the entities from the view and submitted your selection.'),
    );
    $element['allow_duplicates'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow the same entity to be referenced multiple times'),
      '#default_value' => $this->getSetting('allow_duplicates'),
      '#description' => t('If enabled, this will allow you to reference the same entity multiple times.'),
    );
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();

    if (!empty($settings['view'])) {
      $summary[] = t('View: @view', array('@view' => $settings['view']));
    }
    else {
      $summary[] = t('No view configured');
    }
    $summary[] = t('Pass selected entity ids to View: @pass_argument', array('@pass_argument' => ($settings['pass_argument'] ? t('Yes') : t('No'))));
    $summary[] = t('Close modal window after submitting the items: @close_modal', array('@close_modal' => ($settings['close_modal'] ? t('Yes') : t('No'))));
    $summary[] = t('Allow the same entity to be referenced multiple times: @allow_duplicates', array('@allow_duplicates' => ($settings['allow_duplicates'] ? t('Yes') : t('No'))));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()->getCardinality();
    $parents = $form['#parents'];

    // Determine the number of widgets to display.
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $field_state = static::getWidgetState($parents, $field_name, $form_state);
        $max = $field_state['items_count'];
        $is_multiple = TRUE;
        break;

      default:
        $max = $cardinality - 1;
        $is_multiple = ($cardinality > 1);
        break;
    }
    /*$element += array(
      '#title' => $title,
      '#description' => $description,
      '#markup' => t('No items had been added yet. Click "Add items" to launch the widget.'),
    );
    $element['add_more'] = array(
      '#type' => 'button',
      '#value' => t('Add items'),
      '#ajax' => array(
        'callback' => array($this, 'ajaxCallback'),
      ),
      '#limit_validation_errors' => array(),
    );*/
    $id_prefix = implode('-', array_merge($parents, array($field_name)));
    $wrapper_id = Html::getUniqueId($id_prefix . '-ervw-wrapper');
    $element += array(
      '#theme' => 'field_multiple_value_form',
      '#field_name' => $field_name,
      '#cardinality' => $cardinality,
      '#cardinality_multiple' => TRUE,
      '#required' => $this->fieldDefinition->isRequired(),
      '#max_delta' => $max,
    );

    if (!$items->isEmpty()) {

    }
    else {
      $element[0]['#markup'] = t('No items had been added yet. Click "Add items" to launch the widget.');
    }

    $element['add_more'] = array(
      '#name' => strtr($id_prefix, '-', '_') . '_add_more',
      '#type' => 'button',
      '#value' => t('Add items'),
      '#ajax' => array(
        'callback' => array($this, 'ajaxCallback'),
      ),
      '#limit_validation_errors' => array(),
      '#ervw_settings' => array(
        'wrapper' => str_replace('_', '-', $field_name . '-values'),
        'cardinality' => $cardinality,
        'target_type' => $this->getFieldSetting('target_type'),
      ),
      '#element_validate' => array(array($this, 'elementValidate')),
    );

    $form['#attached']['library'][] = 'views/views.ajax';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function elementValidate($element, FormStateInterface $form_state, $form) {
    $form_state->setRebuild();
  }

  /**
   * Overrides \Drupal\file\Plugin\Field\FieldWidget\FileWidget::formMultipleElements().
   *
   * Modify the add_more button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);
    $form['#attached']['library'][] = 'views/views.ajax';

    $elements['add_more'] = array(
      '#type' => 'button',
      '#value' => t('Add items'),
      '#ajax' => array(
        'callback' => array($this, 'ajaxCallback'),
      ),
      '#limit_validation_errors' => array(),
    );

    return $elements;
  }

  /**
   * Returns a renderable array of the rows.
   *
   * @param $entity_ids
   */
  public static function getRows($entity_ids, $settings) {
    $entity_ids = array_values($entity_ids);
    $rows = array();

    if (!empty($entity_ids)) {
      $entities = entity_load_multiple($settings['target_type'], $entity_ids);
      $max = count($entities);
      $delta = 0;

      foreach ($entities as $entity_id => $entity_item) {
        $rows[] = array(
          'target_id' => array(
            '#type' => 'checkbox',
            '#delta' => $delta,
            '#field_suffix' => String::checkPlain($entity_item->label()),
            '#return_value' => $entity_id,
            '#value' => $entity_id,
          ),
          '_weight' => array(
            '#type' => 'weight',
            '#title' => t('Weight for row @number', array('@number' => $delta + 1)),
            '#title_display' => 'invisible',
            // Note: this 'delta' is the FAPI #type 'weight' element's property.
            '#delta' => $max,
            '#default_value' => $delta,
            '#weight' => 100,
          ),
        );
        $delta++;
      }
    }

    return $rows;
  }

  /**
   * Ajax callback for the the add items button.
   */
  public function ajaxCallback(array $form, FormStateInterface &$form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    if (isset($triggering_element['#ervw_settings'])) {
      $response = new AjaxResponse();
      $view = $this->getSetting('view');

      if (!empty($view)) {
        list($view_id, $display_id) = explode('|', $view);
        if ($view = Views::getView($view_id)) {
          $view->setAjaxEnabled(TRUE);
          $preview = $view->preview($display_id);
          $response->addCommand(new SettingsCommand($view->element['#attached']['js'][0]['data'], TRUE));
          $form_settings = array(
            'view' => $view,
            'preview' => $preview,
            'settings' => $triggering_element['#ervw_settings'] + $this->getSettings(),
          );
          $modal_form = \Drupal::formBuilder()
            ->getForm('Drupal\entity_reference_view_widget\Form\ModalForm', $form_settings);
          $response->addCommand(new SettingsCommand($modal_form['actions']['add_items']['#attached']['js'][0]['data'], TRUE));
          $modal_content = '';

          // Display the exposed widgets on top of the modal.
          if (!empty($view->exposed_widgets)) {
            $modal_content .= drupal_render($view->exposed_widgets);
          }
          $modal_content .= drupal_render($modal_form);

          $response->addCommand(new OpenModalDialogCommand($view->storage->label(), $modal_content, array('width' => 700)));
        }
      }

      return $response;
    }
  }

}

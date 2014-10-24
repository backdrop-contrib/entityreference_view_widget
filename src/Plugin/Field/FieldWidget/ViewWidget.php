<?php

/**
 * @file
 * Contains \Drupal\entity_reference_view_widget\Plugin\Field\FieldWidget\ViewWidget.
 */

namespace Drupal\entity_reference_view_widget\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\String;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Field\FieldItemListInterface;
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
 *   }
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
    $element += array(
      '#type' => 'textfield',
    );

    return array('target_id' => $element);
  }

  /**
   * Overrides \Drupal\file\Plugin\Field\FieldWidget\FileWidget::formMultipleElements().
   *
   * Modify the add_more button.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    if (isset($elements['add_more'])) {
      $elements['add_more'] = array_merge($elements['add_more'], array(
        '#type' => 'button',
        '#value' => t('Add items'),
        '#submit' => array(),
        '#ajax' => array(
          'callback' => array($this, 'ajaxCallback'),
        ),
      ));
    }

    return $elements;
  }

  /**
   * Ajax callback for the the add items button.
   */
  public function ajaxCallback(array $form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $view = $this->getSetting('view');

    if (!empty($view)) {
      list($view_id, $display_id) = explode('|', $view);
      if ($view = Views::getView($view_id)) {
        $response->addCommand(new OpenModalDialogCommand($view->storage->label(), drupal_render($view->preview($display_id)), array('width' => '700')));
      }
    }

    return $response;
  }

}

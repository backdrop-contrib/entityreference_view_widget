<?php

/**
 * @file
 * Contains \Drupal\entity_reference_view_widget\Form\ModalForm.
 */

namespace Drupal\entity_reference_view_widget\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the database logging filter form.
 */
class ModalForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'entity_reference_view_widget_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $settings = $build_info['args'][0];
    $view = $settings['view'];
    $html = '';
    // Update the form id to match the jquery selector.
    $form['#id'] = Html::cleanCssIdentifier('views-exposed-form-' . $view->storage->id() . '-' . $view->current_display);

    if (!empty($view->exposed_widgets)) {
      $html .= '<div class="view-filters">' . drupal_render($view->exposed_widgets) . '</div>';
    }
    $html .= drupal_render($settings['preview']);
    $form['view'] = array(
      '#markup' => $html,
    );

    $form['add_items'] = array(
      '#value' => t('Add items'),
      '#type' => 'button',
      '#ajax' => array(
        'callback' => array(get_class($this), 'addItemsAjax'),
      ),
      '#limit_validation_errors' => array(),
    );

    return $form;
  }

  /**
   * Ajax callback for the the add items button.
   */
  public function addItemsAjax(array $form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new AlertCommand('ok'));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

<?php

/**
 * @file
 * Contains \Drupal\entity_reference_view_widget\Form\ModalForm.
 */

namespace Drupal\entity_reference_view_widget\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\PrependCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_view_widget\Plugin\Field\FieldWidget\ViewWidget;

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
    $form_state->set('ervw_settings', $settings['settings']);
    $form['view'] = array(
      '#markup' => drupal_render($settings['preview']),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['add_items'] = array(
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
    $input = $form_state->getUserInput();
    $messages = drupal_get_messages('error', FALSE);

    if (!empty($messages)) {
      $status_messages = array('#theme' => 'status_messages', '#display' => 'error');
      $response->addCommand(new RemoveCommand('#drupal-modal .messages'));
      $response->addCommand(new PrependCommand('#drupal-modal', drupal_render($status_messages)));
    }
    else {
      if (!empty($input['entity_ids'])) {
        $settings = $form_state->get('ervw_settings');
        $rows = ViewWidget::getRows($input['entity_ids'], $settings);
        $response->addCommand(new ReplaceCommand('#' . $settings['wrapper'] . ' > tbody', '<tbody>' . drupal_render($rows) . '</tbody>'));

        // Close the modal if the widget setting has been selected.
        if (!empty($settings['close_modal'])) {
          $response->addCommand(new CloseModalDialogCommand());
        }
      }
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $settings = $form_state->get('ervw_settings');
    $input = $form_state->getUserInput();

    if (!empty($input['entity_ids'])) {
      if ($settings['cardinality'] > 0 && $settings['cardinality'] < count($input['entity_ids'])) {
        drupal_set_message(t('Please select no more than @cardinality values', array('@cardinality' => $settings['cardinality'])), 'error');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}

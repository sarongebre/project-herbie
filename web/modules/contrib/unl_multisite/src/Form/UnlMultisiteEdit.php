<?php

namespace Drupal\unl_multisite\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Site deletion confirmation.
 */
class UnlMultisiteEdit extends ConfirmFormBase {

  /**
   * Base database API.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $databaseConnection;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * Class constructor for form object.
   *
   * @param \Drupal\Core\Database\Connection $database_connection
   *   Base database API.
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   The messenger service.
   */
  public function __construct(Connection $database_connection, Messenger $messenger) {
    $this->databaseConnection = $database_connection;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'unl_multisite_site_edit';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the site?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('unl_multisite.site_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getSaveText() {
    return $this->t('Save');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormName() {
    return 'delete';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $site_id = NULL) {
    $site_data = $this->databaseConnection->select('unl_sites', 's')
      ->fields('s', array('site_id', 'drupal_seven_id', 'site_path', 'uri', 'installed'))
      ->condition('site_id', $site_id)
      ->execute()
      ->fetchAll();
    if(count($site_data) > 1 ) {
      $form['error_display'] = [
        '#markup' => '<p>Can not edit site. More than one site has this site ID:' . $site_id . '</p>',
      ];
    } else {

      foreach ($site_data as $site) {
        $form['site_id'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Site ID'),
          '#disabled' => TRUE,
          '#value' => $site->site_id,
        );
        $form['drupal_seven_id'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Drupal 7 ID'),
          // '#disabled' => TRUE,
          '#default_value' => $site->drupal_seven_id,

          // '#value' => $site->drupal_seven_id,
        );

        $form['site_path'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Site path'),
          '#disabled' => TRUE,
          '#default_value' => $site->site_path,

          // '#value' => $site->site_path,
        );

        $form['uri'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Site URI'),
          '#disabled' => TRUE,
          '#value' => $site->uri,
        );

        $form['installed'] = array(
          '#type' => 'textfield',
          '#title' => $this->t('Installed'),
          '#disabled' => TRUE,
          '#value' => ($site->installed == 2 ? 'Installed': 'Not Installed'),
        );

      }
    }
    // $form['site_id'] = array(
    //   '#type' => 'value',
    //   '#value' => $site_data->site_id,
    // );
    // $form['confirm_delete'] = array(
    //   '#type' => 'checkbox',
    //   '#title' => t('Confirm'),
    //   '#description' => $this->getQuestion(),
    //   '#required' => TRUE,
    // );
    // $form['confirm_again'] = array(
    //   '#type' => 'checkbox',
    //   '#title' => t('Confirm again'),
    //   '#description' => t('I am sure I want to permanently delete the site at: %site_path', array('%site_path' => $site_path[0])),
    //   '#required' => TRUE,
    // );

    // $form['description'] = ['#markup' => $this->getDescription()];
    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    $form['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancelForm'],
    ];


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $drupal_seven_id = $form_state->getValue('drupal_seven_id');
    $site_id = $form_state->getValue('site_id');
    $site_path = $form_state->getValue('site_path');

    $query = $this->databaseConnection->update('unl_sites');
    $query->fields([
      'drupal_seven_id' => $drupal_seven_id,
      'site_path' => $site_path,
    ]);
    $query->condition('site_id', $site_id);
    $result = $query->execute();

    // Check database update result.
    if ($result) {
      \Drupal::messenger()->addMessage($this->t('Database table updated successfully.'));
    }
    else {
      \Drupal::messenger()->addMessage($this->t('Failed to update database table.'), 'error');
     }

    // $values = $form_state->getValues();
    // if (!isset($values['site_id'])) {
    //   return;
    // }
    // $this->flagSiteToRemove($values['site_id']);
    // $this->messenger->addStatus(t('The site has been scheduled for removal.'));
    // $form_state->setRedirect('unl_multisite.site_list');
  }

  public function cancelForm(array &$form, FormStateInterface $form_state) {
    // Cancel button logic.
    $form_state->setRedirect('unl_multisite.site_list');
  }

  // private function updateUnlSiteTable($) {

  // }

  // private function flagSiteToRemove($site_id) {
  //   $this->databaseConnection->update('unl_sites')
  //     ->fields(array('installed' => 3))
  //     ->condition('site_id', $site_id)
  //     ->execute();
  //   $this->databaseConnection->update('unl_sites_aliases')
  //     ->fields(array('installed' => 3))
  //     ->condition('site_id', $site_id)
  //     ->execute();

  //   return TRUE;
  // }

}

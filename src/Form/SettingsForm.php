<?php

/**
 * @file
 * Contains \Drupal\nopremium\Form\SettingsForm.
 */

namespace Drupal\nopremium\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Defines a form that configures devel settings.
 */
class SettingsForm extends ConfigFormBase {
  
  /**
   * Entity Manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new SettingsForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $$$entity_manager
   *   Entity Manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nopremium_admin_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nopremium.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $current_url = Url::createFromRequest($request);
    $nopremium_config = $this->config('nopremium.settings');
    $form['message'] = array(
      '#type' => 'fieldset',
      '#title' => t('Premium messages'),
      '#description' => t('You may customize the messages displayed to unprivileged users trying to view full premium contents.'),
    );
    $form['message']['nopremium_message'] = array(
      '#type' => 'textarea',
      '#title' => t('Default message'),
      '#description' => t('This message will apply to all content types with blank messages below.'),
      '#default_value' => $nopremium_config->get('default_message'),
      '#rows' => 3,
      '#required' => TRUE,
    );
    foreach ($this->entityManager->getStorage('node_type')->loadMultiple() as $content_type) {
       $form['message']['nopremium_message_'. $content_type->id()] = array(
         '#type' => 'textarea',
         '#title' => t('Message for %type content type', array('%type' => $content_type->label())),
         '#default_value' => !empty($nopremium_config->get('default_message' . $content_type->id())) ? $nopremium_config->get('default_message' . $content_type->id()) : $nopremium_config->get('default_message'),
         '#rows' => 3,
       );
     }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('nopremium.settings')
      ->set('default_message', $values['nopremium_message'])
      ->save();
    foreach ($this->entityManager->getStorage('node_type')->loadMultiple() as $content_type) {
      $this->config('nopremium.settings')
        ->set('default_message' . $content_type->id(), $values['nopremium_message_'. $content_type->id()])
        ->save();
    }
    parent::submitForm($form, $form_state);
  }
}

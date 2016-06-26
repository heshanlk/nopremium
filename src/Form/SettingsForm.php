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
 * Defines a form that configures settings.
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
    if (\Drupal::moduleHandler()->moduleExists('token')) {
      $form['message']['token_tree'] = array(
        '#theme' => 'token_tree_link',
        '#token_types' => array('user', 'node'),
        '#weight' => 90,
      );
    }
    else {
      $form['message']['token_tree'] = array(
        '#markup' => '<p>' . t('Enable the <a href="@drupal-token">Token module</a> to view the available token browser.', array('@drupal-token' => 'http://drupal.org/project/token')) . '</p>',
      );
    }
    $options = array();
    foreach($this->entityManager->getViewModes('node') as $id => $view_mode){
     $options[$id] = $view_mode['label'];
    }
    $form['nopremium_view_mode'] = array(
      '#type' => 'select',
      '#title' => t('Premium display mode'),
      '#description' => t('The premium display view mode which we restrict access.'),
      '#default_value' => $nopremium_config->get('view_mode'),
      '#options' => $options,
    );
    $form['nopremium_teaser_view_mode'] = array(
      '#type' => 'select',
      '#title' => t('Teaser display mode'),
      '#description' => t('Teaser display view mode to render for premium contents.'),
      '#default_value' => $nopremium_config->get('teaser_view_mode'),
      '#options' => $options,
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('nopremium.settings')
      ->set('default_message', $values['nopremium_message'])
      ->set('view_mode', $values['nopremium_view_mode'])
      ->set('teaser_view_mode', $values['nopremium_teaser_view_mode'])
      ->save();
    foreach ($this->entityManager->getStorage('node_type')->loadMultiple() as $content_type) {
      $this->config('nopremium.settings')
        ->set('default_message' . $content_type->id(), $values['nopremium_message_'. $content_type->id()])
        ->save();
    }
    parent::submitForm($form, $form_state);
  }
}

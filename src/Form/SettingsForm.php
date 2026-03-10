<?php

namespace Drupal\portfolio_github\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure GitHub Activity settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'portfolio_github_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['portfolio_github.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('portfolio_github.settings');

    $form['personal'] = [
      '#type' => 'details',
      '#title' => $this->t('Personal Account (Green)'),
      '#open' => TRUE,
    ];

    $form['personal']['personal_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('personal_username'),
    ];

    $form['personal']['personal_token'] = [
      '#type' => 'password',
      '#title' => $this->t('Personal Access Token'),
      '#description' => $this->t('Token with "read:user" or "repo" permission.'),
    ];

    $form['work'] = [
      '#type' => 'details',
      '#title' => $this->t('Work Account (Orange)'),
      '#open' => TRUE,
    ];

    $form['work']['work_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('work_username'),
    ];

    $form['work']['work_token'] = [
      '#type' => 'password',
      '#title' => $this->t('Work Access Token'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('portfolio_github.settings');
    
    $config->set('personal_username', $form_state->getValue('personal_username'))
      ->set('work_username', $form_state->getValue('work_username'));

    // Only update tokens if provided.
    if ($personal_token = $form_state->getValue('personal_token')) {
      $config->set('personal_token', $personal_token);
    }
    if ($work_token = $form_state->getValue('work_token')) {
      $config->set('work_token', $work_token);
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }
}

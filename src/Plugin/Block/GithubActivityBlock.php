<?php

namespace Drupal\portfolio_github\Plugin\Block;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\portfolio_github\GithubActivityService;

/**
 * Provides a 'GitHub Activity Block' block.
 *
 * @Block(
 *   id = "portfolio_github_activity_block",
 *   admin_label = @Translation("GitHub Activity Pulse"),
 *   category = @Translation("Portfolio")
 * )
 */
class GithubActivityBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $activityService;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, GithubActivityService $activity_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->activityService = $activity_service;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('portfolio_github.activity_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['usernames'] = [
      '#type' => 'textarea',
      '#title' => $this->t('GitHub Usernames'),
      '#description' => $this->t('Enter GitHub usernames, one per line. If left empty, global settings will be used.'),
      '#default_value' => isset($config['usernames']) ? implode("\n", $config['usernames']) : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $usernames = explode("\n", $form_state->getValue('usernames'));
    $usernames = array_filter(array_map('trim', $usernames));
    $this->setConfigurationValue('usernames', $usernames);
  }

  public function build() {
    $config = $this->getConfiguration();
    $usernames = !empty($config['usernames']) ? $config['usernames'] : NULL;
    $activity = $this->activityService->getMergedActivity($usernames);
    
    return [
      '#theme' => 'portfolio_github_block',
      '#activity' => $activity,
      '#attached' => [
        'library' => ['portfolio_github/activity-grid'],
      ],
      '#cache' => [
        'tags' => ['config:portfolio_github.settings'],
        'max-age' => 86400,
      ],
    ];
  }
}

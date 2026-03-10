<?php

namespace Drupal\portfolio_github;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;

/**
 * Service to fetch and merge GitHub activity.
 */
class GithubActivityService {

  protected $config;
  protected $httpClient;
  protected $cache;

  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client, CacheBackendInterface $cache) {
    $this->config = $config_factory->get('portfolio_github.settings');
    $this->httpClient = $http_client;
    $this->cache = $cache;
  }

  public function getMergedActivity() {
    $personal_user = $this->config->get('personal_username');
    $work_user = $this->config->get('work_username');
    
    $cache_id = 'portfolio_github_activity_' . md5($personal_user . $work_user);
    if ($cache_data = $this->cache->get($cache_id)) {
      return $cache_data->data;
    }

    // Initialize full year.
    $days = [];
    $end = new \DateTime();
    $start = (clone $end)->modify('-52 weeks')->modify('last Sunday');
    $interval = new \DateInterval('P1D');
    $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

    foreach ($period as $dt) {
      $days[$dt->format('Y-m-d')] = [
        'personal' => 0,
        'work' => 0,
        'total' => 0,
      ];
    }

    $total_count = 0;

    // Fetch Personal.
    if ($personal_user) {
      $personal_token = $this->config->get('personal_token');
      $activity = $this->fetchFromGithub($personal_user, $personal_token);
      foreach ($activity as $date => $count) {
        if (isset($days[$date])) {
          $days[$date]['personal'] = $count;
          $days[$date]['total'] += $count;
          $total_count += $count;
        }
      }
    }

    // Fetch Work.
    if ($work_user) {
      $work_token = $this->config->get('work_token');
      $activity = $this->fetchFromGithub($work_user, $work_token);
      foreach ($activity as $date => $count) {
        if (isset($days[$date])) {
          $days[$date]['work'] = $count;
          $days[$date]['total'] += $count;
          $total_count += $count;
        }
      }
    }

    $result = [
      'days' => $days,
      'total' => $total_count,
    ];

    $this->cache->set($cache_id, $result, time() + 86400);
    return $result;
  }

  protected function fetchFromGithub($username, $token = NULL) {
    if ($token) {
      return $this->fetchWithToken($username, $token);
    }
    return $this->fetchPublic($username);
  }

  protected function fetchWithToken($username, $token) {
    $query = <<<'GRAPHQL'
query($username: String!) {
  user(login: $username) {
    contributionsCollection {
      contributionCalendar {
        weeks { contributionDays { date contributionCount } }
      }
    }
  }
}
GRAPHQL;

    try {
      $response = $this->httpClient->post('https://api.github.com/graphql', [
        'headers' => [
          'Authorization' => 'Bearer ' . $token,
          'User-Agent' => 'Drupal Portfolio',
        ],
        'json' => [
          'query' => $query,
          'variables' => ['username' => $username],
        ],
      ]);
      $data = json_decode($response->getBody()->getContents(), TRUE);
      $activity = [];
      if (isset($data['data']['user']['contributionsCollection']['contributionCalendar']['weeks'])) {
        foreach ($data['data']['user']['contributionsCollection']['contributionCalendar']['weeks'] as $week) {
          foreach ($week['contributionDays'] as $day) {
            $activity[$day['date']] = (int) $day['contributionCount'];
          }
        }
      }
      return $activity;
    } catch (\Exception $e) {
      return [];
    }
  }

  protected function fetchPublic($username) {
    try {
      $response = $this->httpClient->get("https://github-contributions-api.deno.dev/$username.json");
      $data = json_decode($response->getBody()->getContents(), TRUE);
      $activity = [];
      if (isset($data['contributions'])) {
        foreach ($data['contributions'] as $week) {
          foreach ($week as $day) {
            $activity[$day['date']] = (int) $day['contributionCount'];
          }
        }
      }
      return $activity;
    } catch (\Exception $e) {
      return [];
    }
  }
}

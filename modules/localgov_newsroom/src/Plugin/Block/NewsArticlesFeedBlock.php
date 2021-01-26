<?php

namespace Drupal\localgov_newsroom\PLugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Session\AccountInterface;

/**
 * Class ArticlesFeedBlock.
 *
 * @Block(
 *   id = "localgov_news_feed",
 *   admin_label = "News articles feed"
 * )
 */
class NewsArticlesFeedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    if (!\Drupal::routeMatch()->getParameter('node')) {
      return AccessResult::forbidden();
    }

    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $build['infinite_scroll'] = [
      '#theme' => 'infinite_scroll',
      '#attached' => [
        'library' => [
          'localgov_newsroom/infinite-scroll',
        ],
      ],
      '#ajax_callback_route' => 'localgov_newsroom.newsroom_ajax_callback',
    ];

    foreach (\Drupal::service('localgov_newsroom.newsroom')->getPage(0) as $node) {
      $build['infinite_scroll']['#content'][] = \Drupal::entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), [
      'node_list',
    ]);
  }

}

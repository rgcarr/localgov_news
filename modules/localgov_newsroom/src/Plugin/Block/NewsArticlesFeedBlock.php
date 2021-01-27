<?php

namespace Drupal\localgov_newsroom\Plugin\Block;

use Drupal\Core\Cache\Cache;

/**
 * Class NewsArticlesFeedBlock.
 *
 * @Block(
 *   id = "localgov_news_feed",
 *   admin_label = "News articles feed"
 * )
 */
class NewsArticlesFeedBlock extends NewsAbstractBlockBase {

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
      $build['infinite_scroll']['#content'][] = $this->entityTypeManager
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

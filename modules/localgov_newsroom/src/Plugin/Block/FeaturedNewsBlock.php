<?php

namespace Drupal\localgov_newsroom\Plugin\Block;

/**
 * Class FeaturedNewsBlock.
 *
 * @package Drupal\localgov_newsroom\Plugin\Block
 *
 * @Block(
 *   id = "localgov_featured_news_block",
 *   admin_label = @Translation("Featured news articles"),
 * )
 */
class FeaturedNewsBlock extends NewsAbstractBlockBase {

  /**
   * Maximum number of featured articles.
   */
  const MAX_FEATURED_ARTICLES = 3;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $promotedNewsIds = [];

    $featuredNewsIds = $this->getFeaturedNews();

    if (count($featuredNewsIds) < self::MAX_FEATURED_ARTICLES) {
      $promotedNewsIds = $this->getPromotedNews($featuredNewsIds, self::MAX_FEATURED_ARTICLES);
    }

    $displayNewsIds = array_merge($featuredNewsIds, $promotedNewsIds);

    // Prevent rendering if no news is featured or promoted.
    if (empty($displayNewsIds)) {
      return NULL;
    }

    $displayNews = $this->entityTypeManager->getStorage('node')->loadMultiple($displayNewsIds);

    $featured = $this->entityTypeManager
      ->getViewBuilder('node')
      ->viewMultiple(
        $displayNews,
        'teaser'
      );

    $build[] = [
      '#theme' => 'localgov_featured_news_block',
      '#featured' => $featured,
    ];

    return $build;
  }

  /**
   * Get the nids for any featured news articles in this newsroom.
   */
  private function getFeaturedNews() {
    return (array_column($this->newsroom->loadNewsroom()->get('localgov_newsroom_featured')->getValue(), 'target_id'));
  }

  /**
   * Get the most recent promoted news.
   *
   * @param array $excludeNids
   *   A list of node ids to exclude.
   *   Prevents duplication of articles which are both promoted and featured.
   * @param int $limit
   *   The maximum number of news articles to show in the block.
   *
   * @return array
   *   List of promoted news article ids to include.
   */
  private function getPromotedNews(array $excludeNids, $limit) {
    $promotedNewsQuery = $this->entityTypeManager->getStorage('node')->getQuery();
    $promotedNewsQuery->condition('type', 'localgov_news_article')
      ->condition('promote', 1)
      ->condition('status', 1)
      ->range(0, $limit - count($excludeNids))
      ->sort('localgov_news_date', 'DESC');

    if ($excludeNids) {
      $promotedNewsQuery->condition('nid', $excludeNids, 'NOT IN');
    }
    $promotedNewsIds = $promotedNewsQuery->execute();
    return $promotedNewsIds;
  }

}

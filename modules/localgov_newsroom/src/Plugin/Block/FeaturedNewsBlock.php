<?php

namespace Drupal\localgov_newsroom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class FeaturedNewsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Maximum number of featured articles.
   */
  const MAX_FEATURED_ARTICLES = 3;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The newsroom node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $newsroom;

  /**
   * Route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  private $routeMatch;

  /**
   * FeaturedNewsBlock constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->newsroom = $this->routeMatch->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

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
      '#attached' => [
        'library' => [
          'localgov_newsroom/localgov-featured-news',
        ],
      ],
    ];

    return $build;
  }

  /**
   * Get the nids for any featured news articles in this newsroom.
   */
  private function getFeaturedNews() {
    return (array_column($this->newsroom->localgov_newsroom_featured->getValue(), 'target_id'));
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

<?php

namespace Drupal\localgov_news;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Featured and all news views blocks.
 */
class FeaturedNewsHelper implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * EntityChildRelationshipUi constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   Block plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, BlockManagerInterface $block_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.block')
    );
  }

  /**
   * Gets the "extra fields" for a bundle.
   *
   * @see localgov_news_entity_extra_field_info()
   */
  public function entityExtraFieldInfo() {
    $fields = [];

    $fields['node']['localgov_newsroom']['display']['localgov_newsroom_featured_view'] = [
      'label' => $this->t('Featured news listing'),
      'description' => $this->t("Output from the embedded view for featured, and promoted, news."),
      'weight' => -20,
      'visible' => TRUE,
    ];
    $fields['node']['localgov_newsroom']['display']['localgov_newsroom_all_view'] = [
      'label' => $this->t('All other news listing'),
      'description' => $this->t("Output facets the embedded view for all other news in newsroom."),
      'weight' => -20,
      'visible' => TRUE,
    ];
    $fields['node']['localgov_newsroom']['display']['localgov_news_search'] = [
      'label' => $this->t('News search'),
      'description' => $this->t("Free text search block for news."),
      'weight' => -20,
      'visible' => TRUE,
    ];
    $fields['node']['localgov_newsroom']['display']['localgov_news_facets'] = [
      'label' => $this->t('News facets'),
      'description' => $this->t("Output facets block, field alternative to enabling the block."),
      'weight' => -20,
      'visible' => TRUE,
    ];

    return $fields;
  }

  /**
   * Adds view with arguments to view render array if required.
   *
   * @see localgov_newsroom_node_view()
   */
  public function nodeView(array &$build, NodeInterface $node, EntityViewDisplayInterface $display, $view_mode) {
    // Add view if enabled.
    if ($display->getComponent('localgov_newsroom_featured_view')) {
      $build['localgov_newsroom_featured_view'] = $this->getViewEmbed($node, 'featured_news');
    }
    if ($display->getComponent('localgov_newsroom_all_view')) {
      $build['localgov_newsroom_all_view'] = $this->getViewEmbed($node, 'all_news');
    }
    if ($display->getComponent('localgov_news_search')) {
      $build['localgov_news_search'] = $this->getSearchBlock();
    }
    if ($display->getComponent('localgov_news_facets')) {
      $build['localgov_news_facets'] = $this->getFacetsBlock();
    }
  }

  /**
   * Views query alter to add/remove featured news.
   *
   * *** FEATURED *** items only include those referenced by the newsroom, or
   * that are 'Promoted to frontpage'.
   * The limit on the featureD_news display pages is the maximum number.
   * There are between 0 (if nothing referenced or promoted) and the maximum
   * number in *** FEATURED ***.
   * The list can be exclusive IN (the featured listed) and excluded NOT IN (the
   * rest of the items list).
   *
   * @param Drupal\views\ViewExecutable $view
   *   The 'localgov_news_list' view.
   * @param Drupal\views\Plugin\views\query\QueryPluginBase $query
   *   The query to be altered.
   *
   * @see localgov_news_views_query_alter()
   */
  public function alterFeaturedNewsCondition(ViewExecutable $view, Sql $query): void {
    $newsroom_id = $view->argument['localgov_newsroom_target_id']->getValue();
    if (empty($newsroom_id)) {
      return;
    }
    $max_featured_articles = $view->displayHandlers->get('featured_news')->getOption('pager')['options']['items_per_page'];
    $ids = $this->getFeaturedPromotedNews($newsroom_id, $max_featured_articles);

    foreach ($query->where as $where_delta => $where) {
      foreach ($where['conditions'] as $conditions_delta => $conditions) {
        if ($conditions['value'] == '*** FEATURED ***') {
          if (!empty($ids)) {
            $query->where[$where_delta]['conditions'][$conditions_delta]['value'] = $ids;
            if ($conditions['operator'] == '=') {
              $query->where[$where_delta]['conditions'][$conditions_delta]['operator'] = 'in';
            }
            else {
              $query->where[$where_delta]['conditions'][$conditions_delta]['operator'] = 'not in';
            }
          }
          else {
            $query->where[$where_delta]['conditions'][$conditions_delta]['value'] = 0;
          }
        }
      }
    }
  }

  /**
   * Retrieves view, and sets render array.
   */
  protected function getViewEmbed(NodeInterface $node, string $display_id) {
    $view = Views::getView('localgov_news_list');
    if (!$view || !$view->access($display_id)) {
      return;
    }
    return [
      '#type' => 'view',
      '#name' => 'localgov_news_list',
      '#display_id' => $display_id,
      '#arguments' => [$node->id()],
      '#attached' => [
        'library' => ['localgov_news/localgov-newsroom'],
      ],
    ];
  }

  /**
   * Retrieves the news search block.
   *
   * This presently is a sitewide news search.
   */
  protected function getSearchBlock() {
    $block = $this->blockManager->createInstance('views_exposed_filter_block:localgov_news_search-page_search_news');
    return $block->build();
  }

  /**
   * Retrieves the news facets blocks.
   */
  protected function getFacetsBlock() {
    $blocks = [];

    $block = $this->blockManager->createInstance('facet_block' . PluginBase::DERIVATIVE_SEPARATOR . 'localgov_news_category');
    if ($block) {
      $blocks[] = $block->build();
    }
    $block = $this->blockManager->createInstance('facet_block' . PluginBase::DERIVATIVE_SEPARATOR . 'localgov_news_date');
    if ($block) {
      $blocks[] = $block->build();
    }

    return $blocks;
  }

  /**
   * Get the featured and promoted news items.
   *
   * If there are news items in the featured entity reference field these take
   * priority. Then any 'promoted to front page' news items. Up to the maximum
   * set. This up to the maximum featured articles limit.
   *
   * @param int $newsroom_id
   *   The Node ID of the newsroom.
   * @param int $max_featured_articles
   *   The maximum number of ids to return.
   *
   * @return int[]
   *   Array of Node IDs.
   */
  protected function getFeaturedPromotedNews($newsroom_id, $max_featured_articles): array {
    $promoted_ids = [];

    $featured_ids = $this->getFeaturedNews($newsroom_id);

    if (count($featured_ids) < $max_featured_articles) {
      $promoted_ids = $this->getPromotedNews($newsroom_id, $featured_ids, $max_featured_articles);
    }

    return array_merge($featured_ids, $promoted_ids);
  }

  /**
   * Get featured news articles in this newsroom.
   *
   * @param int $newsroom_id
   *   The Node ID of the newsroom.
   *
   * @return array
   *   List of ids for featured news articles.
   */
  protected function getFeaturedNews($newsroom_id): array {
    $newsroom = $this->entityTypeManager->getStorage('node')->load($newsroom_id);
    if ($newsroom) {
      return (array_column($newsroom->get('localgov_newsroom_featured')->getValue(), 'target_id'));
    }
    else {
      return [];
    }
  }

  /**
   * Get the most recent promoted news.
   *
   * @param int $newsroom_id
   *   The Node ID of the newsroom.
   * @param array $excludeNids
   *   A list of node ids to exclude.
   *   Prevents duplication of articles which are both promoted and featured.
   * @param int $limit
   *   The maximum number of news articles to show in the block.
   *
   * @return array
   *   List of promoted news article ids to include.
   */
  protected function getPromotedNews($newsroom_id, array $excludeNids, $limit): array {
    $promotedNewsQuery = $this->entityTypeManager->getStorage('node')->getQuery();
    $promotedNewsQuery->condition('type', 'localgov_news_article')
      ->condition('localgov_newsroom', $newsroom_id)
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

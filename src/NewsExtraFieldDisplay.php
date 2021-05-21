<?php

namespace Drupal\localgov_news;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\NodeInterface;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * News views, and search, blocks.
 */
class NewsExtraFieldDisplay implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The block plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * EntityChildRelationshipUi constructor.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   Block plugin manager.
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
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

}

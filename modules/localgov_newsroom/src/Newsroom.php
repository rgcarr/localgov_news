<?php

namespace Drupal\localgov_newsroom;

use Drupal\node\Entity\Node;

/**
 * Class Newsroom.
 *
 * @package Drupal\localgov_news
 */
class Newsroom {

  /**
   * Total items per page.
   */
  const TOTAL_PER_PAGE = 2;

  /**
   * Returns the nodes to be displayed per page.
   *
   * @param int $page
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getPage($page = 0) {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
      ->condition('type', 'localgov_news_article')
      ->condition('status', Node::PUBLISHED)
      ->range($page * self::TOTAL_PER_PAGE, self::TOTAL_PER_PAGE)
      ->sort('created', 'DESC');
    $exclude_nodes = $this->excludeNodes();
    if (!empty($exclude_nodes)) {
      $query->condition('nid', $exclude_nodes, 'NOT IN');
    }
    $result = $query->execute();

    return \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($result);
  }

  /**
   * Returns a total count for nodes to be displayed.
   *
   * @return int
   */
  public function getCount() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'localgov_news_article')
      ->condition('status', Node::PUBLISHED);
    $exclude_nodes = $this->excludeNodes();
    if (!empty($exclude_nodes)) {
      $query->condition('nid', $exclude_nodes, 'NOT IN');
    }
    return (int) $query->count()
      ->execute();
  }

  /**
   * Returns an array of node ids to ignore.
   *
   * @return array
   *
   * @throws
   */
  private function excludeNodes() {
    $nodes = [];
    foreach ($this->loadNewsroom()->get('localgov_newsroom_featured')->getValue() as $item) {
      $nodes[] = $item["target_id"];
    }
    return $nodes;
  }

  /**
   * Load the newsroom page.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadNewsroom() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'localgov_newsroom')
      ->condition('status', Node::PUBLISHED)
      ->sort('created', 'DESC')
      ->execute();
    $query = reset($query);

    return \Drupal::entityTypeManager()->getStorage('node')->load($query);
  }

}

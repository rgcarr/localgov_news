<?php

namespace Drupal\localgov_news;

/**
 * Interface for lazy loading.
 */
interface LazyLoaderInterface {

  /**
   * Load all service hub nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Entity interface
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadAll();

  /**
   * Load all published service hub nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Entity interface
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadAllPublished();

  /**
   * Load multiple published nodes.
   *
   * @param int $offset
   *   Offset for range to load.
   * @param int $count
   *   Count for range to load.
   */
  public function loadMultiplePublished($offset, $count);

  /**
   * Load all unpublished service hub nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Entity interface.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadAllUnpublished();

}

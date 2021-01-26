<?php

namespace Drupal\localgov_news;

/**
 *
 */
interface LazyLoaderInterface {

  /**
   * Load all service hub nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadAll();

  /**
   * Load all published service hub nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadAllPublished();

  /**
   * Load multiple published nodes.
   *
   * @param $offset
   * @param $count
   */
  public function loadMultiplePublished($offset, $count);

  /**
   * Load all unpublished service hub nodes.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadAllUnpublished();

}

<?php

namespace Drupal\localgov_news;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Class LazyLoader.
 *
 * Provides base functions for lazy loaders.
 *
 * @package Drupal\bhcc_helper.
 */
abstract class LazyLoaderBase implements LazyLoaderInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LazyLoader constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function loadAll() {
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['type' => $this->type]);
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllPublished() {
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'type' => $this->type,
        'status' => NodeInterface::PUBLISHED,
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiplePublished($count, $offset = 0) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', $this->type)
      ->condition('status', NodeInterface::PUBLISHED)
      ->range($offset, $count)
      ->sort('created', 'DESC')
      ->execute();

    return $this->entityTypeManager->getStorage('node')->loadMultiple($query);
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllUnpublished() {
    return $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'type' => $this->type,
        'status' => NodeInterface::NOT_PUBLISHED,
      ]);
  }

}

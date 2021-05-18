<?php

namespace Drupal\localgov_newsroom;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Class Newsroom.
 *
 * @package Drupal\localgov_news
 */
class Newsroom {

  /**
   * Entity Type Manager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Initialise a Newsroom instance.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Load the newsroom page.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Entity interface.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function loadNewsroom() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'localgov_newsroom')
      ->condition('status', Node::PUBLISHED)
      ->sort('created', 'DESC')
      ->execute();
    $query = reset($query);

    return $this->entityTypeManager->getStorage('node')->load($query);
  }

}

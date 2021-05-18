<?php

namespace Drupal\localgov_newsroom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\localgov_newsroom\Newsroom;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsroomController.
 *
 * @package Drupal\localgov_newsroom\Controller
 */
class NewsroomController extends ControllerBase {
  /**
   * Newsroom.
   *
   * @var Newsroom
   */
  private $newsroom;

  /**
   * {@inheritdoc}
   */
  public function __construct(Newsroom $newsroom) {
    $this->newsroom = $newsroom;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('localgov_newsroom.newsroom')
    );
  }
}

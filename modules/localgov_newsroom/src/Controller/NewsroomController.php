<?php

namespace Drupal\localgov_newsroom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Zend\Diactoros\Response\JsonResponse;

/**
 * Class NewsroomController.
 *
 * @package Drupal\localgov_newsroom\Controller
 */
class NewsroomController extends ControllerBase {

  /**
   * @return \Zend\Diactoros\Response\JsonResponse
   */
  public function ajaxCallback() {
    $results = [];

    foreach (\Drupal::service('localgov_newsroom.newsroom')->getPage(\Drupal::request()->get('page')) as $node) {
      $render_array = $this->entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');

      $results[] = \Drupal::service('renderer')
        ->render($render_array);
    }

    return new JsonResponse([
      'html' => implode('', $results),
      'totalItems' => \Drupal::service('localgov_newsroom.newsroom')->getCount(),
      'finished' => ((\Drupal::request()->get('page') + 1) * 10) >= \Drupal::service('localgov_newsroom.newsroom')->getCount(),
    ]);
  }

}

<?php

namespace Drupal\localgov_newsroom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\localgov_newsroom\Newsroom;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class NewsroomController.
 *
 * @package Drupal\localgov_newsroom\Controller
 */
class NewsroomController extends ControllerBase {
  /**
   * Newsroom.
   *
   * @var \Drupal\localgov_newsroom\Newsroom
   */
  private $newsroom;

  /**
   * Core request_stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * Renderer interface.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  private $renderer;

  /**
   * {@inheritdoc}
   */
  public function __construct(Newsroom $newsroom, RequestStack $request_stack, RendererInterface $renderer) {
    $this->newsroom = $newsroom;
    $this->requestStack = $request_stack;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('localgov_newsroom.newsroom'),
      $container->get('request_stack'),
      $container->get('renderer')
    );
  }

  /**
   * Ajax callback.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   JSON response
   */
  public function ajaxCallback() {
    $results = [];

    foreach ($this->newsroom->getPage($this->requestStack->getCurrentRequest()->get('page')) as $node) {
      $render_array = $this->entityTypeManager()
        ->getViewBuilder('node')
        ->view($node, 'teaser');

      $results[] = $this->renderer
        ->render($render_array);
    }

    return new JsonResponse([
      'html' => implode('', $results),
      'totalItems' => $this->newsroom->getCount(),
      'finished' => (($this->requestStack->getCurrentRequest()->get('page') + 1) * 10) >= $this->newsroom->getCount(),
    ]);
  }

}

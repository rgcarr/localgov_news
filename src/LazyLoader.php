<?php

namespace Drupal\localgov_news;

/**
 * Class LazyLoader.
 *
 * Provides lazy loader functions for news pages.
 *
 * @package Drupal\localgov_news
 */
class LazyLoader extends LazyLoaderBase implements LazyLoaderInterface {
  /**
   * The content type to load.
   *
   * @var string
   */
  public $type = 'localgov_news_articles';

}

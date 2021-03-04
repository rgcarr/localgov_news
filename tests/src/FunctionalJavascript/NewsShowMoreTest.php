<?php

namespace Drupal\Tests\localgov_alert_banner\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\NodeInterface;

/**
 * Functional tests for LocalGovDrupal Alert banner block.
 */
class NewsShowMoreTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_theme';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'localgov_news',
    'localgov_newsroom',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node fields',
    ]);
    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public function testShowMore() {
    // Create 11 news articles.
    $newsRange = range(11, 1);
    foreach ($newsRange as $newsIndex) {
      $newsIndex = str_pad($newsIndex, 2, "0");
      $body = $this->randomMachineName(64);
      $this->createNode([
        'title' => 'News article ' . $newsIndex,
        'localgov_news_date' => '2021-01-' . $newsIndex,
        'body' => $body,
        'type' => 'localgov_news_article',
        'status' => NodeInterface::PUBLISHED,
      ]);
    }

    $this->createNode([
      'title' => 'News',
      'type' => 'localgov_newsroom',
      'path' => [
        'alias' => '/news',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Load newsroom.
    $this->drupalGet('/news');

    // Test 10 news articles are visible.
    $this->assertSession()->elementsCount('css', 'div#infinite-scroll--wrapper article', 10);

    // News article 1 should not be visible, as the oldest.
    $this->assertSession()->pageTextNotContains('News article 01');

    // Activate show more button.
    $page = $this->getSession()->getPage();
    $page->pressButton('infinite-scroll--trigger');

    // Test 1st news article is now visible.
    $this->assertSession()->waitForElementVisible('css', 'a:contains("News article 01")');

  }

}

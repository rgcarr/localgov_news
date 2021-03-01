<?php

namespace Drupal\Tests\localgov_news\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;

/**
 * Tests LocalGov News article page.
 *
 * @group localgov_news
 */
class NewsPageTest extends BrowserTestBase {

  use NodeCreationTrait;
  use AssertBreadcrumbTrait;

  /**
   * Test breadcrumbs in the Standard profile.
   *
   * @var string
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_theme';

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_core',
    'localgov_media',
    'localgov_topics',
    'localgov_news',
    'localgov_newsroom',
    'field_ui',
    'pathauto',
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
   * Verifies basic functionality with all modules.
   */
  public function testNewsFields() {
    $this->drupalLogin($this->adminUser);

    // Check news article fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_news_article/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('body');
    $this->assertSession()->pageTextContains('localgov_news_categories');
    $this->assertSession()->pageTextContains('localgov_news_date');
    $this->assertSession()->pageTextContains('localgov_news_image');
    $this->assertSession()->pageTextContains('localgov_news_related');
  }

  /**
   * News article, newsroom, featured news.
   */
  public function testNewsPages() {
    $body = $this->randomMachineName(64);
    $newsArticle = $this->createNode([
      'title' => 'News article 1',
      'body' => $body,
      'type' => 'localgov_news_article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet('news/' . date('Y') . '/news-article-1');
    $this->assertText('News article 1');
    $this->assertText($body);

    $newsroom = $this->createNode([
      'title' => 'News',
      'type' => 'localgov_newsroom',
      'path' => [
        'alias' => '/news',
      ],
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet('news');
    $this->assertText('News article 1');

    // Add News article 1 to the featured news block
    $newsroom->set('localgov_newsroom_featured', $newsArticle->id());
    $newsroom->save();
    drupal_flush_all_caches();
    $this->drupalGet('news');

    // Test the Featured news block displays
    $this->assertSession()->elementExists('css', 'div#block-localgov-featured-news-articles');

    // Test that News article 1 is no longer included in the news listing
    $this->assertSession()->elementNotExists('css', 'div#infinite-scroll--wrapper article');

  }

}

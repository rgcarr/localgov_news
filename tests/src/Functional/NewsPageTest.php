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
  protected $profile = 'standard';

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
    'localgov_news_article',
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
   * Pathauto and breadcrumbs.
   */
  public function testNewsPaths() {
    $this->createNode([
      'title' => 'News article 1',
      'type' => 'localgov_news_article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalGet('news');
    $this->assertText('News article 1');

    $this->drupalGet('news/' . date('Y') . '/news-article-1');
    $this->assertText('News article 1');

    $trail = ['' => 'Home'];
    $trail += ['news' => 'News'];
    $this->assertBreadcrumb(NULL, $trail);
  }

}

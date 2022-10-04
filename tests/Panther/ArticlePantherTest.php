<?php

namespace App\Tests\Panther;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\PantherTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;


class ArticlePantherTest extends PantherTestCase
{
  protected $client;

  protected $databaseTool;

  protected function setUp(): void
  {
    $this->client = self::createPantherClient();

    $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

    // Attend le chemin des fichiers fixtures YAML
    $this->databaseTool->loadAliceFixture([
      dirname(__DIR__) . '/Fixtures/UserFixtures.yaml', // const magique __DIR__ / dirname() = le parent au dessus du fichier
      dirname(__DIR__) . '/Fixtures/TagFixtures.yaml',
      dirname(__DIR__) . '/Fixtures/ArticleFixtures.yaml',
    ]);
  }

  public function testArticleNumberPage()
  {
    $crawler = $this->client->request('GET', '/article/liste'); // Envoie une requete client -- (GET) verbes HTTP 

    $this->assertCount(6, $crawler->filter('.blog-list .blog-card'));
  }

  public function testArticleBtnShowMore()
  {
    $crawler = $this->client->request('GET', '/article/liste');

    $this->client->waitFor('.btn-show-more');

    $this->client->executeScript("document.querySelector('.btn-show-more').click()");

    $this->client->waitForEnabled('.btn-show-more', 5);

    $crawler = $this->client->refreshCrawler();

    $this->assertCount(6 * 2, $crawler->filter('.blog-list .blog-card'));
  }

  public function testLastPageBtnShowMore()
  {
    $this->client->request('GET', '/article/liste');

    $this->client->waitFor('.btn-show-more');

    // Clicks 3 fois
    foreach (range(1, 3) as $i) {
      $this->client->executeScript("document.querySelector('.btn-show-more').click()");

      $this->client->waitForEnabled('.btn-show-more', 3);
    }

    $this->assertSelectorIsNotVisible('.btn-show-more');
  }

  public function testFormSearchFilter()
  {
    $crawler = $this->client->request('GET', '/article/liste');

    $this->client->waitFor('.form-filter', 3);

    $search = $this->client->findElement(WebDriverBy::cssSelector('.form-filter input[type="text"]'));
    $search->sendKeys('Article de test');

    $this->client->waitFor('.content-response', 3);

    // Wait for animation Flipper
    sleep(5);

    $crawler = $this->client->refreshCrawler();

    $this->assertCount(1, $crawler->filter('.blog-list .blog-card'));
  }

  public function testFormSearchFilterCheckBox()
  {
    $crawler = $this->client->request('GET', '/article/liste');

    $this->client->waitFor('.form-filter', 3);

    $this->client->findElement(WebDriverBy::cssSelector('.form-filter input[type="checkbox"]'))->click();

    $this->client->waitFor('.content-response', 3);

    // Wait for animation Flipper
    sleep(3);

    $crawler = $this->client->refreshCrawler();

    $this->assertCount(2, $crawler->filter('.blog-list .blog-card'));
  }

  public function testFormSearchNoResultFilter()
  {
    $this->client->request('GET', '/article/liste');

    $this->client->waitFor('.form-filter', 5);

    $search = $this->client->findElement(WebDriverBy::cssSelector('.form-filter input[type="text"]'));
    $search->sendKeys('Article de test zazazaza');

    $this->client->waitFor('.content-response', 5);

    // Wait for animation Flipper
    sleep(3);

    $this->assertSelectorExists('#article-no-response');
  }

  public function testSortableBtnFilter()
  {
    $this->client->request('GET', '/article/liste');

    $this->client->waitFor('.sortable[title="Titre"]', 5);

    $this->client->findElement(WebDriverBy::cssSelector('.sortable[title="Titre"]'))->click();

    $this->client->waitFor('.content-response', 5);

    sleep(3);

    $this->assertSelectorTextContains('.blog-list .blog-card .blog-card-content .blog-card-header', 'Article 1');
  }
}

<?php

namespace App\Tests\Controller\Frontend;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\DomCrawler\Crawler;

class ArticleControllerTest extends WebTestCase
{
  protected $client; // simulation du client en methode GET

  protected $databaseTool;

  protected function setUp(): void
  {
    $this->client = self::createClient(); // Creer un client

    $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

    $this->databaseTool->loadAliceFixture([
      dirname(__DIR__) . '/../Fixtures/UserFixtures.yaml',
      dirname(__DIR__) . '/../Fixtures/TagFixtures.yaml',
      dirname(__DIR__) . '/../Fixtures/ArticleFixtures.yaml',
    ]);
  }

  public function getPageArticleListe(): Crawler
  {
    return $this->client->request('GET', '/article/liste');
  }

  public function testGetPagesArticleList()
  {
    $this->getPageArticleListe();

    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }

  public function testFormArticlesListPage()
  {
    $this->getPageArticleListe();

    $this->assertSelectorExists('form.form-filter');
  }

  public function testNumberArticlesListPage()
  {
    $crawler = $this->getPageArticleListe();

    $this->assertCount(6, $crawler->filter('.blog-card'));
  }
}

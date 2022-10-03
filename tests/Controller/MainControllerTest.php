<?php

namespace App\Tests\Controller;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase; // test focntionnnel WebTestCase
use Symfony\Component\HttpFoundation\Response;

class MainControllerTest extends WebTestCase // Donne plus d'access que des testUnitaire
{
  protected $client; // simulation du client en methode GET

  protected $databaseTool;

  protected function setUp(): void
  {
    $this->client = self::createClient(); // Creer un client

    $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

    $this->databaseTool->loadAliceFixture([
      dirname(__DIR__) . '/Fixtures/UserFixtures.yaml',
      dirname(__DIR__) . '/Fixtures/TagFixtures.yaml',
      dirname(__DIR__) . '/Fixtures/ArticleFixtures.yaml',
    ]);
  }

  public function testGetHomePage()
  {
    $this->client->request('GET', '/');

    // HTTP_OK = reponse 200
    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    // OU
    // $this->assertResponseIsSuccessful();
  }

  public function testGetH1HomePage()
  {
    $this->client->request('GET', '/');

    $this->assertSelectorTextContains('h1.title', 'Bienvenue sur l\'Application Symfony 6');
  }

  public function testNavbarHomePage()
  {
    $this->client->request('GET', '/');

    $this->assertSelectorExists('header');
  }

  public function testArticlesNumberHomePage()
  {
    $crawler =  $this->client->request('GET', '/');
    // Compte s'il y a 6 articles
    $this->assertCount(6, $crawler->filter('.blog-card'));
  }
}

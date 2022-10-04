<?php

namespace App\Tests\Controller\Backend;

use Exception;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class SecurityControllerTest extends WebTestCase
{
  protected $client;

  protected $databaseTool;

  protected $repoUser;

  protected function setUp(): void
  {
    $this->client = self::createClient();

    $this->repoUser = self::getContainer()->get(UserRepository::class);

    $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();

    $this->databaseTool->loadAliceFixture([
      dirname(__DIR__) . '/../Fixtures/UserFixtures.yaml',
    ]);
  }

  public function testGetLoginPage()
  {
    $this->client->request('GET', '/login');

    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }

  public function testHeading1LoginPage()
  {
    $this->client->request('GET', '/login');

    $this->assertSelectorTextContains('h1', 'Se connecter');
  }

  public function testAdminNotLoggedIn()
  {
    $this->client->request('GET', '/admin');

    $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
  }

  public function testAdminUserNotLoggedIn()
  {
    $this->client->request('GET', '/admin/user');

    $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
  }

  public function testAdminBadUserLoggerIn()
  {
    $user = $this->repoUser->find(3);

    $this->client->loginUser($user);

    $this->client->request('GET', '/admin');

    $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
  }

  public function testAdminUserBadUserLoggerIn()
  {
    $user = $this->repoUser->find(3);

    $this->client->loginUser($user);

    $this->client->request('GET', '/admin');

    $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
  }

  public function testAdminArticleGoodUserLoggerIn()
  {
    $user = $this->repoUser->findOneByEmail('admin@example.com');

    $this->client->loginUser($user);

    $this->client->request('GET', '/admin');

    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }

  public function testAdminUserArticleGoodUserLoggerIn()
  {
    $user = $this->repoUser->findOneByEmail('admin@example.com');

    $this->client->loginUser($user);

    $this->client->request('GET', '/admin/user');

    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }

  public function testGetRegisterPage()
  {
    $this->client->request('GET', '/register');

    $this->assertResponseStatusCodeSame(Response::HTTP_OK);
  }

  public function testGetHeading1RegisterPage()
  {
    $this->client->request('GET', '/register');

    $this->assertSelectorTextContains('h1', 'S\'enregistrer');
  }

  public function testRegisterConnection()
  {
    $crawler = $this->client->request('GET', '/register');

    $form = $crawler->selectButton("S'inscrire")->form([
      'register_form[prenom]' => 'Pierre',
      'register_form[nom]' => 'Bertrand',
      'register_form[username]' => 'testfonctionnel',
      'register_form[age]' => 20,
      'register_form[email]' => 'anthony@example.com',
      'register_form[password][first]' => 'Test1234',
      'register_form[password][second]' => 'Test1234',
      'register_form[address]' => 'XX rue du symfo',
      'register_form[ville]' => 'Valence',
      'register_form[zipCode]' => '26000',
    ]);

    $this->client->submit($form);

    $newUser = $this->repoUser->findOneByEmail('anthony@example.com');

    if (!$newUser) {
      throw new Exception('User not created.');
    }

    $this->assertResponseRedirects();
  }

  public function testRegisterNewUserWithInvalidEmail()
  {
    $crawler = $this->client->request('GET', '/register');

    $form = $crawler->selectButton("S'inscrire")->form([
      'register_form[prenom]' => 'Pierre',
      'register_form[nom]' => 'Bertrand',
      'register_form[username]' => 'testfonctionnel',
      'register_form[age]' => 20,
      'register_form[email]' => 'antho@d',
      'register_form[password][first]' => 'Test1234',
      'register_form[password][second]' => 'Test1234',
      'register_form[address]' => 'XX rue du symfo',
      'register_form[ville]' => 'Valence',
      'register_form[zipCode]' => '26000',
    ]);

    $this->client->submit($form);

    $this->assertSelectorTextContains('div.invalid-feedback', 'Veuillez rentrer un email valide');
  }

  public function testRegisterNewUserWithInvalidZipCode()
  {
    $crawler = $this->client->request('GET', '/register');

    $form = $crawler->selectButton("S'inscrire")->form([
      'register_form[prenom]' => 'Pierre',
      'register_form[nom]' => 'Bertrand',
      'register_form[username]' => 'testfonctionnel',
      'register_form[age]' => 20,
      'register_form[email]' => 'antho@test.com',
      'register_form[password][first]' => 'Test1234',
      'register_form[password][second]' => 'Test1234',
      'register_form[address]' => 'XX rue du symfo',
      'register_form[ville]' => 'Valence',
      'register_form[zipCode]' => 'ezezaezaqsdqs',
    ]);

    $this->client->submit($form);

    $this->assertSelectorTextContains('div.invalid-feedback', 'Veuillez rentrer un code postal valide');
  }

  public function testRegisterNewUserWithInvalidPassword()
  {
    $crawler = $this->client->request('GET', '/register');

    $form = $crawler->selectButton("S'inscrire")->form([
      'register_form[prenom]' => 'Pierre',
      'register_form[nom]' => 'Bertrand',
      'register_form[username]' => 'testfonctionnel',
      'register_form[age]' => 20,
      'register_form[email]' => 'antho@test.com',
      'register_form[password][first]' => 'ljkzas',
      'register_form[password][second]' => 'ljkzas',
      'register_form[address]' => 'XX rue du symfo',
      'register_form[ville]' => 'Valence',
      'register_form[zipCode]' => '26000',
    ]);

    $this->client->submit($form);

    $this->assertSelectorTextContains('div.invalid-feedback', 'Votre mot de passe doit comporter au moins 6 caractères, une lettre majuscule, une lettre miniscule et 1 chiffre sans espace blanc');
  }

  public function testRegisterNewUserWithInvalidRepeatedPassword()
  {
    $crawler = $this->client->request('GET', '/register');

    $form = $crawler->selectButton("S'inscrire")->form([
      'register_form[prenom]' => 'Pierre',
      'register_form[nom]' => 'Bertrand',
      'register_form[username]' => 'testfonctionnel',
      'register_form[age]' => 20,
      'register_form[email]' => 'antho@test.com',
      'register_form[password][first]' => 'Test1234',
      'register_form[password][second]' => 'Ljkzassa13',
      'register_form[address]' => 'XX rue du symfo',
      'register_form[ville]' => 'Valence',
      'register_form[zipCode]' => '26000',
    ]);

    $this->client->submit($form);

    $this->assertSelectorTextContains('div.invalid-feedback', 'Les valeurs ne correspondent pas.');
  }

  public function testRegisterNewUserWithInvalidPasswordNoRepeat()
  {
    $crawler = $this->client->request('GET', '/register');

    $form = $crawler->selectButton("S'inscrire")->form([
      'register_form[prenom]' => 'Pierre',
      'register_form[nom]' => 'Bertrand',
      'register_form[username]' => 'testfonctionnel',
      'register_form[age]' => 20,
      'register_form[email]' => 'antho@test.com',
      'register_form[password][first]' => 'ljkzas',
      'register_form[password][second]' => '',
      'register_form[address]' => 'XX rue du symfo',
      'register_form[ville]' => 'Valence',
      'register_form[zipCode]' => '26000',
    ]);

    $this->client->submit($form);

    $this->assertSelectorTextContains('div.invalid-feedback', 'Les valeurs ne correspondent pas.');
  }
}

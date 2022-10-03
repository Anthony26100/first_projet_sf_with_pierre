<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Utils\AssertTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

class UserEntityTest extends KernelTestCase
{
  use AssertTestTrait;

  protected $databaseTool;

  protected function setUp(): void
  {
    parent::setUp();

    $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
  }

  public function testRepositoryUserCount()
  {
    $users = $this->databaseTool->loadAliceFixture([
      dirname(__DIR__) . '/Fixtures/UserFixtures.yaml',
    ]);

    $users = self::getContainer()->get(UserRepository::class)->count([]);

    $this->assertSame(11, $users); // J'attend 11 résultat Users = 10 + Admin = 1 soit 11 Utilisateurs
  }

  public function getEntity(): User
  {
    return (new User())
      ->setEmail('test@test.com')
      ->setUsername('PierreBertrand')
      ->setNom('Bertrand')
      ->setPrenom('Pierre')
      ->setPassword('Test1234')
      ->setAddress('XX rue de test')
      ->setZipCode('73250')
      ->setVille('Chambéry')
      ->setAge(25);
  }

  public function testValideEntityUser()
  {
    $this->assertHasErrors($this->getEntity());
  }

  public function testNonUniqueEntityUser()
  {
    $user = $this->getEntity()
      ->setEmail('admin@example.com');

    $this->assertHasErrors($user, 1);
  }

  public function testInvalideEmailEntity()
  {
    $user = $this->getEntity()
      ->setEmail('azazaza');

    $this->assertHasErrors($user, 1);
  }

  public function testRegexEmailEntity()
  {
    $user = $this->getEntity()
      ->setEmail('anthony');

    $this->assertHasErrors($user, 1);
  }

  public function testLengthEmailEntity()
  {
    $user = $this->getEntity()
      ->setEmail('anthondnqsjdqshdqshdqHDQiodfhqifhdIFMHIDFHSDIFDSHFDSJHFSDJFBDSJKFDSHGFSUDIKFNHDSKLJFSHDIFJSDBNIFJKLSnvkldsqnvklsqndfklvnhdsklvdsvfdsniomlfhbifDQSDNQSKLFDSNLKFSDHLHGVSFDHGFQSDHGJSQFHGFSQGHQHGH<SBVJDVBDSJVSDFJQEZAEAZZEAEfnhjkfsdklfjdskhsdfdhnjhqSVBSVJBFJBQVJy@gmail.com');

    $this->assertHasErrors($user, 2);
  }

  public function testNonUniqueEntityUsername()
  {
    $user = $this->getEntity()
      ->setUsername('User Admin');

    $this->assertHasErrors($user, 1);
  }
}

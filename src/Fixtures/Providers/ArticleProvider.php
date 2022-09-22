<?php

namespace App\Fixtures\Providers;

use Faker\Factory;
use Faker\Generator;
use DateTimeImmutable;


class ArticleProvider
{

  private Generator $faker;

  public function __construct(Generator $faker)
  {
    $this->faker = Factory::create('fr-FR');
  }

  public function generateContent(): string
  {
    $content = file_get_contents('https://loripsum.net/api/10/long/headers/link/ul/dl');

    return $content;
  }

  public function generateDate(): DateTimeImmutable
  {
    $datetime = DateTimeImmutable::createFromMutable($this->faker->dateTimeThisYear());

    return $datetime;
  }
}

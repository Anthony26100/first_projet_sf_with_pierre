<?php

namespace App\Fixtures\Providers;

class TagProvider
{
  public function randomTag(): string
  {
    $tagList = [
      'Symfony',
      'Php',
      'Vue',
      'NodeJs',
      'Csgo',
      'Valorant',
      'Dev',
      'Api',
      'Dofus',
      'Sql',
      'Data',
      'Webdesign'
    ];

    return $tagList[array_rand($tagList)];
  }
}

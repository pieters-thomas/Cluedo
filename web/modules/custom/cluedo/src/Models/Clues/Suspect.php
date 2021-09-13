<?php

namespace Drupal\cluedo\Models\Clues;

use JetBrains\PhpStorm\Pure;

class Suspect extends CluedoClue
{
  private string $color;

  #[Pure] public function __construct(int $nodeId, string $name, string $color)
  {
    parent::__construct($nodeId, $name);
    $this->color = $color;
  }

  public function getColor(): string
  {
    return $this->color;
  }


}

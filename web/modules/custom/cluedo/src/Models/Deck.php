<?php

namespace Drupal\cluedo\Models;

use Exception;
use JetBrains\PhpStorm\Pure;

class Deck
{
  /**
   * @var Clue[]
   */
  private array $cards = [];

  public function shuffleDeck():void
  {
    shuffle($this->cards);
  }

  public function addCard(Clue $clue): void
  {
    $this->cards[] = $clue;
  }

  public function drawTopCard(): ?Clue
  {
    return array_pop($this->cards);
  }

  /**
   * @throws Exception
   */
  public function drawFirstOfType(string $type): Clue
  {
    foreach ($this->cards as $index=>$card)
    {
      if ($card->getType() === $type)
      {
        $draw = array_splice($this->cards,$index,1);
        return $draw[0];
      }
    }
    throw new Exception("No card of type found in deck");
  }

  /**
   * @return Clue[]
   */
  #[Pure] public function getAllClueOfType(string $type): array
  {
    $clues = [];
    foreach ($this->cards as $card)
    {
      if ($card->getType() === $type)
      {
        $clues[] = $card;
      }
    }
    return $clues;
  }

  /**
   * @return Clue[]
   */
  public function getCards(): array
  {
    return $this->cards;
  }
}

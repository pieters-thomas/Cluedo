<?php

namespace Drupal\cluedo\Models;

use Drupal\cluedo\Models\Clues\CluedoClue;
use Drupal\cluedo\Models\Clues\Room;
use Drupal\cluedo\Models\Clues\Suspect;
use Drupal\cluedo\Models\Clues\Weapon;
use Exception;
use JetBrains\PhpStorm\Pure;

class Deck
{
  /**
   * @var CluedoClue[]
   */
  private array $cards;

  /**
   * @param CluedoClue[] $cards
   */
  public function __construct(array $cards)
  {
    $this->cards = $cards;
  }


  public function shuffleDeck():void
  {
    shuffle($this->cards);
  }

  public function addCard(CluedoClue $clue): void
  {
    $this->cards[] = $clue;
  }

  /**
   * @throws Exception
   */
  public function drawWeapon(): ?Weapon
  {
    foreach ($this->cards as $index=>$card)
    {
      if ($card instanceof Weapon)
      {
        $draw = array_splice($this->cards,$index,1);
        return $draw[0];
      }
    }
    return null;
  }

  /**
   * @throws Exception
   */
  public function drawRoom(): ?Room
  {
    foreach ($this->cards as $index=>$card)
    {
      if ($card instanceof Room)
      {
        $draw = array_splice($this->cards,$index,1);
        return $draw[0];
      }
    }
    return null;
  }

  public function drawSuspect(): ?Suspect
  {
    foreach ($this->cards as $index=>$card)
    {
      if ($card instanceof Suspect)
      {
        $draw = array_splice($this->cards,$index,1);
        return $draw[0];
      }
    }
    return null;
  }

  /**
   * @return Suspect[]
   */
  #[Pure] public function getAllSuspects(): array
  {
   $suspects = [];

    foreach ($this->cards as $card)
    {
      if ($card instanceof Suspect)
      {
        $suspects[] = $card;
      }
    }
    return $suspects;
  }

  /**
   * @return CluedoClue[]
   */
  public function getCards(): array
  {
    return $this->cards;
  }
}

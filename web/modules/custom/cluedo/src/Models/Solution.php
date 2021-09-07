<?php

namespace Drupal\cluedo\Models;

use JetBrains\PhpStorm\Pure;

class Solution
{

  private Clue $room;
  private Clue $weapon;
  private Clue $murderer;

  public function __construct(Clue $room, Clue $weapon, Clue $murderer)
  {
    $this->room = $room;
    $this->weapon = $weapon;
    $this->murderer = $murderer;
  }

  /**
   * @return Clue
   */
  public function getRoom(): Clue
  {
    return $this->room;
  }

  /**
   * @return Clue
   */
  public function getWeapon(): Clue
  {
    return $this->weapon;
  }

  /**
   * @return Clue
   */
  public function getMurderer(): Clue
  {
    return $this->murderer;
  }


  #[Pure] public function equalsSuggested($room, $weapon, $murderer): bool
  {

    if (strtolower($this->room->getName()) !== strtolower($room)) {
      return false;
    }

    if (strtolower($this->weapon->getName()) !== strtolower($weapon)) {
      return false;
    }

    if (strtolower($this->murderer->getName()) !== strtolower($murderer)) {
      return false;
    }

    return true;
  }
}

<?php

namespace Drupal\cluedo\Models;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class ClueSet
{

  private int $roomId;
  private int $weaponId;
  private int $murderId;

  public function __construct(int $roomId, int $weaponId, int $murderId)
  {
    $this->roomId = $roomId;
    $this->weaponId = $weaponId;
    $this->murderId = $murderId;
  }

  public function getRoomId(): int
  {
    return $this->roomId;
  }

  public function getWeaponId(): int
  {
    return $this->weaponId;
  }

  public function getMurderId(): int
  {
    return $this->murderId;
  }

  /**
   * Compares current ClueSet with another, returns array that indicates whether properties are same.
   */
  #[Pure] public function compare(ClueSet $other): array
  {
    $solved = true;
    $response = [
      'room' => ($this->roomId === $other->getRoomId()) ? 'correct' : 'incorrect',
      'weapon' => ($this->weaponId === $other->getWeaponId()) ? 'correct' : 'incorrect',
      'murderer' => ($this->murderId === $other->getMurderId()) ? 'correct' : 'incorrect',
    ];

    foreach ($response as $value) {
      if ($value === 'incorrect') {
        $solved = false;
      }
    }

    if ($solved) {
      return ['You solved it super-sleuth!'];
    }

    return $response;
  }
}

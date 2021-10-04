<?php

namespace Drupal\cluedo\Models;

use Drupal\cluedo\Models\Clues\Room;
use Drupal\cluedo\Models\Clues\Suspect;
use Drupal\cluedo\Models\Clues\Weapon;
use JetBrains\PhpStorm\Pure;

class Solution
{
  private Room $room;
  private Weapon $weapon;
  private Suspect $suspect;

  public function __construct(Room $room, Weapon $weapon, Suspect $suspect)
  {
    $this->room = $room;
    $this->weapon = $weapon;
    $this->suspect = $suspect;
  }


  public function getWeapon(): Weapon
  {
    return $this->weapon;
  }


  public function getRoom(): Room
  {
    return $this->room;
  }


  public function getSuspect(): Suspect
  {
    return $this->suspect;
  }


  #[Pure] public function verifyAccusation($suspectId, $weaponId, $roomId): bool
  {
    return (
      $roomId === $this->room->getNodeId()
      && $weaponId === $this->weapon->getNodeId()
      && $suspectId === $this->suspect->getNodeId()
    );
  }
}

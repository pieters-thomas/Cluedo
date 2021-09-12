<?php

namespace Drupal\cluedo\Models;

use Drupal\cluedo\Models\Clues\Room;
use Drupal\cluedo\Models\Clues\Suspect;
use Drupal\cluedo\Models\Clues\Weapon;
use JetBrains\PhpStorm\Pure;

class Solution
{
  private Weapon $weapon;
  private Room $room;
  private Suspect $suspect;

  public function __construct(Weapon $weapon, Room $room, Suspect $suspect)
  {
    $this->weapon = $weapon;
    $this->room = $room;
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


  #[Pure] public function verifyAccusation($weaponName, $roomName, $suspectName): bool
  {
    return (
      $weaponName === $this->weapon->getName()
      && $roomName === $this->room->getName()
      && $suspectName === $this->suspect->getName()
    );
  }
}

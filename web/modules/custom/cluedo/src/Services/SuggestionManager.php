<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Witness;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class SuggestionManager
{
  /**
   * @param Witness[] $witnesses
   * @param string $roomName
   * @param string $weaponName
   * @param string $murdererName
   * @return array
   */
  #[Pure]
  public function disproveSuggestion(array $witnesses, string $roomName, string $weaponName, string $murdererName): array
  {
    foreach ($witnesses as $witness) {
      foreach ($witness->getClues() as $clue)
      {
        if (in_array(strtolower($clue->getName()), [strtolower($roomName), strtolower($weaponName), strtolower($murdererName)], true)) {


          return [
            'getuige' => $witness->getName(),
            'weerlegging' => $clue->getName(),
          ];
//            return ['is_correct'=>false];

        }
      }
    }
    return [
      'getuige' => '',
      'weerlegging' => '',
      ];
//    return ['is_correct'=>true];
  }
}

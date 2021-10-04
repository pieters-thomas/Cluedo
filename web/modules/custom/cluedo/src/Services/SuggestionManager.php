<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Witness;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class SuggestionManager
{
  /**
   * @param Witness[] $witnesses
   * @param int $suspectId
   * @param int $weaponId
   * @param int $roomId
   * @return array
   */
  #[Pure]
  public function disproveSuggestion(array $witnesses, int $suspectId, int $weaponId , int $roomId): array
  {

    foreach ($witnesses as $witness) {
      foreach ($witness->getClues() as $clue)
      {
        if (in_array($clue->getNodeId(), [$suspectId,$weaponId, $roomId], true))
        {
          return [
            'getuige' => (string) $witness->getProfile()->getNodeId(),
            'weerlegging' => (string) $clue->getNodeId(),
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

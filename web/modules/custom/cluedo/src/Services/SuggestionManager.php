<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Player;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class SuggestionManager
{
  /**
   * @param Player[] $players
   * @param string $roomName
   * @param string $weaponName
   * @param string $murdererName
   * @return array
   */
   #[Pure]
   #[ArrayShape(['player' => "string", 'disproves' => "string", 'type' => "string"])]
   public function disproveSuggestion(array $players, string $roomName, string $weaponName, string $murdererName): array
  {
    foreach ($players as $player) {
      foreach ($player->getClues() as $clue) {
        if (in_array($clue->getName(), [$roomName, $weaponName, $murdererName], true)) {
          return [
            'getuige' => $player->getName(),
            'weerlegging' => $clue->getName(),
            'type' => $clue->getType()
          ];
        }
      }
    }
    return ['getuige' => '', 'weerlegging' => '', 'type' => ''];
  }
}

<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Solution;
use JetBrains\PhpStorm\Pure;

class SuggestionManager
{
  /**
   * @param Solution $solution
   * @param int $suspectId
   * @param int $weaponId
   * @param int $roomId
   * @return array
   */
  #[Pure]
  public function disproveSuggestion(Solution $solution, int $suspectId, int $weaponId , int $roomId): array
  {
    $inCorrect = [];

    if ($suspectId !== $solution->getSuspect()->getNodeId()){$inCorrect[] = $suspectId;}
    if ($weaponId !== $solution->getWeapon()->getNodeId()){$inCorrect[] = $weaponId;}
    if ($roomId !== $solution->getRoom()->getNodeId()){$inCorrect[] = $roomId;}

    shuffle($inCorrect);

    return [
      'num_correct' => (string) 3 - count($inCorrect),
      'incorrect' => (string) $inCorrect[0],
    ];

  }

  public function masterMindResponse(Solution $solution, int $suspectId, int $weaponId , int $roomId)
  {
    $correct=0;

    if($solution->getSuspect()->getNodeId() === $suspectId){++$correct;}
    if($solution->getWeapon()->getNodeId() === $weaponId){++$correct;}
    if($solution->getRoom()->getNodeId() === $roomId){++$correct;}

    return ['correct' => $correct];
  }
}

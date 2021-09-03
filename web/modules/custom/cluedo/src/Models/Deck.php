<?php

namespace Drupal\cluedo\Models;

use Drupal\cluedo\Services\SuggestionManager;
use Exception;

class Deck
{
  private int $firstWeaponId;
  private int $firstRoomId;
  private int $firstSuspectId;
  private array $pool;

  /**
   * @throws Exception
   */
  public function __construct(SuggestionManager $dataBase)
  {
    $deck = $dataBase->fetchCards();

    $this->firstWeaponId = $this->getFirstIdOfType('weapon',$deck);
    $this->firstRoomId = $this->getFirstIdOfType('room',$deck);
    $this->firstSuspectId = $this->getFirstIdOfType('suspect',$deck);
    $this->pool = $this->arrayToPool($deck);

  }

  /**
   * @return int
   */
  public function getFirstWeaponId(): int
  {
    return $this->firstWeaponId;
  }

  /**
   * @return int
   */
  public function getFirstRoomId(): int
  {
    return $this->firstRoomId;
  }

  /**
   * @return int
   */
  public function getFirstSuspectId(): int
  {
    return $this->firstSuspectId;
  }

  /**
   * @return array
   */
  public function getPool(): array
  {
    return $this->pool;
  }



  /**
   * @throws Exception
   */
  private function getFirstIdOfType(string $type, array &$deck): int
  {
    foreach ($deck as $index=>$card)
    {
      if ($card['type'] === $type)
      {
        $cardId = array_splice($deck,$index,1);
        return (int) $cardId[0]['nid'];
      }
    }
    throw new Exception('Card type not found in deck');
  }

  private function arrayToPool(array $deck): array
  {
    $pool = [];
    foreach ($deck as $card)
    {
      $pool[] = $card['nid'];
    }
    return $pool;
  }
}

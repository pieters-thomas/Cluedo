<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\Cluedo;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Exception;

class GameManager
{
  private const KEY_VALID_CHAR = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  private const KEY_LENGTH = 6;

  /**
   * Creates and stores a new game in database while returning the game's key.
   * @throws Exception
   */
  public function createNewGame( Repository $repository): string
  {

    $gameKey = $this->generateUniqueKey($repository);
    $deck = $repository->fetchAllClues();
    $deck->shuffleDeck();

    //Draw one of each type of card from deck for solution and distribute remaining cards among witnesses.

    $room = $deck->drawRoom();
    $weapon = $deck->drawWeapon();
    $suspect = $deck->drawSuspect();

    if (!$weapon || !$room || !$suspect) {
      throw new Exception("Could not create valid game");
    }

    //Create game node

    $gameNode = Node::create([
      'type' => 'game',
      'title' => 'Cluedo-spel',
      'field_game_over' => false,
      'field_game_key' => $gameKey,
      'field_murderer' => $suspect->getNodeId(),
      'field_murder_room' => $room->getNodeId(),
      'field_murder_weapon' => $weapon->getNodeId(),
    ]);

    $gameNode->enforceIsNew();
    $gameNode->save();

    return $gameKey;
  }

  /** Generate a unique key, repeat if already exists */
  private function generateUniqueKey($repository): string
  {
    $newKey = substr(str_shuffle(self::KEY_VALID_CHAR), 0, self::KEY_LENGTH);

    if (!$repository->fetchGame($newKey)) {
      return $newKey;
    }

    return $this->generateUniqueKey($repository);
  }

  /**
   * @throws EntityStorageException
   */
  public function endGame(Cluedo $game): void
  {
    $node = Node::load($game->getNodeId());
    $node?->set('field_game_over', true);
    $node?->save();
  }

}

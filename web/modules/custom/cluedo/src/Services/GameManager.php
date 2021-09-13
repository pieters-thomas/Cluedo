<?php

namespace Drupal\cluedo\Services;

use Drupal;
use Drupal\cluedo\Models\Cluedo;
use Drupal\cluedo\Models\Witness;
use Drupal\cluedo\Models\Solution;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Exception;
use JetBrains\PhpStorm\Pure;

class GameManager
{
  private const KEY_VALID_CHAR = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  private const KEY_LENGTH = 6;

  private const WITNESS_MAX = 6;
  private const WITNESS_MIN = 2;


  /**
   * Creates and stores a new game in database while returning the game's key.
   * @throws Exception
   */
  public function createNewGame(int $witnessAmount, Drupal\cluedo\Models\Deck $deck, Repository $repository): string
  {

    $gameKey = $this->generateUniqueKey($repository);
    $deck->shuffleDeck();

    //Create required number of witnesses:

    $witnesses = [];
    $witnessNodeIds = [];
    $witnessAmount = $this->returnValidWitnessAmount($witnessAmount);
    $witnessProfiles = $deck->getAllSuspects();

    /** @var Drupal\cluedo\Models\Clues\Suspect $witness */
    for ($i = 0; $i < $witnessAmount; $i++) {
      $witnesses[] = new Witness(0, $witnessProfiles[$i]->getName(), []);
    }

//Draw one of each type of card from deck for solution and distribute remaining cards among witnesses.

    $room = $deck->drawRoom();
    $weapon = $deck->drawWeapon();
    $suspect = $deck->drawSuspect();

    if (!$weapon || !$room || !$suspect) {
      return "An error has occurred, could not create game";
    }

    //Distribute remaining cards in deck among witnesses:

    $count = 0;
    $countMax = $witnessAmount;

    foreach ($deck->getCards() as $card) {
      $witnesses[$count]->addClue($card);

      if (++$count === $countMax) {
        $count = 0;
      }
    }

    //Create the witness and game nodes:

    foreach ($witnesses as $index => $witness) {

      $node = Node::create([
        'type' => 'witness',
        'title' => $witness->getName(),
        'field_profile' => $witnessProfiles[$index]->getNodeId(),
        'field_clues' => $witness->getClueIds(),
      ]);

      $node->enforceIsNew();
      $node->save();

      $witnessNodeIds[] = $node->id();
    }

    //Create game node

    $gameNode = Node::create([
      'type' => 'game',
      'title' => 'Cluedo-spel',
      'field_game_over' => false,
      'field_game_key' => $gameKey,
      'field_witnesses' => $witnessNodeIds,
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


  private function returnValidWitnessAmount(int $witnessAmount): int
  {
    if ($witnessAmount < self::WITNESS_MIN) {
      $witnessAmount = self::WITNESS_MIN;
    }
    if ($witnessAmount > self::WITNESS_MAX) {
      $witnessAmount = self::WITNESS_MAX;
    }
    return $witnessAmount;
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

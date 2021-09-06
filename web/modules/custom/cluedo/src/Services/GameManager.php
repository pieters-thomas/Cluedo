<?php

namespace Drupal\cluedo\Services;

use Drupal;
use Drupal\cluedo\Models\Player;
use Drupal\cluedo\Models\Solution;
use Drupal\node\Entity\Node;
use Exception;

class GameManager
{
  /**
   * Creates and stores a new game in database while returning the game's key.
   * @throws Exception
   */
  public function createNewGame(Repository $repo, int $playerAmount): string
  {
    try {

      $gameKey = $this->generateUniqueKey();

      $deck = $repo->fetchAllClues();
      $deck->shuffleDeck();

      // Generate witnesses for the game
      $playerProfiles = $deck->getAllClueOfType('suspect');

      $players = [];
      $playerNodeIds = [];

      $count = 0;
      $countMax = $playerAmount;

      for ($i = 0; $i < $playerAmount; $i++) {
        $players[] = new Player(0, $playerProfiles[$i]->getName(), []);
      }

      //Remove 3 solution cards from deck and distribute cards among witnesses
      $solution = new Solution(
        $deck->drawFirstOfType('room'),
        $deck->drawFirstOfType('weapon'),
        $deck->drawFirstOfType('suspect')
      );

      foreach ($deck->getCards() as $card) {

        $players[$count]->addClue($card);

        if (++$count === $countMax) {
          $count = 0;
        }
      }

      //Create player nodes and keep track of the ids
      foreach ($players as $index => $player)
      {

        $node = Node::create([
          'type' => 'player',
          'title' => 'Player ' . $index + 1,
          'field_player_is_main' => false,
          'field_player_profile' => $playerProfiles[$index]->getNodeId(),
          'field_player_clues' => $player->getClueIds(),
        ]);

        $node->enforceIsNew();
        $node->save();

        $player->setNodeId($node->id());
        $playerNodeIds[] = $node->id();
      }

      //Create game node
      $gameNode = Node::create([
        'type' => 'game',
        'title' => 'game',
        'field_game_key' => $gameKey,
        'field_game_players' => $playerNodeIds,
        'field_game_room' => $solution->getRoom()->getNodeId(),
        'field_game_weapon' => $solution->getWeapon()->getNodeId(),
        'field_game_murderer' => $solution->getMurderer()->getNodeId(),

      ]);

      $gameNode->enforceIsNew();
      $gameNode->save();

      return $gameKey;
    }catch (Exception){

      return new Exception("Unable to create new Game");
    }
  }


  private function generateUniqueKey(): string
  {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newKey = substr(str_shuffle($chars), 0, 6);

    $query = Drupal::database()->select('node__field_game_key', 'keys')
      ->fields('keys', ['field_game_key_value'])
      ->condition('field_game_key_value', $newKey, 'LIKE');
    $keys = $query->execute()->fetch();

    if ($keys === false) {
      return $newKey;
    }
    return $this->generateUniqueKey();
  }

}

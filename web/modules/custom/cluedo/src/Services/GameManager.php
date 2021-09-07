<?php

namespace Drupal\cluedo\Services;

use Drupal;
use Drupal\cluedo\Models\Player;
use Drupal\cluedo\Models\Solution;
use Drupal\node\Entity\Node;
use Exception;

class GameManager
{
   private const CLUE_TYPES = [
     'kamer'=>'kamer',
     'wapen'=>'wapen',
     'karakter'=>'karakter',
   ];

   private const GETUIGEN_MAX = 6;
   private const GETUIGEN_MIN = 2;
   private const SLEUTEL_LENGTE = 6;

  /**
   * Creates and stores a new game in database while returning the game's key.
   * @throws Exception
   */
  public function createNewGame(Repository $repo, int $getuigenAantal): string
  {
    //Getuigen aantal naar valide aantal;

    if ($getuigenAantal < self::GETUIGEN_MIN)
    {
      $getuigenAantal = self::GETUIGEN_MIN;
    }
    if ($getuigenAantal > self::GETUIGEN_MAX)
    {
      $getuigenAantal = self::GETUIGEN_MAX;
    }


    try {

      $gameKey = $this->generateUniqueKey();

      $deck = $repo->fetchAllClues();
      $deck->shuffleDeck();

      // Generate witnesses for the game
      $playerProfiles = $deck->getAllClueOfType(self::CLUE_TYPES['karakter']);

      $players = [];
      $playerNodeIds = [];

      $count = 0;
      $countMax = $getuigenAantal;

      for ($i = 0; $i < $getuigenAantal; $i++) {
        $players[] = new Player(0, $playerProfiles[$i]->getName(), []);
      }

      //Remove 3 solution cards from deck and distribute cards among witnesses
      $solution = new Solution(
        $deck->drawFirstOfType(self::CLUE_TYPES['kamer']),
        $deck->drawFirstOfType(self::CLUE_TYPES['wapen']),
        $deck->drawFirstOfType(self::CLUE_TYPES['karakter'])
      );

      foreach ($deck->getCards() as $card) {

        $players[$count]->addClue($card);

        if (++$count === $countMax) {
          $count = 0;
        }
      }

//      Create player nodes and keep track of the ids
      foreach ($players as $index => $player)
      {

        $node = Node::create([
          'type' => 'getuige',
          'title' => 'Getuige ' . $index + 1,
          'field_getuige_profiel' =>$playerProfiles[$index]->getNodeId(),
          'field_getuige_clues' =>$player->getClueIds(),

        ]);

        $node->enforceIsNew();
        $node->save();

        $player->setNodeId($node->id());
        $playerNodeIds[] = $node->id();
      }

      //Create game node
      $gameNode = Node::create([
        'type' => 'spel',
        'title' => 'spel',
        'field_speler' => Drupal::currentUser()->id(),
        'field_spel_sleutel' => $gameKey,
        'field_spel_getuigen' => $playerNodeIds,
        'field_spel_kamer' => $solution->getRoom()->getNodeId(),
        'field_spel_wapen' => $solution->getWeapon()->getNodeId(),
        'field_spel_karakter' => $solution->getMurderer()->getNodeId(),

      ]);

      $gameNode->enforceIsNew();
      $gameNode->save();

      return $gameKey;
    }catch (Exception $e){

      return $e->getMessage();
    }
  }


  private function generateUniqueKey(): string
  {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $newKey = substr(str_shuffle($chars), 0, self::SLEUTEL_LENGTE);

    $query = Drupal::database()->select('node__field_spel_sleutel', 'sleutels')
      ->fields('sleutels', ['field_spel_sleutel_value'])
      ->condition('field_spel_sleutel_value', $newKey, 'LIKE');
    $keys = $query->execute()->fetch();

    if ($keys === false) {
      return $newKey;
    }
    return $this->generateUniqueKey();
  }

}

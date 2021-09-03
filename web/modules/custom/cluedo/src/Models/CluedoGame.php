<?php

namespace Drupal\cluedo\Models;

use Drupal;
use Drupal\cluedo\Services\SuggestionManager;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\node\Entity\Node;
use Exception;
use PDO;

class CluedoGame
{
  private string $gameKey;
  private int $roomId;
  private int $weaponId;
  private int $murdererId;
  private array $playerNodeIds;

  /**
   * @throws Exception
   */
  public function __construct(SuggestionManager $manager)
  {
    $this->gameKey = $this->generateGameKey();
    $deck = new Deck($manager);

    $this->roomId = $deck->getFirstRoomId();
    $this->weaponId = $deck->getFirstWeaponId();
    $this->murdererId = $deck->getFirstSuspectId();
    $this->playerNodeIds = $this->generatePlayers(3, $deck->getPool());
  }

  /**
   * @throws EntityStorageException
   */
  public function storeNewGame():void
  {
    $node = Node::create([
      'type'=>'game',
      'title'=>'game '.$this->gameKey,
      'field_game_key'=>$this->gameKey,
      'field_game_murderer'=>$this->murdererId,
      'field_game_room'=>$this->roomId,
      'field_game_weapon'=>$this->weaponId,
      'field_game_players'=>$this->playerNodeIds,
    ]);

    $node->enforceIsNew();
    $node->save();
  }

  /**
   * @return string
   */
  public function getGameKey(): string
  {
    return $this->gameKey;
  }

  /**
   * Generates a key of length = 6.
   * @throws Exception
   */
  private function generateGameKey(): string
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
    return $this->generateGameKey();
  }

  /**
   * @throws EntityStorageException
   */
  private function generatePlayers($amount, $cardPool): array
  {
    //todo get profiles from database instead.
    $profiles = [16, 17, 18, 19, 20, 21];
    shuffle($profiles);

    $cardPerPlayer = [];
    $playerNodeIds = [];

    $i = 0;
    while ($cardPool !== []) {
      $cardPerPlayer[$i][] = array_shift($cardPool);

      $i++;
      if ($i === $amount) {
        $i = 0;
      }
    }

    for ($q = 0; $q < $amount; $q++)
    {
      $node = Node::create([
        'type' => 'player',
        'title' => 'player '.$q+1,
        'field_player_is_main' => false,
        'field_player_profile' => $profiles[$q],
        'field_player_clues' => $cardPerPlayer[$q],
      ]);

      $node->enforceIsNew();
      $node->save();

      $playerNodeIds[] = $node->id();
    }

    return $playerNodeIds;
  }
}

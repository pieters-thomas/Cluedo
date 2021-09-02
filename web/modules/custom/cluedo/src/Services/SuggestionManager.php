<?php

namespace Drupal\cluedo\Services;

use Drupal\cluedo\Models\ClueSet;
use Drupal\Core\Database\Connection;
use Exception;
use PDO;

class SuggestionManager
{
  private Connection $connection;

  public function __construct(Connection $connection)
  {
    $this->connection = $connection;
  }

  /**
   * @throws Exception
   */
  public function processSuggestion(string $gameKey, string $room, string $weapon, string $murderer): array
  {
    $suggestion = new ClueSet($this->getNodeId($room), $this->getNodeId($weapon), $this->getNodeId($murderer));
    $solution = $this->getSolution($gameKey);

    return $solution->compare($suggestion);
  }

  /**
   * @throws Exception
   */
  private function getNodeId(string $target): int
  {
    try {
      $query = $this->connection->select('node_field_revision', 'nfr');
      $query->fields('nfr', ['nid']);
      $query->condition('title', $target, 'LIKE');
      return (int)$query->execute()->fetch(PDO::FETCH_ASSOC)['nid'];
    } catch (Exception) {
      throw new Exception("Unable to retrieve nodeId");
    }
  }

  /**
   * @throws Exception
   */
  private function getSolution($key): ClueSet
  {
    try {
      $query = $this->connection->select('node__field_game_key', 'key');
      $query->leftJoin('node__field_game_murderer', 'm', 'key.entity_id = m.entity_id');
      $query->leftJoin('node__field_game_room', 'r', 'key.entity_id = r.entity_id');
      $query->leftJoin('node__field_game_weapon', 'w', 'key.entity_id = w.entity_id');
      $query->addField('r', 'field_game_room_target_id', 'room');
      $query->addField('w', 'field_game_weapon_target_id', 'weapon');
      $query->addField('m', 'field_game_murderer_target_id', 'murderer');
      $query->condition('field_game_key_value', $key, 'LIKE');
      $solution = $query->execute()->fetch(PDO::FETCH_ASSOC);

      return new ClueSet($solution['room'], $solution['weapon'], $solution['murderer']);

    } catch (Exception) {
      throw new Exception('Could not fetch solution');
    }

  }
}
